<?php

namespace App\Livewire;

use App\Enums\CoreFacilityStatus;
use App\Models\CoreFacility;
use App\Services\CoreFacilityService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CoreFacilityTable extends Component
{
    use WithPagination;

    public $myFacilities = false;

    public $sort = 'facility_name';

    public $direction = 'asc';

    public $selectedStatuses = [];

    public $search = '';

    public function mount($myFacilities = false)
    {
        $this->myFacilities = $myFacilities;

        // get status from query string and filter out invalid values
        $statuses = request()->query('statuses', [CoreFacilityStatus::PENDING->value, CoreFacilityStatus::ACCEPTED->value]);
        $this->selectedStatuses = array_intersect($statuses, array_column(CoreFacilityStatus::cases(), 'value'));

        // get search and sort
        $this->sort = request()->query('sort', $this->myFacilities ? 'last_refreshed_at' : 'facility_name');
        $this->direction = request()->query('direction', $this->myFacilities ? 'desc' : 'asc');
        $this->search = request()->query('search', '');
    }

    private $allowedPublicColumns = [
        // institution combined with facility name
        // contact email combined with contact name
        'facility_name' => ['name' => 'Facility', 'sortable' => true],
        'web_address' => ['name' => 'Web Address', 'sortable' => true],
        'contact_name' => ['name' => 'Contact', 'sortable' => true],
        'analysis_types' => ['name' => 'Analysis Types', 'sortable' => false],
    ];

    protected $queryString = [
        'sort' => ['except' => 'facility'],
        'direction' => ['except' => 'asc'],
        'selectedStatuses' => ['as' => 'statuses'],
    ];

    public function sortBy($field)
    {
        // auto reset page with href
        if ($this->sort === $field && $this->direction === 'asc') {
            $this->direction = 'desc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
        $this->resetPage();
    }

    public function updatedSelectedStatuses()
    {
        $this->resetPage();
    }

    private function getColumns()
    {
        // columns to display in table
        // public columns
        $columns = $this->allowedPublicColumns;

        // admin columns/my facilities columns
        if ($this->myFacilities || Gate::check('manage-facilities')) {
            $columns['status'] = ['name' => 'Status', 'sortable' => false];
            $columns['last_refreshed_at'] = ['name' => 'Last Refreshed', 'sortable' => true];
            $columns['expires_at'] = ['name' => 'Expires On', 'sortable' => true];

            // cf_id first in array
            $columns = array_merge(['cf_id' => ['name' => 'ID', 'sortable' => false]], $columns);
        }
        // admin columns on main page
        if (Gate::check('manage-facilities') && ! $this->myFacilities) {
            // no extra columns
            $columns['web_status'] = ['name' => 'Web Status', 'sortable' => true];
            $columns['user_id'] = ['name' => 'Creator Email', 'sortable' => false];
        }

        return $columns;
    }

    public function render()
    {
        $facilities = CoreFacility::query();
        // early exit - view facilities page
        if ($facilities->count() === 0) {
            return view('livewire.core-facility-table', [
                'facilities' => null,
                'myFacilities' => $this->myFacilities,
            ]);
        }

        // select columns from db (separate from getColumns())
        $selectableColumns = [
            'facility_name', 'web_address', 'institution', 'contact_name', 'contact_email', 'secondary_contact_name', 'secondary_contact_email', 'analysis_types',
        ];

        // apply sort
        if (array_key_exists($this->sort, $this->getColumns()) && $this->getColumns()[$this->sort]['sortable']) {
            // null values last - case where expiration date is null - keeps null at bottom
            $facilities->orderByRaw("{$this->sort} IS NULL")
                ->orderBy($this->sort, $this->direction);
        }

        // filterting and selecting columns
        if ($this->myFacilities) {
            $facilities->where('user_id', Auth::id());
            // early exit - myFacilities page
            if ($facilities->count() === 0) {
                return view('livewire.core-facility-table', [
                    'facilities' => null,
                    'myFacilities' => true,
                ]);
            }
            $facilities->whereIn('status', $this->selectedStatuses);
            $selectableColumns = array_merge($selectableColumns, ['id', 'cf_id', 'status', 'last_refreshed_at', 'expires_at', 'rejection_reason']);
        } elseif (Gate::check('manage-facilities')) {
            // admin on main page
            $facilities->whereIn('status', $this->selectedStatuses);
            $selectableColumns = array_merge($selectableColumns, ['id', 'cf_id', 'status', 'last_refreshed_at', 'expires_at', 'rejection_reason', 'user_id', 'reviewed_at', 'reviewer_id', 'web_status', 'web_status_desc', 'web_status_error', 'web_checked_at']);
        } else {
            // non-admin on main page
            $facilities->whereIn('status', [CoreFacilityStatus::ACCEPTED, CoreFacilityStatus::EXPIRED]);
            // early exit - view facilities page (accepted)
            if ($facilities->count() === 0) {
                return view('livewire.core-facility-table', [
                    'facilities' => null,
                    'myFacilities' => $this->myFacilities,
                ]);
            }
        }
        $facilities->select($selectableColumns);

        // search
        if ($this->search) {
            $facilities->where(function ($query) {
                $query->where(DB::raw('LOWER(facility_name)'), 'like', '%'.strtolower($this->search).'%')
                    ->orWhere(DB::raw('LOWER(web_address)'), 'like', '%'.strtolower($this->search).'%')
                    ->orWhere(DB::raw('LOWER(institution)'), 'like', '%'.strtolower($this->search).'%')
                    ->orWhere(DB::raw('LOWER(contact_name)'), 'like', '%'.strtolower($this->search).'%')
                    ->orWhere(DB::raw('LOWER(contact_email)'), 'like', '%'.strtolower($this->search).'%')
                    ->orWhereRaw('LOWER(CAST(analysis_types AS TEXT)) ILIKE ?', ['%'.strtolower($this->search).'%'])
                    ->orWhere(function ($query) {
                        if ($this->myFacilities || Gate::check('manage-facilities')) {
                            $query->where(DB::raw('LOWER(cf_id)'), 'like', '%'.strtolower($this->search).'%');
                        }
                    });
            });
        }

        return view('livewire.core-facility-table', [
            'facilities' => $facilities->paginate($this->myFacilities ? 5 : 10),
            'columns' => $this->getColumns(),
            'selectableStatuses' => CoreFacilityStatus::cases(),
            'myFacilities' => $this->myFacilities,
        ]);
    }

    // ACTIONS
    // admin protected routes
    #[On('cf-status-update-confirmed')]
    public function updateStatusConfirmed($data, CoreFacilityService $coreFacilityService)
    {
        $facility = CoreFacility::findOrFail($data['facilityId']);
        $status = CoreFacilityStatus::from($data['status']);
        $reason = $data['reason'] ?? null;

        try {
            // only supported actions
            match ($status) {
                CoreFacilityStatus::ACCEPTED => $coreFacilityService->accept($facility),
                CoreFacilityStatus::REJECTED => $coreFacilityService->reject($facility, $reason),
                CoreFacilityStatus::PENDING => $coreFacilityService->markAsPending($facility),
                default => throw new Exception('Invalid status action.'),
            };

            $this->dispatch('livewire-alert', ['status' => 'success', 'message' => 'Core facility status updated successfully: '.$status->label()]);
        } catch (Exception $e) {
            $this->dispatch('livewire-alert', ['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // admin protected routes
    #[On('cf-delete-confirmed')]
    public function deleteFacilityConfirmed($data, CoreFacilityService $coreFacilityService)
    {
        $facility = CoreFacility::findOrFail($data['facilityId']);
        try {
            $coreFacilityService->delete($facility);
            $this->dispatch('livewire-alert', ['status' => 'success', 'message' => 'Core facility deleted successfully.']);
        } catch (Exception $e) {
            $this->dispatch('livewire-alert', ['status' => 'error', 'message' => 'Failed to delete core facility.']);
        }
    }

    #[On('refreshTable')]
    public function refreshTable()
    {
        // this will refresh on its own
    }
}
