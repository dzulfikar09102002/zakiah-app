<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SaleTransaction;
use App\Models\CustomerOrderDetail;
use App\Models\Brand;
use App\Models\Location;
use App\Models\Employee;
use App\Models\Loyalty;
use App\Models\LoyaltyRewardProduct;
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
        Schema::create('sale_transaction_details', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(SaleTransaction::class);
            $table->foreignIdFor(CustomerOrderDetail::class);
            $table->foreignIdFor(Brand::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(OrderType::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductCategory::class)->nullable();
            $table->foreignIdFor(ProductUnit::class);
            $table->foreignIdFor(Tax::class)->nullable();

            $table->enum("status", ['ok', 'void']);
            $table->timestamp('sales_at', precision: 6)->nullable();
            $table->timestamp('local_sales_at', precision: 6)->nullable();

            $table->longText("brand_name");
            $table->longText("location_name");
            $table->longText("order_type_name");

            $table->longText("product_name");
            $table->longText("product_sku");
            $table->longText("product_code");
            $table->longText("product_description")->nullable();

            $table->longText("product_category_name")->nullable();

            $table->longText("product_unit_name");

            $table->longText("tax_name")->nullable();
            $table->integer("tax_rate")->nullable();
            $table->enum('tax_setting', ['price_exclude_tax', 'price_include_tax'])->nullable();

            $table->longText("notes")->nullable();
            $table->integer('quantity')->unsigned();
            $table->integer('cancelled_quantity')->unsigned();

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

            $table->integer('service_charge')->unsigned();
            $table->integer('service_charge_tax_amount')->unsigned();
            $table->integer('service_charge_rate')->unsigned();
            $table->boolean('service_charge_include_tax')->default(true);
            
            $table->integer("prorate_promo_amount")->unsigned();
            $table->integer("prorate_promo_amount_tax_amount")->unsigned();
            $table->integer("prorate_discount_amount")->unsigned();
            $table->integer("prorate_discount_amount_tax_amount")->unsigned();
            $table->integer("prorate_surcharge_amount")->unsigned();
            $table->integer("prorate_surcharge_amount_tax_amount")->unsigned();
            $table->integer("prorate_free_of_charge_amount")->unsigned();
            $table->integer("prorate_free_of_charge_amount_tax_amount")->unsigned();

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

            $table->integer('total_line_amount')->unsigned(); # sum all
            $table->integer('total_line_amount_tax_amount')->unsigned();

            $table->integer('total_amount')->unsigned(); # total_line_amount * quantity
            $table->integer('total_amount_tax_amount')->unsigned();

            $table->json('modifier_ids');
            $table->json('modifier_option_ids');

            $table->foreignIdFor(Loyalty::class)->nullable();
            $table->foreignIdFor(LoyaltyRewardProduct::class)->nullable();
            $table->integer('loyalty_point')->nullable();

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
        Schema::dropIfExists('sale_transaction_details');
    }
};
