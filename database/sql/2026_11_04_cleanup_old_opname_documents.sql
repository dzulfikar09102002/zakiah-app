-- =============================================================================
-- Membersihkan dokumen stok opname sistem lama yang tidak pernah menggerakkan stok.
--
-- Yang dihapus (semua syarat harus terpenuhi):
--   1. Status 'requested' atau 'rejected'
--   2. Tidak terhubung ke sesi SO baru
--   3. Tidak punya gerakan stok di product_stock_movements
--
-- Yang TIDAK disentuh: dokumen 'approved', sesi SO baru, stok, dan kartu stok.
-- Jalankan bagian PRATINJAU dulu. Backup dulu kalau ini database VPS, karena
-- data yang terhapus tidak bisa dikembalikan.
-- =============================================================================

-- PRATINJAU: dokumen yang akan dihapus.
SELECT o.`id`, o.`entity_id`, o.`code`, o.`status`, o.`created_at`,
  (SELECT COUNT(*) FROM `product_opname_service_details` d WHERE d.`product_opname_service_id` = o.`id`) AS jumlah_detail
FROM `product_opname_services` o
WHERE o.`status` IN ('requested', 'rejected')
  AND NOT EXISTS (SELECT 1 FROM `product_opname_sessions` s WHERE s.`product_opname_service_id` = o.`id`)
  AND NOT EXISTS (
    SELECT 1 FROM `product_opname_service_details` d
    JOIN `product_stock_movements` m ON m.`resource_id` = d.`id`
      AND m.`resource_type` = 'App\\Models\\ProductOpnameServiceDetail'
    WHERE d.`product_opname_service_id` = o.`id`
  )
ORDER BY o.`id`;

START TRANSACTION;

-- Tabel detail tidak punya foreign key ke dokumen, jadi dihapus lebih dulu secara eksplisit.
DELETE d FROM `product_opname_service_details` d
JOIN `product_opname_services` o ON o.`id` = d.`product_opname_service_id`
WHERE o.`status` IN ('requested', 'rejected')
  AND NOT EXISTS (SELECT 1 FROM `product_opname_sessions` s WHERE s.`product_opname_service_id` = o.`id`)
  AND NOT EXISTS (
    SELECT 1 FROM (
      SELECT x.`product_opname_service_id` AS service_id
      FROM `product_opname_service_details` x
      JOIN `product_stock_movements` m ON m.`resource_id` = x.`id`
        AND m.`resource_type` = 'App\\Models\\ProductOpnameServiceDetail'
    ) moved WHERE moved.service_id = o.`id`
  );

DELETE o FROM `product_opname_services` o
WHERE o.`status` IN ('requested', 'rejected')
  AND NOT EXISTS (SELECT 1 FROM `product_opname_sessions` s WHERE s.`product_opname_service_id` = o.`id`)
  AND NOT EXISTS (
    SELECT 1 FROM `product_opname_service_details` d
    JOIN `product_stock_movements` m ON m.`resource_id` = d.`id`
      AND m.`resource_type` = 'App\\Models\\ProductOpnameServiceDetail'
    WHERE d.`product_opname_service_id` = o.`id`
  );

COMMIT;

-- Cek hasil: dokumen yang tersisa.
SELECT o.`id`, o.`code`, o.`status`, s.`code` AS sesi
FROM `product_opname_services` o
LEFT JOIN `product_opname_sessions` s ON s.`product_opname_service_id` = o.`id`
ORDER BY o.`id`;
