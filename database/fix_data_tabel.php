<?php
/**
 * Fix data_tabel — tambah kolom yang hilang
 * http://localhost/muaradua-web/database/fix_data_tabel.php
 */
require_once __DIR__ . '/../config/db.php';

$fixes = [
    "Cek & tambah desa_id"       => "ALTER TABLE data_tabel ADD COLUMN IF NOT EXISTS desa_id INT NOT NULL DEFAULT 0 AFTER id",
    "Cek & tambah kategori"      => "ALTER TABLE data_tabel ADD COLUMN IF NOT EXISTS kategori VARCHAR(50) NOT NULL DEFAULT '' AFTER desa_id",
    "Cek & tambah judul"         => "ALTER TABLE data_tabel ADD COLUMN IF NOT EXISTS judul VARCHAR(200) NOT NULL DEFAULT '' AFTER kategori",
    "Cek & tambah headers (JSON)"=> "ALTER TABLE data_tabel ADD COLUMN IF NOT EXISTS headers JSON NOT NULL AFTER judul",
    "Cek & tambah rows (JSON)"   => "ALTER TABLE data_tabel ADD COLUMN IF NOT EXISTS rows JSON NOT NULL AFTER headers",
    "Cek & tambah created_at"    => "ALTER TABLE data_tabel ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER rows",
];

echo '<h2>🔧 Fix Tabel data_tabel</h2><ul>';
foreach ($fixes as $label => $sql) {
    try {
        $pdo->exec($sql);
        echo "<li style='color:green'>✅ $label</li>";
    } catch (PDOException $e) {
        // Jika kolom sudah ada (1060), abaikan
        if (str_contains($e->getMessage(), '1060') || str_contains($e->getMessage(), 'Duplicate column')) {
            echo "<li style='color:gray'>⏭️ $label (sudah ada)</li>";
        } else {
            echo "<li style='color:red'>❌ $label — " . htmlspecialchars($e->getMessage()) . "</li>";
        }
    }
}

// Tambah foreign key jika belum ada (opsional, skip jika error)
try {
    $pdo->exec("ALTER TABLE data_tabel ADD CONSTRAINT fk_dt_desa FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE");
    echo "<li style='color:green'>✅ Foreign key desa_id → desa.id</li>";
} catch (PDOException $e) {
    echo "<li style='color:gray'>⏭️ Foreign key (sudah ada atau skip)</li>";
}

echo '</ul>';

// Tampilkan struktur tabel saat ini
echo '<h3>📋 Struktur Tabel Saat Ini:</h3>';
$cols = $pdo->query("DESCRIBE data_tabel")->fetchAll();
echo '<table border="1" cellpadding="6" style="border-collapse:collapse">';
echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
foreach ($cols as $c) {
    echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td><td>{$c['Null']}</td><td>{$c['Key']}</td><td>{$c['Default']}</td></tr>";
}
echo '</table>';
echo '<br><p style="color:green;font-weight:bold">✅ Selesai! <a href="http://localhost/muaradua-web/muaradua/muara-dua/admin.php">Buka Admin Panel</a></p>';
