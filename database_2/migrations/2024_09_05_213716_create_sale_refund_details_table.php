<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use App\Models\SaleRefund;
use App\Models\Brand;
use App\Models\Location;
use App\Models\Employee;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Tax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_refund_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SaleTransaction::class);
            $table->foreignIdFor(SaleTransactionDetail::class);
            $table->foreignIdFor(SaleRefund::class);
            $table->foreignIdFor(Brand::class)->nullable();
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(Employee::class);
            $table->foreignIdFor(OrderType::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductCategory::class)->nullable();
            $table->foreignIdFor(ProductUnit::class);
            $table->foreignIdFor(Tax::class)->nullable();

            $table->longText("brand_name")->nullable();
            $table->longText("location_name");
            $table->longText("employee_first_name");
            $table->longText("employee_last_name");
            $table->longText("order_type_name");

            $table->longText("product_name");
            $table->longText("product_sku");
            $table->longText("product_code");
            $table->longText("product_description")->nullable();

            $table->longText("product_category_name")->nullable();

            $table->longText("product_unit_name");

            $table->longText("tax_name")->nullable();
            $table->integer("tax_rate")->nullable();
            $table->string("tax_setting")->nullable();

            $table->longText("notes")->nullable();
            $table->integer('quantity')->unsigned();

            $table->integer('sell_price')->unsigned();
            $table->integer('sell_price_tax_amount')->unsigned();

            $table->integer("promo_amount")->unsigned();
            $table->integer("promo_amount_tax_amount")->unsigned();
            $table->integer("discount_amount")->unsigned();
            $table->integer("discount_amount_tax_amount")->unsigned();
            $table->integer("surcharge_amount")->unsigned();
            $table->integer("surcharge_amount_tax_amount")->unsigned();
            $table->integer("free_of_charge_amount")->unsigned();
            $table->integer("free_of_charge_amount_tax_amount")->unsigned();
            
            $table->integer("prorate_promo_amount")->unsigned();
            $table->integer("prorate_promo_amount_tax_amount")->unsigned();
            $table->integer("prorate_discount_amount")->unsigned();
            $table->integer("prorate_discount_amount_tax_amount")->unsigned();
            $table->integer("prorate_surcharge_amount")->unsigned();
            $table->integer("prorate_surcharge_amount_tax_amount")->unsigned();
            $table->integer("prorate_free_of_charge_amount")->unsigned();
            $table->integer("prorate_free_of_charge_amount_tax_amount")->unsigned();

            $table->integer('total_line_amount')->unsigned();
            $table->integer('total_line_amount_tax_amount')->unsigned();

            $table->integer('service_charge')->unsigned();
            $table->integer('service_charge_tax_amount')->unsigned();
            $table->integer('service_charge_rate')->unsigned();
            $table->boolean('service_charge_include_tax')->default(true);

            $table->integer('modifier_subtotal')->unsigned();
            $table->integer('modifier_subtotal_tax_amount')->unsigned();
            $table->integer('modifier_service_charge')->unsigned();
            $table->integer('modifier_service_charge_tax_amount')->unsigned();
            $table->integer("modifier_prorate_promo_amount")->unsigned();
            $table->integer("modifier_prorate_promo_amount_tax_amount")->unsigned();
            $table->integer("modifier_prorate_discount_amount")->unsigned();
            $table->integer("modifier_prorate_discount_amount_tax_amount")->unsigned();
            $table->integer("modifier_prorate_surcharge_amount")->unsigned();
            $table->integer("modifier_prorate_surcharge_amount_tax_amount")->unsigned();
            $table->integer("modifier_prorate_free_of_charge_amount")->unsigned();
            $table->integer("modifier_prorate_free_of_charge_amount_tax_amount")->unsigned();
            $table->integer('modifier_total_amount')->unsigned();
            $table->integer('modifier_total_amount_tax_amount')->unsigned();

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
        Schema::dropIfExists('sale_refund_details');
    }
};
