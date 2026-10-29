<?php

namespace App\Livewire\Admin;

use App\Enums\ApiTokenRequestStatus;
use App\Models\ApiTokenRequest;
use Livewire\Component;
use Livewire\WithPagination;

class ApiTokenRequestsTable extends Component
{
    use WithPagination;

    public string $search = '';

    public array $selectedStatuses = [];

    public string $sort = 'created_at';

    public string $direction = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedStatuses' => ['as' => 'statuses'],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $statuses = request()->query('statuses');

        if ($statuses === null) {
            $this->selectedStatuses = [ApiTokenRequestStatus::Pending->value];
        } else {
            $this->selectedStatuses = array_values(array_intersect(
                (array) $statuses,
                array_column(ApiTokenRequestStatus::cases(), 'value')
            ));
        }

        $this->search = request()->query('search', '');
        $this->sort = request()->query('sort', 'created_at');
        $this->direction = request()->query('direction', 'desc');
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['created_at', 'status'], true)) {
            return;
        }

        if ($this->sort === $field && $this->direction === 'asc') {
            $this->direction = 'desc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSelectedStatuses(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ApiTokenRequest::query()->with(['user.country']);

        if ($this->selectedStatuses !== []) {
            $query->whereIn('status', $this->selectedStatuses);
        } else {
            $query->whereRaw('0 = 1');
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%")
                        ->orWhere('department', 'LIKE', "%{$search}%")
                        ->orWhere('organization', 'LIKE', "%{$search}%")
                        ->orWhereHas('country', fn ($c) => $c->where('name', 'LIKE', "%{$search}%")));
            });
        }

        $query->orderBy($this->sort, $this->direction);

        $tokenRequests = $query->paginate(20);

        return view('livewire.admin.api-token-requests-table', [
            'tokenRequests' => $tokenRequests,
            'statuses' => ApiTokenRequestStatus::cases(),
        ]);
    }
}
