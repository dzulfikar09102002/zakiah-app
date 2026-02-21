<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entity = Entity::where('code', 'secaca')->first();

        Brand::factory()->create([
            'entity_id' => $entity->id,
            'name' => 'Zakiah',
            'code' => 'zakiah',
            'initial' => 'zakiah',
        ]);
    }
}
