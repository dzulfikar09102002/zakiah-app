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
        Schema::create('promo_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Promo::class);

            $table->integer("minimum_sales_purchase")->unsigned()->nullable();
            $table->boolean('customer_only')->default(false);
            $table->boolean('customer_category')->default(false);

            $table->enum("product_buy_condition", ['or', 'and']);
            $table->enum("product_category_buy_condition", ['or', 'and']);

            $table->integer("maximum_redemption")->unsigned()->nullable();
            $table->integer("maximum_redemption_daily")->unsigned()->nullable(); # jm 00.00 - 24.00
            $table->integer("maximum_redemption_weekly")->unsigned()->nullable(); # hr sun - sat
            $table->integer("maximum_redemption_monthly")->unsigned()->nullable(); # tgl 1 - 31
            $table->integer("maximum_redemption_anual")->unsigned()->nullable(); # tgl 1 jan - 31 des

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
        Schema::dropIfExists('promo_rules');
    }
};
