<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(ProductUnit::class);
            $table->bigInteger("original_product_unit_id")->unsigned()->nullable();
            $table->foreign("original_product_unit_id")->references("id")->on("product_units");

            $table->morphs('resource'); # bisa dari stock opnam, sales, transfer

            $table->integer("original_stock_in")->unsigned()->default(0);
            $table->integer("original_stock_out")->unsigned()->default(0);
            $table->integer("original_buying_price")->unsigned()->default(0);
            $table->integer("conversion_stock")->unsigned()->default(0);

            $table->integer("stock_in")->unsigned(); # original_stock_in * conversion_stock
            $table->integer("stock_out")->unsigned(); # original_stock_out * conversion_stock
            $table->integer("buying_price")->unsigned(); # original_buying_price * conversion_stock

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
        Schema::dropIfExists('product_stock_movements');
    }
};
