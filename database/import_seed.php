<?php
/**
 * import_seed.php — Jalankan SEKALI via browser:
 * http://localhost/muaradua-web/database/import_seed.php
 */
require_once __DIR__ . '/../config/db.php';

$sqlFile = __DIR__ . '/seed_desa_lain.sql';
if (!file_exists($sqlFile)) {
    die('<p style="color:red">❌ File seed_desa_lain.sql tidak ditemukan.</p>');
}

$sql = file_get_contents($sqlFile);

// Pecah per statement (abaikan komentar dan baris kosong)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => strlen($s) > 5 && !str_starts_with(ltrim($s), '--')
);

echo '<h2>🌱 Import Seed Data Desa Lain</h2><ul>';
$ok = 0; $fail = 0;
foreach ($statements as $stmt) {
    if (empty(trim($stmt))) continue;
    try {
        $pdo->exec($stmt);
        echo '<li style="color:green">✅ OK: ' . htmlspecialchars(substr($stmt, 0, 80)) . '...</li>';
        $ok++;
    } catch (PDOException $e) {
        // Lewati jika data sudah ada (duplicate)
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), '1062') !== false) {
            echo '<li style="color:orange">⚠️ Skip (sudah ada): ' . htmlspecialchars(substr($stmt, 0, 80)) . '...</li>';
        } else {
            echo '<li style="color:red">❌ Error: ' . htmlspecialchars($e->getMessage()) . '<br><small>' . htmlspecialchars(substr($stmt, 0, 120)) . '</small></li>';
            $fail++;
        }
    }
}
echo '</ul>';
echo "<p><strong>✅ Berhasil: $ok</strong> | ❌ Gagal: $fail</p>";
echo '<p><a href="http://localhost/muaradua-web/">← Buka Website</a></p>';
