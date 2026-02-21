<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomerCategory;
use App\Models\CustomerCategoryRule;

class CustomerCategoryRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $custCategory = CustomerCategory::where('name', 'Basic')->first();

        CustomerCategoryRule::factory()->create([
            'customer_category_id' => $custCategory->id,
            'minimal_spend' => 500_000,
        ]);
    }
}
