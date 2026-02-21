<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductVarianceAttribute;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_variance_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductVarianceAttribute::class);

            $table->longText('name');
            $table->smallInteger("sequence")->unsigned();
            $table->integer("sell_price")->unsigned();

            $table->timestamps();

            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variance_attribute_values');
    }
};
