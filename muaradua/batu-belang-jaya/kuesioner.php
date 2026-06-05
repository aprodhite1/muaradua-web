<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

$soal    = require '../../config/kuesioner_soal.php';
$tahunQ  = intval($_GET['tahun'] ?? date('Y'));
$bagianQ = trim($_GET['bagian'] ?? '');

// Daftar tahun tersedia (hanya yg selesai)
$stTahun = $pdo->prepare("SELECT tahun FROM kuesioner WHERE desa_id=? AND status='selesai' ORDER BY tahun DESC");
$stTahun->execute([$desa['id']]);
$tahunList = $stTahun->fetchAll(PDO::FETCH_COLUMN);

// Ambil kuesioner header
$stK = $pdo->prepare("SELECT * FROM kuesioner WHERE desa_id=? AND tahun=? AND status='selesai' LIMIT 1");
$stK->execute([$desa['id'], $tahunQ]);
$kHeader = $stK->fetch() ?: null;

$nilai = [];
if ($kHeader) {
    $stN = $pdo->prepare("SELECT kode, nilai FROM kuesioner_nilai WHERE kuesioner_id=?");
    $stN->execute([$kHeader['id']]);
    foreach ($stN->fetchAll() as $r) $nilai[$r['kode']] = $r['nilai'];
}

$bagianList = array_keys($soal);
if (!$bagianQ || !isset($soal[$bagianQ])) $bagianQ = $bagianList[0];

// Helper: format nilai tampil
function formatNilai($val, $tipe, $label) {
    if ($val === null || $val === '') return null;
    if ($tipe === 'number') {
        $n = number_format((float)$val, 0, ',', '.');
        if (stripos($label, '(Rp)') !== false) return 'Rp ' . $n;
        return $n;
    }
    return htmlspecialchars($val, ENT_QUOTES);
}

// Hitung jumlah field terisi per bagian
$statsBagian = [];
foreach ($soal as $bg => $perList) {
    $terisi = 0;
    foreach ($perList as $p) {
        if (isset($nilai[$p['kode']]) && $nilai[$p['kode']] !== '') $terisi++;
    }
    $statsBagian[$bg] = ['terisi' => $terisi, 'total' => count($perList)];
}

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

