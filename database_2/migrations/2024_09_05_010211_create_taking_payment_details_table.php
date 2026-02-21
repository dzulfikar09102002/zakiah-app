<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Taking;
use App\Models\PaymentMethod;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taking_payment_details', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Taking::class);
            $table->foreignIdFor(PaymentMethod::class);

            $table->integer("sales_amount");
            $table->integer("sales_count");

            $table->integer("refund_amount");
            $table->integer("refund_count");

            $table->integer("counted_amount");
            $table->integer("recorded_amount");
            $table->integer("difference_amount");

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
        Schema::dropIfExists('taking_payment_details');
    }
};
