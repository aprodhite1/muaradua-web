<?php
/**
 * database/run_migration.php
 * Jalankan migrasi 5 desa
 * AKSES: http://localhost/muaradua-web/database/run_migration.php
 */
require_once '../config/db.php';

$sqlFile = __DIR__ . '/migrate_5desa.sql';
$sql     = file_get_contents($sqlFile);

// Split by semicolon, skip empty statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => $s !== '' && !preg_match('/^--/', $s)
);

$errors = [];
$ok     = 0;
foreach ($statements as $stmt) {
    if (trim($stmt) === '') continue;
    try {
        $pdo->exec($stmt);
        $ok++;
    } catch (PDOException $e) {
        $errors[] = '<b>Error:</b> ' . htmlspecialchars($e->getMessage()) . '<br><pre>' . htmlspecialchars(substr($stmt,0,200)) . '</pre>';
    }
}

// Generate hashed passwords
$desaPass = [
    1 => ['kisau',         'kisau123'],
    2 => ['batabelangjaya','batabelangjaya123'],
    3 => ['pasarmuaradua', 'pasarmuaradua123'],
    4 => ['pancurpungah',  'pancurpungah123'],
    5 => ['bumiagung',     'bumiagung123'],
];

foreach ($desaPass as $did => [$uname, $pass]) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    try {
        $pdo->prepare("UPDATE admin_desa SET password_hash=? WHERE desa_id=?")->execute([$hash, $did]);
        $ok++;
    } catch (PDOException $e) {
        $errors[] = 'Password update error: ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Migrasi Database – 5 Desa</title>
  <style>
    body{font-family:monospace;padding:2rem;background:#f4f7fb;}
    .ok{color:#065F46;background:#D1FAE5;padding:.5rem 1rem;border-radius:6px;margin-bottom:1rem;}
    .err{color:#B91C1C;background:#FEF2F2;padding:.5rem 1rem;border-radius:6px;margin-bottom:.5rem;}
    pre{background:#fff;padding:.5rem;border-radius:4px;font-size:.8rem;margin:.25rem 0;}
    h1{color:#1D4E89;}
    table{border-collapse:collapse;margin-top:1rem;}
    th,td{border:1px solid #ccc;padding:.4rem .75rem;font-size:.9rem;}
    th{background:#EEF3FA;}
  </style>
</head>
<body>
<h1>🗄️ Migrasi Database – 5 Desa Kecamatan Muaradua</h1>

<?php if (empty($errors)): ?>
<div class="ok">✅ Migrasi berhasil! <?= $ok ?> query dijalankan.</div>
<?php else: ?>
<div class="ok">✅ <?= $ok ?> query berhasil.</div>
<?php foreach($errors as $e): ?><div class="err"><?= $e ?></div><?php endforeach; ?>
<?php endif; ?>

<h3>🔑 Akun Admin Desa:</h3>
<table>
  <tr><th>Desa</th><th>Username</th><th>Password</th></tr>
  <tr><td>Kisau</td><td>admin.kisau</td><td>kisau123</td></tr>
  <tr><td>Batu Belang Jaya</td><td>admin.batabelangjaya</td><td>batabelangjaya123</td></tr>
  <tr><td>Pasar Muaradua</td><td>admin.pasarmuaradua</td><td>pasarmuaradua123</td></tr>
  <tr><td>Pancur Pungah</td><td>admin.pancurpungah</td><td>pancurpungah123</td></tr>
  <tr><td>Bumi Agung</td><td>admin.bumiagung</td><td>bumiagung123</td></tr>
</table>

<p style="margin-top:1.5rem;"><a href="/muaradua-web/">← Kembali ke Beranda Kecamatan</a></p>
</body>
</html>
