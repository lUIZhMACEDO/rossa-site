<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = PermissionType::all();

        // THIS LIST IS A SOURCE OF TRUTH FOR DEFAULT ROLES AND THEIR PERMISSIONS
        // MODIFYING THIS LIST WILL MODIFY THE ROLES AND THEIR PERMISSIONS
        $preset_roles = [
            'admin' => [
                PermissionType::NO_RESTRICTION->value,
            ],
            // (test dummy role)
            'researcher' => [
                PermissionType::MANAGE_STUDIES->value,
                PermissionType::MANAGE_FACILITIES->value,
            ],
            'facility-manager' => [
                PermissionType::MANAGE_FACILITIES->value,
            ],
        ];

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['description' => PermissionType::description(PermissionType::from($permission))]
            );
        }

        // Delete permissions that are no longer in the enum
        Permission::whereNotIn('name', $permissions)->delete();

        // Create Roles
        foreach ($preset_roles as $role_name => $role_permissions) {
            $role = Role::updateOrCreate(['name' => $role_name]);

            // Assign Permissions to Role
            $permission_ids = Permission::whereIn('name', $role_permissions)->pluck('id');

            // The role will have only these permissions
            $role->permissions()->sync($permission_ids);
        }

        // Remove preset roles that are no longer in the preset list (don't remove custom roles)
        Role::whereNotIn('name', array_keys($preset_roles))
            ->where('is_custom', false)
            ->delete();
    }
}
