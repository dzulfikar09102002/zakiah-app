<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\ProductUnit;
use App\Models\Tax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_sell_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(OrderType::class)->nullable();
            $table->foreignIdFor(ProductUnit::class)->nullable();
            $table->foreignIdFor(Tax::class)->nullable();
            $table->enum('tax_setting', ['price_exclude_tax', 'price_include_tax'])->nullable();
            $table->integer("sell_price")->unsigned();
            $table->boolean("default")->default(false);

            $table->timestamps();
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");
            
            $table->softDeletesTz('deleted_at', precision: 0);


            $table->char('checksum', 32);
            $table->unique(['checksum']);

            // $table->unique(['product_id', 'location_id', 'product_unit_id', 'order_type_id', 'deleted_at'], 'uq_main');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sell_prices');
    }
};
