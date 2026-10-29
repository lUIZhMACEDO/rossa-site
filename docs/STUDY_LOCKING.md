# Study Locking

When a study is submitted for admin approval its status is set to `PENDING` (and later `APPROVED`). During this time the study is **locked**, no further edits are permitted. Locking is enforced by a combination of frontend and backend mechanisms.

---

## Backend Locks

> The backend locks are a safety net against malicious or programmatic editing. For normal users the frontend locks are sufficient.

### `LocksWithStudy` Trait

`app/Traits/LocksWithStudy.php` is a Laravel model trait that hooks into Eloquent's `saving` and `deleting` events. Any model that uses this trait will automatically throw a `StudyLockedException` if a save or delete is attempted while the parent study is locked.

**Adding the trait to a model**

Use the trait on any Eloquent model whose records belong to a study and should be protected:

```php
use App\Traits\LocksWithStudy;

class SampleGroup extends Model
{
    use LocksWithStudy;
    // ...
}
```

Models that already use this trait: `AnimalModel`, `AnimalLine`, `AnalysisUpload`, `ComparisonGroup`, `DrugAdministrationEvent`, `GonadectomyProcedure`, `Measurement`, `MediaUpload`, `SampleGroup`.

**How the trait finds the study: `getStudyForLock()`**

The trait's default `getStudyForLock()` implementation assumes the model has a `study()` Eloquent relationship:

```php
protected function getStudyForLock(): ?Study
{
    if (!method_exists($this, 'study')) {
        return null;
    }

    $this->loadMissing('study');
    $study = $this->getRelation('study');

    return $study instanceof Study ? $study : null;
}
```

If the model **does not** have a direct `study` relationship (e.g. it belongs to an intermediate model), override `getStudyForLock()` in that model. For example, `DrugAdministrationEvent` belongs to a `DrugAdministrationPlan` which in turn belongs to a `Study`, so it overrides the method:

```php
// app/Models/DrugAdministrationEvent.php
protected function getStudyForLock(): ?Study
{
    $this->loadMissing('plan.study');

    $study = $this->plan?->study;

    return $study instanceof Study ? $study : null;
}
```

The `Study` model itself also overrides `getStudyForLock()` to return `$this`, allowing the trait to work when applied directly to a `Study`:

```php
// app/Models/Study.php
protected function getStudyForLock(): ?self
{
    return $this;
}
```

**Bypassing the lock (admin / system operations)**

When a privileged operation must write to a locked study, wrap it in `withoutLocking()`:

```php
AnimalModel::withoutLocking(function () use ($study) {
    $study->animalModels()->create([...]);
});
```

---

### Custom `ensureStudyUnlocked()` Guards (when the trait is not enough)

The `LocksWithStudy` trait only intercepts Eloquent `saving` and `deleting` events. Some Livewire actions manipulate in-memory state (e.g. building up an array before any DB write) or use query builder methods like `->pluck()` that never fire model events. In those cases the trait will not catch the action.

The pattern is to add a private `ensureStudyUnlocked()` helper to the Livewire component and call it at the top of any method that should be blocked:

```php
private function ensureStudyUnlocked(): void
{
    if ($this->study->isLockedForEditing()) {
        throw new StudyLockedException();
    }
}
```

`StudyLockedException` extends `RuntimeException` and is handled globally to display a user-friendly "study is locked" message.

Example: `DrugAdministrationPlanForm` calls `ensureStudyUnlocked()` in `addEvent()` and `removeEvent()` even though those methods only modify an in-memory array and do not write to the database, because modifying a locked study's data (even ephemerally in the UI) should be prevented:

```php
public function addEvent()
{
    $this->ensureStudyUnlocked(); // doesn't write to db, but should still be blocked
    // ...
}

public function removeEvent($index)
{
    $this->ensureStudyUnlocked(); // doesn't write to db, but should still be blocked
    // ...
}

public function save()
{
    $this->ensureStudyUnlocked();
    // ... DB writes follow
}
```

---

## Frontend Locks

### `data-input-locked` and `data-input-unlock` Attributes

The frontend locking mechanism is driven by two HTML attributes and `resources/js/inputlocks.js`.

**Locking a section**

Wrap any section of the form in a container and conditionally add `data-input-locked` when the study is locked:

```blade
<div @if($study->isLockedForEditing()) data-input-locked @endif>
    {{-- all inputs inside here will be disabled --}}
</div>
```

`isLockedForEditing()` returns `true` when the study's status is `PENDING` or `APPROVED`.

All `input`, `select`, `textarea`, and `button` elements inside a `[data-input-locked]` container are automatically disabled by `inputlocks.js`.

**Unlocking a specific element inside a locked container**

If a particular element inside a locked container should remain interactive (e.g. an "Edit" button that opens a modal), add `data-input-unlock` to that element or any of its ancestors:

```blade
{{-- outer container is locked --}}
<div @if($study->isLockedForEditing()) data-input-locked @endif>

    {{-- this button remains clickable --}}
    <button wire:click="editPlan({{ $plan->id }})" data-input-unlock>
        Edit
    </button>

    {{-- the tab navigation bar stays interactive --}}
    <ul class="nav nav-tabs" data-input-unlock>
        ...
    </ul>
</div>
```

**How `inputlocks.js` works**

```js
function applyInputLocks() {
	document.querySelectorAll("[data-input-locked]").forEach((container) => {
		container
			.querySelectorAll("input, select, textarea, button")
			.forEach((control) => {
				const hasUnlockOverride =
					control.hasAttribute("data-input-unlock") ||
					Boolean(control.closest("[data-input-unlock]"));

				if (hasUnlockOverride) {
					// restore if previously disabled by this mechanism
					if (control.dataset.lockDisabled === "true") {
						control.disabled = false;
						delete control.dataset.lockDisabled;
					}
					return;
				}

				if (!control.disabled) {
					control.dataset.lockDisabled = "true";
				}
				control.disabled = true;
			});
	});
}
```

A `MutationObserver` watches `document.body` for DOM changes and re-runs `applyInputLocks()` automatically, so locks are correctly applied after Livewire re-renders.

---

### Considerations When Adding New Inputs

The locking mechanism targets: **`input`, `select`, `textarea`, `button`**.

- Always use one of these elements for interactive controls in the study form. In particular, **do not use `<a>` tags as buttons** (e.g. for a "Save & Continue" action): `<a>` tags are not matched by the selector and will remain clickable even when the study is locked.
- Place a `data-input-locked` container as high up the DOM as practical to avoid having to annotate every individual control.
- If a control inside a locked region must stay active, use `data-input-unlock` on the control itself or a wrapping ancestor, not on the locked container.
