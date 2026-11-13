<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index untuk filter laporan & daftar yang sebelumnya full table scan (lihat EXPLAIN).
 * Tabel-tabel ini milik database bersama (zakiah-app + NewZakicaPOS), jadi migration ini
 * hanya MENAMBAH index: dilewati bila tabel tidak ada atau index sudah ada, dan di MySQL
 * dibuat online (ALGORITHM=INPLACE, LOCK=NONE) agar POS tetap bisa menulis selama proses.
 */
return new class extends Migration
{
    /**
     * @var array<string, array<string, array<int, string>>>
     */
    private const INDEXES = [
        // Laporan penjualan per produk / per lokasi (filter lokasi + tanggal).
        'sale_transaction_details' => ['idx_zk_std_location_sales_at' => ['location_id', 'local_sales_at']],
        // Laporan Penjualan & Transaksi Penjualan (filter entity + tanggal).
        'sale_transactions' => ['idx_zk_st_entity_sales_at' => ['entity_id', 'local_sales_at']],
        // Eager load pembayaran & promo per nota.
        'sale_transaction_payments' => ['idx_zk_stp_sale_transaction' => ['sale_transaction_id']],
        'sale_transaction_promos' => ['idx_zk_stpr_sale_transaction' => ['sale_transaction_id']],
        // Total stok per produk (daftar Produk, laporan aset).
        'product_location_stocks' => ['idx_zk_pls_product_location' => ['product_id', 'location_id']],
        // Laporan pergerakan stok (filter lokasi + tanggal).
        'product_stock_movements' => ['idx_zk_psm_location_created' => ['location_id', 'created_at']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name)) {
                    continue;
                }

                if (DB::getDriverName() === 'mysql') {
                    $cols = implode(', ', array_map(fn (string $c) => "`{$c}`", $columns));
                    DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$cols}), ALGORITHM=INPLACE, LOCK=NONE");
                } else {
                    Schema::table($table, fn ($t) => $t->index($columns, $name));
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn ($t) => $t->dropIndex($name));
                }
            }
        }
    }
};
