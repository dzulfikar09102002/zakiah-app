-- =============================================================================
-- Sesi Stok Opname (scan barcode, banyak karyawan)
-- Target : MySQL 8.4, dijalankan manual (tidak lewat migration Laravel).
-- Urutan : jalankan dari atas ke bawah. Rollback ada di bagian paling bawah.
--
-- Status sesi TIDAK disimpan, dihitung dari tanggal:
--   start_at kosong                     = Dijadwalkan
--   start_at terisi, review_at kosong   = Mulai
--   review_at terisi, end_at kosong     = Review
--   end_at terisi                       = Selesai
--
-- Tabel scan, review, dan revisi bersifat insert-only (tidak ada kolom upd_*).
-- =============================================================================

CREATE TABLE `product_opname_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_id` BIGINT UNSIGNED NOT NULL,
  `location_id` BIGINT UNSIGNED NOT NULL COMMENT 'Toko yang di-SO',
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 = SO, 2 = Revisi SO',
  `parent_session_id` BIGINT UNSIGNED NULL COMMENT 'Sesi SO asal, wajib diisi untuk Revisi SO',
  `code` VARCHAR(255) NOT NULL COMMENT 'Nomor dokumen, dipakai di laporan dan QR',
  `opname_date` DATE NOT NULL COMMENT 'Tanggal SO',
  `pic_employee_id` BIGINT UNSIGNED NOT NULL COMMENT 'PJ SO',
  `start_at` DATETIME NULL COMMENT 'Sesi dimulai',
  `review_at` DATETIME NULL COMMENT 'Scan ditutup dan review dimulai',
  `end_at` DATETIME NULL COMMENT 'Sesi diakhiri',
  `product_opname_service_id` BIGINT UNSIGNED NULL COMMENT 'Dokumen opname, dibuat saat centang pertama',
  `product_adjustment_stock_id` BIGINT UNSIGNED NULL COMMENT 'Dokumen penyesuaian untuk Revisi SO',
  `note` TEXT NULL,
  `ins_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ins_by` BIGINT UNSIGNED NOT NULL COMMENT 'employees.id',
  `upd_date` DATETIME NULL,
  `upd_by` BIGINT UNSIGNED NULL COMMENT 'employees.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_opname_sessions_entity_code_unique` (`entity_id`, `code`),
  KEY `product_opname_sessions_location_date_index` (`location_id`, `opname_date`),
  KEY `product_opname_sessions_parent_index` (`parent_session_id`),
  KEY `product_opname_sessions_pic_index` (`pic_employee_id`),
  KEY `product_opname_sessions_service_index` (`product_opname_service_id`),
  KEY `product_opname_sessions_adjustment_index` (`product_adjustment_stock_id`),
  KEY `product_opname_sessions_ins_by_index` (`ins_by`),
  KEY `product_opname_sessions_upd_by_index` (`upd_by`),
  CONSTRAINT `product_opname_sessions_entity_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`),
  CONSTRAINT `product_opname_sessions_location_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `product_opname_sessions_parent_foreign` FOREIGN KEY (`parent_session_id`) REFERENCES `product_opname_sessions` (`id`),
  CONSTRAINT `product_opname_sessions_pic_foreign` FOREIGN KEY (`pic_employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_sessions_service_foreign` FOREIGN KEY (`product_opname_service_id`) REFERENCES `product_opname_services` (`id`),
  CONSTRAINT `product_opname_sessions_adjustment_foreign` FOREIGN KEY (`product_adjustment_stock_id`) REFERENCES `product_adjustment_stocks` (`id`),
  CONSTRAINT `product_opname_sessions_ins_by_foreign` FOREIGN KEY (`ins_by`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_sessions_upd_by_foreign` FOREIGN KEY (`upd_by`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_sessions_type_check` CHECK (`type` IN (1, 2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesi stok opname';

CREATE TABLE `product_opname_session_employees` (
  `session_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `ins_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ins_by` BIGINT UNSIGNED NOT NULL COMMENT 'employees.id',
  PRIMARY KEY (`session_id`, `employee_id`),
  KEY `product_opname_session_employees_employee_index` (`employee_id`),
  KEY `product_opname_session_employees_ins_by_index` (`ins_by`),
  CONSTRAINT `product_opname_session_employees_session_foreign` FOREIGN KEY (`session_id`) REFERENCES `product_opname_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_opname_session_employees_employee_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_session_employees_ins_by_foreign` FOREIGN KEY (`ins_by`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Karyawan yang boleh masuk ke sesi';

CREATE TABLE `product_opname_session_scans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL COMMENT 'ID dari perangkat, mencegah scan terhitung dua kali saat kirim ulang',
  `session_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `barcode` VARCHAR(255) NOT NULL COMMENT 'Kode yang terbaca saat scan',
  `qty` INT NOT NULL DEFAULT 1 COMMENT 'Jumlah; pembatalan scan memakai nilai minus',
  `recorded_stock` INT NOT NULL COMMENT 'Stok sistem saat scan; yang dipakai adalah baris pertama per produk',
  `ins_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ins_by` BIGINT UNSIGNED NOT NULL COMMENT 'employees.id, karyawan yang scan',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_opname_session_scans_uuid_unique` (`uuid`),
  KEY `product_opname_session_scans_session_product_index` (`session_id`, `product_id`),
  KEY `product_opname_session_scans_session_ins_by_index` (`session_id`, `ins_by`),
  KEY `product_opname_session_scans_product_index` (`product_id`),
  KEY `product_opname_session_scans_ins_by_index` (`ins_by`),
  CONSTRAINT `product_opname_session_scans_session_foreign` FOREIGN KEY (`session_id`) REFERENCES `product_opname_sessions` (`id`),
  CONSTRAINT `product_opname_session_scans_product_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_opname_session_scans_ins_by_foreign` FOREIGN KEY (`ins_by`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_session_scans_qty_check` CHECK (`qty` <> 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Setiap scan satu baris, jumlah dihitung dengan SUM';

CREATE TABLE `product_opname_session_reviews` (
  `session_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `recorded_stock` INT NOT NULL COMMENT 'Stok sistem saat diputuskan',
  `counted_stock` INT NOT NULL COMMENT 'Stok SO; 0 bila barang tidak ter-scan',
  `difference_stock` INT NOT NULL COMMENT 'counted_stock - recorded_stock',
  `decision` TINYINT UNSIGNED NOT NULL COMMENT '1 = Terima revisi (stok berubah), 2 = Tolak (stok tetap)',
  `product_opname_service_detail_id` BIGINT UNSIGNED NULL COMMENT 'Baris opname yang menggerakkan stok, terisi bila diterima',
  `note` TEXT NULL,
  `ins_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ins_by` BIGINT UNSIGNED NOT NULL COMMENT 'employees.id, PJ yang memutuskan',
  PRIMARY KEY (`session_id`, `product_id`),
  KEY `product_opname_session_reviews_product_index` (`product_id`),
  KEY `product_opname_session_reviews_detail_index` (`product_opname_service_detail_id`),
  KEY `product_opname_session_reviews_ins_by_index` (`ins_by`),
  CONSTRAINT `product_opname_session_reviews_session_foreign` FOREIGN KEY (`session_id`) REFERENCES `product_opname_sessions` (`id`),
  CONSTRAINT `product_opname_session_reviews_product_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_opname_session_reviews_detail_foreign` FOREIGN KEY (`product_opname_service_detail_id`) REFERENCES `product_opname_service_details` (`id`),
  CONSTRAINT `product_opname_session_reviews_ins_by_foreign` FOREIGN KEY (`ins_by`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_session_reviews_decision_check` CHECK (`decision` IN (1, 2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Keputusan final PJ per produk; baris cocok tidak dicatat di sini';

CREATE TABLE `product_opname_session_revisions` (
  `session_id` BIGINT UNSIGNED NOT NULL COMMENT 'Sesi dengan type = 2',
  `product_id` BIGINT UNSIGNED NOT NULL,
  `seq` INT UNSIGNED NOT NULL,
  `adjustment_qty` INT NOT NULL COMMENT 'Penyesuaian stok: plus menambah, minus mengurangi, 0 tanpa perubahan',
  `reason` TINYINT UNSIGNED NOT NULL COMMENT '1 = Ketelisut, 2 = Rusak, 3 = Hilang',
  `product_adjustment_stock_detail_id` BIGINT UNSIGNED NULL COMMENT 'Baris penyesuaian yang menggerakkan stok',
  `note` TEXT NULL,
  `ins_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ins_by` BIGINT UNSIGNED NOT NULL COMMENT 'employees.id',
  PRIMARY KEY (`session_id`, `product_id`, `seq`),
  KEY `product_opname_session_revisions_product_index` (`product_id`),
  KEY `product_opname_session_revisions_detail_index` (`product_adjustment_stock_detail_id`),
  KEY `product_opname_session_revisions_ins_by_index` (`ins_by`),
  CONSTRAINT `product_opname_session_revisions_session_foreign` FOREIGN KEY (`session_id`) REFERENCES `product_opname_sessions` (`id`),
  CONSTRAINT `product_opname_session_revisions_product_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_opname_session_revisions_detail_foreign` FOREIGN KEY (`product_adjustment_stock_detail_id`) REFERENCES `product_adjustment_stock_details` (`id`),
  CONSTRAINT `product_opname_session_revisions_ins_by_foreign` FOREIGN KEY (`ins_by`) REFERENCES `employees` (`id`),
  CONSTRAINT `product_opname_session_revisions_reason_check` CHECK (`reason` IN (1, 2, 3))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hasil pencarian barang pada Revisi SO';

-- =============================================================================
-- ROLLBACK: hapus tanda komentar bila perlu. Semua data sesi SO ikut terhapus.
-- =============================================================================
-- DROP TABLE IF EXISTS `product_opname_session_revisions`;
-- DROP TABLE IF EXISTS `product_opname_session_reviews`;
-- DROP TABLE IF EXISTS `product_opname_session_scans`;
-- DROP TABLE IF EXISTS `product_opname_session_employees`;
-- DROP TABLE IF EXISTS `product_opname_sessions`;
