-- =============================================================================
-- Menu dinamis dan Hak Akses backoffice
-- Target : MySQL 8.4, dijalankan manual (tidak lewat migration Laravel).
-- Urutan : jalankan dari atas ke bawah. Rollback ada di bagian paling bawah.
--
-- Pola mengikuti SurabayaTaxWeb (mn_headermenu, mn_submenu, mn_submenu_group,
-- mn_headermenu_detail, mn_controller_detail, uc_role_detail):
--
--   menu_headers         Header menu            (Laporan, Stok, Administrasi, ...)
--   menu_groups          Group di dalam header  (Penjualan, Stok, CV / Badan Usaha, ...)
--   menu_submenus        Sub menu = satu halaman (Kartu Stok, Karyawan, ...)
--   menu_header_details  Penempatan sub menu di header, opsional di dalam group
--   menu_actions         Aksi per sub menu       (Lihat, Tambah, Ubah, Hapus, ...)
--   menu_action_routes   Route Laravel milik tiap aksi, untuk pengecekan di server
--   access_roles         Hak Akses backoffice per entity (dinamis, dibuat admin)
--   access_role_menu_actions  Centangan aksi per Hak Akses
--   employees.access_role_id  Hak Akses karyawan
--
-- PENTING: Hak Akses TERPISAH dari tabel roles. roles tetap dipakai sebagai jabatan
-- di toko untuk POS (pos-secaca dan NewZakicaPOS mencari Sales lewat id template),
-- jadi tabel roles, employee_locations, dan employees.role_id tidak diubah.
--
-- Data yang ada tidak diubah: script ini hanya membuat tabel baru, menambah satu
-- kolom nullable di employees, dan mengisi data awal menu.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. Header menu
-- -----------------------------------------------------------------------------
CREATE TABLE `menu_headers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL COMMENT 'Kode tetap, dipakai seed dan kode program',
  `section` VARCHAR(20) NOT NULL DEFAULT 'main' COMMENT 'main = menu utama, setting = menu pengaturan di bawah sidebar',
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(100) NULL COMMENT 'Nama ikon lucide-react, mis. Warehouse',
  `route_name` VARCHAR(150) NULL COMMENT 'Diisi bila header langsung membuka satu halaman (mis. Dashboard)',
  `seq` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  `updated_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_headers_code_unique` (`code`),
  KEY `menu_headers_section_seq_index` (`section`, `seq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Header menu sidebar';

-- -----------------------------------------------------------------------------
-- 2. Group di dalam header
-- -----------------------------------------------------------------------------
CREATE TABLE `menu_groups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(100) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  `updated_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_groups_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Group sub menu di dalam header';

-- -----------------------------------------------------------------------------
-- 3. Sub menu (satu halaman)
-- -----------------------------------------------------------------------------
CREATE TABLE `menu_submenus` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(100) NULL,
  `route_name` VARCHAR(150) NOT NULL COMMENT 'Halaman yang dibuka dari sidebar',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  `updated_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_submenus_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sub menu, satu halaman aplikasi';

-- -----------------------------------------------------------------------------
-- 4. Penempatan sub menu di header (opsional di dalam group)
-- -----------------------------------------------------------------------------
CREATE TABLE `menu_header_details` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_header_id` BIGINT UNSIGNED NOT NULL,
  `menu_submenu_id` BIGINT UNSIGNED NOT NULL,
  `menu_group_id` BIGINT UNSIGNED NULL COMMENT 'Kosong = langsung di bawah header',
  `seq` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Urutan di dalam header; group ikut urutan sub menu pertamanya',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_header_details_header_submenu_unique` (`menu_header_id`, `menu_submenu_id`),
  KEY `menu_header_details_submenu_index` (`menu_submenu_id`),
  KEY `menu_header_details_group_index` (`menu_group_id`),
  CONSTRAINT `menu_header_details_header_fk` FOREIGN KEY (`menu_header_id`) REFERENCES `menu_headers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_header_details_submenu_fk` FOREIGN KEY (`menu_submenu_id`) REFERENCES `menu_submenus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_header_details_group_fk` FOREIGN KEY (`menu_group_id`) REFERENCES `menu_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Isi header: sub menu dan group-nya';

-- -----------------------------------------------------------------------------
-- 5. Aksi per sub menu
-- -----------------------------------------------------------------------------
CREATE TABLE `menu_actions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_submenu_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(30) NOT NULL COMMENT 'view, create, update, delete, restore, approve, cancel, activate, import, export, void, scan',
  `name` VARCHAR(100) NOT NULL COMMENT 'Label di matriks Hak Akses',
  `seq` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_actions_submenu_code_unique` (`menu_submenu_id`, `code`),
  CONSTRAINT `menu_actions_submenu_fk` FOREIGN KEY (`menu_submenu_id`) REFERENCES `menu_submenus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Aksi yang bisa diberikan per sub menu';

