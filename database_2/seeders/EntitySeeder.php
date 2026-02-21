<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\Role;
use App\Models\User;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Entity::factory()->create([
            'name' => 'Secaca',
            'code' => 'secaca',
            'initial' => 'secaca',
            'phone_number' => '61',
            'phone_number_country_code' => '62',
            'email' => 'secaca@gmail.com',
            'full_address' => '-',
            'postal_code' => '-',
            'city' => '-',
            'province' => '-',
            'country' => 'indonesia',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);
    }
}
