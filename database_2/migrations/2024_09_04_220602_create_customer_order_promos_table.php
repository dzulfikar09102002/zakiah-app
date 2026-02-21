<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderDetail;
// use App\Models\Promo;
// use App\Models\PromoReward;
// use App\Models\PromoRewardProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promo;
use App\Models\PromoReward;
use App\Models\PromoRewardProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_order_promos', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(CustomerOrder::class);
            $table->foreignIdFor(CustomerOrderDetail::class)->nullable();

            $table->foreignIdFor(Promo::class);
            $table->foreignIdFor(PromoReward::class);
            $table->foreignIdFor(PromoRewardProduct::class)->nullable();
            $table->foreignIdFor(Product::class)->nullable();
            $table->foreignIdFor(ProductCategory::class)->nullable();

            $table->longText("promo_name");
            $table->integer("amount");
            $table->integer("applied_promo_amount");

            $table->string("promo_reward_template");
            $table->boolean("promo_reward_percentage")->default(false);
            $table->integer("promo_reward_amount")->unsigned();
            $table->integer("promo_reward_maximum_amount")->unsigned()->nullable();

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
        Schema::dropIfExists('customer_order_promos');
    }
};
