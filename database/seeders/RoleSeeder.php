<?php

namespace Database\Seeders;

use App\Class\Constant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // for roles
        foreach (Constant::ROLES as $role) {
            if (!Role::where('name', $role)->first()) {
                Role::create([
                    'name' => $role
                ]);
            }
        }

        // for permissions
        foreach (Constant::getPermissions() as $feature => $permissions) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate([
                    'name' => $feature . '_' . $permission
                ]);
            }
        }

        // assign permissions to roles
        foreach (Constant::getRolesWithPermissions() as $role => $features) {
            if ($role != 'Super Admin') {
                $role = Role::whereName($role)->first();
                if ($role) {
                    foreach ($features as $name => $permissions) {
                        $modifiedPermissions = collect($permissions)->map(fn($item) => $name . '_' . $item)->toArray();
                        $role->syncPermissions($modifiedPermissions);
                    }
                }
            }
        }
    }
}
