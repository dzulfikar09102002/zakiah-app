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
        Schema::table('product_stock_movements', function (Blueprint $table) {
            //
            $table->index(['product_id', 'resource_type', 'original_stock_in', 'original_stock_out'], 'idx_cogs_calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            //
            $table->dropIndex('idx_cogs_calculation');
        });
    }
};
