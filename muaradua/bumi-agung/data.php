<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

// Helper: buat slug aman dari nama kategori
function katSlug(string $kat): string {
    return preg_replace('/[^a-z0-9]+/', '-', strtolower($kat));
}

// Data penduduk
$stmtP = $pdo->prepare("SELECT * FROM penduduk WHERE desa_id=? ORDER BY rw,rt");
$stmtP->execute([$desa['id']]); $penduduk = $stmtP->fetchAll();
$totalLaki=0; $totalPerempuan=0; $totalPenduduk=0;
foreach($penduduk as $r){ $totalLaki+=$r['laki_laki']; $totalPerempuan+=$r['perempuan']; $totalPenduduk+=$r['total']; }

// Data tabel dinamis
$stmtT = $pdo->prepare("SELECT id,kategori,judul,headers,`rows` FROM data_tabel WHERE desa_id=? ORDER BY kategori,created_at ASC");
$stmtT->execute([$desa['id']]); $rawTabel = $stmtT->fetchAll();

$tabelByKat = []; // ['Pendidikan' => [ [...tbl...] ]]
foreach($rawTabel as $t){
    $t['headers'] = json_decode($t['headers'], true) ?? [];
    $t['rows']    = json_decode($t['rows'], true) ?? [];
    $tabelByKat[$t['kategori']][] = $t;
}
$kategoriList = array_keys($tabelByKat);

