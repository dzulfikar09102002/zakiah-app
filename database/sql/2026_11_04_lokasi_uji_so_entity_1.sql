-- =============================================================================
-- DATA UJI, KHUSUS DATABASE LOKAL. Jangan dijalankan di VPS.
-- Toko baru di entity_id 1 berisi 4 produk yang tadi di-scan pada sesi SO-261104-PQML7,
-- supaya alur tutup SO dan cetak laporan bisa dicoba dengan daftar review yang pendek.
--
--   32586 Celana CJ536AB  stok 2
--   32909 Celana CJ933YY  stok 2
--   33118 Celana CJ034MT  stok 1
--   33119 Celana CJ044MT  stok 1
-- =============================================================================

START TRANSACTION;

INSERT INTO `locations`
  (`entity_id`, `code`, `initial`, `name`, `search_name`, `kind`, `warehouse`, `timezone`,
   `allow_transfer_stock`, `allow_external_supplier`, `franchise`, `status`, `created_at`, `updated_at`, `checksum`)
VALUES
  (1, 'UJI-SO-1', 'UJISO', 'TOKO UJI SO', 'toko uji so', 'outlet', 0, 'Asia/Jakarta',
   1, 1, 0, 'active', NOW(), NOW(), MD5('uji-so-entity-1'));

SET @lokasi_uji_so = LAST_INSERT_ID();

INSERT INTO `product_location_stocks`
  (`product_id`, `location_id`, `product_unit_id`, `stock`, `last_buy_price`, `average_buy_price`,
   `lowest_buy_price`, `highest_buy_price`, `created_at`, `updated_at`, `checksum`)
SELECT
  p.`id`, @lokasi_uji_so, p.`product_unit_id`,
  CASE WHEN p.`id` IN (32586, 32909) THEN 2 ELSE 1 END,
  p.`last_buying_price`, p.`last_buying_price`, p.`last_buying_price`, p.`last_buying_price`,
  NOW(), NOW(), MD5(CONCAT('uji-so-', @lokasi_uji_so, '-', p.`id`))
FROM `products` p
WHERE p.`entity_id` = 1
  AND p.`id` IN (32586, 32909, 33118, 33119);

COMMIT;

-- Cek hasil: harus 4 baris.
SELECT l.`name` AS toko, p.`name` AS produk, p.`barcode`, s.`stock`
FROM `product_location_stocks` s
JOIN `locations` l ON l.`id` = s.`location_id`
JOIN `products` p ON p.`id` = s.`product_id`
WHERE l.`code` = 'UJI-SO-1'
ORDER BY p.`id`;

-- =============================================================================
-- HAPUS DATA UJI: bisa dipakai selama belum ada sesi SO di toko ini. Kalau sudah ada,
-- gerakan stok dan dokumen hasil SO ikut tercatat di data lokal dan perlu dibersihkan dulu.
-- =============================================================================
-- DELETE s FROM `product_location_stocks` s JOIN `locations` l ON l.`id` = s.`location_id` WHERE l.`code` = 'UJI-SO-1';
-- DELETE FROM `locations` WHERE `code` = 'UJI-SO-1';
