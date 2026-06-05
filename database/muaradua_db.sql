-- ============================================================
-- DATABASE: muaradua_db
-- Website Kecamatan Muaradua, OKU Selatan, Sumatera Selatan
-- ============================================================

CREATE DATABASE IF NOT EXISTS muaradua_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE muaradua_db;

-- ============================================================
-- TABLE: desa
-- ============================================================
CREATE TABLE IF NOT EXISTS desa (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(50)  NOT NULL UNIQUE,
    nama            VARCHAR(100) NOT NULL,
    kepala_desa     VARCHAR(100) NOT NULL,
    penduduk_total  INT          NOT NULL DEFAULT 0,
    luas_wilayah    VARCHAR(20)  NOT NULL DEFAULT '0 km²',
    jumlah_kk       INT          NOT NULL DEFAULT 0,
    jumlah_rt       INT          NOT NULL DEFAULT 0,
    jumlah_rw       INT          NOT NULL DEFAULT 0,
    kode_pos        VARCHAR(10)  NOT NULL DEFAULT '32211',
    emoji           VARCHAR(10)  NOT NULL DEFAULT '🏘️',
    color_gradient  VARCHAR(100) NOT NULL DEFAULT 'linear-gradient(135deg,#1D4E89,#2E6DB4)',
    deskripsi       TEXT,
    alamat          VARCHAR(200),
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: admin_desa
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_desa (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    desa_id       INT          NOT NULL,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama_lengkap  VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: penduduk
-- ============================================================
CREATE TABLE IF NOT EXISTS penduduk (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    desa_id    INT NOT NULL,
    rt         VARCHAR(10) NOT NULL,
    rw         VARCHAR(10) NOT NULL,
    laki_laki  INT NOT NULL DEFAULT 0,
    perempuan  INT NOT NULL DEFAULT 0,
    total      INT GENERATED ALWAYS AS (laki_laki + perempuan) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pendidikan
-- ============================================================
CREATE TABLE IF NOT EXISTS pendidikan (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    desa_id      INT NOT NULL,
    tingkat      VARCHAR(100) NOT NULL,
    laki_laki    INT NOT NULL DEFAULT 0,
    perempuan    INT NOT NULL DEFAULT 0,
    total        INT GENERATED ALWAYS AS (laki_laki + perempuan) STORED,
    urutan       INT NOT NULL DEFAULT 0,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: ekonomi
-- ============================================================
CREATE TABLE IF NOT EXISTS ekonomi (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    desa_id       INT NOT NULL,
    mata_pencaharian VARCHAR(100) NOT NULL,
    jumlah        INT NOT NULL DEFAULT 0,
    persentase    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    emoji         VARCHAR(10),
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: kesehatan
-- ============================================================
CREATE TABLE IF NOT EXISTS kesehatan (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    desa_id    INT NOT NULL,
    fasilitas  VARCHAR(100) NOT NULL,
    jumlah     VARCHAR(50)  NOT NULL,
    keterangan VARCHAR(200),
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: infrastruktur
-- ============================================================
CREATE TABLE IF NOT EXISTS infrastruktur (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    desa_id    INT NOT NULL,
    jenis      VARCHAR(100) NOT NULL,
    panjang_jumlah VARCHAR(50) NOT NULL,
    kondisi    ENUM('Baik','Sedang','Rusak','Perlu Perbaikan') DEFAULT 'Baik',
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pertanian
-- ============================================================
CREATE TABLE IF NOT EXISTS pertanian (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    desa_id         INT NOT NULL,
    komoditas       VARCHAR(100) NOT NULL,
    luas_tanam      VARCHAR(50)  NOT NULL,
    produksi_tahun  VARCHAR(50)  NOT NULL,
    emoji           VARCHAR(10),
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: infografis
-- ============================================================
CREATE TABLE IF NOT EXISTS infografis (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    desa_id    INT NOT NULL,
    judul      VARCHAR(150) NOT NULL,
    kategori   VARCHAR(50)  NOT NULL,
    emoji      VARCHAR(10)  DEFAULT '📊',
    deskripsi  TEXT,
    warna_bg   VARCHAR(30)  DEFAULT '#EEF3FA',
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: pengumuman
-- ============================================================
CREATE TABLE IF NOT EXISTS pengumuman (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    desa_id    INT NOT NULL,
    judul      VARCHAR(200) NOT NULL,
    isi        TEXT,
    tanggal    DATE         NOT NULL,
    aktif      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: sosial_budaya
-- ============================================================
CREATE TABLE IF NOT EXISTS sosial_budaya (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    desa_id     INT NOT NULL,
    kategori    ENUM('Agama','Organisasi') DEFAULT 'Organisasi',
    nama        VARCHAR(100) NOT NULL,
    jumlah      VARCHAR(50)  NOT NULL,
    keterangan  VARCHAR(200),
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATA AWAL: 8 Desa Kecamatan Muaradua
-- ============================================================
INSERT INTO desa (slug, nama, kepala_desa, penduduk_total, luas_wilayah, jumlah_kk, jumlah_rt, jumlah_rw, kode_pos, emoji, color_gradient, deskripsi) VALUES
('muara-dua',      'Muara Dua',      'Bapak Syahril',     3245, '12.5 km²', 892,  8, 4, '32211', '🏘️', 'linear-gradient(135deg,#1D4E89,#2E6DB4)', 'Desa Muara Dua merupakan salah satu desa utama di Kecamatan Muaradua.'),
('gunung-batu',    'Gunung Batu',    'Bapak Ruslan',      2180, '8.3 km²',  615,  6, 3, '32211', '⛰️', 'linear-gradient(135deg,#2E6DB4,#5a9fd4)', 'Desa Gunung Batu terletak di kawasan perbukitan Kecamatan Muaradua.'),
('batu-belang',    'Batu Belang',    'Bapak Armansyah',   1850, '6.7 km²',  524,  5, 3, '32211', '🌄', 'linear-gradient(135deg,#163A66,#1D4E89)', 'Desa Batu Belang dengan potensi pertanian yang kuat.'),
('tanjung-jaya',   'Tanjung Jaya',   'Bapak Irfan',       2560, '9.1 km²',  724,  7, 4, '32211', '🌊', 'linear-gradient(135deg,#79B443,#5E8E33)', 'Desa Tanjung Jaya terkenal dengan sumber air dan irigasinya.'),
('sukarami',       'Sukarami',       'Bapak Junaidi',     1620, '5.4 km²',  458,  5, 2, '32211', '🌾', 'linear-gradient(135deg,#E8820C,#C96A00)', 'Desa Sukarami dengan sawah dan ladang yang luas.'),
('sinar-harapan',  'Sinar Harapan',  'Bapak Rahmat',      2090, '7.8 km²',  591,  6, 3, '32211', '🌟', 'linear-gradient(135deg,#5E8E33,#79B443)', 'Desa Sinar Harapan dengan semangat pembangunan yang tinggi.'),
('pasar-muaradua', 'Pasar Muaradua', 'Bapak Hermansyah',  4120, '4.2 km²', 1163,  9, 5, '32211', '🏪', 'linear-gradient(135deg,#C96A00,#E8820C)', 'Pusat perdagangan dan ekonomi Kecamatan Muaradua.'),
('padang-bindu',   'Padang Bindu',   'Bapak Muslidin',    1480, '10.2 km²', 418,  5, 2, '32211', '🌿', 'linear-gradient(135deg,#1D4E89,#163A66)', 'Desa Padang Bindu dengan keindahan alam yang terjaga.');

-- ============================================================
-- DATA AWAL: Admin per Desa (password = nama_slug + "123")
-- Hash dibuat dengan password_hash() PHP, bcrypt
-- Gunakan reset_passwords.php jika ingin update hash
-- ============================================================
-- NOTE: Hash di bawah harus di-generate ulang via PHP.
-- Jalankan file database/generate_passwords.php setelah import.
INSERT INTO admin_desa (desa_id, username, password_hash, nama_lengkap) VALUES
(1, 'admin.muaradua',      '$2y$10$placeholder_hash_muaradua',      'Admin Desa Muara Dua'),
(2, 'admin.gunungbatu',    '$2y$10$placeholder_hash_gunungbatu',    'Admin Desa Gunung Batu'),
(3, 'admin.batabelang',    '$2y$10$placeholder_hash_batabelang',    'Admin Desa Batu Belang'),
(4, 'admin.tanjungjaya',   '$2y$10$placeholder_hash_tanjungjaya',   'Admin Desa Tanjung Jaya'),
(5, 'admin.sukarami',      '$2y$10$placeholder_hash_sukarami',      'Admin Desa Sukarami'),
(6, 'admin.sinarharapan',  '$2y$10$placeholder_hash_sinarharapan',  'Admin Desa Sinar Harapan'),
(7, 'admin.pasarmuaradua', '$2y$10$placeholder_hash_pasarmuaradua', 'Admin Pasar Muaradua'),
(8, 'admin.padangbindu',   '$2y$10$placeholder_hash_padangbindu',   'Admin Desa Padang Bindu');

-- ============================================================
-- DATA AWAL: Penduduk Desa Muara Dua
-- ============================================================
INSERT INTO penduduk (desa_id, rt, rw, laki_laki, perempuan) VALUES
(1, 'RT 01', 'RW 01', 210, 198),
(1, 'RT 02', 'RW 01', 185, 172),
(1, 'RT 03', 'RW 02', 230, 220),
(1, 'RT 04', 'RW 02', 165, 159),
(1, 'RT 05', 'RW 03', 192, 188),
(1, 'RT 06', 'RW 03', 178, 170),
(1, 'RT 07', 'RW 04', 145, 140),
(1, 'RT 08', 'RW 04', 347, 346),
-- Gunung Batu
(2, 'RT 01', 'RW 01', 195, 185),
(2, 'RT 02', 'RW 01', 180, 172),
(2, 'RT 03', 'RW 02', 210, 200),
(2, 'RT 04', 'RW 02', 155, 148),
(2, 'RT 05', 'RW 03', 170, 165),
-- Batu Belang
(3, 'RT 01', 'RW 01', 185, 175),
(3, 'RT 02', 'RW 01', 160, 152),
(3, 'RT 03', 'RW 02', 195, 185),
(3, 'RT 04', 'RW 02', 150, 148),
-- Tanjung Jaya
(4, 'RT 01', 'RW 01', 220, 210),
(4, 'RT 02', 'RW 01', 195, 188),
(4, 'RT 03', 'RW 02', 230, 215),
(4, 'RT 04', 'RW 03', 175, 168),
(4, 'RT 05', 'RW 03', 185, 174);

-- ============================================================
-- DATA AWAL: Pendidikan Desa Muara Dua
-- ============================================================
INSERT INTO pendidikan (desa_id, tingkat, laki_laki, perempuan, urutan) VALUES
(1, 'Tidak/Belum Sekolah',   142, 158, 1),
(1, 'Belum Tamat SD',        210, 195, 2),
(1, 'Tamat SD/Sederajat',    385, 362, 3),
(1, 'SMP/Sederajat',         420, 398, 4),
(1, 'SMA/SMK/Sederajat',     328, 322, 5),
(1, 'Diploma/Sarjana',       167, 158, 6);

-- ============================================================
-- DATA AWAL: Ekonomi Desa Muara Dua
-- ============================================================
INSERT INTO ekonomi (desa_id, mata_pencaharian, jumlah, persentase, emoji) VALUES
(1, 'Petani / Buruh Tani',     542, 35.2, '🌾'),
(1, 'Pedagang / Wiraswasta',   218, 14.2, '🏪'),
(1, 'PNS / TNI / Polri',       127,  8.2, '🏢'),
(1, 'Buruh Bangunan/Industri', 195, 12.7, '🔧'),
(1, 'Jasa Transportasi',        86,  5.6, '🚗'),
(1, 'Lainnya',                 172, 11.2, '📦'),
(1, 'Tidak Bekerja/Pelajar',   200, 13.0, '🎒');

-- ============================================================
-- DATA AWAL: Kesehatan Desa Muara Dua
-- ============================================================
INSERT INTO kesehatan (desa_id, fasilitas, jumlah, keterangan) VALUES
(1, 'Posyandu',               '3 Unit',  'Aktif setiap bulan'),
(1, 'Polindes',               '1 Unit',  'Bidan desa bertugas'),
(1, 'Puskesmas Pembantu',     '1 Unit',  'Jarak 0,5 km dari kantor desa'),
(1, 'Bidan Praktek Mandiri',  '2 Orang', 'Aktif praktek');

-- ============================================================
-- DATA AWAL: Infrastruktur Desa Muara Dua
-- ============================================================
INSERT INTO infrastruktur (desa_id, jenis, panjang_jumlah, kondisi) VALUES
(1, 'Jalan Aspal',     '4,2 km',  'Baik'),
(1, 'Jalan Makadam',   '3,8 km',  'Sedang'),
(1, 'Jalan Tanah',     '2,1 km',  'Perlu Perbaikan'),
(1, 'Jembatan Beton',  '3 unit',  'Baik'),
(1, 'Jembatan Kayu',   '2 unit',  'Sedang'),
(1, 'Masjid/Musala',   '6 unit',  'Baik'),
(1, 'Sekolah Dasar',   '2 unit',  'Baik'),
(1, 'SMP',             '1 unit',  'Baik'),
(1, 'PAUD/TK',         '2 unit',  'Baik'),
(1, 'Kantor Desa',     '1 unit',  'Baik'),
(1, 'Balai Desa',      '1 unit',  'Sedang');

-- ============================================================
-- DATA AWAL: Pertanian Desa Muara Dua
-- ============================================================
INSERT INTO pertanian (desa_id, komoditas, luas_tanam, produksi_tahun, emoji) VALUES
(1, 'Padi Sawah', '48,5 Ha', '285 Ton',          '🌾'),
(1, 'Jagung',     '12,3 Ha', '74 Ton',           '🌽'),
(1, 'Sayuran',    '5,8 Ha',  '52 Ton',           '🥬'),
(1, 'Karet',      '28,4 Ha', '38 Ton (kering)',  '🌿'),
(1, 'Kopi',       '15,2 Ha', '22 Ton',           '☕');

-- ============================================================
-- DATA AWAL: Sosial Budaya Desa Muara Dua
-- ============================================================
INSERT INTO sosial_budaya (desa_id, kategori, nama, jumlah, keterangan) VALUES
(1, 'Agama',      'Islam',          '3.210 jiwa', '98,9%'),
(1, 'Agama',      'Kristen',        '28 jiwa',    '0,9%'),
(1, 'Agama',      'Lainnya',        '7 jiwa',     '0,2%'),
(1, 'Organisasi', 'PKK Desa',       '145 orang',  'Aktif'),
(1, 'Organisasi', 'Karang Taruna',  '87 orang',   'Aktif'),
(1, 'Organisasi', 'Kelompok Tani',  '4 kelompok', 'Aktif'),
(1, 'Organisasi', 'Koperasi Desa',  '212 anggota','Aktif');

-- ============================================================
-- DATA AWAL: Infografis Desa Muara Dua
-- ============================================================
INSERT INTO infografis (desa_id, judul, kategori, emoji, deskripsi, warna_bg) VALUES
(1, 'Komposisi Penduduk', 'Kependudukan', '👥', 'Sebaran penduduk berdasarkan usia dan jenis kelamin.', '#EEF3FA'),
(1, 'Tingkat Pendidikan', 'Pendidikan',   '📚', 'Profil tingkat pendidikan warga desa.',              '#FFF8EE'),
(1, 'Mata Pencaharian',   'Ekonomi',      '💼', 'Distribusi pekerjaan utama masyarakat desa.',        '#F0FFF4'),
(1, 'Kesehatan Warga',    'Kesehatan',    '🏥', 'Data aksesibilitas layanan kesehatan.',              '#FFF0F0');

-- ============================================================
-- DATA AWAL: Pengumuman Desa Muara Dua
-- ============================================================
INSERT INTO pengumuman (desa_id, judul, isi, tanggal, aktif) VALUES
(1, 'Musyawarah Desa Rencana Pembangunan 2025', 'Warga desa diundang hadir dalam musyawarah perencanaan pembangunan tahun 2025.', '2025-04-28', 1),
(1, 'Pembagian Bantuan Pangan Non-Tunai',       'Distribusi BPNT akan dilaksanakan pada 20 April 2025 di Balai Desa.',           '2025-04-20', 1),
(1, 'Jadwal Posyandu Balita Bulan Mei',         'Posyandu balita akan diadakan setiap Jumat pertama di bulan Mei 2025.',          '2025-04-15', 1),
(1, 'Pembaruan Data Administrasi Kependudukan', 'Warga dimohon memperbarui data administrasi kependudukan ke kantor desa.',       '2025-04-05', 1),
(1, 'Gotong Royong Bersih Desa',                'Kegiatan gotong royong bersih desa akan dilaksanakan setiap Minggu pagi.',       '2025-04-01', 1);
