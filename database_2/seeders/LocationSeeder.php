<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $orderTypes = array("Office", "Tulangan", "Mojosari", "Porong");
        $entity = Entity::where('code', 'secaca')->first();

        foreach ($orderTypes as $orderType) {
            Location::factory()->create([
                'entity_id' => $entity->id,
                'name' => $orderType,
                'code' => $orderType,
                'initial' => strtolower(substr($orderType, 0, 3)), # ambil 3 terdepan
                'kind' => 'outlet',
                'status' => 'active',
                'timezone' => 'Asia/Jakarta',
            ]);
        }
    }
}
