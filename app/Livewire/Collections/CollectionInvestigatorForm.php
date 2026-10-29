<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use App\Models\Country;
use App\Models\Investigator;
use App\Models\User;
use App\Services\InvestigatorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CollectionInvestigatorForm extends Component
{
    private InvestigatorService $investigatorService;

    public Collection $collection;

    public $investigators;

    public $states;

    public $countries;

    public $editingInvestigatorId = null;

    public $editInvestigator = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'department' => '',
        'organization' => '',
        'country' => '',
        'state' => '',
        'corresponding' => false,
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

    private function authorizeInvestigatorEditing(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user !== null && $this->collection->canUserEditMetadata($user), 403);
    }

    public function mount(Collection $collection)
    {
        $this->collection = $collection;
        $this->authorizeInvestigatorEditing();
        $this->investigators = $collection->investigators;
        $this->states = $this->investigatorService->getStates();
        $this->countries = Country::all();
    }

    public function addInvestigator()
    {
        $this->authorizeInvestigatorEditing();

        $this->validate();
        $this->ensureRoleSelection($this->newInvestigator, 'newInvestigator');

        $this->newInvestigator['country_id'] = Country::firstWhere('code', $this->newInvestigator['country'])->id;
        $this->collection->investigators()->create($this->newInvestigator);

        $this->newInvestigator = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'department' => '',
            'organization' => '',
            'country' => '',
            'state' => '',
            'corresponding' => false,
            'is_contributing' => false,
        ];

        $this->investigators = $this->collection->fresh()->investigators;
        session()->flash('success', 'Investigator added successfully.');
    }

    public function removeInvestigator($id)
    {
        $this->authorizeInvestigatorEditing();

        $investigator = $this->collection->investigators()->find($id);

        if ($investigator instanceof Investigator) {
            $investigator->delete();
        }

        $this->investigators = $this->collection->fresh()->investigators;
        session()->flash('lower-success', 'Investigator removed successfully.');
    }

    public function alterInvestigator($id)
    {
        $this->authorizeInvestigatorEditing();

        $investigator = $this->collection->investigators()->find($id);

        if ($investigator instanceof Investigator) {
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
                'is_contributing' => $investigator->is_contributing,
            ];
        }
    }

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
        $this->authorizeInvestigatorEditing();

        $this->validate([
            'editInvestigator.first_name' => 'required',
            'editInvestigator.last_name' => 'required',
            'editInvestigator.email' => 'required|email',
            'editInvestigator.department' => 'required',
            'editInvestigator.organization' => 'required',
            'editInvestigator.country' => 'required',
            'editInvestigator.state' => 'required_if:editInvestigator.country,US',
            'editInvestigator.corresponding' => 'boolean',
            'editInvestigator.is_contributing' => 'boolean',
        ]);

        $this->ensureRoleSelection($this->editInvestigator, 'editInvestigator');

        $investigator = $this->collection->investigators()->find($this->editingInvestigatorId);

        if ($investigator instanceof Investigator) {
            $investigator['country_id'] = Country::firstWhere('code', $this->editInvestigator['country'])->id;
            $investigator->update($this->editInvestigator);
            $this->cancelEdit();
            $this->investigators = $this->collection->fresh()->investigators;

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
            'is_contributing' => false,
        ];
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
            $this->newInvestigator['corresponding'] = false;
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
            $this->editInvestigator['corresponding'] = false;
        }
    }

    public function render()
    {
        return view('livewire.collections.collection-investigator-form');
    }

    private function ensureRoleSelection(array $investigator, string $propName): void
    {
        $message = Investigator::roleSelectionError(
            false,
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
