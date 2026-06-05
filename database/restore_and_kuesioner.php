<?php
/**
 * database/restore_and_kuesioner.php
 * Restore semua data + buat tabel kuesioner
 * Jalankan: http://localhost/muaradua-web/database/restore_and_kuesioner.php
 */
require_once __DIR__ . '/../config/db.php';

$files = [
    'muaradua_db.sql',
    'migrate_5desa.sql',
    'seed_desa_lain.sql',
    'kuesioner_v2.sql',
];

header('Content-Type: text/html; charset=UTF-8');
echo '<h2>🔄 Restore Database + Kuesioner Migration</h2><pre>';

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (!file_exists($path)) { echo "⚠️  Skip: $f (tidak ada)\n"; continue; }
    $sql   = file_get_contents($path);
    $stmts = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0; $err = 0;
    foreach ($stmts as $s) {
        if (!$s || preg_match('/^--/', $s) || preg_match('/^\/\*/', $s)) continue;
        try { $pdo->exec($s . ';'); $ok++; }
        catch (PDOException $e) { $err++; echo "  ERROR: ".$e->getMessage()."\n"; }
    }
    echo "✅ $f — OK: $ok, Error: $err\n";
}

echo '</pre><p style="color:green;font-size:1.2rem;font-weight:bold">🎉 Selesai! <a href="/muaradua-web/muaradua/kisau/admin.php">Buka Admin Kisau →</a></p>';
