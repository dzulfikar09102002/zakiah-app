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
        Schema::create('product_location_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(ProductUnit::class);

            $table->integer("stock")->default(0);

            $table->integer("last_in_stock")->default(0);
            $table->integer("last_out_stock")->default(0);
            $table->integer("last_buy_price")->default(0);
            $table->integer("average_buy_price")->default(0);
            $table->integer("lowest_buy_price")->default(0);
            $table->integer("highest_buy_price")->default(0);

            $table->timestamps();
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");
            
            $table->softDeletesTz('deleted_at', precision: 0);

            $table->char('checksum', 32);
            $table->unique(['checksum']);
            // $table->unique(['product_id', 'location_id', 'product_unit_id', 'deleted_at'], 'uq_main');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_location_stocks');
    }
};
