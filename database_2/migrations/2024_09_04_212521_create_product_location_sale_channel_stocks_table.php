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
        Schema::create('product_location_sale_channel_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductUnit::class);
            $table->foreignIdFor(Location::class);
            
            $table->enum('sales_channel', ['pos']);
            $table->integer("minimal_stock")->unsigned()->nullable(); # will create waring
            $table->integer("maximum_stock")->unsigned()->nullable(); # maximum sell stock set by user

            $table->timestamps();
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");
            
            $table->softDeletesTz('deleted_at', precision: 0);

            $table->unique(['product_id', 'location_id', 'product_unit_id', 'sales_channel', 'deleted_at'], 'uq_main');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_location_sale_channel_stocks');
    }
};
