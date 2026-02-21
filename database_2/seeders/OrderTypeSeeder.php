<?php

namespace Database\Seeders;

use App\Helpers\UniqueCodeGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderType;
use App\Models\Entity;

class OrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderTypes = array("Ecer");
        $entity = Entity::where('code', 'secaca')->first();

        foreach ($orderTypes as $orderType) {
            OrderType::factory()->create([
                'entity_id' => $entity->id,
                'name' => $orderType,
                'search_name' => UniqueCodeGenerator::generateSearchName($orderType),
                'fixed_fee' => 0,
                'variable_fee' => 0,
            ]);
        }
    }
}