$base = desaBaseUrl($slug);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Profil Desa <?= e($desa['nama']) ?> <?= $tahunQ ?> — Kecamatan Muaradua</title>
  <meta name="description" content="Data profil dan potensi Desa <?= e($desa['nama']) ?> tahun <?= $tahunQ ?>, Kecamatan Muaradua, OKU Selatan."/>
  <link rel="stylesheet" href="../../css/style.css"/>
  <style>
    body { background:#F4F7FB; }

    /* ── PAGE HERO ── */
    .kq-hero {
      background: linear-gradient(135deg,#0F2944 0%,#1a5276 60%,#2471a3 100%);
      color:#fff; padding:2.5rem 0 4rem; position:relative; overflow:hidden;
    }
    .kq-hero::after {
      content:''; position:absolute; bottom:-2px; left:0; width:100%; line-height:0;
    }
    .kq-hero-inner { display:flex; align-items:center; gap:2rem; flex-wrap:wrap; }
    .kq-hero-icon { font-size:4rem; filter:drop-shadow(0 4px 16px rgba(0,0,0,.3)); flex-shrink:0; }
    .kq-hero h1 { color:#fff; margin-bottom:.4rem; font-size:clamp(1.4rem,3vw,2rem); }
    .kq-hero p  { color:rgba(255,255,255,.8); font-size:.9rem; }
    .kq-hero-wave { position:absolute; bottom:-1px; left:0; width:100%; line-height:0; z-index:2; }

    /* ── YEAR TABS ── */
    .kq-year-bar { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:1.75rem; }
    .kq-ytab {
      padding:.35rem 1.1rem; border-radius:20px; font-size:.83rem; font-weight:600;
      text-decoration:none; border:2px solid #1a3a5c; color:#1a3a5c; background:#fff;
      transition:.18s;
    }
    .kq-ytab:hover,.kq-ytab.active { background:#1a3a5c; color:#fff; }

    /* ── LAYOUT ── */
    .kq-layout { display:grid; grid-template-columns:260px 1fr; gap:1.5rem; align-items:start; padding:2rem 0 3rem; }

    /* ── SIDEBAR ── */
    .kq-sidebar { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden; position:sticky; top:80px; }
    .kq-sidebar-head {
      background:linear-gradient(135deg,#0F2944,#1a5276); color:#fff;
      padding:1rem 1.25rem; font-weight:700; font-size:.85rem; letter-spacing:.04em; text-transform:uppercase;
    }
    .kq-nav-item {
      display:flex; align-items:center; gap:.75rem; padding:.7rem 1.1rem;
      font-size:.83rem; color:#444; text-decoration:none; border-left:3px solid transparent;
      transition:.15s; border-bottom:1px solid #f0f4f8;
    }
    .kq-nav-item:last-child { border-bottom:none; }
    .kq-nav-item:hover { background:#EEF3FA; color:#0F2944; border-left-color:#0F2944; }
    .kq-nav-item.active { background:#EEF3FA; color:#0F2944; border-left-color:#0F2944; font-weight:700; }
    .kq-nav-icon { font-size:1.1rem; width:24px; text-align:center; flex-shrink:0; }
    .kq-nav-label { flex:1; line-height:1.3; }
    .kq-nav-prog {
      font-size:.7rem; font-weight:700; padding:.1rem .45rem; border-radius:10px;
      background:#e8f4ff; color:#1a5276; flex-shrink:0;
    }
    .kq-nav-item.active .kq-nav-prog { background:#0F2944; color:#fff; }

    /* ── CONTENT AREA ── */
    .kq-content-wrap { min-width:0; }

    /* Kosong/tidak ada data */
    .kq-empty {
      background:#fff; border-radius:14px; padding:4rem 2rem; text-align:center;
      box-shadow:0 2px 12px rgba(0,0,0,.07);
    }
    .kq-empty-icon { font-size:3.5rem; margin-bottom:1rem; }

    /* ── SECTION CARD ── */
    .kq-section-card {
      background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07);
      overflow:hidden; margin-bottom:1.25rem;
    }
    .kq-section-card:last-child { margin-bottom:0; }
    .kq-sec-head {
      display:flex; align-items:center; gap:.75rem;
      background:linear-gradient(135deg,#0F2944,#1a5276); color:#fff;
      padding:.85rem 1.5rem; font-weight:700; font-size:.95rem;
    }
    .kq-sec-icon { font-size:1.3rem; }
    .kq-sec-progress {
      margin-left:auto; font-size:.75rem; background:rgba(255,255,255,.2);
      padding:.2rem .65rem; border-radius:12px; font-weight:600;
    }

    /* ── DATA TABLE ── */
    .kq-data-table { width:100%; border-collapse:collapse; }
    .kq-data-table tr:nth-child(even) td { background:#f8fafe; }
    .kq-data-table td { padding:.7rem 1.25rem; border-bottom:1px solid #eef2f7; vertical-align:top; font-size:.875rem; }
    .kq-data-table tr:last-child td { border-bottom:none; }
    .td-no  { width:5%; color:#aaa; font-size:.75rem; text-align:center; font-weight:600; }
    .td-lbl { width:40%; color:#555; }
    .td-sep { width:3%; color:#ccc; text-align:center; }
    .td-val { font-weight:600; color:#111; white-space:pre-line; }
    .td-val.empty { color:#ccc; font-weight:400; font-style:italic; }
    .td-val.number { color:#1a5276; }
    .td-val.currency { color:#059669; }

    /* ── SUMMARY STATS STRIP ── */
    .kq-summary {
      display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr));
      gap:1rem; margin-bottom:1.5rem;
    }
    .kq-sum-card {
      background:#fff; border-radius:10px; padding:1rem 1.25rem;
      box-shadow:0 2px 8px rgba(0,0,0,.06); border-left:4px solid;
      transition:.2s;
    }
    .kq-sum-card:hover { transform:translateY(-3px); box-shadow:0 4px 16px rgba(0,0,0,.1); }
    .kq-sum-card .label { font-size:.72rem; color:#888; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.25rem; }
    .kq-sum-card .value { font-size:1.3rem; font-weight:800; color:#111; line-height:1.2; }
    .kq-sum-card .sub   { font-size:.72rem; color:#aaa; margin-top:.15rem; }

    /* ── METADATA FOOTER ── */
    .kq-meta-bar {
      background:#fff; border-radius:10px; padding:.85rem 1.25rem;
      box-shadow:0 2px 8px rgba(0,0,0,.06); display:flex; align-items:center;
      flex-wrap:wrap; gap:1rem; font-size:.8rem; color:#888; margin-bottom:1.5rem;
    }
    .kq-meta-bar strong { color:#333; }
    .kq-badge-selesai { background:#D1FAE5; color:#065F46; padding:.2rem .65rem; border-radius:12px; font-weight:700; font-size:.75rem; }

    /* ── NO-DATA CHIP ── */
    .chip-empty { display:inline-block; background:#f5f5f5; color:#bbb; padding:.15rem .55rem; border-radius:8px; font-size:.75rem; }

    /* ── SUB-SECTION ── */
    .kq-subsec {
      background:#EEF3FA; border-left:4px solid #1a5276; padding:.45rem 1.25rem;
      font-size:.78rem; font-weight:700; color:#1a3a5c; text-transform:uppercase;
      letter-spacing:.06em;
    }

    @media(max-width:800px) {
      .kq-layout { grid-template-columns:1fr; }
      .kq-sidebar { position:static; }
      .kq-nav-item { padding:.6rem 1rem; }
      .kq-data-table td { padding:.55rem .9rem; }
    }
    @media(max-width:480px) {
      .kq-hero { padding:1.75rem 0 3rem; }
      .kq-data-table .td-no,.kq-data-table .td-sep { display:none; }
      .kq-data-table .td-lbl { width:50%; }
    }
  </style>
</head>
<body>

<?php $currentPage = 'kuesioner'; require '_navbar.php'; ?>


<!-- HERO -->
<section class="kq-hero">
  <div class="container">
    <div class="kq-hero-inner animate-fade-in-up">
      <div class="kq-hero-icon"><?= e($desa['emoji']) ?></div>
      <div>
        <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:.4rem;">📋 Profil & Potensi Desa</div>
        <h1>Desa <?= e($desa['nama']) ?></h1>
        <p>Data potensi dan kondisi desa tahun <?= $tahunQ ?> · Kecamatan Muaradua, OKU Selatan</p>
      </div>
    </div>
  </div>
  <div class="kq-hero-wave">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none">
      <path fill="#F4F7FB" d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z"/>
    </svg>
  </div>
</section>

<div class="container">

  <!-- YEAR TABS & META -->
  <div style="padding-top:1.5rem;">
    <?php if ($tahunList): ?>
    <div class="kq-year-bar">
      <strong style="font-size:.85rem;color:#555;">Tahun Data:</strong>
      <?php foreach ($tahunList as $t): ?>
      <a class="kq-ytab <?= $t==$tahunQ?'active':'' ?>"
         href="?tahun=<?=$t?>&bagian=<?=urlencode($bagianQ)?>"><?= $t ?></a>
      <?php endforeach; ?>
      <a class="kq-ytab" href="kuesioner-perbandingan.php"
         style="border-color:#7C3AED;color:#7C3AED;margin-left:.5rem;"
         title="Lihat perbandingan multi-tahun">📊 Perbandingan</a>
    </div>
    <?php endif; ?>

    <?php if ($kHeader): ?>
    <!-- META BAR -->
    <div class="kq-meta-bar">
      <span>📅 Data per: <strong><?= date('d M Y', strtotime($kHeader['tanggal_pengisian'] ?? $kHeader['updated_at'])) ?></strong></span>
      <span>✍️ Diisi oleh: <strong><?= e($kHeader['pengisi_nama'] ?? '-') ?></strong> (<?= e($kHeader['pengisi_jabatan'] ?? '-') ?>)</span>
      <span class="kq-badge-selesai">✅ Data Final</span>
      <a href="/muaradua-web/rekap-kuesioner.php?tahun=<?=$tahunQ?>" style="margin-left:auto;font-size:.8rem;color:#1a5276;">📊 Lihat Rekap Kecamatan →</a>
    </div>

    <!-- SUMMARY STATS (angka kunci dari kuesioner) -->
    <?php
    $sumKeys = [
      ['kode'=>'pddk_total',  'label'=>'Total Penduduk', 'sub'=>'jiwa',  'color'=>'#1a5276'],
      ['kode'=>'jml_kk',      'label'=>'Kepala Keluarga','sub'=>'KK',    'color'=>'#E8820C'],
      ['kode'=>'luas_wilayah','label'=>'Luas Wilayah',   'sub'=>'Ha',    'color'=>'#059669'],
      ['kode'=>'jml_rt',      'label'=>'Jumlah RT',      'sub'=>'RT',    'color'=>'#7C3AED'],
      ['kode'=>'jml_umkm',    'label'=>'UMKM',           'sub'=>'unit',  'color'=>'#DC2626'],
      ['kode'=>'fas_posyandu','label'=>'Posyandu',        'sub'=>'unit',  'color'=>'#DB2777'],
    ];
    $hasSum = false;
    foreach ($sumKeys as $s) { if (!empty($nilai[$s['kode']])) { $hasSum = true; break; } }
    if ($hasSum):
    ?>
    <div class="kq-summary animate-fade-in-up delay-1">
      <?php foreach ($sumKeys as $s):
        $v = $nilai[$s['kode']] ?? null;
        if (!$v) continue;
        $disp = is_numeric($v) ? number_format((float)$v, 0, ',', '.') : htmlspecialchars($v);
      ?>
      <div class="kq-sum-card" style="border-color:<?= $s['color'] ?>;">
        <div class="label"><?= $s['label'] ?></div>
        <div class="value" style="color:<?= $s['color'] ?>"><?= $disp ?></div>
        <div class="sub"><?= $s['sub'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- LAYOUT GRID -->
    <div class="kq-layout">

      <!-- SIDEBAR NAV -->
      <div class="kq-sidebar">
        <div class="kq-sidebar-head">📂 Kategori Data</div>
        <?php foreach ($soal as $bg => $perList):
          $ikon = $ikonBagian[$bg] ?? '📄';
          $st   = $statsBagian[$bg];
          $pct  = $st['total'] ? round($st['terisi']/$st['total']*100) : 0;
          $url  = '?tahun='.$tahunQ.'&bagian='.urlencode($bg);
        ?>
        <a href="<?= $url ?>" class="kq-nav-item <?= $bg===$bagianQ?'active':'' ?>">
          <span class="kq-nav-icon"><?= $ikon ?></span>
          <span class="kq-nav-label"><?= htmlspecialchars($bg) ?></span>
          <span class="kq-nav-prog" title="<?= $st['terisi'] ?>/<?= $st['total'] ?> terisi"><?= $pct ?>%</span>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- CONTENT -->
      <div class="kq-content-wrap">
        <?php
        $ikon = $ikonBagian[$bagianQ] ?? '📄';
        $st   = $statsBagian[$bagianQ];
        ?>
        <div class="kq-section-card animate-fade-in-up">
          <div class="kq-sec-head">
            <span class="kq-sec-icon"><?= $ikon ?></span>
            <?= htmlspecialchars($bagianQ) ?>
            <span class="kq-sec-progress"><?= $st['terisi'] ?>/<?= $st['total'] ?> terisi</span>
          </div>
          <?php
          $no = 1;
          $inTable = false;
          foreach ($soal[$bagianQ] as $p):
            $val    = $nilai[$p['kode']] ?? null;
            $isNum  = $p['tipe'] === 'number';
            $isRp   = stripos($p['label'], '(Rp)') !== false;
            $tampil = null; $cls = '';
            if ($val !== null && $val !== '') {
              if ($isNum && $isRp) {
                $tampil = 'Rp ' . number_format((float)$val, 0, ',', '.');
                $cls    = 'currency';
              } elseif ($isNum) {
                $tampil = number_format((float)$val, 0, ',', '.');
                $cls    = 'number';
              } else {
                $tampil = htmlspecialchars($val, ENT_QUOTES);
              }
            }
            // Start sub-section? Close current table first
            if (!empty($p['sub'])):
              if ($inTable) { echo '</table>'; $inTable = false; }
              echo '<div class="kq-subsec">' . htmlspecialchars($p['sub']) . '</div>';
            endif;
            // Open table if not already open
            if (!$inTable) { echo '<table class="kq-data-table">'; $inTable = true; }
          ?>
          <tr>
            <td class="td-no"><?= $no++ ?></td>
            <td class="td-lbl"><?= htmlspecialchars($p['label']) ?></td>
            <td class="td-sep">:</td>
            <td class="td-val <?= $tampil ? $cls : 'empty' ?>">
              <?php if ($tampil): echo $tampil;
              else: ?><span class="chip-empty">Belum diisi</span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach;
          if ($inTable) echo '</table>';
          ?>
        </div>

        <!-- NAVIGASI ANTAR BAGIAN -->
        <?php
        $idx  = array_search($bagianQ, $bagianList);
        $prev = $idx > 0 ? $bagianList[$idx-1] : null;
        $next = $idx < count($bagianList)-1 ? $bagianList[$idx+1] : null;
        ?>
        <div style="display:flex;gap:1rem;justify-content:space-between;margin-top:1rem;flex-wrap:wrap;">
          <?php if ($prev): ?>
          <a href="?tahun=<?=$tahunQ?>&bagian=<?=urlencode($prev)?>" class="btn btn-outline" style="font-size:.85rem;">
            ← <?= htmlspecialchars($prev) ?>
          </a>
          <?php else: ?><span></span><?php endif; ?>
          <?php if ($next): ?>
          <a href="?tahun=<?=$tahunQ?>&bagian=<?=urlencode($next)?>" class="btn btn-outline" style="font-size:.85rem;">
            <?= htmlspecialchars($next) ?> →
          </a>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /.kq-layout -->

    <?php else: /* Tidak ada kHeader */ ?>
    <div class="kq-empty animate-fade-in-up" style="margin:2rem 0;">
      <div class="kq-empty-icon">📋</div>
      <h3 style="margin-bottom:.5rem;">Data Belum Tersedia</h3>
      <p style="color:#888;">Data profil desa tahun <strong><?= $tahunQ ?></strong> untuk Desa <?= e($desa['nama']) ?> belum tersedia.</p>
      <?php if ($tahunList): ?>
      <div style="margin-top:1.25rem;display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;">
        <span style="font-size:.85rem;color:#999;">Tersedia untuk tahun:</span>
        <?php foreach ($tahunList as $t): ?>
        <a href="?tahun=<?=$t?>" class="kq-ytab"><?= $t ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div><!-- /padding-top -->

</div><!-- /.container -->

<!-- FOOTER -->
<footer class="footer" style="margin-top:3rem;">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo"><?= e($desa['emoji']) ?></div>
        <h4>Desa <?= e($desa['nama']) ?></h4>
        <p>Kecamatan Muaradua, Kabupaten OKU Selatan, Sumatera Selatan</p>
      </div>
      <div>
        <h4>Navigasi</h4>
        <ul>
          <li><a href="index.php">Beranda</a></li>
          <li><a href="data.php">Data Desa</a></li>
          <li><a href="infografis.php">Infografis</a></li>
          <li><a href="kuesioner.php">Profil Desa</a></li>
          <li><a href="login.php">Admin Login</a></li>
        </ul>
      </div>
      <div>
        <h4>Kecamatan</h4>
        <ul>
          <li><a href="/muaradua-web/">← Kecamatan Muaradua</a></li>
          <li><a href="/muaradua-web/rekap-kuesioner.php">Rekap Kuesioner</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom"><p>© <?= date('Y') ?> Desa <?= e($desa['nama']) ?> · Kecamatan Muaradua</p></div>
  </div>
</footer>

<script>
document.getElementById('hamburgerBtn').addEventListener('click', function(){
  document.getElementById('villageNav').classList.toggle('open');
});
</script>
</body>
</html>
