<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SaleTransaction;
use App\Models\Device;
use App\Models\Taking;
use App\Models\Employee;
use App\Models\Location;
use App\Models\OrderType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SaleTransaction::class);
            $table->foreignIdFor(Taking::class)->nullable();
            $table->foreignIdFor(Device::class);
            $table->bigInteger("checkpoint_device_id")->unsigned()->nullable();
            $table->foreign("checkpoint_device_id")->references("id")->on("devices");
            $table->foreignIdFor(Employee::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(OrderType::class);
            
            $table->bigInteger("sales_reference_id")->unsigned()->nullable();
            
            $table->longText('employee_first_name');
            $table->longText('employee_last_name');

            $table->longText('location_name');
            $table->longText('location_timezone');
            
            $table->longText('order_type_name');

            $table->string("code");
            $table->enum("status", ['ok', 'void']);

            $table->timestamp('sales_at', precision: 6)->nullable();
            $table->timestamp('local_sales_at', precision: 6)->nullable();

            $table->timestamp('refund_at', precision: 6)->nullable();
            $table->timestamp('local_refund_at', precision: 6)->nullable();
            
            $table->longText("reason")->nullable();
            $table->longText("notes")->nullable();

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
            $table->integer("platform_fee")->unsigned();
            $table->integer("total_processing_fee")->unsigned();
            $table->integer("total_subsidize")->unsigned();

            $table->bigInteger("void_by")->unsigned()->nullable();
            $table->foreign("void_by")->references("id")->on("users");
            $table->timestamp('void_at', precision: 6)->nullable();
            $table->timestamp('local_void_at', precision: 6)->nullable();
            $table->longText('void_reason')->nullable();
            $table->longText('void_notes')->nullable();

            $table->json('product_ids');
            $table->json('product_category_ids');
            $table->json('modifier_ids');
            $table->json('modifier_option_ids');

            $table->integer("discount_amount")->unsigned()->default(0);
            $table->integer("surcharge_amount")->unsigned()->default(0);
            $table->integer("promo_amount")->unsigned()->default(0);
            $table->integer("subtotal")->unsigned()->default(0);

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
        Schema::dropIfExists('sale_refunds');
    }
};
