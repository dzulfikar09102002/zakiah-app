<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Location;
use App\Models\Tax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(ProductCategory::class)->nullable();
            $table->foreignIdFor(ProductUnit::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(Tax::class)->nullable();

            $table->bigInteger("child_product_category_id")->unsigned()->nullable();
            $table->foreign("child_product_category_id")->references("id")->on("product_categories");

            $table->bigInteger("product_sell_unit_id")->unsigned();
            $table->foreign("product_sell_unit_id")->references("id")->on("product_units");

            $table->bigInteger("parent_variance_id")->unsigned()->nullable();
            $table->foreign("parent_variance_id")->references("id")->on("products");

            $table->longText('name');
            $table->string('code')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->unique();
            $table->longText('description')->nullable();

            $table->longText('image_url')->nullable();

            $table->boolean('sell_to_customer')->default(true);
            $table->boolean('service')->default(false);
            $table->boolean('modifier')->default(false);
            $table->boolean('has_variance')->default(false);
            $table->boolean('allow_custom_price')->default(false);
            $table->boolean('select_all_location')->default(true);
            $table->json('location_ids')->nullable();
            $table->json('exclude_location_ids')->nullable();
            $table->enum('tax_setting', ['price_exclude_tax', 'price_include_tax'])->nullable();
            $table->integer("sell_price")->unsigned();
            
            $table->enum('status', ['active', 'archived']);

            $table->softDeletesTz('deleted_at', precision: 0);
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
        Schema::dropIfExists('products');
    }
};
