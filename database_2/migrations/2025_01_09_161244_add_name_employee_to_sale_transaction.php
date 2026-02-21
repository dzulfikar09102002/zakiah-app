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
        Schema::table('sale_transactions', function (Blueprint $table) {
            //
            $table->string('employee_sales_first_name');
            $table->string('employee_sales_last_name');

            $table->string('cashier_first_name');
            $table->string('cashier_last_name');

            $table->string('receive_paid_by_first_name')->nullable();
            $table->string('receive_paid_by_last_name')->nullable();

            $table->string('paid_by_first_name')->nullable();
            $table->string('paid_by_last_name')->nullable();

            $table->string('void_by_first_name')->nullable();
            $table->string('void_by_last_name')->nullable();
            
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_phone_number')->nullable();
            $table->string('customer_phone_number_country_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_transactions', function (Blueprint $table) {
            //
            $table->dropColumn('employee_sales_first_name');
            $table->dropColumn('employee_sales_last_name');

            $table->dropColumn('cashier_first_name');
            $table->dropColumn('cashier_last_name');

            $table->dropColumn('receive_paid_by_first_name');
            $table->dropColumn('receive_paid_by_last_name');

            $table->dropColumn('paid_by_first_name');
            $table->dropColumn('paid_by_last_name');

            $table->dropColumn('void_by_first_name');
            $table->dropColumn('void_by_last_name');

            $table->dropColumn('customer_first_name');
            $table->dropColumn('customer_last_name');
            $table->dropColumn('customer_phone_number');
            $table->dropColumn('customer_phone_number_country_code');
        });
    }
};
