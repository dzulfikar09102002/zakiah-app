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
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropUnique(['code']);
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);

            $table->unique(['code', 'entity_id'], 'uq_code_entity');
            $table->unique(['sku', 'entity_id'], 'uq_sku_entity');
            $table->unique(['barcode', 'entity_id'], 'uq_barcode_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('products', function (Blueprint $table) {
            //
            // $table->unique(['code']);
            // $table->unique(['sku']);
            // $table->unique(['barcode']);

            // $table->dropUnique('uq_code_entity');
            // $table->dropUnique('uq_sku_entity');
            // $table->dropUnique('uq_barcode_entity');
        });
    }
};
