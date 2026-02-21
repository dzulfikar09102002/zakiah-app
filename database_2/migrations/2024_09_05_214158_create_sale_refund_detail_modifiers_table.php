<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use App\Models\SaleTransactionDetailModifier;
use App\Models\SaleRefund;
use App\Models\SaleRefundDetail;
use App\Models\Modifier;
use App\Models\ModifierOption;
use App\Models\Tax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_refund_detail_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SaleTransaction::class);
            $table->foreignIdFor(SaleTransactionDetail::class);
            $table->foreignIdFor(SaleTransactionDetailModifier::class);

            $table->foreignIdFor(SaleRefund::class);
            $table->foreignIdFor(SaleRefundDetail::class);

            $table->foreignIdFor(Modifier::class);
            $table->foreignIdFor(ModifierOption::class);

            $table->foreignIdFor(Tax::class)->nullable();
            $table->longText("tax_name")->nullable();
            $table->integer("tax_rate")->nullable();
            $table->string("tax_setting")->nullable();

            $table->longText("modifier_name");

            $table->bigInteger("option_id")->unsigned();
            $table->longText("option_name");
            $table->longText("option_kind");

            $table->integer("modifier_quantity")->unsigned();

            $table->integer("quantity")->unsigned(); # same with sale detail refund

            $table->integer("sell_price")->unsigned();
            $table->integer("sell_price_tax_amount")->unsigned();

            $table->integer("promo_amount")->unsigned();
            $table->integer("promo_amount_tax_amount")->unsigned();
            $table->integer("discount_amount")->unsigned();
            $table->integer("discount_amount_tax_amount")->unsigned();
            $table->integer("surcharge_amount")->unsigned();
            $table->integer("surcharge_amount_tax_amount")->unsigned();
            $table->integer("free_of_charge_amount")->unsigned();
            $table->integer("free_of_charge_amount_tax_amount")->unsigned();

            $table->integer('subtotal')->unsigned();
            $table->integer('subtotal_tax_amount')->unsigned();

            $table->integer('service_charge')->unsigned();
            $table->integer('service_charge_tax_amount')->unsigned();
            
            $table->integer("prorate_promo_amount")->unsigned();
            $table->integer("prorate_promo_amount_tax_amount")->unsigned();
            $table->integer("prorate_discount_amount")->unsigned();
            $table->integer("prorate_discount_amount_tax_amount")->unsigned();
            $table->integer("prorate_surcharge_amount")->unsigned();
            $table->integer("prorate_surcharge_amount_tax_amount")->unsigned();
            $table->integer("prorate_free_of_charge_amount")->unsigned();
            $table->integer("prorate_free_of_charge_amount_tax_amount")->unsigned();

            $table->integer('total_amount')->unsigned();
            $table->integer('total_amount_tax_amount')->unsigned();

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
        Schema::dropIfExists('sale_refund_detail_modifiers');
    }
};
