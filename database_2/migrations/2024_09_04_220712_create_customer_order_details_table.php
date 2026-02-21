<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CustomerOrder;
use App\Models\Brand;
// use App\Models\Catalogue;
// use App\Models\CatalogueDetail;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Loyalty;
use App\Models\LoyaltyRewardProduct;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Promo;
use App\Models\Tax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(CustomerOrder::class);
            $table->foreignIdFor(Brand::class)->nullable();
            $table->foreignIdFor(Customer::class)->nullable();
            $table->foreignIdFor(Employee::class);
            $table->foreignIdFor(OrderType::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductCategory::class)->nullable();
            $table->foreignIdFor(ProductUnit::class);
            $table->foreignIdFor(Tax::class)->nullable();

            // $table->foreignIdFor(Catalogue::class);
            // $table->foreignIdFor(CatalogueDetail::class);
            $table->foreignIdFor(Loyalty::class)->nullable();
            $table->foreignIdFor(LoyaltyRewardProduct::class)->nullable();

            $table->longText('product_name');
            $table->longText('product_category_name')->nullable();
            $table->longText('product_unit_name');

            $table->integer('modifier_count')->default(0);

            $table->integer('sell_price')->unsigned();
            $table->boolean('custom_price')->default(false);
            $table->integer('modifier_price')->unsigned()->default(0);

            $table->integer('quantity')->unsigned();
            $table->integer('total_line_amount')->unsigned(); 

            $table->integer('tax_rate')->unsigned()->nullable();
            $table->enum('tax_setting', ['price_exclude_tax', 'price_include_tax'])->nullable();
            $table->integer('tax_inclusive_amount')->unsigned()->default(0);
            $table->integer('tax_exclusive_amount')->unsigned()->default(0);

            $table->integer('service_charge')->default(0);
            $table->integer('service_charge_rate')->default(0);
            $table->boolean('service_charge_include_tax')->default(true);

            $table->integer("promo_amount")->unsigned()->default(0);
            $table->integer("discount_amount")->unsigned()->default(0);
            $table->integer("surcharge_amount")->unsigned()->default(0);
            $table->integer("free_of_charge_amount")->unsigned()->default(0);
            
            $table->integer("prorate_promo_amount")->unsigned()->default(0);
            $table->integer("prorate_discount_amount")->unsigned()->default(0);
            $table->integer("prorate_surcharge_amount")->unsigned()->default(0);
            $table->integer("prorate_free_of_charge_amount")->unsigned()->default(0);
            $table->integer('total_amount')->unsigned(); # total_amount + modifier

            $table->foreignIdFor(Promo::class)->nullable();
            $table->longText('promo_name')->nullable();

            $table->integer('loyalty_point')->nullable();
            $table->longText('notes')->nullable();
            $table->json("adjustment")->nullable();

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
        Schema::dropIfExists('customer_order_details');
    }
};
