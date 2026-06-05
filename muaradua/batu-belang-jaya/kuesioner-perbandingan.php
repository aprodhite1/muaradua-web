<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

$soal = require '../../config/kuesioner_soal.php';

// Ambil semua tahun selesai
$stT = $pdo->prepare("SELECT tahun FROM kuesioner WHERE desa_id=? AND status='selesai' ORDER BY tahun ASC");
$stT->execute([$desa['id']]);
$allTahun = $stT->fetchAll(PDO::FETCH_COLUMN);

// Rentang 5 tahun: default 5 tahun terakhir dari data yang ada
$tahunMax = !empty($allTahun) ? max($allTahun) : (int)date('Y');
$tahunMin = $tahunMax - 4;
if (!empty($_GET['dari'])) $tahunMin = (int)$_GET['dari'];
if (!empty($_GET['sampai'])) $tahunMax = (int)$_GET['sampai'];
// Pastikan max 5 tahun
if ($tahunMax - $tahunMin > 4) $tahunMin = $tahunMax - 4;

$rentang = range($tahunMin, $tahunMax);

// Ambil data semua tahun dalam rentang
$nilaiByTahun = [];
foreach ($rentang as $thn) {
    $stK = $pdo->prepare("SELECT id FROM kuesioner WHERE desa_id=? AND tahun=? AND status='selesai' LIMIT 1");
    $stK->execute([$desa['id'], $thn]);
    $kid = $stK->fetchColumn();
    if ($kid) {
        $stN = $pdo->prepare("SELECT kode, nilai FROM kuesioner_nilai WHERE kuesioner_id=?");
        $stN->execute([$kid]);
        foreach ($stN->fetchAll() as $r) $nilaiByTahun[$thn][$r['kode']] = $r['nilai'];
    }
}

$tahunAda = array_filter($rentang, fn($t) => isset($nilaiByTahun[$t]));

// Ikon per bagian
$ikonBagian = [
    'I. IDENTITAS DESA'              => '🗺️',
    'II. PEMERINTAHAN DESA'          => '🏛️',
    'III. KEPENDUDUKAN'              => '👥',
    'IV. PENDIDIKAN'                 => '🎓',
    'V. KESEHATAN'                   => '🏥',
    'VI. EKONOMI'                    => '💼',
    'VII. INFRASTRUKTUR'             => '🏗️',
    'VIII. SOSIAL BUDAYA & POTENSI DESA' => '🌿',
];

// Bagian aktif
$bagianList = array_keys($soal);
$bagianQ    = trim($_GET['bagian'] ?? '');
if (!$bagianQ || !isset($soal[$bagianQ])) $bagianQ = $bagianList[0];

// Format nilai
function fmtVal($v, $tipe, $label) {
    if ($v === null || $v === '') return null;
    if ($tipe === 'number') {
        $n = number_format((float)$v, 0, ',', '.');
        if (stripos($label, '(Rp)') !== false) return 'Rp '.$n;
        return $n;
    }
    return htmlspecialchars($v, ENT_QUOTES);
}

