<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pos_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class);

            $table->boolean("calculate_tax_after_discount")->default(true);
            $table->boolean("calculate_service_charge_after_discount")->default(true);
            $table->boolean("show_zero_amount_on_closing")->default(false);
            $table->boolean("auto_fill_on_closing")->default(true);
            $table->boolean("show_detail_tax_on_closing")->default(true);
            $table->boolean("show_detaill_product_on_closing")->default(true);
            $table->boolean("group_product_by_category_on_closing")->default(true);
            $table->boolean("round_service_charge")->default(false);
            $table->boolean("round_tax")->default(false);
            $table->boolean("allow_merge_order")->default(true);
            $table->boolean("allow_split_order")->default(true);
            $table->boolean("print_cancelled_item")->default(true);
            $table->boolean("no_outstanding_order")->default(false);
            $table->boolean("show_modifier")->default(true);
            $table->boolean("group_product_by_category")->default(true);
            $table->boolean("group_product_by_catalogue")->default(true);
            $table->boolean("show_tax_inclusive")->default(true);

            $table->string("sales_prefix")->default('P');
            $table->string("order_prefix")->default('P');

            $table->enum("rounding_config", ['0.1', '1', '10', '50', '100', '1000']);
            $table->enum("rounding_type", ['up', 'down', 'natural']);
            $table->enum("service_charge_label", ['Service Charge', 'SC']);

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
        Schema::dropIfExists('pos_settings');
    }
};
