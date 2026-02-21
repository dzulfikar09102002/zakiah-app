<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Location;
use App\Models\Device;
use App\Models\OrderType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(Device::class);
            $table->foreignIdFor(OrderType::class)->nullable();
            $table->foreignIdFor(Customer::class)->nullable();

            $table->bigInteger("checkpoint_device_id")->unsigned();
            $table->foreign("checkpoint_device_id")->references("id")->on("devices");

            $table->string("code")->unique();
            $table->enum("status", ['initiate', 'confirmed', 'paid', 'order_placed', 'want_to_pay_cash']);

            $table->json("product_ids")->nullable();
            $table->json("product_category_ids")->nullable();
            $table->json("modifier_ids")->nullable();
            $table->json("modifier_option_ids")->nullable();

            $table->integer("subtotal")->unsigned();
            $table->integer("tax_inclusive_amount")->unsigned();
            $table->integer("tax_exclusive_amount")->unsigned();
            $table->integer("service_charge")->unsigned();
            $table->integer("promo_amount")->unsigned();
            $table->integer("discount_amount")->unsigned();
            $table->integer("surcharge_amount")->unsigned();
            $table->integer("free_of_charge_amount")->unsigned();
            $table->integer("rounding_amount")->unsigned();
            $table->integer("payment_platform_fee")->unsigned();
            $table->integer("platform_fee")->unsigned();
            $table->integer("delivery_fee")->unsigned();
            $table->integer("promo_delivery_fee")->unsigned();
            $table->integer("total_amount")->unsigned();

            $table->integer("service_charge_rate")->unsigned()->default(0);
            $table->boolean("service_charge_include_tax")->default(true);

            $table->timestamp('paid_at')->nullable();
            $table->bigInteger("paid_by")->unsigned()->nullable();
            $table->foreign("paid_by")->references("id")->on("users");
            $table->longText("notes")->nullable();
            $table->json("adjustment")->nullable();

            $table->timestamps();
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            # index
            $table->index(['entity_id', 'location_id', 'device_id', 'checkpoint_device_id', 'order_type_id'], 'idx_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};