-- -----------------------------------------------------------------------------
-- 6. Route Laravel per aksi. Satu route hanya milik satu aksi.
--    Route yang tidak terdaftar di sini (profil, logout, dropdown bersama) bebas untuk
--    semua yang login; daftarnya ada di kode program, bukan di tabel.
-- -----------------------------------------------------------------------------
CREATE TABLE `menu_action_routes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_action_id` BIGINT UNSIGNED NOT NULL,
  `route_name` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_action_routes_route_unique` (`route_name`),
  KEY `menu_action_routes_action_index` (`menu_action_id`),
  CONSTRAINT `menu_action_routes_action_fk` FOREIGN KEY (`menu_action_id`) REFERENCES `menu_actions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pemetaan route Laravel ke aksi menu';

-- -----------------------------------------------------------------------------
-- 7. Hak Akses backoffice per entity
-- -----------------------------------------------------------------------------
CREATE TABLE `access_roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `note` VARCHAR(255) NULL,
  `default_for_role_id` BIGINT UNSIGNED NULL COMMENT 'roles.id template (Entity Owner .. Sales). Dipakai karyawan yang access_role_id-nya kosong, sesuai role utamanya',
  `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = bawaan, tidak bisa dihapus (centangan tetap bisa diubah)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  `updated_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `access_roles_entity_name_unique` (`entity_id`, `name`),
  UNIQUE KEY `access_roles_entity_default_unique` (`entity_id`, `default_for_role_id`),
  KEY `access_roles_entity_index` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hak Akses backoffice, dibuat per entity';

CREATE TABLE `access_role_menu_actions` (
  `access_role_id` BIGINT UNSIGNED NOT NULL,
  `menu_action_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL COMMENT 'users.id',
  PRIMARY KEY (`access_role_id`, `menu_action_id`),
  KEY `access_role_menu_actions_action_index` (`menu_action_id`),
  CONSTRAINT `access_role_menu_actions_role_fk` FOREIGN KEY (`access_role_id`) REFERENCES `access_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `access_role_menu_actions_action_fk` FOREIGN KEY (`menu_action_id`) REFERENCES `menu_actions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Aksi yang dicentang per Hak Akses';

-- -----------------------------------------------------------------------------
-- 8. Hak Akses karyawan. Nullable dan tanpa foreign key, supaya aplikasi lama dan
--    NewZakicaPOS (EF Core) tidak terpengaruh; kolom tambahan diabaikan keduanya.
-- -----------------------------------------------------------------------------
ALTER TABLE `employees`
  ADD COLUMN `access_role_id` BIGINT UNSIGNED NULL
    COMMENT 'access_roles.id. Kosong = pakai Hak Akses bawaan sesuai role utama (employees.role_id)'
    AFTER `role_id`,
  ADD KEY `employees_access_role_index` (`access_role_id`);

-- =============================================================================
-- DATA AWAL: menu sama dengan sidebar sekarang (resources/js/lib/nav-items.ts)
-- =============================================================================

INSERT INTO `menu_headers` (`code`, `section`, `name`, `icon`, `route_name`, `seq`) VALUES
  ('dashboard',      'main',    'Dashboard',      'LayoutGrid',          'dashboard.index', 10),
  ('laporan',        'main',    'Laporan',        'ChartNoAxesCombined', NULL,              20),
  ('produk',         'main',    'Produk',         'ScanBarcode',         NULL,              30),
  ('stok',           'main',    'Stok',           'Warehouse',           NULL,              40),
  ('people',         'main',    'People',         'UsersRound',          NULL,              50),
  ('revenue',        'main',    'Revenue Center', 'CircleDollarSign',    NULL,              60),
  ('administrasi',   'main',    'Administrasi',   'Cog',                 NULL,              70),
  ('sistem',         'setting', 'Sistem',         'MonitorCog',          NULL,              10),
  ('konfigurasi',    'setting', 'Konfigurasi',    'UserCog',             NULL,              20);

INSERT INTO `menu_groups` (`code`, `name`) VALUES
  ('laporan-penjualan', 'Penjualan'),
  ('laporan-stok',      'Stok'),
  ('laporan-sales',     'Perfoma Sales'),
  ('laporan-pelanggan', 'Pelanggan'),
  ('people-pelanggan',  'Pelanggan'),
  ('revenue-penjualan', 'Penjualan'),
  ('admin-cv',          'CV / Badan Usaha');

