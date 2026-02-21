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
        //
        Schema::table('customers', function (Blueprint $table) {
            //
            if (Schema::hasIndex('customers', ['email'], 'unique')) {
                $table->dropUnique(['email']);
            }

            if (Schema::hasIndex('customers', ['phone_number', 'phone_number_country_code'], 'unique')) {
                $table->dropUnique(['phone_number', 'phone_number_country_code']);
            }

            if (!Schema::hasIndex('customers', ['email', 'entity_id'], 'unique')) {
                $table->unique(['email', 'entity_id']);
            }

            if (!Schema::hasIndex('customers', 'uq_phone_number', 'unique')) {
                $table->unique(['phone_number', 'phone_number_country_code', 'entity_id'], 'uq_phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('customers', function (Blueprint $table) {
            //
            // $table->dropUnique(['email', 'entity_id']);
            // $table->dropUnique(['phone_number', 'phone_number_country_code', 'entity_id']);

            // $table->unique(['email']);
            // $table->unique(['phone_number', 'phone_number_country_code']);
        });
    }
};
