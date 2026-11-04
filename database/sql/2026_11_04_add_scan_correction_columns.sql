-- =============================================================================
-- Koreksi scan khusus PJ.
-- Scan yang salah tidak dihapus dan tidak diubah: PJ menambah satu baris qty -1 yang
-- menunjuk scan yang dibatalkan, beserta alasannya. UNIQUE memastikan satu scan
-- hanya bisa dibatalkan sekali.
--
-- Target : MySQL 8.4, dijalankan manual SEBELUM kode baru dipakai.
-- =============================================================================

ALTER TABLE `product_opname_session_scans`
  ADD COLUMN `cancelled_scan_id` BIGINT UNSIGNED NULL COMMENT 'Scan yang dibatalkan oleh baris koreksi ini' AFTER `recorded_stock`,
  ADD COLUMN `reason` VARCHAR(255) NULL COMMENT 'Alasan koreksi dari PJ' AFTER `cancelled_scan_id`,
  ADD UNIQUE KEY `product_opname_session_scans_cancelled_unique` (`cancelled_scan_id`),
  ADD CONSTRAINT `product_opname_session_scans_cancelled_foreign` FOREIGN KEY (`cancelled_scan_id`) REFERENCES `product_opname_session_scans` (`id`);

-- =============================================================================
-- ROLLBACK: hapus tanda komentar bila perlu. Hapus dulu baris koreksi (qty < 0)
-- supaya jumlah hasil scan kembali seperti sebelum dikoreksi.
-- =============================================================================
-- DELETE FROM `product_opname_session_scans` WHERE `cancelled_scan_id` IS NOT NULL;
-- ALTER TABLE `product_opname_session_scans`
--   DROP FOREIGN KEY `product_opname_session_scans_cancelled_foreign`,
--   DROP INDEX `product_opname_session_scans_cancelled_unique`,
--   DROP COLUMN `reason`,
--   DROP COLUMN `cancelled_scan_id`;
