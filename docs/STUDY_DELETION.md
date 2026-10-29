# Study deletion and restoration

Owners can delete their studies from Your Studies or their collection's study
list after confirming the action. Contributors and other users cannot use the
owner deletion endpoint. Deletion is allowed for in-progress, pending, approved,
and rejected studies.

Deletion sets `studies.deleted_at` using Laravel's existing `SoftDeletes` support.
The study's status, metadata, related records, and uploaded files are retained.
Normal Eloquent queries and route binding exclude deleted studies.

To restore a study, open Tinker in the application container:

```sh
php artisan tinker
```

Then replace `123` with the study's database ID:

```php
$study = App\Models\Study::onlyTrashed()->findOrFail(123);
App\Models\Study::withoutLocking(fn () => $study->restore());
```

To view all deleted studies, use the `onlyTrashed()` query scope:

```php
App\Models\Study::onlyTrashed()->get();
```

The lock bypass is necessary for pending and approved studies. Restoration
retains the original status and visibility rules, so an approved public study
becomes public again.
