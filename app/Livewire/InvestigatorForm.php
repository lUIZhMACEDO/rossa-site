<?php

namespace App\Livewire;

use App\Exceptions\StudyLockedException;
use App\Models\Country;
use App\Models\Investigator;
use App\Models\Study;
use App\Services\InvestigatorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class InvestigatorForm extends Component
{
    private InvestigatorService $investigatorService;

    public $study;

    public $investigators;

    public $states;

    public $countries;

    public $editingInvestigatorId = null;

    public $user;

    public $editInvestigator = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'department' => '',
        'organization' => '',
        'country' => '',
        'state' => '',
        'corresponding' => false,
        'is_submitting' => false,
        'is_contributing' => false,
    ];

    public $newInvestigator = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'department' => '',
        'organization' => '',
        'country' => '',
        'state' => '',
        'corresponding' => false,
        'is_submitting' => false,
        'is_contributing' => false,
    ];

    protected $rules = [
        'newInvestigator.first_name' => 'required',
        'newInvestigator.last_name' => 'required',
        'newInvestigator.email' => 'required|email',
        'newInvestigator.department' => 'required',
        'newInvestigator.organization' => 'required',
        'newInvestigator.country' => 'required|exists:countries,code',
        'newInvestigator.state' => 'required_if:newInvestigator.country,US',
        'newInvestigator.corresponding' => 'boolean',
        'newInvestigator.is_submitting' => 'boolean',
        'newInvestigator.is_contributing' => 'boolean',
    ];

    protected $validationAttributes = [
        'newInvestigator.first_name' => 'first name',
        'newInvestigator.last_name' => 'last name',
        'newInvestigator.email' => 'email',
        'newInvestigator.department' => 'primary department',
        'newInvestigator.organization' => 'primary organization',
        'newInvestigator.country' => 'country',
        'newInvestigator.state' => 'state',
        'editInvestigator.first_name' => 'first name',
        'editInvestigator.last_name' => 'last name',
        'editInvestigator.email' => 'email',
        'editInvestigator.department' => 'primary department',
        'editInvestigator.organization' => 'primary organization',
        'editInvestigator.country' => 'country',
        'editInvestigator.state' => 'state',
    ];

    public function __construct()
    {
        $this->investigatorService = new InvestigatorService;
    }

    public function mount(Study $study)
    {
        $this->study = $study->loadMissing('collection.investigators');
        $this->investigators = $study->collection_id !== null
            ? ($study->collection?->investigators ?? collect())
            : $study->getRelationValue('investigators');
        $this->states = $this->investigatorService->getStates();
        $this->countries = Country::all();
        $this->user = Auth::user();
    }

    public function addInvestigator()
    {
        $this->ensureNotCollectionOwnedStudy();
        $this->ensureStudyUnlocked();

        $this->validate();
        $this->ensureRoleSelection($this->newInvestigator, 'newInvestigator');
        $this->newInvestigator['country_id'] = Country::firstWhere('code', $this->newInvestigator['country'])->id;
        $this->study->investigators()->create($this->newInvestigator);

        $this->newInvestigator = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'department' => '',
            'organization' => '',
            'country' => '',
            'state' => '',
            'corresponding' => false,
            'is_submitting' => false,
            'is_contributing' => false,
        ];

        $this->investigators = $this->study->fresh()->investigators;
        session()->flash('success', 'Investigator added successfully.');
    }

    public function addMyself()
    {
        $this->ensureNotCollectionOwnedStudy();
        $this->ensureStudyUnlocked();

        $user = $this->user;

        if ($user->email && $this->investigators->contains(fn ($i) => strtolower((string) $i->email) === strtolower($user->email))) {
            $this->addError('newInvestigator.email', 'You are already listed as an investigator on this study.');

            return;
        }

        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2, PREG_SPLIT_NO_EMPTY);

        $this->newInvestigator['first_name'] = $user->first_name ?: ($nameParts[0] ?? '');
        $this->newInvestigator['last_name'] = $user->last_name ?: ($nameParts[1] ?? '');
        $this->newInvestigator['email'] = $user->email ?? '';
        $this->newInvestigator['department'] = $user->department ?? '';
        $this->newInvestigator['organization'] = $user->organization ?? '';
        $this->newInvestigator['country'] = $user->country?->code ?? '';
        $this->newInvestigator['state'] = $user->state ?? '';
        $this->newInvestigator['is_contributing'] = false;

        // The first investigator added is usually the corresponding one.
        if (! $this->investigators->contains(fn ($i) => $i->corresponding)) {
            $this->newInvestigator['corresponding'] = true;
        }
    }

    public function updatedNewInvestigatorCorresponding($value)
    {
        if ($value) {
            $this->newInvestigator['is_contributing'] = false;
        }
    }

    public function updatedNewInvestigatorIsContributing($value)
    {
        if ($value) {
            $this->newInvestigator['is_submitting'] = false;
            $this->newInvestigator['corresponding'] = false;
        }
    }

    public function updatedEditInvestigatorIsSubmitting($value)
    {
        if ($value) {
            $this->editInvestigator['is_contributing'] = false;
        }
    }

    public function updatedEditInvestigatorCorresponding($value)
    {
        if ($value) {
            $this->editInvestigator['is_contributing'] = false;
        }
    }

    public function updatedEditInvestigatorIsContributing($value)
    {
        if ($value) {
            $this->editInvestigator['is_submitting'] = false;
            $this->editInvestigator['corresponding'] = false;
        }
    }

    public function removeInvestigator($id)
    {
        $this->ensureNotCollectionOwnedStudy();
        $this->ensureStudyUnlocked();

        $investigator = $this->study->investigators()->find($id);

        if ($investigator) {
            $investigator->delete();
        }

        $this->investigators = $this->study->fresh()->investigators;
        session()->flash('lower-success', 'Investigator removed successfully.');
    }

    public function alterInvestigator($id)
    {
        $this->ensureNotCollectionOwnedStudy();

        $investigator = $this->study->investigators()->find($id);

        if ($investigator) {
            $this->editingInvestigatorId = $id;

            $this->editInvestigator = [
                'first_name' => $investigator->first_name,
                'last_name' => $investigator->last_name,
                'email' => $investigator->email,
                'department' => $investigator->department,
                'organization' => $investigator->organization,
                'country' => $investigator->country->code,
                'state' => $investigator->state,
                'corresponding' => $investigator->corresponding,
                'is_submitting' => $investigator->is_submitting,
                'is_contributing' => $investigator->is_contributing,
            ];
        }
    }

    // must clear state if we are changing country from U.S. to non-U.S.
    public function updatedEditInvestigatorCountry($value)
    {
        if ($value !== 'US') {
            $this->editInvestigator['state'] = '';
        }
    }

    public function updatedNewInvestigatorCountry($value)
    {
        if ($value !== 'US') {
            $this->newInvestigator['state'] = '';
        }
    }

    public function updateInvestigator()
    {
        $this->ensureNotCollectionOwnedStudy();
        $this->ensureStudyUnlocked();

        $this->validate([
            'editInvestigator.first_name' => 'required',
            'editInvestigator.last_name' => 'required',
            'editInvestigator.email' => 'required|email',
            'editInvestigator.department' => 'required',
            'editInvestigator.organization' => 'required',
            'editInvestigator.country' => 'required',
            'editInvestigator.state' => 'required_if:editInvestigator.country,US',
            'editInvestigator.corresponding' => 'boolean',
            'editInvestigator.is_submitting' => 'boolean',
            'editInvestigator.is_contributing' => 'boolean',
        ]);

        $this->ensureRoleSelection($this->editInvestigator, 'editInvestigator');

        $investigator = $this->study->investigators()->find($this->editingInvestigatorId);

        if ($investigator) {
            $investigator['country_id'] = Country::firstWhere('code', $this->editInvestigator['country'])->id;
            $investigator->update($this->editInvestigator);
            $this->cancelEdit();
            $this->investigators = $this->study->fresh()->investigators;

            session()->flash('lower-success', 'Investigator updated successfully.');
        }
    }

    public function cancelEdit()
    {
        $this->editingInvestigatorId = null;

        $this->editInvestigator = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'department' => '',
            'organization' => '',
            'country' => '',
            'state' => '',
            'corresponding' => false,
            'is_submitting' => false,
            'is_contributing' => false,
        ];
    }

    public function render()
    {
        return view('livewire.investigator-form');
    }

    private function ensureNotCollectionOwnedStudy(): void
    {
        if ($this->study->collection_id !== null) {
            abort(403, 'Investigators for collection studies are managed on the collection.');
        }
    }

    private function ensureStudyUnlocked(): void
    {
        if ($this->study->isLockedForEditing(auth()->user())) {
            throw new StudyLockedException;
        }
    }

    private function ensureRoleSelection(array $investigator, string $propName): void
    {
        $message = Investigator::roleSelectionError(
            (bool) ($investigator['is_submitting'] ?? false),
            (bool) ($investigator['corresponding'] ?? false),
            (bool) ($investigator['is_contributing'] ?? false),
        );

        if ($message === null) {
            return;
        }

        throw ValidationException::withMessages([
            $propName.'.is_contributing' => $message,
        ]);
    }
}
