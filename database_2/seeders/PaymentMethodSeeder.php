<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $orderTypes = array("Tunai");
        $entity = Entity::where('code', 'secaca')->first();

        foreach ($orderTypes as $orderType) {
            PaymentMethod::factory()->create([
                'entity_id' => $entity->id,
                'name' => $orderType,
                'status' => 'active',
                'kind' => 'cash',
                'fixed_fee' => 0,
                'variable_fee' => 0,
            ]);
        }
    }
}