// Delta (selisih) antara tahun pertama dan terakhir yang ada data
function deltaClass($a, $b) {
    if ($a === null || $b === null) return '';
    if (is_numeric($a) && is_numeric($b)) {
        $d = (float)$b - (float)$a;
        if ($d > 0) return 'trend-up';
        if ($d < 0) return 'trend-down';
    }
    return '';
}
function deltaIcon($a, $b) {
    if ($a === null || $b === null) return '';
    if (is_numeric($a) && is_numeric($b)) {
        $d = (float)$b - (float)$a;
        if ($d > 0) return '↑';
        if ($d < 0) return '↓';
        return '→';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Perbandingan <?= $tahunMin ?>–<?= $tahunMax ?> – Desa <?= e($desa['nama']) ?></title>
  <meta name="description" content="Perbandingan data kuesioner desa <?= e($desa['nama']) ?> tahun <?= $tahunMin ?>–<?= $tahunMax ?>"/>
  <link rel="stylesheet" href="../../css/style.css"/>
  <style>
    body { background:#F4F7FB; }

    /* HERO */
    .pb-hero {
      background:linear-gradient(135deg,#0b1e35 0%,#1a4068 60%,#1e6091 100%);
      color:#fff; padding:2rem 0 3.5rem; position:relative; overflow:hidden;
    }
    .pb-hero-wave { position:absolute; bottom:-1px; left:0; width:100%; line-height:0; z-index:2; }
    .pb-hero h1 { color:#fff; margin-bottom:.35rem; font-size:clamp(1.3rem,3vw,1.9rem); }
    .pb-hero p  { color:rgba(255,255,255,.78); font-size:.9rem; }

    /* RANGE FORM */
    .pb-form {
      display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
      background:rgba(255,255,255,.12); backdrop-filter:blur(8px);
      border:1px solid rgba(255,255,255,.2); border-radius:10px;
      padding:.75rem 1.25rem; margin-top:1.25rem; font-size:.88rem;
    }
    .pb-form label { color:rgba(255,255,255,.85); font-weight:600; }
    .pb-form input[type=number] {
      width:80px; border:1.5px solid rgba(255,255,255,.4); border-radius:6px;
      padding:.3rem .55rem; font-size:.85rem; background:rgba(255,255,255,.12);
      color:#fff; outline:none;
    }
    .pb-form input[type=number]:focus { border-color:#fff; }
    .pb-form .pb-submit {
      padding:.35rem 1.1rem; border-radius:6px; border:2px solid #fff;
      background:transparent; color:#fff; font-weight:700; font-size:.85rem;
      cursor:pointer; transition:.15s;
    }
    .pb-form .pb-submit:hover { background:#fff; color:#0b1e35; }

    /* LAYOUT */
    .pb-layout { display:grid; grid-template-columns:220px 1fr; gap:1.5rem; padding:2rem 0 3rem; align-items:start; }

    /* SIDEBAR */
    .pb-sidebar { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden; position:sticky; top:80px; }
    .pb-sidebar-head { background:linear-gradient(135deg,#0b1e35,#1a4068); color:#fff; padding:.9rem 1.1rem; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
    .pb-nav-item {
      display:flex; align-items:center; gap:.6rem; padding:.65rem 1.1rem;
      font-size:.82rem; color:#444; text-decoration:none; border-left:3px solid transparent;
      border-bottom:1px solid #f0f4f8; transition:.15s;
    }
    .pb-nav-item:last-child { border-bottom:none; }
    .pb-nav-item:hover { background:#EEF3FA; color:#0b1e35; border-left-color:#0b1e35; }
    .pb-nav-item.active { background:#EEF3FA; color:#0b1e35; border-left-color:#0b1e35; font-weight:700; }
    .pb-nav-icon { font-size:1rem; width:22px; text-align:center; flex-shrink:0; }

    /* NO DATA */
    .pb-nodata {
      background:#fff; border-radius:12px; padding:3.5rem 2rem; text-align:center;
      box-shadow:0 2px 12px rgba(0,0,0,.07);
    }
    .pb-nodata-icon { font-size:3rem; margin-bottom:1rem; }

    /* TABLE CARD */
    .pb-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden; margin-bottom:1.25rem; }
    .pb-card:last-child { margin-bottom:0; }
    .pb-card-head {
      display:flex; align-items:center; gap:.75rem; padding:.8rem 1.25rem;
      background:linear-gradient(135deg,#0b1e35,#1a4068); color:#fff;
      font-weight:700; font-size:.92rem;
    }
    .pb-card-icon { font-size:1.2rem; }

    /* COMPARISON TABLE */
    .pb-tbl-wrap { overflow-x:auto; }
    .pb-tbl {
      width:100%; border-collapse:collapse; font-size:.83rem;
      min-width:500px;
    }
    .pb-tbl thead th {
      padding:.6rem .9rem; background:#0b1e35; color:#fff; font-weight:700;
      font-size:.77rem; text-align:center; white-space:nowrap; border-right:1px solid rgba(255,255,255,.12);
    }
    .pb-tbl thead th:first-child { text-align:left; background:#0b1e35; min-width:200px; }
    .pb-tbl thead th.th-year { min-width:90px; }
    .pb-tbl thead th.th-trend { min-width:80px; background:#163150; }
    .pb-tbl tbody tr:nth-child(even) td { background:#f8fbff; }
    .pb-tbl tbody td {
      padding:.6rem .9rem; border-bottom:1px solid #eef2f7;
      border-right:1px solid #eef2f7; font-size:.82rem; text-align:center;
    }
    .pb-tbl tbody td:first-child { text-align:left; color:#555; font-weight:500; }
    .pb-tbl tbody tr:last-child td { border-bottom:none; }

    /* Sub-section row */
    .pb-tbl .tr-sub td {
      background:#EEF3FA; color:#0b1e35; font-weight:700; font-size:.75rem;
      text-transform:uppercase; letter-spacing:.06em; text-align:left;
      border-left:4px solid #0b1e35; padding:.4rem .9rem;
    }

    /* Values */
    .val-num     { color:#1a5276; font-weight:700; }
    .val-rp      { color:#059669; font-weight:700; }
    .val-text    { color:#333; }
    .val-empty   { color:#ccc; font-style:italic; font-size:.75rem; }

    /* Trend indicator */
    .trend-up   { color:#059669; font-weight:700; }
    .trend-down { color:#DC2626; font-weight:700; }
    .trend-flat { color:#6B7280; }

    /* Year badge — available vs missing */
    .year-badge {
      display:inline-block; padding:.15rem .5rem; border-radius:20px;
      font-size:.72rem; font-weight:700; margin-bottom:.25rem;
    }
    .year-ada     { background:#D1FAE5; color:#065F46; }
    .year-missing { background:#FEF3C7; color:#92400E; }

    /* Summary bar */
    .pb-summary {
      display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr));
      gap:.75rem; margin-bottom:1.5rem;
    }
    .pb-sum-card {
      background:#fff; border-radius:10px; padding:.85rem 1rem;
      box-shadow:0 2px 8px rgba(0,0,0,.06); text-align:center; border-top:3px solid;
    }
    .pb-sum-card .year-lbl { font-size:.7rem; color:#999; font-weight:600; margin-bottom:.2rem; }
    .pb-sum-card .year-status { font-size:.78rem; font-weight:700; }

    /* Nav prev/next */
    .pb-nav-arrows { display:flex; gap:1rem; justify-content:space-between; margin-top:1rem; flex-wrap:wrap; }

    @media(max-width:800px) {
      .pb-layout { grid-template-columns:1fr; }
      .pb-sidebar { position:static; }
    }
  </style>
</head>
<body>

<?php $currentPage = 'kuesioner'; require '_navbar.php'; ?>

<!-- HERO -->
<section class="pb-hero">
  <div class="container">
    <div class="animate-fade-in-up">
      <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:.4rem;">📊 Perbandingan Multi-Tahun</div>
      <h1><?= e($desa['emoji']) ?> Desa <?= e($desa['nama']) ?></h1>
      <p>Perbandingan data kuesioner desa dalam satu tabel · Tahun <?= $tahunMin ?>–<?= $tahunMax ?></p>

      <!-- FORM RENTANG TAHUN -->
      <form method="get" class="pb-form">
        <label>Rentang Tahun:</label>
        <input type="number" name="dari"   value="<?= $tahunMin ?>" min="2015" max="2099" placeholder="2021"/>
        <span style="color:#fff;">–</span>
        <input type="number" name="sampai" value="<?= $tahunMax ?>" min="2015" max="2099" placeholder="2025"/>
        <input type="hidden" name="bagian" value="<?= htmlspecialchars($bagianQ) ?>"/>
        <button type="submit" class="pb-submit">Tampilkan</button>
        <a href="kuesioner.php" style="color:rgba(255,255,255,.7);font-size:.82rem;margin-left:.5rem;">← Per Tahun</a>
      </form>
    </div>
  </div>
  <div class="pb-hero-wave">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 50" preserveAspectRatio="none">
      <path fill="#F4F7FB" d="M0,25 C360,50 1080,0 1440,25 L1440,50 L0,50 Z"/>
    </svg>
  </div>
</section>

<div class="container">

  <!-- STATUS TAHUN -->
  <div class="pb-summary animate-fade-in-up" style="margin-top:1.5rem;">
    <?php foreach ($rentang as $thn):
      $ada = isset($nilaiByTahun[$thn]);
    ?>
    <div class="pb-sum-card" style="border-color:<?= $ada ? '#059669' : '#F59E0B' ?>">
      <div class="year-lbl">Tahun</div>
      <div style="font-size:1.1rem;font-weight:800;color:#111;"><?= $thn ?></div>
      <div class="year-status" style="color:<?= $ada ? '#059669' : '#F59E0B' ?>">
        <?= $ada ? '✅ Ada' : '⚠️ Kosong' ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($tahunAda)): ?>
  <div class="pb-nodata">
    <div class="pb-nodata-icon">📋</div>
    <h3>Belum Ada Data</h3>
    <p style="color:#888;">Tidak ada data kuesioner selesai untuk rentang tahun <?= $tahunMin ?>–<?= $tahunMax ?>.</p>
    <a href="kuesioner.php" class="btn btn-outline" style="margin-top:1rem;">← Lihat Per Tahun</a>
  </div>
  <?php else: ?>

  <!-- LAYOUT GRID -->
  <div class="pb-layout">

    <!-- SIDEBAR KATEGORI -->
    <div class="pb-sidebar">
      <div class="pb-sidebar-head">📂 Kategori</div>
      <?php foreach ($soal as $bg => $perList):
        $ikon = $ikonBagian[$bg] ?? '📄';
        $url  = '?dari='.$tahunMin.'&sampai='.$tahunMax.'&bagian='.urlencode($bg);
      ?>
      <a href="<?= $url ?>" class="pb-nav-item <?= $bg===$bagianQ?'active':'' ?>">
        <span class="pb-nav-icon"><?= $ikon ?></span>
        <?= htmlspecialchars($bg) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- CONTENT -->
    <div>
      <?php
      $ikon = $ikonBagian[$bagianQ] ?? '📄';
      $kolTahun = array_values($tahunAda);
      ?>
      <div class="pb-card animate-fade-in-up">
        <div class="pb-card-head">
          <span class="pb-card-icon"><?= $ikon ?></span>
          <?= htmlspecialchars($bagianQ) ?>
          <span style="margin-left:auto;font-size:.75rem;opacity:.8;"><?= count($kolTahun) ?> tahun data tersedia</span>
        </div>

        <div class="pb-tbl-wrap">
          <table class="pb-tbl">
            <thead>
              <tr>
                <th>Indikator</th>
                <?php foreach ($kolTahun as $thn): ?>
                <th class="th-year"><?= $thn ?></th>
                <?php endforeach; ?>
                <?php if (count($kolTahun) >= 2): ?>
                <th class="th-trend">Tren</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            foreach ($soal[$bagianQ] as $p):
              $kode  = $p['kode'];
              $tipe  = $p['tipe'];
              $isNum = $tipe === 'number';
              $isRp  = stripos($p['label'], '(Rp)') !== false;

              // Sub-section header row
              if (!empty($p['sub'])):
            ?>
              <tr class="tr-sub"><td colspan="<?= count($kolTahun) + (count($kolTahun)>=2?2:1) ?>">
                <?= htmlspecialchars($p['sub']) ?>
              </td></tr>
            <?php endif; ?>
              <tr>
                <td><?= $no++ ?>. <?= htmlspecialchars($p['label']) ?></td>
                <?php
                $firstVal = null; $lastVal = null;
                foreach ($kolTahun as $thn):
                  $raw = $nilaiByTahun[$thn][$kode] ?? null;
                  if ($raw !== null && $raw !== '') {
                    if ($firstVal === null) $firstVal = $raw;
                    $lastVal = $raw;
                  }
                  $tampil = fmtVal($raw, $tipe, $p['label']);
                  $cls = $tampil ? ($isRp ? 'val-rp' : ($isNum ? 'val-num' : 'val-text')) : 'val-empty';
                ?>
                <td class="<?= $cls ?>"><?= $tampil ?? '<span class="val-empty">—</span>' ?></td>
                <?php endforeach; ?>
                <?php if (count($kolTahun) >= 2):
                  $tc  = deltaClass($firstVal, $lastVal);
                  $ico = deltaIcon($firstVal, $lastVal);
                  // Hitung selisih absolut kalau numerik
                  $diff = '';
                  if ($isNum && $firstVal !== null && $lastVal !== null) {
                      $d = (float)$lastVal - (float)$firstVal;
                      if ($d != 0) {
                          $diff = ($d > 0 ? '+' : '') . number_format($d, 0, ',', '.');
                          if ($isRp) $diff = ($d > 0 ? '+' : '-') . 'Rp ' . number_format(abs($d), 0, ',', '.');
                      }
                  }
                ?>
                <td class="<?= $tc ?: 'trend-flat' ?>">
                  <?= $ico ?>
                  <?php if ($diff): ?><br><small style="font-size:.7rem;"><?= $diff ?></small><?php endif; ?>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- NAVIGASI PREV/NEXT -->
      <?php
      $idx  = array_search($bagianQ, $bagianList);
      $prev = $idx > 0 ? $bagianList[$idx-1] : null;
      $next = $idx < count($bagianList)-1 ? $bagianList[$idx+1] : null;
      $qBase = '?dari='.$tahunMin.'&sampai='.$tahunMax.'&bagian=';
      ?>
      <div class="pb-nav-arrows">
        <?php if ($prev): ?>
        <a href="<?= $qBase.urlencode($prev) ?>" class="btn btn-outline" style="font-size:.85rem;">← <?= htmlspecialchars($prev) ?></a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?>
        <a href="<?= $qBase.urlencode($next) ?>" class="btn btn-outline" style="font-size:.85rem;"><?= htmlspecialchars($next) ?> →</a>
        <?php endif; ?>
      </div>

    </div><!-- /content -->
  </div><!-- /layout -->
  <?php endif; ?>

</div><!-- /.container -->

<!-- FOOTER -->
<footer class="footer" style="margin-top:3rem;">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo"><?= e($desa['emoji']) ?></div>
        <h4>Desa <?= e($desa['nama']) ?></h4>
        <p>Kecamatan Muaradua, OKU Selatan</p>
      </div>
      <div>
        <h4>Navigasi</h4>
        <ul>
          <li><a href="index.php">Beranda</a></li>
          <li><a href="kuesioner.php">Kuesioner Desa</a></li>
          <li><a href="kuesioner-perbandingan.php">Perbandingan Multi-Tahun</a></li>
          <li><a href="login.php">Admin Login</a></li>
        </ul>
      </div>
      <div>
        <h4>Kecamatan</h4>
        <ul><li><a href="/muaradua-web/">← Kecamatan Muaradua</a></li></ul>
      </div>
    </div>
    <div class="footer-bottom"><p>© <?= date('Y') ?> Desa <?= e($desa['nama']) ?> · Kecamatan Muaradua</p></div>
  </div>
</footer>

</body>
</html>
