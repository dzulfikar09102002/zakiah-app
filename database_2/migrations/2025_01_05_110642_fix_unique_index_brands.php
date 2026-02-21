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
        Schema::table('brands', function (Blueprint $table) {
            //
            $table->dropUnique(['code']);
            $table->dropUnique(['initial']);

            $table->unique(['code', 'entity_id'], 'uq_code_entity');
            $table->unique(['initial', 'entity_id'], 'uq_initial_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('brands', function (Blueprint $table) {
            //
            // $table->unique(['code']);
            // $table->unique(['initial']);

            // $table->dropUnique('uq_code_entity');
            // $table->dropUnique('uq_initial_entity');
        });
    }
};
