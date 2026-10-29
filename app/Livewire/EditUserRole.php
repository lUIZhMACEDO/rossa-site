<?php

namespace App\Livewire;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Livewire\Component;

class EditUserRole extends Component
{
    public User $user;

    public $selectedRoleId = null;

    public $isCustomRole = false;

    public $selectedPermissions = [];

    public $showPermissions = false;

    public function mount($user)
    {
        $this->user = $user;

        if ($this->user->role) {
            $this->selectedRoleId = $this->user->role->id;
            $this->isCustomRole = $this->user->role->is_custom ?? false;
            $this->selectedPermissions = $this->user->role->permissions->pluck('id')->toArray();
            $this->showPermissions = true;
        }
    }

    public function updatedSelectedRoleId($value)
    {
        if ($value) {
            $this->isCustomRole = false;
            $this->showPermissions = true;
            $role = Role::where('id', $value)->first();
            $this->selectedPermissions = $role ? $role->permissions->pluck('id')->toArray() : [];
        } else {
            $this->resetRoleSelection();
        }
    }

    public function updatedIsCustomRole($value)
    {
        if ($value) {
            $this->selectedRoleId = null;
            $this->showPermissions = true;
            $this->selectedPermissions = [];
        }
    }

    private function resetRoleSelection()
    {
        $this->selectedRoleId = null;
        $this->isCustomRole = false;
        $this->showPermissions = false;
        $this->selectedPermissions = [];
    }

    public function updateRole()
    {
        if ($this->isCustomRole) {
            $customRole = Role::firstOrCreate([
                'name' => 'custom'.'-'.$this->user->id,
                'is_custom' => true,
                'user_id' => $this->user->id,
            ]);
            $customRole->permissions()->sync($this->selectedPermissions);
            $this->user->role_id = $customRole->id;
            $this->user->save();

            session()->flash('success', 'Custom role updated successfully!');
        } elseif ($this->selectedRoleId) {
            $this->user->role_id = $this->selectedRoleId;
            $this->user->save();

            session()->flash('success', 'Role updated successfully!');
        } else {
            $this->user->role_id = null;
            $this->user->save();

            session()->flash('success', 'Role removed successfully!');
        }

        return redirect()->route('admin.users');
    }

    public function render()
    {
        return view('livewire.edit-user-role', [
            'roles' => Role::where('is_custom', false)->get(),
            'permissions' => Permission::query()
                ->orderBy('id')
                ->get()
                ->unique('name')
                ->values(),
        ]);
    }
}
