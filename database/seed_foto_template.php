<?php
/**
 * database/seed_foto_template.php
 * Masukkan foto-foto template ke semua 5 desa
 * Akses: http://localhost/muaradua-web/database/seed_foto_template.php
 */
require_once '../config/db.php';

$msgs = [];

// Ambil semua desa
$desas = $pdo->query("SELECT id, slug, nama FROM desa ORDER BY id")->fetchAll();

if (empty($desas)) {
    die('<p style="color:red;font-family:sans-serif;padding:2rem">Tidak ada desa di database! Jalankan fix_setup.php terlebih dahulu.</p>');
}

// Template foto yang berbeda per desa (4-5 foto masing-masing)
// file tersimpan di /uploads/foto/
$templateFoto = [
    // [filename, judul]
    ['template_pemandangan.jpg', 'Pemandangan Desa'],
    ['template_balai_desa.jpg',  'Balai Desa'],
    ['template_panen_padi.jpg',  'Kegiatan Panen Padi'],
    ['template_pasar_desa.jpg',  'Pasar Desa'],
    ['template_sungai.jpg',      'Sungai dan Alam Desa'],
    ['template_masjid.jpg',      'Masjid Desa'],
    ['template_warga.jpg',       'Kegiatan Warga'],
];

// Tiap desa dapat 4-7 foto berbeda (rotasi dari template)
// Hapus foto lama dulu (hanya template), lalu insert baru
$pdo->exec("DELETE FROM foto_desa WHERE filename LIKE 'template_%'");
$msgs[] = "🗑️ Foto template lama dihapus";

$stmt = $pdo->prepare("INSERT INTO foto_desa (desa_id, judul, filename, urutan) VALUES (?,?,?,?)");

$total = 0;
foreach ($desas as $i => $desa) {
    // Tiap desa dapat 4 foto dengan rotasi offset
    $offset = $i % count($templateFoto);
    $count  = 4; // minimal 4 foto

    for ($j = 0; $j < $count; $j++) {
        $idx     = ($offset + $j) % count($templateFoto);
        $foto    = $templateFoto[$idx];
        $filename = $foto[0];
        $judul    = $foto[1] . ' – ' . $desa['nama'];
        $urutan   = $j + 1;
        $stmt->execute([$desa['id'], $judul, $filename, $urutan]);
        $total++;
    }
    $msgs[] = "✅ Desa <strong>{$desa['nama']}</strong>: 4 foto template ditambahkan";
}

$msgs[] = "🎉 Total <strong>$total</strong> foto berhasil ditambahkan ke " . count($desas) . " desa";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <title>Seed Foto Template</title>
  <style>
    body{font-family:sans-serif;padding:2rem;background:#f4f7fb;max-width:700px;margin:0 auto;}
    h1{color:#1D4E89;}
    .msg{padding:.5rem 1rem;border-radius:6px;margin:.4rem 0;font-size:.9rem;}
    .ok{background:#D1FAE5;color:#065F46;}
    a.btn{display:inline-block;margin-top:1.5rem;padding:.7rem 1.5rem;background:#1D4E89;color:#fff;border-radius:8px;text-decoration:none;}
  </style>
</head>
<body>
<h1>📷 Seed Foto Template – <?= count($desas) ?> Desa</h1>
<?php foreach($msgs as $m): ?>
<div class="msg ok"><?= $m ?></div>
<?php endforeach; ?>
<a href="/muaradua-web/" class="btn">← Kembali ke Beranda</a>
<a href="/muaradua-web/muaradua/kisau/index.php" class="btn" style="background:#E8820C;margin-left:.5rem;">Lihat Desa Kisau →</a>
</body>
</html>
