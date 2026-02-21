<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Promo;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Promo::class);

            $table->enum('template', ['discount_percentage', 'discount_fixed', 'get_product', 'special_price']);
            $table->enum('applied_to', ['total_order', 'product', 'product_category'])->nullable();

            $table->boolean("percentage")->default(false)->nullable();
            $table->integer("reward_amount")->unsigned()->nullable();
            $table->integer("reward_maximum_amount")->unsigned()->nullable();
            $table->integer("in_house_percentage")->unsigned()->nullable();

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
        Schema::dropIfExists('promo_rewards');
    }
};