INSERT INTO `menu_submenus` (`code`, `name`, `route_name`) VALUES
  ('dashboard',                'Dashboard',          'dashboard.index'),
  ('report-sales',             'Per Transaksi',      'report-sales.index'),
  ('report-by-products',       'Per Produk',         'report-by-products.index'),
  ('report-by-locations',      'Per Toko',           'report-by-locations.index'),
  ('report-stock-card',        'Kartu Stok',         'report-stock-card.index'),
  ('report-stock-movement',    'Pergerakan',         'report-stock-movement.index'),
  ('stock-remaining',          'Sisa Stok',          'stock-remaining.choose-location'),
  ('assets-by-category',       'Nilai Aset',         'reports.assets-by-category.index'),
  ('report-employee-summary',  'Ringkasan',          'report-employee-summary.index'),
  ('report-employee-detail',   'Detail',             'report-employee-detail.index'),
  ('report-top-customers',     'Top Pelanggan',      'report-top-customers.index'),
  ('products',                 'Kelola Produk',      'products.index'),
  ('product-categories',       'Produk Kategori',    'product-categories.index'),
  ('product-units',            'Produk Unit',        'product-units.index'),
  ('stock-opname',             'Stok Opname',        'product-opname-sessions.index'),
  ('stock-transfer',           'Pindah Stok',        'product-transfer-services.index'),
  ('stock-adjustment',         'Penyesuaian Stok',   'product-adjustment-stocks.index'),
  ('customers',                'Daftar Pelanggan',   'customers.index'),
  ('customer-categories',      'Kategori',           'customer-categories.index'),
  ('loyalties',                'Loyalty',            'loyalties.index'),
  ('daily-sales',              'Rekapan',            'daily-sales.index'),
  ('payment-recaps',           'Detail Rekapan',     'payment-recaps.index'),
  ('sale-transactions',        'Data Penjualan',     'sale-transactions.index'),
  ('promos',                   'Promosi',            'promos.index'),
  ('entity',                   'Entity',             'entity.edit'),
  ('locations',                'Lokasi',             'locations.index'),
  ('legal-entities',           'Daftar CV',          'legal-entities.index'),
  ('legal-entity-locations',   'CV per Toko',        'legal-entity-locations.index'),
  ('employees',                'Karyawan',           'employees.index'),
  ('user-accounts',            'Akun Login',         'user-accounts.index'),
  ('access-roles',             'Role',               'access-roles.index'),
  ('payment-methods',          'Pembayaran',         'payment-methods.index'),
  ('taxes',                    'Pajak',              'taxes.index'),
  ('order-types',              'Jenis Pesanan',      'order-types.index'),
  ('menus',                    'Menu',               'menu-headers.index');

