<?php

use App\Models\Employee;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductAdjustmentStock;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_adjustment_stock_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductAdjustmentStock::class);
            $table->foreignIdFor(Employee::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductCategory::class)->nullable();
            $table->foreignIdFor(ProductUnit::class);

            $table->longText("product_name");
            $table->longText("product_sku");
            $table->longText("product_code");
            $table->longText("product_description");

            $table->longText("product_category_name")->nullable();
            $table->longText("product_unit_name")->nullable();

            $table->integer("recorded_stock");
            $table->integer("counted_stock");
            $table->integer("difference_stock");
            $table->longText("note")->nullable();

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
        Schema::dropIfExists('product_adjustment_stock_details');
    }
};
