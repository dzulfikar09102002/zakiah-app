-- =============================================================================
-- Soft delete akun login (modul Akun Login)
-- Target : MySQL 8.4, dijalankan manual (tidak lewat migration Laravel).
--
-- NewZakicaPOS dan pos-secaca login hanya mencari users.email tanpa melihat
-- deleted_at. Jadi saat akun dihapus, aplikasi memindahkan email ke deleted_email
-- dan mengosongkan users.email, sehingga login gagal di ketiga aplikasi tanpa
-- mengubah kode POS. Saat dipulihkan, email dikembalikan dan password lama berlaku.
--
-- Hanya menambah dua kolom nullable; isi data yang ada tidak berubah.
-- =============================================================================

ALTER TABLE `users`
  ADD COLUMN `deleted_email` VARCHAR(255) NULL
    COMMENT 'Email saat akun dihapus, dikembalikan saat dipulihkan'
    AFTER `email`,
  ADD COLUMN `deleted_at` TIMESTAMP NULL
    COMMENT 'Akun login dihapus (soft delete)'
    AFTER `updated_at`,
  ADD KEY `users_deleted_at_index` (`deleted_at`);

-- =============================================================================
-- ROLLBACK (pulihkan dulu email akun yang terhapus sebelum kolom dibuang)
-- =============================================================================
-- UPDATE `users` SET `email` = `deleted_email` WHERE `deleted_at` IS NOT NULL AND `email` IS NULL;
-- ALTER TABLE `users` DROP KEY `users_deleted_at_index`, DROP COLUMN `deleted_at`, DROP COLUMN `deleted_email`;
