<?php
require_once __DIR__ . '/../config/db.php';
$sql = file_get_contents(__DIR__ . '/kuesioner_v2.sql');
$stmts = array_filter(array_map('trim', explode(';', $sql)));
$ok = 0; $errors = [];
foreach ($stmts as $s) {
    if (!$s || preg_match('/^--/', $s)) continue;
    try { $pdo->exec($s . ';'); $ok++; } catch (PDOException $e) { $errors[] = $e->getMessage(); }
}
header('Content-Type: text/html; charset=UTF-8');
echo "<h2>Migration Kuesioner v2</h2><p>✅ OK: $ok</p>";
if ($errors) { echo '<ul>'; foreach($errors as $e) echo "<li style='color:red'>$e</li>"; echo '</ul>'; }
else echo '<p style="color:green;font-weight:700">🎉 Tabel kuesioner &amp; kuesioner_nilai berhasil dibuat!</p>';
echo '<p><a href="/muaradua-web/">← Kembali</a></p>';
