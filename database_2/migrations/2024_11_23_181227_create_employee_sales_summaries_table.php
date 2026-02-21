<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_sales_summaries', function (Blueprint $table) {
            $table->id();

            $table->bigInteger("employee_sales_id")->unsigned();
            $table->string('employee_sales_name');

            $table->bigInteger("location_id")->unsigned();
            $table->string('location_name');

            $table->string('local_sales_date');

            $table->bigInteger("sales_amount")->unsigned();
            $table->bigInteger("refund_amount")->unsigned();
            $table->bigInteger("net_sales_amount")->unsigned(); # sale_amt - refund_amt

            $table->bigInteger("sales_count")->unsigned();
            $table->bigInteger("refund_count")->unsigned();
            $table->bigInteger("net_count")->unsigned();

            $table->bigInteger("sales_quantity")->unsigned();
            $table->bigInteger("refund_quantity")->unsigned();
            $table->bigInteger("net_quantity")->unsigned();

            $table->timestamps();

            // $table->foreign("employee_sales_id")->references("id")->on("employees");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_sales_summaries');
    }
};
