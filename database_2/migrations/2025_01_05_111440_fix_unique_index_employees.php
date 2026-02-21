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
        Schema::table('employees', function (Blueprint $table) {
            //
            if (Schema::hasIndex('employees', ['code'], 'unique')) {
                $table->dropUnique(['code']);
            }

            if (!Schema::hasIndex('employees', ['code', 'entity_id'], 'unique')) {
                $table->unique(['code', 'entity_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
