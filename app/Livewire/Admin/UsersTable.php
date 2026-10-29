<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedRole = 'all';

    public string $selectedStatus = 'active';

    public string $sort = 'created_at';

    public string $direction = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedRole' => ['as' => 'role', 'except' => 'all'],
        'selectedStatus' => ['as' => 'status', 'except' => 'active'],
        'sort' => ['except' => 'created_at'],
        'direction' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->search = request()->query('search', '');
        $this->selectedRole = request()->query('role', 'all');
        $this->selectedStatus = request()->query('status', 'active');
        $this->sort = request()->query('sort', 'created_at');
        $this->direction = request()->query('direction', 'desc');
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['created_at', 'name', 'email'], true)) {
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

    public function updatedSelectedRole(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleDisabled(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->disabled_at = $user->isDisabled() ? null : now();
        $user->save();
    }

    public function render()
    {
        $query = User::query()->with(['role.permissions', 'country']);

        if ($this->selectedRole !== 'all') {
            $query->whereHas('role', fn ($q) => $q->where('name', $this->selectedRole));
        }

        if ($this->selectedStatus === 'active') {
            $query->whereNull('disabled_at');
        } elseif ($this->selectedStatus === 'disabled') {
            $query->whereNotNull('disabled_at');
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('department', 'ilike', "%{$search}%")
                    ->orWhere('organization', 'ilike', "%{$search}%")
                    ->orWhereHas('country', fn ($c) => $c->where('name', 'ilike', "%{$search}%"));

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if ($this->search !== '' && is_numeric($this->search)) {
            $query->orderByRaw('CASE WHEN users.id = ? THEN 0 ELSE 1 END', [(int) $this->search]);
        }

        $query->orderBy($this->sort, $this->direction);

        return view('livewire.admin.users-table', [
            'users' => $query->paginate(20),
            'roles' => Role::where('is_custom', false)->orderBy('name')->get(),
        ]);
    }
}
