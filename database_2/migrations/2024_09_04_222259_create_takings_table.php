<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Location;
use App\Models\Device;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('takings', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Device::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(Entity::class);
            $table->bigInteger("checkpoint_device_id")->unsigned()->nullable();
            $table->foreign("checkpoint_device_id")->references("id")->on("devices");

            $table->bigInteger("parent_id")->unsigned()->nullable();
            $table->foreign("parent_id")->references("id")->on("takings");

            $table->bigInteger("sale_reference_id")->unsigned()->nullable();
            // $table->foreign("sale_reference_id")->references("id")->on("sale_transactions");

            $table->timestamp('taking_at', precision: 6)->nullable();
            $table->timestamp('local_taking_at', precision: 6)->nullable();

            $table->boolean('is_shift')->default(false);
            $table->tinyInteger('shift_number')->nullable();
            $table->timestamp('last_taking_at')->nullable();

            $table->integer("counted_amount");
            $table->integer("recorded_amount");
            $table->integer("difference_amount");

            $table->integer("sales_count");
            $table->integer("refund_count");

            $table->integer("gross_sales");
            $table->integer("gross_refund");
            $table->integer("discount_amount");
            $table->integer("discount_amount_refund");
            $table->integer("surcharge_amount");
            $table->integer("surcharge_amount_refund");
            $table->integer("promo_amount");
            $table->integer("promo_amount_refund");
            $table->integer("free_of_charge_amount");
            $table->integer("free_of_charge_amount_refund");
            $table->integer("net_sales");
            $table->integer("net_sales_refund");
            $table->integer("service_charge");
            $table->integer("service_charge_refund");
            $table->integer("tax_amount");
            $table->integer("tax_amount_refund");
            $table->integer("rounding_amount");
            $table->integer("rounding_amount_refund");
            $table->integer("net_sales_after_tax");
            $table->integer("net_sales_after_tax_refund");

            $table->integer("money_movement_in_amount");
            $table->integer("money_movement_in_count");
            $table->integer("money_movement_out_amount");
            $table->integer("money_movement_out_count");

            $table->integer("customer_deposit_amount");
            $table->integer("customer_deposit_count");

            $table->integer("product_sold_count");
            $table->integer("product_category_sold_count");

            $table->integer("product_return_count");
            $table->integer("product_category_return_count");

            $table->json("sale_transaction_ids");
            $table->json("sale_refund_ids");
            
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
        Schema::dropIfExists('takings');
    }
};
