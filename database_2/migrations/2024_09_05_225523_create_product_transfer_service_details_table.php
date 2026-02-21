<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductTransferService;
use App\Models\Product;
use App\Models\ProductUnit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_transfer_service_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductTransferService::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductUnit::class);
            $table->bigInteger("original_product_unit_id")->unsigned(); # get from product.product_unit_id
            $table->foreign("original_product_unit_id")->references("id")->on("product_units")->name('smallest_product_unit_id_fk');

            $table->longText("smallest_product_unit_name");
            $table->integer("conversion_quantity")->unsigned();

            $table->longText("product_name");
            $table->longText("product_sku");
            $table->longText("product_code");
            $table->longText("product_unit_name");

            $table->integer("quantity")->unsigned();
            $table->integer("buying_price")->unsigned();

            $table->integer("transfered_quantity")->unsigned(); # quantity * conversion_quantity
            $table->integer("transfered_buying_price")->unsigned(); # buying_price * conversion_quantity

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
        Schema::dropIfExists('product_transfer_service_details');
    }
};
