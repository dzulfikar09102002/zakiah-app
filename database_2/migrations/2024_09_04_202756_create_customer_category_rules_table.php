<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CustomerCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_category_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CustomerCategory::class);

            $table->integer("minimal_spend")->unsigned()->nullable()->default(null);

            $table->boolean('include_tax')->default(true);
            $table->boolean('include_service_charge')->default(true);
            $table->boolean('include_promo')->default(true);
            $table->boolean('include_surcharge')->default(true);
            $table->boolean('include_free_of_charge')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_category_rules');
    }
};