-- Penempatan: header, group (boleh kosong), sub menu, urutan.
CREATE TEMPORARY TABLE `tmp_menu_placements` (
  `header_code` VARCHAR(100), `group_code` VARCHAR(100) NULL, `submenu_code` VARCHAR(100), `seq` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tmp_menu_placements` VALUES
  ('dashboard',    NULL,                'dashboard',               10),
  ('laporan',      'laporan-penjualan', 'report-sales',            10),
  ('laporan',      'laporan-penjualan', 'report-by-products',      20),
  ('laporan',      'laporan-penjualan', 'report-by-locations',     30),
  ('laporan',      'laporan-stok',      'report-stock-card',       40),
  ('laporan',      'laporan-stok',      'report-stock-movement',   50),
  ('laporan',      'laporan-stok',      'stock-remaining',         60),
  ('laporan',      'laporan-stok',      'assets-by-category',      70),
  ('laporan',      'laporan-sales',     'report-employee-summary', 80),
  ('laporan',      'laporan-sales',     'report-employee-detail',  90),
  ('laporan',      'laporan-pelanggan', 'report-top-customers',   100),
  ('produk',       NULL,                'products',                10),
  ('produk',       NULL,                'product-categories',      20),
  ('produk',       NULL,                'product-units',           30),
  ('stok',         NULL,                'stock-opname',            10),
  ('stok',         NULL,                'stock-transfer',          20),
  ('stok',         NULL,                'stock-adjustment',        30),
  ('people',       'people-pelanggan',  'customers',               10),
  ('people',       'people-pelanggan',  'customer-categories',     20),
  ('people',       'people-pelanggan',  'loyalties',               30),
  ('revenue',      'revenue-penjualan', 'daily-sales',             10),
  ('revenue',      'revenue-penjualan', 'payment-recaps',          20),
  ('revenue',      'revenue-penjualan', 'sale-transactions',       30),
  ('revenue',      NULL,                'promos',                  40),
  ('administrasi', NULL,                'entity',                  10),
  ('administrasi', NULL,                'locations',               20),
  ('administrasi', 'admin-cv',          'legal-entities',          30),
  ('administrasi', 'admin-cv',          'legal-entity-locations',  40),
  ('administrasi', NULL,                'employees',               50),
  ('administrasi', NULL,                'user-accounts',           60),
  ('administrasi', NULL,                'access-roles',            70),
  ('sistem',       NULL,                'payment-methods',         10),
  ('sistem',       NULL,                'taxes',                   20),
  ('konfigurasi',  NULL,                'order-types',             10),
  ('konfigurasi',  NULL,                'menus',                   20);

INSERT INTO `menu_header_details` (`menu_header_id`, `menu_submenu_id`, `menu_group_id`, `seq`)
SELECT h.`id`, s.`id`, g.`id`, p.`seq`
FROM `tmp_menu_placements` p
JOIN `menu_headers` h ON h.`code` = p.`header_code`
JOIN `menu_submenus` s ON s.`code` = p.`submenu_code`
LEFT JOIN `menu_groups` g ON g.`code` = p.`group_code`;

-- Aksi per sub menu.
CREATE TEMPORARY TABLE `tmp_menu_actions` (
  `submenu_code` VARCHAR(100), `code` VARCHAR(30), `name` VARCHAR(100), `seq` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tmp_menu_actions` VALUES
  ('dashboard','view','Lihat',10),
  ('report-sales','view','Lihat',10), ('report-sales','export','Cetak / Export',20),
  ('report-by-products','view','Lihat',10), ('report-by-products','export','Cetak / Export',20),
  ('report-by-locations','view','Lihat',10), ('report-by-locations','export','Cetak / Export',20),
  ('report-stock-card','view','Lihat',10), ('report-stock-card','export','Cetak / Export',20),
  ('report-stock-movement','view','Lihat',10), ('report-stock-movement','export','Cetak / Export',20),
  ('stock-remaining','view','Lihat',10), ('stock-remaining','export','Cetak / Export',20),
  ('assets-by-category','view','Lihat',10), ('assets-by-category','export','Cetak / Export',20),
  ('report-employee-summary','view','Lihat',10), ('report-employee-summary','export','Cetak / Export',20),
  ('report-employee-detail','view','Lihat',10), ('report-employee-detail','export','Cetak / Export',20),
  ('report-top-customers','view','Lihat',10), ('report-top-customers','export','Cetak / Export',20),
  ('products','view','Lihat',10), ('products','create','Tambah',20), ('products','update','Ubah',30),
    ('products','import','Import',40), ('products','export','Export',50),
  ('product-categories','view','Lihat',10), ('product-categories','create','Tambah',20), ('product-categories','update','Ubah',30),
    ('product-categories','delete','Hapus',40), ('product-categories','restore','Pulihkan',50),
  ('product-units','view','Lihat',10), ('product-units','create','Tambah',20), ('product-units','update','Ubah',30),
    ('product-units','delete','Hapus',40), ('product-units','restore','Pulihkan',50),
  ('stock-opname','view','Lihat',10), ('stock-opname','create','Buat Sesi',20), ('stock-opname','update','Ubah Jadwal',30),
    ('stock-opname','delete','Hapus / Batalkan Sesi',40), ('stock-opname','scan','Scan',50),
    ('stock-opname','approve','Kelola Sesi & Putuskan',60), ('stock-opname','export','Laporan',70),
  ('stock-transfer','view','Lihat',10), ('stock-transfer','create','Tambah',20),
    ('stock-transfer','approve','Setujui / Tolak',30), ('stock-transfer','cancel','Batalkan',40),
  ('stock-adjustment','view','Lihat',10), ('stock-adjustment','create','Tambah',20), ('stock-adjustment','approve','Setujui / Tolak',30),
  ('customers','view','Lihat',10), ('customers','create','Tambah',20), ('customers','update','Ubah',30),
    ('customers','activate','Aktifkan',40), ('customers','delete','Hapus',50), ('customers','restore','Pulihkan',60),
  ('customer-categories','view','Lihat',10), ('customer-categories','create','Tambah',20), ('customer-categories','update','Ubah',30),
    ('customer-categories','delete','Hapus',40), ('customer-categories','restore','Pulihkan',50),
  ('loyalties','view','Lihat',10), ('loyalties','create','Tambah',20), ('loyalties','update','Ubah',30),
    ('loyalties','activate','Aktif / Nonaktif / Arsip',40), ('loyalties','delete','Hapus',50),
  ('daily-sales','view','Lihat',10), ('daily-sales','export','Cetak Struk',20),
  ('payment-recaps','view','Lihat',10), ('payment-recaps','export','Cetak / Export',20),
  ('sale-transactions','view','Lihat',10), ('sale-transactions','export','Cetak Struk',20), ('sale-transactions','void','Batalkan Transaksi',30),
  ('promos','view','Lihat',10), ('promos','create','Tambah',20), ('promos','update','Ubah',30),
  ('entity','view','Lihat',10), ('entity','update','Ubah',20),
  ('locations','view','Lihat',10), ('locations','create','Tambah',20), ('locations','update','Ubah',30),
    ('locations','delete','Hapus',40), ('locations','restore','Pulihkan',50),
  ('legal-entities','view','Lihat',10), ('legal-entities','create','Tambah',20), ('legal-entities','update','Ubah',30),
    ('legal-entities','delete','Hapus',40), ('legal-entities','restore','Pulihkan',50),
  ('legal-entity-locations','view','Lihat',10), ('legal-entity-locations','create','Tambah',20), ('legal-entity-locations','delete','Hapus',30),
  ('employees','view','Lihat',10), ('employees','create','Tambah',20), ('employees','update','Ubah',30),
    ('employees','delete','Hapus',40), ('employees','restore','Pulihkan',50),
  ('user-accounts','view','Lihat',10), ('user-accounts','create','Tambah',20), ('user-accounts','update','Ubah',30),
    ('user-accounts','delete','Hapus',40), ('user-accounts','restore','Pulihkan',50),
  ('access-roles','view','Lihat',10), ('access-roles','create','Tambah',20), ('access-roles','update','Ubah',30),
    ('access-roles','delete','Hapus',40), ('access-roles','restore','Pulihkan',50),
  ('payment-methods','view','Lihat',10), ('payment-methods','create','Tambah',20), ('payment-methods','update','Ubah',30),
    ('payment-methods','delete','Hapus',40), ('payment-methods','restore','Pulihkan',50),
  ('taxes','view','Lihat',10), ('taxes','create','Tambah',20), ('taxes','update','Ubah',30), ('taxes','activate','Aktif / Arsip',40),
  ('order-types','view','Lihat',10), ('order-types','create','Tambah',20), ('order-types','update','Ubah',30),
    ('order-types','delete','Hapus',40), ('order-types','restore','Pulihkan',50),
  ('menus','view','Lihat',10), ('menus','create','Tambah',20), ('menus','update','Ubah',30), ('menus','delete','Hapus',40);

INSERT INTO `menu_actions` (`menu_submenu_id`, `code`, `name`, `seq`)
SELECT s.`id`, a.`code`, a.`name`, a.`seq`
FROM `tmp_menu_actions` a
JOIN `menu_submenus` s ON s.`code` = a.`submenu_code`;

-- Route Laravel per aksi (nama route dari `php artisan route:list`).
-- Route modul yang belum dibuat (user-accounts.store/destroy/restore, access-roles.*,
-- menu-*) sudah didaftarkan supaya langsung terlindungi begitu modulnya jadi.
CREATE TEMPORARY TABLE `tmp_menu_routes` (
  `submenu_code` VARCHAR(100), `action_code` VARCHAR(30), `route_name` VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tmp_menu_routes` VALUES
  ('dashboard','view','dashboard.index'), ('dashboard','view','dashboard.sales-summary'),
  ('dashboard','view','dashboard.sales-refund-summary'), ('dashboard','view','dashboard.profit-potential'),
  ('dashboard','view','dashboard.sales-by-date'), ('dashboard','view','dashboard.top5'),
  ('dashboard','view','dashboard.monthly-sales'), ('dashboard','view','dashboard.yearly-sales'),
  ('report-sales','view','report-sales.index'), ('report-sales','view','sellings.summary'), ('report-sales','export','report-sales.pdf'),
  ('report-by-products','view','report-by-products.index'), ('report-by-products','export','report-by-products.pdf'),
  ('report-by-locations','view','report-by-locations.index'), ('report-by-locations','export','report-by-locations.pdf'),
  ('report-stock-card','view','report-stock-card.index'), ('report-stock-card','export','report-stock-card.pdf'),
  ('report-stock-movement','view','report-stock-movement.index'), ('report-stock-movement','export','report-stock-movement.pdf'),
  ('stock-remaining','view','stock-remaining.choose-location'), ('stock-remaining','view','stock-remaining.report'),
    ('stock-remaining','export','stock-remaining.pdf'), ('stock-remaining','export','stock-remaining.export'),
  ('assets-by-category','view','reports.assets-by-category.index'), ('assets-by-category','export','reports.assets-by-category.pdf'),
  ('report-employee-summary','view','report-employee-summary.index'), ('report-employee-summary','export','report-employee-summary.pdf'),
  ('report-employee-detail','view','report-employee-detail.index'), ('report-employee-detail','export','report-employee-detail.pdf'),
  ('report-top-customers','view','report-top-customers.index'), ('report-top-customers','export','report-top-customers.pdf'),
  ('products','view','products.index'), ('products','view','products.stock'),
    ('products','view','brands.index'), ('products','view','brands.show'),
  ('products','create','products.store'), ('products','create','brands.create'), ('products','create','brands.store'),
  ('products','update','products.update'), ('products','update','brands.edit'), ('products','update','brands.update'),
  ('products','import','products.importPage'), ('products','import','products.import'), ('products','import','products.import.stock-lookup'),
    ('products','import','product-import-services.index'), ('products','import','product-import-services.show'),
    ('products','import','product-import-services.create'), ('products','import','product-import-services.store'),
    ('products','import','product-import-services.upload'), ('products','import','product-import-services.edit'),
    ('products','import','product-import-services.update'),
  ('products','export','products.export'),
  ('product-categories','view','product-categories.index'), ('product-categories','view','product-categories.deleted'),
  ('product-categories','create','product-categories.create'), ('product-categories','create','product-categories.store'),
  ('product-categories','update','product-categories.edit'), ('product-categories','update','product-categories.update'),
  ('product-categories','delete','product-categories.destroy'), ('product-categories','restore','product-categories.restore'),
  ('product-units','view','product-units.index'), ('product-units','view','product-units.deleted'),
  ('product-units','create','product-units.create'), ('product-units','create','product-units.store'),
  ('product-units','update','product-units.edit'), ('product-units','update','product-units.update'),
  ('product-units','delete','product-units.destroy'), ('product-units','restore','product-units.restore'),
  ('stock-opname','view','product-opname-sessions.index'), ('stock-opname','view','product-opname-sessions.show'),
    ('stock-opname','view','product-opname-sessions.live'), ('stock-opname','view','product-opname-sessions.share'),
    ('stock-opname','view','product-opname-services.index'), ('stock-opname','view','product-opname-services.show'),
  ('stock-opname','create','product-opname-sessions.create'), ('stock-opname','create','product-opname-sessions.store'),
    ('stock-opname','create','product-opname-sessions.store-revision-session'),
  ('stock-opname','update','product-opname-sessions.edit'), ('stock-opname','update','product-opname-sessions.update'),
  ('stock-opname','delete','product-opname-sessions.destroy'), ('stock-opname','delete','product-opname-sessions.cancel'),
  ('stock-opname','scan','product-opname-sessions.store-scan'), ('stock-opname','scan','product-opname-sessions.cancel-scan'),
    ('stock-opname','scan','product-opname-sessions.product-scans'),
  ('stock-opname','approve','product-opname-sessions.start'), ('stock-opname','approve','product-opname-sessions.close-scan'),
    ('stock-opname','approve','product-opname-sessions.reopen-scan'), ('stock-opname','approve','product-opname-sessions.finish'),
    ('stock-opname','approve','product-opname-sessions.decide'), ('stock-opname','approve','product-opname-sessions.store-revision'),
    ('stock-opname','approve','product-opname-services.approve'), ('stock-opname','approve','product-opname-services.reject'),
  ('stock-opname','export','product-opname-sessions.report'),
  ('stock-transfer','view','product-transfer-services.index'), ('stock-transfer','view','product-transfer-services.show'),
  ('stock-transfer','create','product-transfer-services.create'), ('stock-transfer','create','product-transfer-services.store'),
  ('stock-transfer','approve','product-transfer-services.approve'), ('stock-transfer','approve','product-transfer-services.reject'),
  ('stock-transfer','cancel','product-transfer-services.cancel'),
  ('stock-adjustment','view','product-adjustment-stocks.index'), ('stock-adjustment','view','product-adjustment-stocks.show'),
  ('stock-adjustment','create','product-adjustment-stocks.create'), ('stock-adjustment','create','product-adjustment-stocks.store'),
  ('stock-adjustment','approve','product-adjustment-stocks.approve'), ('stock-adjustment','approve','product-adjustment-stocks.reject'),
  ('customers','view','customers.index'), ('customers','view','customers.deleted'), ('customers','view','customer-point-movements.index'),
  ('customers','create','customers.create'), ('customers','create','customers.store'),
  ('customers','update','customers.edit'), ('customers','update','customers.update'),
  ('customers','activate','customers.activate'),
  ('customers','delete','customers.destroy'), ('customers','restore','customers.restore'),
  ('customer-categories','view','customer-categories.index'), ('customer-categories','view','customer-categories.deleted'),
  ('customer-categories','create','customer-categories.create'), ('customer-categories','create','customer-categories.store'),
  ('customer-categories','update','customer-categories.edit'), ('customer-categories','update','customer-categories.update'),
  ('customer-categories','delete','customer-categories.destroy'), ('customer-categories','restore','customer-categories.restore'),
  ('loyalties','view','loyalties.index'), ('loyalties','view','loyalties.show'),
  ('loyalties','create','loyalties.create'), ('loyalties','create','loyalties.store'),
  ('loyalties','update','loyalties.edit'), ('loyalties','update','loyalties.update'),
  ('loyalties','activate','loyalties.activate'), ('loyalties','activate','loyalties.deactivate'), ('loyalties','activate','loyalties.archive'),
  ('loyalties','delete','loyalties.destroy'),
  ('daily-sales','view','daily-sales.index'), ('daily-sales','view','daily-sales.show'), ('daily-sales','export','daily-sales.struk'),
  ('payment-recaps','view','payment-recaps.index'), ('payment-recaps','export','payment-recaps.pdf'),
  ('sale-transactions','view','sale-transactions.index'), ('sale-transactions','view','sale-transactions.show'),
  ('sale-transactions','export','sale-transactions.struk'), ('sale-transactions','void','sale-transactions.void'),
  ('promos','view','promos.index'), ('promos','view','promos.show'),
  ('promos','create','promos.create'), ('promos','create','promos.store'),
  ('promos','update','promos.edit'), ('promos','update','promos.update'),
  ('entity','view','entity.edit'), ('entity','update','entity.update'),
  ('locations','view','locations.index'), ('locations','view','locations.deleted'),
  ('locations','create','locations.create'), ('locations','create','locations.store'),
  ('locations','update','locations.edit'), ('locations','update','locations.update'),
  ('locations','delete','locations.destroy'), ('locations','restore','locations.restore'),
  ('legal-entities','view','legal-entities.index'), ('legal-entities','view','legal-entities.deleted'),
  ('legal-entities','create','legal-entities.store'), ('legal-entities','update','legal-entities.update'),
  ('legal-entities','delete','legal-entities.destroy'), ('legal-entities','restore','legal-entities.restore'),
  ('legal-entity-locations','view','legal-entity-locations.index'), ('legal-entity-locations','create','legal-entity-locations.store'),
  ('legal-entity-locations','delete','legal-entity-locations.destroy'),
  ('employees','view','employees.index'), ('employees','view','employees.deleted'),
  ('employees','create','employees.store'), ('employees','update','employees.update'),
  ('employees','delete','employees.destroy'), ('employees','restore','employees.restore'),
  ('user-accounts','view','user-accounts.index'), ('user-accounts','create','user-accounts.store'),
  ('user-accounts','update','user-accounts.update'), ('user-accounts','delete','user-accounts.destroy'),
  ('user-accounts','restore','user-accounts.restore'),
  ('access-roles','view','access-roles.index'), ('access-roles','create','access-roles.store'),
  ('access-roles','update','access-roles.update'), ('access-roles','update','access-roles.permissions'),
  ('access-roles','delete','access-roles.destroy'), ('access-roles','restore','access-roles.restore'),
  ('access-roles','view','roles.index'), ('access-roles','view','roles.show'),
  ('access-roles','create','roles.create'), ('access-roles','create','roles.store'),
  ('access-roles','update','roles.edit'), ('access-roles','update','roles.update'),
  ('access-roles','delete','roles.destroy'),
  ('payment-methods','view','payment-methods.index'), ('payment-methods','view','payment-methods.deleted'),
    ('payment-methods','view','payment-methods.show'),
  ('payment-methods','create','payment-methods.create'), ('payment-methods','create','payment-methods.store'),
  ('payment-methods','update','payment-methods.edit'), ('payment-methods','update','payment-methods.update'),
  ('payment-methods','delete','payment-methods.destroy'), ('payment-methods','restore','payment-methods.restore'),
  ('taxes','view','taxes.index'), ('taxes','create','taxes.store'), ('taxes','update','taxes.update'),
  ('taxes','activate','taxes.toggle-status'),
  ('order-types','view','order-types.index'), ('order-types','view','order-types.deleted'),
  ('order-types','create','order-types.create'), ('order-types','create','order-types.store'),
  ('order-types','update','order-types.edit'), ('order-types','update','order-types.update'),
  ('order-types','delete','order-types.destroy'), ('order-types','restore','order-types.restore'),
  ('menus','view','menu-headers.index'), ('menus','view','menu-submenus.index'), ('menus','view','menu-groups.index'),
  ('menus','create','menu-headers.store'), ('menus','create','menu-submenus.store'), ('menus','create','menu-groups.store'),
  ('menus','update','menu-headers.update'), ('menus','update','menu-submenus.update'), ('menus','update','menu-groups.update'),
  ('menus','delete','menu-headers.destroy'), ('menus','delete','menu-submenus.destroy'), ('menus','delete','menu-groups.destroy');

INSERT INTO `menu_action_routes` (`menu_action_id`, `route_name`)
SELECT a.`id`, r.`route_name`
FROM `tmp_menu_routes` r
JOIN `menu_submenus` s ON s.`code` = r.`submenu_code`
JOIN `menu_actions` a ON a.`menu_submenu_id` = s.`id` AND a.`code` = r.`action_code`;

-- =============================================================================
-- DATA AWAL: Hak Akses bawaan per entity, satu per role template.
-- Karyawan yang access_role_id-nya kosong memakai Hak Akses bawaan sesuai
-- employees.role_id. Pemilik Usaha selalu lolos semua cek di kode program,
-- supaya tidak ada yang terkunci dari aplikasinya sendiri.
-- Centangan bawaan di bawah silakan disesuaikan sebelum pengecekan diaktifkan.
-- =============================================================================

CREATE TEMPORARY TABLE `tmp_default_access` (
  `template_name` VARCHAR(100), `access_name` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tmp_default_access` VALUES
  ('Entity Owner',   'Pemilik Usaha'),
  ('Location Owner', 'Kepala Toko'),
  ('Cashier',        'Kasir'),
  ('Warehouse',      'Gudang'),
  ('Sales',          'Sales');

INSERT INTO `access_roles` (`entity_id`, `name`, `default_for_role_id`, `is_system`, `note`)
SELECT e.`id`, d.`access_name`, r.`id`, 1, 'Hak Akses bawaan'
FROM `entities` e
CROSS JOIN `tmp_default_access` d
JOIN `roles` r ON r.`name` = d.`template_name`
  AND r.`entity_id` IS NULL AND r.`parent_id` IS NULL AND r.`level` = 1;

-- Pemilik Usaha: semua aksi.
INSERT INTO `access_role_menu_actions` (`access_role_id`, `menu_action_id`)
SELECT ar.`id`, a.`id`
FROM `access_roles` ar
CROSS JOIN `menu_actions` a
WHERE ar.`is_system` = 1 AND ar.`name` = 'Pemilik Usaha';

-- Kepala Toko: semua menu utama kecuali Administrasi; menu pengaturan tidak.
INSERT INTO `access_role_menu_actions` (`access_role_id`, `menu_action_id`)
SELECT DISTINCT ar.`id`, a.`id`
FROM `access_roles` ar
JOIN `menu_headers` h ON h.`section` = 'main' AND h.`code` <> 'administrasi'
JOIN `menu_header_details` d ON d.`menu_header_id` = h.`id`
JOIN `menu_actions` a ON a.`menu_submenu_id` = d.`menu_submenu_id`
WHERE ar.`is_system` = 1 AND ar.`name` = 'Kepala Toko';

-- Gudang: Dashboard, Produk, Stok, dan Laporan Stok.
INSERT INTO `access_role_menu_actions` (`access_role_id`, `menu_action_id`)
SELECT DISTINCT ar.`id`, a.`id`
FROM `access_roles` ar
CROSS JOIN `menu_header_details` d
JOIN `menu_headers` h ON h.`id` = d.`menu_header_id`
LEFT JOIN `menu_groups` g ON g.`id` = d.`menu_group_id`
JOIN `menu_actions` a ON a.`menu_submenu_id` = d.`menu_submenu_id`
WHERE ar.`is_system` = 1 AND ar.`name` = 'Gudang'
  AND (h.`code` IN ('dashboard', 'produk', 'stok') OR g.`code` = 'laporan-stok');

-- Kasir dan Sales: Dashboard dan scan Stok Opname (petugas SO).
INSERT INTO `access_role_menu_actions` (`access_role_id`, `menu_action_id`)
SELECT ar.`id`, a.`id`
FROM `access_roles` ar
JOIN `menu_submenus` s ON s.`code` IN ('dashboard', 'stock-opname')
JOIN `menu_actions` a ON a.`menu_submenu_id` = s.`id`
  AND (s.`code` = 'dashboard' OR a.`code` IN ('view', 'scan'))
WHERE ar.`is_system` = 1 AND ar.`name` IN ('Kasir', 'Sales');

DROP TEMPORARY TABLE `tmp_menu_placements`;
DROP TEMPORARY TABLE `tmp_menu_actions`;
DROP TEMPORARY TABLE `tmp_menu_routes`;
DROP TEMPORARY TABLE `tmp_default_access`;

-- =============================================================================
-- CEK SETELAH DIJALANKAN (harus sesuai angka di komentar)
-- =============================================================================
-- SELECT COUNT(*) FROM menu_headers;         -- 9
-- SELECT COUNT(*) FROM menu_groups;          -- 7
-- SELECT COUNT(*) FROM menu_submenus;        -- 35
-- SELECT COUNT(*) FROM menu_header_details;  -- 35
-- SELECT COUNT(*) FROM access_roles;         -- 5 x jumlah entity
-- Aksi yang belum punya route (harus kosong):
-- SELECT s.code, a.code FROM menu_actions a JOIN menu_submenus s ON s.id = a.menu_submenu_id
--   LEFT JOIN menu_action_routes r ON r.menu_action_id = a.id WHERE r.id IS NULL;

-- =============================================================================
-- ROLLBACK
-- =============================================================================
-- ALTER TABLE `employees` DROP KEY `employees_access_role_index`, DROP COLUMN `access_role_id`;
-- DROP TABLE `access_role_menu_actions`;
-- DROP TABLE `access_roles`;
-- DROP TABLE `menu_action_routes`;
-- DROP TABLE `menu_actions`;
-- DROP TABLE `menu_header_details`;
-- DROP TABLE `menu_submenus`;
-- DROP TABLE `menu_groups`;
-- DROP TABLE `menu_headers`;
