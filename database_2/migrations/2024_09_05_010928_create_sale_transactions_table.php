<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Location;
use App\Models\Device;
use App\Models\CustomerOrder;
use App\Models\OrderType;
use App\Models\Taking;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(CustomerOrder::class);
            $table->foreignIdFor(Customer::class)->nullable();
            $table->foreignIdFor(Taking::class)->nullable();
            $table->foreignIdFor(OrderType::class);
            $table->foreignIdFor(Device::class);
            $table->bigInteger("checkpoint_device_id")->unsigned()->nullable();
            $table->foreign("checkpoint_device_id")->references("id")->on("devices");

            $table->bigInteger("employee_sales_id")->unsigned();
            $table->bigInteger("cashier_id")->unsigned();
            // $table->foreign("employee_sales_id")->references("id")->on("employees");

            $table->string("code");
            $table->string("sales_no");
            $table->string("receipt_no");
            $table->enum("status", ['ok', 'void']);
            
            $table->longText('location_name');
            $table->string('location_initial');
            $table->longText('location_timezone');
            
            $table->longText('order_type_name');

            $table->integer("gross_sales")->unsigned();
            $table->integer("discount_amount_before_tax")->unsigned();
            $table->integer("surcharge_amount_before_tax")->unsigned();
            $table->integer("promo_amount_before_tax")->unsigned();
            $table->integer("free_of_charge_amount_before_tax")->unsigned();
            $table->integer("net_sales")->unsigned();
            $table->integer("service_charge_before_tax")->unsigned();
            $table->integer("tax_amount")->unsigned();
            $table->integer("rounding_amount")->unsigned();
            $table->integer("rounding_tax_amount")->unsigned();
            $table->integer("rounding_service_charge_amount")->unsigned();
            $table->integer("net_sales_after_tax")->unsigned();
            $table->integer("payment_platform_fee")->unsigned();
            $table->integer("platform_fee")->unsigned();
            $table->integer("total_processing_fee")->unsigned();
            $table->integer("total_subsidize")->unsigned();

            $table->bigInteger("receive_paid_by")->unsigned()->nullable();
            $table->foreign("receive_paid_by")->references("id")->on("employees");
            $table->bigInteger("paid_by")->unsigned()->nullable();
            $table->foreign("paid_by")->references("id")->on("customers");
            $table->timestamp('paid_at', precision: 6);
            $table->timestamp('local_paid_at', precision: 6)->nullable();

            $table->bigInteger("void_by")->unsigned()->nullable();
            $table->foreign("void_by")->references("id")->on("employees");
            $table->timestamp('void_at', precision: 6)->nullable();
            $table->timestamp('local_void_at', precision: 6)->nullable();
            $table->longText('void_reason')->nullable();
            $table->longText('void_notes')->nullable();

            $table->timestamp('sales_at', precision: 6)->nullable();
            $table->timestamp('local_sales_at', precision: 6)->nullable();

            $table->longText('notes')->nullable();

            $table->json('product_ids');
            $table->json('product_category_ids');
            $table->json('modifier_ids');
            $table->json('modifier_option_ids');

            $table->integer("earn_point")->unsigned()->default(0);
            $table->integer("redeem_point")->unsigned()->default(0);

            $table->integer("discount_amount")->unsigned()->default(0);
            $table->integer("surcharge_amount")->unsigned()->default(0);
            $table->integer("promo_amount")->unsigned()->default(0);
            $table->integer("subtotal")->unsigned()->default(0);

            $table->integer("refunded_amount")->unsigned()->default(0);

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
        Schema::dropIfExists('sale_transactions');
    }
};
