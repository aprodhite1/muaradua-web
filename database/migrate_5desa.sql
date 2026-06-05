-- ============================================================
-- MIGRASI: Ganti menjadi 5 Desa dan tambah tabel foto_desa
-- Kecamatan Muaradua, OKU Selatan
-- ============================================================

USE muaradua_db;

-- Hapus semua data lama (cascade ke tabel terkait)
DELETE FROM admin_desa;
DELETE FROM pengumuman;
DELETE FROM infografis;
DELETE FROM penduduk;
DELETE FROM pendidikan;
DELETE FROM ekonomi;
DELETE FROM kesehatan;
DELETE FROM infrastruktur;
DELETE FROM pertanian;
DELETE FROM sosial_budaya;
DELETE FROM desa;

-- Reset auto increment
ALTER TABLE desa AUTO_INCREMENT = 1;
ALTER TABLE admin_desa AUTO_INCREMENT = 1;

-- ============================================================
-- TABLE: foto_desa (baru)
-- ============================================================
CREATE TABLE IF NOT EXISTS foto_desa (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    desa_id     INT NOT NULL,
    judul       VARCHAR(200) NOT NULL DEFAULT '',
    filename    VARCHAR(255) NOT NULL,
    urutan      INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATA: 5 Desa Kecamatan Muaradua
-- ============================================================
INSERT INTO desa (slug, nama, kepala_desa, penduduk_total, luas_wilayah, jumlah_kk, jumlah_rt, jumlah_rw, kode_pos, emoji, color_gradient, deskripsi) VALUES
('kisau',           'Kisau',           'Bapak Hermanto',    1850, '7.2 km²',  524,  6, 3, '32211', '🌿', 'linear-gradient(135deg,#1D4E89,#2E6DB4)', 'Desa Kisau adalah desa yang asri dengan hamparan persawahan dan kebun yang subur di Kecamatan Muaradua.'),
('batu-belang-jaya','Batu Belang Jaya','Bapak Armansyah',   2120, '8.5 km²',  602,  7, 3, '32211', '🌄', 'linear-gradient(135deg,#163A66,#1D4E89)', 'Desa Batu Belang Jaya dengan potensi pertanian yang kuat dan pemandangan alam perbukitan yang indah.'),
('pasar-muaradua',  'Pasar Muaradua',  'Bapak Hermansyah',  4120, '4.2 km²', 1163,  9, 5, '32211', '🏪', 'linear-gradient(135deg,#C96A00,#E8820C)', 'Pusat perdagangan dan ekonomi Kecamatan Muaradua yang terus berkembang pesat.'),
('pancur-pungah',   'Pancur Pungah',   'Bapak Rusli',       1680, '6.8 km²',  476,  5, 3, '32211', '💧', 'linear-gradient(135deg,#79B443,#5E8E33)', 'Desa Pancur Pungah terkenal dengan sumber air alami dan irigasi persawahan yang baik.'),
('bumi-agung',      'Bumi Agung',      'Bapak Junaidi',     2350, '9.1 km²',  665,  7, 4, '32211', '🌾', 'linear-gradient(135deg,#5E8E33,#79B443)', 'Desa Bumi Agung dengan lahan pertanian yang luas dan masyarakat yang sejahtera.');

-- ============================================================
-- DATA: Admin per Desa
-- Password placeholder — jalankan generate_passwords.php setelah import
-- ============================================================
INSERT INTO admin_desa (desa_id, username, password_hash, nama_lengkap) VALUES
(1, 'admin.kisau',         '$2y$10$placeholder_hash_kisau',         'Admin Desa Kisau'),
(2, 'admin.batabelangjaya','$2y$10$placeholder_hash_batabelangjaya', 'Admin Desa Batu Belang Jaya'),
(3, 'admin.pasarmuaradua', '$2y$10$placeholder_hash_pasarmuaradua', 'Admin Pasar Muaradua'),
(4, 'admin.pancurpungah',  '$2y$10$placeholder_hash_pancurpungah',  'Admin Desa Pancur Pungah'),
(5, 'admin.bumiagung',     '$2y$10$placeholder_hash_bumiagung',     'Admin Desa Bumi Agung');

-- ============================================================
-- DATA: Penduduk dasar per Desa
-- ============================================================
INSERT INTO penduduk (desa_id, rt, rw, laki_laki, perempuan) VALUES
-- Kisau
(1, 'RT 01', 'RW 01', 185, 175),
(1, 'RT 02', 'RW 01', 160, 152),
(1, 'RT 03', 'RW 02', 195, 185),
(1, 'RT 04', 'RW 02', 150, 148),
(1, 'RT 05', 'RW 03', 170, 160),
-- Batu Belang Jaya
(2, 'RT 01', 'RW 01', 200, 190),
(2, 'RT 02', 'RW 01', 175, 165),
(2, 'RT 03', 'RW 02', 210, 200),
(2, 'RT 04', 'RW 03', 155, 148),
(2, 'RT 05', 'RW 03', 178, 170),
-- Pasar Muaradua
(3, 'RT 01', 'RW 01', 280, 270),
(3, 'RT 02', 'RW 01', 265, 255),
(3, 'RT 03', 'RW 02', 310, 295),
(3, 'RT 04', 'RW 02', 245, 235),
(3, 'RT 05', 'RW 03', 270, 260),
-- Pancur Pungah
(4, 'RT 01', 'RW 01', 180, 170),
(4, 'RT 02', 'RW 01', 155, 148),
(4, 'RT 03', 'RW 02', 175, 165),
(4, 'RT 04', 'RW 02', 160, 152),
-- Bumi Agung
(5, 'RT 01', 'RW 01', 210, 200),
(5, 'RT 02', 'RW 01', 195, 185),
(5, 'RT 03', 'RW 02', 225, 215),
(5, 'RT 04', 'RW 03', 180, 170),
(5, 'RT 05', 'RW 04', 170, 162);

-- ============================================================
-- DATA: Pengumuman awal
-- ============================================================
INSERT INTO pengumuman (desa_id, judul, isi, tanggal, aktif) VALUES
(1, 'Musyawarah Desa Kisau 2025', 'Warga Desa Kisau diundang hadir dalam musyawarah perencanaan pembangunan.', '2025-04-28', 1),
(2, 'Gotong Royong Bersih Desa', 'Gotong royong bersih Desa Batu Belang Jaya dilaksanakan setiap Minggu pagi.', '2025-04-25', 1),
(3, 'Jadwal Pasar Malam', 'Pasar malam Muaradua akan diadakan setiap Sabtu di alun-alun.', '2025-04-22', 1),
(4, 'Jadwal Posyandu Balita', 'Posyandu balita Pancur Pungah setiap Jumat pertama di bulan berjalan.', '2025-04-15', 1),
(5, 'Pembagian Bantuan Pangan', 'Distribusi BPNT Bumi Agung dilaksanakan pada 20 April 2025 di Balai Desa.', '2025-04-20', 1);
