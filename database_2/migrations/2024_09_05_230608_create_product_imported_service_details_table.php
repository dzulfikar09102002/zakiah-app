<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ProductImportService;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\OrderType;
use App\Models\ProductCategory;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_import_service_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductImportService::class);

            $table->integer("imported_line_row")->unsigned();

            $table->foreignIdFor(Product::class);
            $table->longText("product_code");
            $table->longText("product_name");
            $table->longText("product_barcode");
            $table->longText("product_description")->nullable();
            $table->boolean("product_created");

            $table->foreignIdFor(ProductUnit::class);
            $table->longText("product_unit_name");
            $table->boolean("product_unit_created");

            $table->foreignIdFor(OrderType::class);
            $table->longText("order_type_name");
            $table->boolean("order_type_created");

            $table->foreignIdFor(ProductCategory::class);
            $table->longText("product_category_name");
            $table->boolean("product_category_created");

            $table->integer("buying_price")->unsigned()->nullable();
            $table->integer("selling_price")->unsigned()->nullable();

            $table->foreignIdFor(Location::class);

            $table->integer("stock_in")->unsigned()->nullable();

            $table->enum("status", ['ok', 'failed']);
            $table->longText("status_message")->nullable();

            $table->longText("kode");
            $table->longText("nama");
            $table->longText("deskripsi");
            $table->longText("satuan");
            $table->longText("berat")->nullable();
            $table->integer("harga_pokok");
            $table->integer("harga_jual_ecer");
            $table->integer("harga_jual_grosir");
            $table->longText("kategori");
            $table->integer("stok_minimum");
            $table->longText("barcode")->nullable();
            $table->longText("nama_lokasi")->nullable();
            $table->integer("stok");

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
        Schema::dropIfExists('product_import_service_details');
    }
};
