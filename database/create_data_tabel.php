<?php
/**
 * Buat tabel data_tabel + jalankan via browser:
 * http://localhost/muaradua-web/database/create_data_tabel.php
 */
require_once __DIR__ . '/../config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS data_tabel (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  desa_id    INT NOT NULL,
  kategori   VARCHAR(50) NOT NULL,
  judul      VARCHAR(200) NOT NULL,
  headers    JSON NOT NULL,
  rows       JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo '<p style="color:green;font-size:1.2rem">✅ Tabel <strong>data_tabel</strong> berhasil dibuat!</p>';
    echo '<p><a href="http://localhost/muaradua-web/">← Kembali ke Website</a></p>';
} catch (PDOException $e) {
    echo '<p style="color:red">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
