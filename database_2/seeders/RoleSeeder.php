<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = array("Entity Owner", "Location Owner");

        foreach ($roles as $role) {
            Role::factory()->create([
                'name' => $role,
                'tier' => 1,
                'level' => 1,
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);
        }

        $roles = array("Cashier");
        foreach ($roles as $role) {
            Role::factory()->create([
                'name' => $role,
                'tier' => 1,
                'level' => 1,
                'allow_backoffice' => false,
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);
        }

        $roles = array("Warehouse");
        foreach ($roles as $role) {
            Role::factory()->create([
                'name' => $role,
                'tier' => 1,
                'level' => 1,
                'allow_pos' => false,
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);
        }

        $roles = array("Sales");
        foreach ($roles as $role) {
            Role::factory()->create([
                'name' => $role,
                'tier' => 1,
                'level' => 1,
                'allow_pos' => false,
                'allow_backoffice' => false,
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);
        }
    }
}
