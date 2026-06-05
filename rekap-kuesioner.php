<?php
require_once 'config/db.php';
require_once 'config/helpers.php';
$soal   = require 'config/kuesioner_soal.php';
$tahunQ = intval($_GET['tahun'] ?? date('Y'));
$bagianQ = trim($_GET['bagian'] ?? array_key_first($soal));
if (!isset($soal[$bagianQ])) $bagianQ = array_key_first($soal);

// Daftar tahun tersedia
$stT = $pdo->query("SELECT DISTINCT tahun FROM kuesioner WHERE status='selesai' ORDER BY tahun DESC");
$tahunList = $stT->fetchAll(PDO::FETCH_COLUMN);

// Semua desa yang punya kuesioner tahun ini
$stD = $pdo->prepare("
  SELECT k.id AS kues_id, d.nama AS nama_desa, d.slug, k.updated_at
  FROM kuesioner k JOIN desa d ON d.id=k.desa_id
  WHERE k.tahun=? AND k.status='selesai' ORDER BY d.nama
");
$stD->execute([$tahunQ]);
$desas = $stD->fetchAll();

// Kumpulkan nilai semua desa
$allNilai = [];
foreach ($desas as $d) {
    $stN = $pdo->prepare("SELECT kode, nilai FROM kuesioner_nilai WHERE kuesioner_id=?");
    $stN->execute([$d['kues_id']]);
    $allNilai[$d['slug']] = [];
    foreach ($stN->fetchAll() as $r) $allNilai[$d['slug']][$r['kode']] = $r['nilai'];
}

// Hitung rekap untuk bagian aktif (jumlah angka, modus untuk select/text)
$rekapSoal = $soal[$bagianQ];
$rekapTotal = [];
$rekapPerDesa = [];
foreach ($desas as $d) {
    $row = [];
    foreach ($rekapSoal as $p) {
        $v = $allNilai[$d['slug']][$p['kode']] ?? null;
        $row[$p['kode']] = $v;
        if ($p['tipe'] === 'number' && is_numeric($v)) {
            $rekapTotal[$p['kode']] = ($rekapTotal[$p['kode']] ?? 0) + (float)$v;
        }
    }
    $rekapPerDesa[] = ['nama' => $d['nama_desa'], 'slug' => $d['slug'], 'nilai' => $row];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Rekap Kuesioner <?= $tahunQ ?> — Kecamatan Muaradua</title>
  <link rel="stylesheet" href="css/style.css"/>
  <style>
    body { background:#f0f4f8; }
    .rk-wrap  { max-width:1200px; margin:2rem auto; padding:0 1rem; }
    .rk-header{ background:linear-gradient(135deg,#0F2944,#1e6091); color:#fff; border-radius:12px; padding:2rem 2.5rem; margin-bottom:1.5rem; }
    .rk-header h1 { margin:0 0 .3rem; font-size:1.6rem; }
    .rk-header p  { margin:0; opacity:.8; }
    .rk-ctrl  { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; margin-bottom:1.25rem; }
    .rk-ytab  { padding:.35rem 1rem; border-radius:20px; font-size:.85rem; font-weight:600;
      text-decoration:none; border:2px solid #1a3a5c; color:#1a3a5c; background:#fff; transition:.15s; }
    .rk-ytab:hover,.rk-ytab.active { background:#1a3a5c; color:#fff; }
    .rk-layout { display:grid; grid-template-columns:220px 1fr; gap:1.25rem; }
    .rk-nav   { background:#fff; border-radius:10px; padding:1rem; box-shadow:0 1px 6px rgba(0,0,0,.08); height:fit-content; }
    .rk-nav-title { font-size:.7rem; font-weight:700; text-transform:uppercase; color:#999; padding:.5rem .5rem .25rem; }
    .rk-nav a { display:block; padding:.5rem .75rem; border-radius:6px; font-size:.8rem; color:#333;
      text-decoration:none; border-left:3px solid transparent; transition:.15s; }
    .rk-nav a:hover,.rk-nav a.active { background:#EEF3FA; color:#0F2944; border-left-color:#0F2944; font-weight:600; }
    .rk-content { background:#fff; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.08); overflow:auto; }
    .rk-sec-head { background:#0F2944; color:#fff; padding:.75rem 1.5rem; font-weight:700; font-size:1rem; }
    .rk-tbl { width:100%; border-collapse:collapse; font-size:.82rem; min-width:700px; }
    .rk-tbl th { background:#1a3a5c; color:#fff; padding:.55rem .75rem; text-align:left; white-space:nowrap; }
    .rk-tbl th.num { text-align:center; }
    .rk-tbl td { padding:.5rem .75rem; border-bottom:1px solid #eee; vertical-align:top; }
    .rk-tbl tr:nth-child(even) td { background:#f8fafe; }
    .rk-tbl td.num { text-align:center; font-weight:600; }
    .rk-tbl tr.total-row td { background:#0F2944!important; color:#fff; font-weight:700; }
    .rk-empty { text-align:center; padding:3rem; color:#aaa; }
    .rk-chip  { display:inline-block; background:#EEF3FA; color:#1a3a5c; padding:.15rem .55rem; border-radius:20px; font-size:.75rem; }
    @media(max-width:700px){.rk-layout{grid-template-columns:1fr;}.rk-nav{display:none;}}
  </style>
</head>
<body>
<div class="rk-wrap">
  <div class="rk-header">
    <h1>📊 Rekap Kuesioner Desa</h1>
    <p>Kecamatan Muaradua, Kabupaten OKU Selatan &nbsp;|&nbsp; Data Terkumpul dari Seluruh Desa</p>
  </div>

  <div class="rk-ctrl">
    <strong style="font-size:.9rem;">Tahun:</strong>
    <?php foreach ($tahunList as $t): ?>
    <a class="rk-ytab <?= $t==$tahunQ?'active':'' ?>"
       href="?tahun=<?=$t?>&bagian=<?=urlencode($bagianQ)?>"><?= $t ?></a>
    <?php endforeach; ?>
    <?php if(!$tahunList): ?>
    <span style="color:#aaa;font-size:.85rem;">Belum ada data kuesioner yang diselesaikan.</span>
    <?php endif; ?>
    <span class="rk-chip" style="margin-left:auto;"><?= count($desas) ?> Desa melaporkan</span>
  </div>

  <?php if (!$desas): ?>
  <div class="card"><div class="rk-empty">
    <div style="font-size:3rem;">📋</div>
    <p>Belum ada data kuesioner yang diselesaikan untuk tahun <strong><?= $tahunQ ?></strong>.</p>
  </div></div>
  <?php else: ?>
  <div class="rk-layout">
    <!-- Nav -->
    <div class="rk-nav">
      <div class="rk-nav-title">Kategori</div>
      <?php foreach (array_keys($soal) as $bg): ?>
      <a href="?tahun=<?=$tahunQ?>&bagian=<?=urlencode($bg)?>"
         class="<?= $bg===$bagianQ?'active':'' ?>"><?= htmlspecialchars($bg) ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Tabel rekap -->
    <div class="rk-content">
      <div class="rk-sec-head">
        <?= htmlspecialchars($bagianQ) ?> — Tahun <?= $tahunQ ?>
      </div>
      <div style="overflow-x:auto;">
      <table class="rk-tbl">
        <thead>
        <tr>
          <th>No</th>
          <th>Pertanyaan</th>
          <?php foreach ($rekapPerDesa as $d): ?>
          <th class="num" title="<?= htmlspecialchars($d['nama']) ?>">
            <?= htmlspecialchars(substr($d['nama'], 0, 12)) ?><?= strlen($d['nama'])>12?'…':'' ?>
          </th>
          <?php endforeach; ?>
          <th class="num" style="background:#145A86;">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rekapSoal as $i => $p):
          $isNum = $p['tipe'] === 'number';
          $total = $rekapTotal[$p['kode']] ?? null;
        ?>
        <tr>
          <td style="color:#888;text-align:center;"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($p['label']) ?></td>
          <?php foreach ($rekapPerDesa as $d):
            $v = $d['nilai'][$p['kode']] ?? null;
            $tampil = '—';
            if ($v !== null && $v !== '') {
              $tampil = $isNum ? number_format((float)$v,0,',','.') : htmlspecialchars($v,ENT_QUOTES);
            }
          ?>
          <td class="<?= $isNum?'num':'' ?>">
            <?php if ($d['nilai'][$p['kode']] !== null && $d['nilai'][$p['kode']] !== ''): ?>
            <a href="/muaradua-web/muaradua/<?= $d['slug'] ?>/kuesioner.php?tahun=<?=$tahunQ?>&bagian=<?=urlencode($bagianQ)?>"
               style="color:inherit;text-decoration:none;" title="Lihat detail <?= htmlspecialchars($d['nama']) ?>">
              <?= $tampil ?>
            </a>
            <?php else: echo '<span style="color:#ddd">—</span>'; endif; ?>
          </td>
          <?php endforeach; ?>
          <td class="num" style="background:#e8f4ff;color:#0F2944;">
            <?php if($isNum && $total !== null): echo number_format($total,0,',','.'); else: echo '—'; endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <!-- Baris total khusus angka -->
        <tr class="total-row">
          <td colspan="2" style="text-align:right;">JUMLAH TOTAL KECAMATAN:</td>
          <?php foreach ($rekapPerDesa as $d): ?>
          <td class="num">—</td>
          <?php endforeach; ?>
          <td class="num">✔</td>
        </tr>
      </table>
      </div>

      <div style="padding:.75rem 1.25rem;font-size:.78rem;color:#888;border-top:1px solid #eee;">
        Data bersumber dari kuesioner yang telah diselesaikan (status: Selesai) oleh admin masing-masing desa.
        Klik nilai untuk melihat detail halaman desa.
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div style="text-align:center;margin:2rem 0;font-size:.82rem;color:#aaa;">
    <a href="/muaradua-web/" style="color:#0F2944;">← Kembali ke Portal Kecamatan</a>
  </div>
</div>
</body>
</html>
