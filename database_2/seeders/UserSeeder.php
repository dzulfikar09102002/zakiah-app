<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $entity = Entity::where('id', 1)->first();
        $roleCashier = Role::where('entity_id', null)->where('parent_id', null)->where('level', 1)->where('name', 'Cashier')->first();
        $roleSales = Role::where('entity_id', null)->where('parent_id', null)->where('level', 1)->where('name', 'Sales')->first();

        $user = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@secaca.com',
            'password' => '12345678'
        ]);

        $role = Role::where('entity_id', null)->where('parent_id', null)->where('level', 1)->where('name', 'Entity Owner')->first();
        $entity->employees()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'code' => 'admin-secaca',
            'first_name' => 'Admin',
            'last_name' => 'secaca',
            'location_permission' => [],
            'entity_permission' => Role::defaultEntityPermission(),
        ]);

        // Nilta - 2 role, kasir & sales
        $users = array("Nilta");
        foreach ($users as $user) {
            $createdUser = User::factory()->create([
                'name' => $user,
                'email' => strtolower("$user@secaca.com"),
                'password' => '12345678'
            ]);

            $entity->employees()->create([
                'user_id' => $createdUser->id,
                'role_id' => $roleCashier->id,
                'code' => "cashier-$user",
                'first_name' => $user,
                'last_name' => 'Kasir',
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);

            $entity->employees()->create([
                'user_id' => $createdUser->id,
                'role_id' => $roleSales->id,
                'code' => "sales-$user",
                'first_name' => $user,
                'last_name' => 'Sales',
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);
        }
        
        $users = array("Putri", "Tiwi", "Eka", "Fuadah", "Rizky", "Tasya", "Alfi");
        foreach ($users as $user) {
            $createdUser = User::factory()->create([
                'name' => $user,
                'email' => strtolower("$user@secaca.com"),
                'password' => '12345678'
            ]);

            $entity->employees()->create([
                'user_id' => $createdUser->id,
                'role_id' => $roleCashier->id,
                'code' => "cashier-$user",
                'first_name' => $user,
                'last_name' => 'Kasir',
                'location_permission' => [],
                'entity_permission' => Role::defaultEntityPermission(),
            ]);
        }

        $user = User::factory()->create([
            'name' => 'Warehouse',
            'email' => 'warehouse@secaca.com',
            'password' => '12345678'
        ]);

        $role = Role::where('entity_id', null)->where('parent_id', null)->where('level', 1)->where('name', 'Warehouse')->first();

        $entity->employees()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'code' => 'warehouse-secaca',
            'first_name' => 'Warehouse',
            'last_name' => 'secaca',
            'location_permission' => [],
            'entity_permission' => Role::defaultEntityPermission(),
        ]);
    }
}
