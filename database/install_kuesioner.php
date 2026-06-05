<?php
/**
 * install_kuesioner.php
 * Script instalasi tabel kuesioner & kuesioner_nilai
 * Jalankan 1x via browser: /muaradua-web/database/install_kuesioner.php
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=UTF-8');
$ok = 0; $errors = [];

$queries = [
    "DROP TABLE IF EXISTS `kuesioner_nilai`",
    "DROP TABLE IF EXISTS `kuesioner`",
    "CREATE TABLE `kuesioner` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE `kuesioner_nilai` (
      `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `kuesioner_id`  INT UNSIGNED NOT NULL,
      `kode`          VARCHAR(80) NOT NULL,
      `nilai`         TEXT DEFAULT NULL,
      UNIQUE KEY `uk_kues_kode` (`kuesioner_id`, `kode`),
      KEY `idx_kuesioner_id` (`kuesioner_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        $ok++;
    } catch (PDOException $e) {
        $errors[] = htmlspecialchars($e->getMessage()) . ' | Query: ' . htmlspecialchars(substr($q, 0, 60));
    }
}

echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'>";
echo "<title>Install Kuesioner</title></head><body style='font-family:Arial;padding:2rem;'>";
echo "<h2>📦 Instalasi Tabel Kuesioner</h2>";
echo "<p>✅ Query berhasil: <strong>$ok</strong> dari " . count($queries) . "</p>";

if ($errors) {
    echo "<div style='color:red;background:#fff5f5;padding:1rem;border:1px solid red;border-radius:4px;'>";
    echo "<strong>⚠️ Error:</strong><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul></div>";
} else {
    echo "<div style='color:green;background:#f0fff4;padding:1rem;border:1px solid green;border-radius:4px;'>";
    echo "🎉 <strong>Tabel <code>kuesioner</code> dan <code>kuesioner_nilai</code> berhasil dibuat!</strong>";
    echo "</div>";
    
    // Verifikasi
    $tbls = $pdo->query("SHOW TABLES LIKE 'kuesioner%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tabel yang ada: " . implode(', ', array_map('htmlspecialchars', $tbls)) . "</p>";
}

echo "<p><a href='/muaradua-web/rekap-kuesioner.php' style='color:#0F2944;'>→ Buka Rekap Kuesioner</a></p>";
echo "<p><a href='/muaradua-web/' style='color:#0F2944;'>← Kembali ke Portal</a></p>";
echo "</body></html>";
