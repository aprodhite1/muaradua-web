-- ============================================================
-- KUESIONER DESA v2 — Struktur fleksibel multi-tahun
-- Jalankan via: /muaradua-web/database/run_kuesioner_v2.php
-- ============================================================

-- Hapus tabel lama jika ada
DROP TABLE IF EXISTS `kuesioner_nilai`;
DROP TABLE IF EXISTS `kuesioner`;
DROP TABLE IF EXISTS `kuesioner_desa`;

-- Header kuesioner (1 per desa per tahun)
CREATE TABLE `kuesioner` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `desa_id`          INT UNSIGNED NOT NULL,
  `tahun`            SMALLINT UNSIGNED NOT NULL DEFAULT 2025,
  `status`           ENUM('draft','selesai') DEFAULT 'draft',
  `pengisi_nama`     VARCHAR(120) DEFAULT NULL,
  `pengisi_jabatan`  VARCHAR(100) DEFAULT NULL,
  `tanggal_pengisian` DATE DEFAULT NULL,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_desa_tahun` (`desa_id`, `tahun`),
  KEY `idx_desa` (`desa_id`),
  KEY `idx_tahun` (`tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nilai kuesioner (key-value per kuesioner)
CREATE TABLE `kuesioner_nilai` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kuesioner_id`  INT UNSIGNED NOT NULL,
  `kode`          VARCHAR(80) NOT NULL,
  `nilai`         TEXT DEFAULT NULL,
  UNIQUE KEY `uk_kues_kode` (`kuesioner_id`, `kode`),
  KEY `idx_kuesioner_id` (`kuesioner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
