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
        Schema::table('takings', function (Blueprint $table) {
            //
            $table->bigInteger("employee_id")->unsigned()->nullable();
            $table->longText('employee_first_name');
            $table->longText('employee_last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('takings', function (Blueprint $table) {
            //
            $table->dropColumn('employee_id');
            $table->dropColumn('employee_first_name');
            $table->dropColumn('employee_last_name');
        });
    }
};
