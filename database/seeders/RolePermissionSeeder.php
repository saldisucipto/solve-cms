<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionIds = $this->seedPermissions();

        foreach (config('solve.roles') as $name => $attributes) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                [
                    'label' => $attributes['label'],
                    'description' => $attributes['description'],
                    'is_system' => true,
                ],
            );

            $assigned = config("solve.role_permissions.{$name}", []);

            $ids = in_array('*', $assigned, true)
                ? $permissionIds->values()->all()
                : collect($assigned)
                    ->map(fn (string $permission) => $permissionIds->get($permission))
                    ->filter()
                    ->values()
                    ->all();

            $role->permissions()->sync($ids);
        }
    }

    /**
     * @return Collection<string, int>
     */
    protected function seedPermissions(): Collection
    {
        $ids = collect();

        foreach (config('solve.permissions') as $group => $permissions) {
            foreach ($permissions as $name => $label) {
                $permission = Permission::updateOrCreate(
                    ['name' => $name],
                    ['label' => $label, 'group' => $group],
                );

                $ids->put($name, $permission->id);
            }
        }

        return $ids;
    }
}
