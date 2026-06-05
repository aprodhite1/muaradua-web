<?php
/**
 * generate_passwords.php
 * Jalankan SEKALI via browser: http://localhost/muaradua-web/database/generate_passwords.php
 * Script ini akan UPDATE hash password admin di database.
 */
require_once __DIR__ . '/../config/db.php';

$passwords = [
    'admin.muaradua'      => 'muaradua123',
    'admin.gunungbatu'    => 'gunungbatu123',
    'admin.batabelang'    => 'batabelang123',
    'admin.tanjungjaya'   => 'tanjungjaya123',
    'admin.sukarami'      => 'sukarami123',
    'admin.sinarharapan'  => 'sinarharapan123',
    'admin.pasarmuaradua' => 'pasarmuaradua123',
    'admin.padangbindu'   => 'padangbindu123',
];

echo "<h2>Generate Password Hash Admin</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Username</th><th>Password</th><th>Status</th></tr>";

foreach ($passwords as $username => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE admin_desa SET password_hash = ? WHERE username = ?");
    $result = $stmt->execute([$hash, $username]);
    echo "<tr>";
    echo "<td>$username</td>";
    echo "<td>$password</td>";
    echo "<td>" . ($result ? "<span style='color:green'>✅ Updated</span>" : "<span style='color:red'>❌ Failed</span>") . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><p style='color:green;font-weight:bold'>✅ Semua password berhasil di-hash dan disimpan ke database!</p>";
echo "<p>Sekarang Anda bisa <a href='http://localhost/muaradua-web/'>membuka website</a>.</p>";
