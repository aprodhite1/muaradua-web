<?php
/**
 * database/fix_setup.php
 * Fix emoji, create foto_desa table, set passwords
 * Akses: http://localhost/muaradua-web/database/fix_setup.php
 */
require_once '../config/db.php';

$msgs = [];

// Fix emoji values
$emojis = [
    'kisau'           => '🌿',
    'batu-belang-jaya'=> '🌄',
    'pasar-muaradua'  => '🏪',
    'pancur-pungah'   => '💧',
    'bumi-agung'      => '🌾',
];
foreach ($emojis as $slug => $emoji) {
    $pdo->prepare("UPDATE desa SET emoji=? WHERE slug=?")->execute([$emoji, $slug]);
    $msgs[] = "✅ Emoji updated: $slug → $emoji";
}

// Fix luas_wilayah (km² character)
$areas = [
    'kisau'           => '7.2 km²',
    'batu-belang-jaya'=> '8.5 km²',
    'pasar-muaradua'  => '4.2 km²',
    'pancur-pungah'   => '6.8 km²',
    'bumi-agung'      => '9.1 km²',
];
foreach ($areas as $slug => $luas) {
    $pdo->prepare("UPDATE desa SET luas_wilayah=? WHERE slug=?")->execute([$luas, $slug]);
    $msgs[] = "✅ Luas updated: $slug → $luas";
}

// Create foto_desa table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS foto_desa (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        desa_id     INT NOT NULL,
        judul       VARCHAR(200) NOT NULL DEFAULT '',
        filename    VARCHAR(255) NOT NULL,
        urutan      INT NOT NULL DEFAULT 0,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $msgs[] = "✅ Tabel foto_desa siap";
} catch (Exception $e) {
    $msgs[] = "⚠️ foto_desa: " . $e->getMessage();
}

// Set passwords
$desaPass = [
    'kisau'           => 'kisau123',
    'batu-belang-jaya'=> 'batabelangjaya123',
    'pasar-muaradua'  => 'pasarmuaradua123',
    'pancur-pungah'   => 'pancurpungah123',
    'bumi-agung'      => 'bumiagung123',
];

// Get desa IDs
$desaRows = $pdo->query("SELECT id, slug FROM desa")->fetchAll();
$slugToId = [];
foreach ($desaRows as $r) { $slugToId[$r['slug']] = $r['id']; }

foreach ($desaPass as $slug => $pass) {
    $id = $slugToId[$slug] ?? null;
    if (!$id) { $msgs[] = "⚠️ Desa '$slug' tidak ditemukan di DB"; continue; }

    // Check if admin exists
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM admin_desa WHERE desa_id=?")->execute([$id]);

    $hash = password_hash($pass, PASSWORD_BCRYPT);

    // Upsert admin
    $check = $pdo->prepare("SELECT id FROM admin_desa WHERE desa_id=?");
    $check->execute([$id]);
    $existing = $check->fetch();

    $uname = 'admin.' . str_replace('-','', $slug);
    $namaLengkap = 'Admin Desa ' . ucwords(str_replace('-',' ', $slug));

    if ($existing) {
        $pdo->prepare("UPDATE admin_desa SET password_hash=?, username=?, nama_lengkap=? WHERE desa_id=?")
            ->execute([$hash, $uname, $namaLengkap, $id]);
        $msgs[] = "✅ Password updated: $uname";
    } else {
        $pdo->prepare("INSERT INTO admin_desa (desa_id, username, password_hash, nama_lengkap) VALUES (?,?,?,?)")
            ->execute([$id, $uname, $hash, $namaLengkap]);
        $msgs[] = "✅ Admin created: $uname";
    }
}

// Add pengumuman if empty
$pengCount = $pdo->query("SELECT COUNT(*) FROM pengumuman")->fetchColumn();
if ((int)$pengCount === 0) {
    $pengData = [
        [$slugToId['kisau']??1,           'Musyawarah Desa Kisau 2025',         'Warga Desa Kisau diundang hadir dalam musyawarah perencanaan pembangunan.', '2025-04-28'],
        [$slugToId['batu-belang-jaya']??2, 'Gotong Royong Bersih Desa',          'Gotong royong Batu Belang Jaya dilaksanakan setiap Minggu pagi.',           '2025-04-25'],
        [$slugToId['pasar-muaradua']??3,   'Jadwal Pasar Malam',                 'Pasar malam Muaradua akan diadakan setiap Sabtu di alun-alun.',             '2025-04-22'],
        [$slugToId['pancur-pungah']??4,    'Jadwal Posyandu Balita',             'Posyandu balita Pancur Pungah setiap Jumat pertama di bulan berjalan.',     '2025-04-15'],
        [$slugToId['bumi-agung']??5,       'Pembagian Bantuan Pangan',           'Distribusi BPNT Bumi Agung pada 20 April 2025 di Balai Desa.',              '2025-04-20'],
    ];
    $stmt = $pdo->prepare("INSERT INTO pengumuman (desa_id, judul, isi, tanggal, aktif) VALUES (?,?,?,?,1)");
    foreach ($pengData as $row) { $stmt->execute($row); }
    $msgs[] = "✅ Pengumuman awal ditambahkan";
}

// Display results
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Fix Setup – 5 Desa</title>
  <style>
    body{font-family:monospace;padding:2rem;background:#f4f7fb;}
    h1{color:#1D4E89;}
    .msg{padding:.4rem .75rem;border-radius:4px;margin:.3rem 0;font-size:.9rem;}
    .ok{background:#D1FAE5;color:#065F46;}
    .err{background:#FEF2F2;color:#B91C1C;}
    table{border-collapse:collapse;margin:1.5rem 0;}
    th,td{border:1px solid #ccc;padding:.4rem .75rem;font-size:.9rem;}
    th{background:#EEF3FA;}
    a{color:#1D4E89;}
  </style>
</head>
<body>
<h1>🔧 Fix Setup – 5 Desa Kecamatan Muaradua</h1>
<?php foreach($msgs as $m): ?>
<div class="msg <?= str_starts_with($m,'✅') ? 'ok' : 'err' ?>"><?= htmlspecialchars($m) ?></div>
<?php endforeach; ?>

<h3 style="margin-top:1.5rem;">🔑 Akun Admin Desa:</h3>
<table>
  <tr><th>Desa</th><th>Username</th><th>Password</th></tr>
  <tr><td>Kisau</td><td>admin.kisau</td><td>kisau123</td></tr>
  <tr><td>Batu Belang Jaya</td><td>admin.batabelangjaya</td><td>batabelangjaya123</td></tr>
  <tr><td>Pasar Muaradua</td><td>admin.pasarmuaradua</td><td>pasarmuaradua123</td></tr>
  <tr><td>Pancur Pungah</td><td>admin.pancurpungah</td><td>pancurpungah123</td></tr>
  <tr><td>Bumi Agung</td><td>admin.bumiagung</td><td>bumiagung123</td></tr>
</table>

<p><a href="/muaradua-web/">← Kembali ke Beranda</a></p>
</body>
</html>
