<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Promo;
use App\Models\PromoReward;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_reward_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Promo::class);
            $table->foreignIdFor(PromoReward::class);
            $table->foreignIdFor(Product::class)->nullable();
            $table->foreignIdFor(ProductCategory::class)->nullable();
            $table->foreignIdFor(ProductUnit::class)->nullable();

            $table->longText('product_name')->nullable();
            $table->longText('product_category_name')->nullable();
            $table->longText('product_unit_name')->nullable();

            $table->integer("sell_price")->unsigned()->nullable();
            $table->integer("reward_quantity")->unsigned()->nullable();
            $table->integer("maximum_reward_quantity")->unsigned()->nullable();
            $table->integer("reward_maximum_amount")->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletesTz('deleted_at', precision: 0);
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
        Schema::dropIfExists('promo_reward_products');
    }
};