// Peta ikon per kategori
$katIcon = [
    'Kependudukan' => '👥', 'Pendidikan' => '📚', 'Ekonomi' => '💼',
    'Kesehatan' => '🏥', 'Infrastruktur' => '🏗️', 'Pertanian' => '🌾',
    'Sosial & Budaya' => '🤝', 'Lainnya' => '📋',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Data Desa – <?= e($desa['nama']) ?> | Kecamatan Muaradua</title>
  <meta name="description" content="Data statistik Desa <?= e($desa['nama']) ?>, Kecamatan Muaradua, OKU Selatan."/>
  <link rel="stylesheet" href="../../css/style.css"/>
  <style>
    /* ====== DATA PAGE LAYOUT ====== */
    .page-data { background: var(--bg-light, #F7F8FC); min-height: 100vh; }

    .data-hero {
      background: linear-gradient(135deg, var(--primary, #1E3A5F) 0%, #2D5F8A 100%);
      color: #fff; padding: 2.5rem 0 2rem;
    }
    .data-hero .badge-pill {
      display: inline-block; background: rgba(255,255,255,0.18);
      color: #fff; font-size: .78rem; font-weight: 600;
      padding: .3rem .9rem; border-radius: 99px; letter-spacing:.05em;
      margin-bottom: .75rem;
    }
    .data-hero h1 { font-size: 2rem; font-weight: 800; margin: 0 0 .4rem; color:#fff; }
    .data-hero p  { font-size: .95rem; opacity: .82; margin: 0; }

    /* ====== LAYOUT: SIDEBAR + MAIN ====== */
    .dl-wrap { display: flex; gap: 1.75rem; align-items: flex-start; padding: 2rem 0 3rem; }

    /* SIDEBAR */
    .dl-sidebar {
      width: 230px; flex-shrink: 0;
      background: #fff; border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,.07);
      padding: 1.25rem 0; position: sticky; top: 80px;
    }
    .dl-sidebar-title {
      font-size: .68rem; font-weight: 700; letter-spacing: .1em;
      color: var(--text-light, #9CA3AF); text-transform: uppercase;
      padding: .5rem 1.25rem .35rem;
    }
    .dl-sidebar ul { list-style: none; margin: 0; padding: 0; }
    .dl-sidebar ul li a {
      display: flex; align-items: center; gap: .6rem;
      padding: .6rem 1.25rem; font-size: .88rem; font-weight: 500;
      color: var(--text, #374151); text-decoration: none;
      border-left: 3px solid transparent;
      transition: background .15s, color .15s, border-color .15s;
    }
    .dl-sidebar ul li a:hover { background: var(--bg-light, #F7F8FC); color: var(--primary, #1E3A5F); }
    .dl-sidebar ul li a.active {
      background: rgba(30,58,95,.07); color: var(--primary, #1E3A5F);
      border-left-color: var(--primary, #1E3A5F); font-weight: 700;
    }
    .dl-sidebar ul li a .si { font-size: 1rem; }
    .dl-sidebar hr { border: none; border-top: 1px solid var(--border, #E5E7EB); margin: .6rem 1.25rem; }

    /* MAIN */
    .dl-main { flex: 1; min-width: 0; }

    /* SECTION VISIBILITY */
    .ds { display: none; }
    .ds.active { display: block; animation: fadeUp .3s ease; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

    /* STAT CARDS */
    .stat-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.25rem; }
    @media(max-width:600px){ .stat-row { grid-template-columns: repeat(2,1fr); } }
    .stat-card {
      background: #fff; border-radius: 12px; padding: 1.1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,.06); text-align: center;
    }
    .stat-card .sn { font-size: 1.6rem; font-weight: 800; color: var(--primary, #1E3A5F); }
    .stat-card .sl { font-size: .78rem; color: var(--text-light, #9CA3AF); margin-top: .2rem; }

    /* CARD */
    .dc { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.06); margin-bottom:1.25rem; overflow:hidden; }
    .dc-head {
      display:flex; justify-content:space-between; align-items:center;
      padding:.85rem 1.25rem; font-weight:700; font-size:.9rem;
      background:var(--bg-section,#F9FAFB); border-bottom:1px solid var(--border,#E5E7EB);
    }
    .dc-head .badge { font-size:.72rem; padding:.25rem .7rem; border-radius:99px; background:var(--primary,#1E3A5F); color:#fff; }
    .dc-body { overflow-x:auto; }

    /* TABLE */
    .dc-body table { width:100%; border-collapse:collapse; font-size:.88rem; }
    .dc-body thead tr { background:var(--bg-section,#F9FAFB); }
    .dc-body th { padding:.7rem 1rem; text-align:left; font-weight:700; font-size:.8rem; color:var(--text-light,#6B7280); border-bottom:2px solid var(--border,#E5E7EB); white-space:nowrap; }
    .dc-body td { padding:.65rem 1rem; border-bottom:1px solid var(--border,#E5E7EB); color:var(--text,#374151); }
    .dc-body tbody tr:last-child td { border-bottom:none; }
    .dc-body tbody tr:hover { background:rgba(30,58,95,.03); }

    /* EMPTY STATE */
    .empty-state { text-align:center; padding:2.5rem; color:var(--text-light,#9CA3AF); }
    .empty-state .ei { font-size:2.5rem; margin-bottom:.5rem; }

    /* SECTION HEADER */
    .sec-head { margin-bottom:1.25rem; }
    .sec-head h3 { font-size:1.3rem; font-weight:800; color:var(--primary,#1E3A5F); margin:0 0 .25rem; }
    .sec-head p  { font-size:.88rem; color:var(--text-light,#6B7280); margin:0; }

    /* RESPONSIVE */
    @media(max-width:768px){
      .dl-wrap { flex-direction:column; }
      .dl-sidebar { width:100%; position:static; }
      .dl-sidebar ul { display:flex; flex-wrap:wrap; gap:.25rem; padding:.25rem .75rem; }
      .dl-sidebar ul li a { border-left:none; border-radius:8px; padding:.4rem .75rem; font-size:.82rem; }
      .dl-sidebar ul li a.active { border-left:none; }
    }
  </style>
</head>
<body class="page-data">

  <!-- NAVBAR -->
<?php $currentPage = 'data'; require '_navbar.php'; ?>


  <!-- HERO -->
  <section class="data-hero">
    <div class="container">
      <div class="badge-pill">📊 Data Desa</div>
      <h1>Data Statistik Desa <?= e($desa['nama']) ?></h1>
      <p>Data kependudukan dan informasi desa yang dikelola secara transparan dan terbuka.</p>
    </div>
  </section>

  <!-- CONTENT -->
  <div class="container">
    <div class="dl-wrap">

      <!-- SIDEBAR -->
      <aside class="dl-sidebar">
        <div class="dl-sidebar-title">Jenis Data</div>
        <ul>
          <li>
            <a href="#" class="active" onclick="return showSec('kependudukan',this)">
              <span class="si">👥</span> Kependudukan
            </a>
          </li>
          <?php foreach($kategoriList as $kat):
            $sid = katSlug($kat);
            $ico = $katIcon[$kat] ?? '📊';
          ?>
          <li>
            <a href="#" onclick="return showSec('<?= e($sid) ?>',this)">
              <span class="si"><?= $ico ?></span> <?= e($kat) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
        <hr/>
        <div class="dl-sidebar-title">Tautan</div>
        <ul>
          <li><a href="infografis.php"><span class="si">📈</span> Infografis</a></li>
          <li><a href="pengumuman.php"><span class="si">📢</span> Pengumuman</a></li>
          <li><a href="login.php"><span class="si">🔐</span> Admin</a></li>
          <li><a href="/muaradua-web/"><span class="si">🏛️</span> ← Kecamatan</a></li>
        </ul>
      </aside>

      <!-- MAIN -->
      <main class="dl-main">

        <!-- KEPENDUDUKAN -->
        <div id="ds-kependudukan" class="ds active">
          <div class="sec-head">
            <h3>👥 Data Kependudukan</h3>
            <p>Statistik penduduk Desa <?= e($desa['nama']) ?> per RT/RW.</p>
          </div>

          <div class="stat-row">
            <div class="stat-card"><div class="sn"><?= fmtNum($totalPenduduk) ?></div><div class="sl">Total Penduduk</div></div>
            <div class="stat-card"><div class="sn"><?= fmtNum($totalLaki) ?></div><div class="sl">Laki-laki</div></div>
            <div class="stat-card"><div class="sn"><?= fmtNum($totalPerempuan) ?></div><div class="sl">Perempuan</div></div>
            <div class="stat-card"><div class="sn"><?= fmtNum($desa['jumlah_kk'] ?? 0) ?></div><div class="sl">Kepala Keluarga</div></div>
          </div>

          <div class="dc">
            <div class="dc-head">📋 Data Penduduk per RT/RW</div>
            <div class="dc-body">
              <table>
                <thead><tr><th>RW</th><th>RT</th><th>Laki-laki</th><th>Perempuan</th><th>Total</th><th>KK</th></tr></thead>
                <tbody>
                  <?php if(empty($penduduk)): ?>
                  <tr><td colspan="6" class="empty-state">Belum ada data penduduk.</td></tr>
                  <?php else: foreach($penduduk as $r): ?>
                  <tr>
                    <td>RW <?= e($r['rw']) ?></td>
                    <td>RT <?= e($r['rt']) ?></td>
                    <td><?= fmtNum($r['laki_laki']) ?></td>
                    <td><?= fmtNum($r['perempuan']) ?></td>
                    <td><strong><?= fmtNum($r['total']) ?></strong></td>
                    <td><?= fmtNum($r['jumlah_kk'] ?? 0) ?></td>
                  </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- KATEGORI DINAMIS -->
        <?php if(empty($kategoriList)): ?>
        <div id="ds-kosong" class="ds">
          <div class="dc"><div class="empty-state">
            <div class="ei">📋</div>
            <h4>Belum Ada Data</h4>
            <p>Admin desa belum menambahkan data tabel.</p>
            <a href="login.php" class="btn btn-outline" style="margin-top:.75rem">🔐 Login Admin</a>
          </div></div>
        </div>

        <?php else: foreach($tabelByKat as $kat => $tabels):
          $sid = katSlug($kat);
          $ico = $katIcon[$kat] ?? '📊';
        ?>
        <div id="ds-<?= e($sid) ?>" class="ds">
          <div class="sec-head">
            <h3><?= $ico ?> Data <?= e($kat) ?></h3>
            <p>Informasi <?= strtolower(e($kat)) ?> Desa <?= e($desa['nama']) ?>.</p>
          </div>

          <?php foreach($tabels as $tbl): ?>
          <div class="dc">
            <div class="dc-head">
              <span><?= e($tbl['judul']) ?></span>
              <span class="badge"><?= e($tbl['kategori']) ?></span>
            </div>
            <div class="dc-body">
              <?php if(empty($tbl['headers'])): ?>
              <div class="empty-state"><div class="ei">📭</div><p>Tabel belum memiliki kolom.</p></div>
              <?php else: ?>
              <table>
                <thead>
                  <tr><?php foreach($tbl['headers'] as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                  <?php if(empty($tbl['rows'])): ?>
                  <tr><td colspan="<?= count($tbl['headers']) ?>" class="empty-state">Belum ada data baris.</td></tr>
                  <?php else: foreach($tbl['rows'] as $row): ?>
                  <tr><?php foreach($row as $cell): ?><td><?= e($cell) ?></td><?php endforeach; ?></tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; endif; ?>

      </main>
    </div><!-- /.dl-wrap -->
  </div><!-- /.container -->

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-logo"><?= e($desa['emoji']) ?></div>
          <h4>Desa <?= e($desa['nama']) ?></h4>
          <p>Kecamatan Muaradua, OKU Selatan</p>
        </div>
        <div><h4>Data Desa</h4><ul>
          <li><a href="#" onclick="return showSec('kependudukan',null)">Kependudukan</a></li>
          <?php foreach($kategoriList as $kat): ?>
          <li><a href="#" onclick="return showSec('<?= katSlug($kat) ?>',null)"><?= e($kat) ?></a></li>
          <?php endforeach; ?>
        </ul></div>
        <div><h4>Navigasi</h4><ul>
          <li><a href="index.php">Beranda Desa</a></li>
          <li><a href="infografis.php">Infografis</a></li>
          <li><a href="pengumuman.php">Pengumuman</a></li>
          <li><a href="login.php">Admin</a></li>
          <li><a href="/muaradua-web/">← Kecamatan</a></li>
        </ul></div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Desa <?= e($desa['nama']) ?> · Kecamatan Muaradua</p>
      </div>
    </div>
  </footer>

  <script src="../../js/main.js"></script>
  <script>
    function showSec(id, el) {
      // Sembunyikan semua section
      document.querySelectorAll('.ds').forEach(s => s.classList.remove('active'));
      // Tampilkan section yang dipilih
      const target = document.getElementById('ds-' + id);
      if (target) target.classList.add('active');
      // Update active state di sidebar
      document.querySelectorAll('.dl-sidebar a').forEach(a => a.classList.remove('active'));
      if (el) el.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return false;
    }
  </script>
</body>
</html>
