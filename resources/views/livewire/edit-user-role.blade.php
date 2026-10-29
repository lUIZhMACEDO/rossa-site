<div>
    <div class="mb-4">
        <label for="role" class="form-label">Select a role</label>
        <select wire:model.live="selectedRoleId" class="form-select" @if($isCustomRole) disabled @endif>
            <option value="">Select a role...</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="customRole" wire:model.live="isCustomRole">
            <label class="form-check-label" for="customRole">
                Or, create a <strong>custom role</strong> with specific permissions
            </label>
        </div>
    </div>

    @if($showPermissions)
        <div class="mb-4">
            <label class="form-label">Permissions</label>
            <div class="d-flex flex-column gap-2">
                @foreach($permissions as $permission)
                    @php
                        $permissionType = \App\Enums\PermissionType::tryFrom($permission->name);
                        $label = $permissionType && \App\Enums\PermissionType::description($permissionType)
                            ? \App\Enums\PermissionType::description($permissionType)
                            : \Illuminate\Support\Str::headline(str_replace('-', ' ', $permission->name));
                    @endphp
                    <div class="form-check form-switch" wire:key="permission-{{ $permission->id }}">
                        <input class="form-check-input" 
                                type="checkbox" 
                                id="permission_{{ $permission->id }}" 
                                wire:model="selectedPermissions"
                                value="{{ $permission->id }}"
                                @if(!$isCustomRole) disabled @endif>
                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <button wire:click="updateRole" class="btn btn-primary">Update Role</button>
    <a href="{{ route('admin.users') }}" class="btn btn-secondary ms-2">Cancel</a>
</div>
