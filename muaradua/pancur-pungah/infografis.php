<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

$stmt = $pdo->prepare("SELECT * FROM infografis WHERE desa_id = ? ORDER BY created_at DESC");
$stmt->execute([$desa['id']]);
$infografis = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Infografis – Desa <?= e($desa['nama']) ?></title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <style>
    body{background:var(--bg-light);}
    .infografis-hero{background:<?= e($desa['color_gradient']) ?>;color:var(--white);padding:3rem 0;}
    .infografis-hero h1{color:var(--white);font-size:2rem;}
    .breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:rgba(255,255,255,.6);margin-bottom:.75rem;flex-wrap:wrap;}
    .breadcrumb a{color:rgba(255,255,255,.8);}
    .filter-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:2rem;}
    .filter-tab{padding:.45rem 1.1rem;border-radius:100px;font-size:.875rem;font-weight:500;cursor:pointer;border:1.5px solid var(--border);background:var(--white);color:var(--text-mid);transition:var(--transition);}
    .filter-tab:hover,.filter-tab.active{background:var(--primary);color:var(--white);border-color:var(--primary);}
    .inf-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;}
    .inf-card{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;transition:var(--transition);}
    .inf-card:hover{box-shadow:var(--shadow-md);transform:translateY(-4px);}
    .inf-visual{height:200px;display:flex;align-items:center;justify-content:center;font-size:4rem;}
    .inf-body{padding:1.25rem 1.5rem 1.5rem;}
    .inf-body h4{font-size:1rem;margin-bottom:.35rem;}
    .inf-body p{font-size:.82rem;color:var(--text-light);}

    /* Static featured infografis */
    .inf-bar{height:8px;border-radius:4px;margin-bottom:.35rem;background:rgba(255,255,255,.5);overflow:hidden;}
    .inf-bar-fill{height:100%;border-radius:4px;background:rgba(255,255,255,.9);}
    .inf-bar-label{display:flex;justify-content:space-between;font-size:.7rem;color:rgba(255,255,255,.8);margin-bottom:.5rem;}
    .inf-chart{width:100%;padding:0 1.5rem;margin-top:.5rem;}
    .bar-chart-vertical{display:flex;align-items:flex-end;justify-content:center;gap:.75rem;height:100px;padding:0 1rem;width:100%;}
    .bar-col{display:flex;flex-direction:column;align-items:center;gap:.3rem;}
    .bar-fill{border-radius:4px 4px 0 0;background:rgba(255,255,255,.85);width:28px;}
    .bar-label{font-size:.65rem;color:rgba(255,255,255,.7);}
    .bar-val{font-size:.65rem;color:rgba(255,255,255,.9);font-weight:700;}
  </style>
</head>
<body>

<?php $currentPage = 'infografis'; require '_navbar.php'; ?>

  <section class="infografis-hero">
    <div class="container">
      <div class="breadcrumb"><a href="/muaradua-web/">Kecamatan Muaradua</a><span>/</span><a href="index.php">Desa <?= e($desa['nama']) ?></a><span>/</span><span>Infografis</span></div>
      <h1>📈 Infografis Desa <?= e($desa['nama']) ?></h1>
      <p>Visualisasi data kependudukan, sosial, dan ekonomi dalam format yang mudah dipahami.</p>
    </div>
  </section>

  <section style="padding:2.5rem 0 4rem;">
    <div class="container">
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterInf('semua',this)">Semua</button>
        <button class="filter-tab" onclick="filterInf('kependudukan',this)">👥 Kependudukan</button>
        <button class="filter-tab" onclick="filterInf('pendidikan',this)">📚 Pendidikan</button>
        <button class="filter-tab" onclick="filterInf('ekonomi',this)">💼 Ekonomi</button>
        <button class="filter-tab" onclick="filterInf('kesehatan',this)">🏥 Kesehatan</button>
      </div>

      <!-- Infografis dari database -->
      <?php if (!empty($infografis)): ?>
      <h3 class="mb-3" style="font-size:1.1rem;">📌 Infografis Desa</h3>
      <div class="inf-grid mb-4" id="infGrid">
        <?php foreach($infografis as $item): ?>
        <div class="inf-card" data-cat="<?= strtolower(e($item['kategori'])) ?>">
          <div class="inf-visual" style="background:<?= e($item['warna_bg']) ?>"><?= e($item['emoji']) ?></div>
          <div class="inf-body">
            <span class="badge badge-primary mb-1"><?= e($item['kategori']) ?></span>
            <h4><?= e($item['judul']) ?></h4>
            <p><?= e($item['deskripsi']) ?></p>
            <div style="font-size:.75rem;color:var(--text-light);margin-top:.5rem;">📅 <?= date('d M Y', strtotime($item['created_at'])) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Infografis Unggulan Statis -->
      <h3 class="mb-3" style="font-size:1.1rem;">📊 Infografis Unggulan</h3>
      <div class="inf-grid" id="featuredGrid">
        <div class="inf-card" data-cat="kependudukan">
          <div class="inf-visual" style="background:linear-gradient(135deg,#1D4E89,#2E6DB4)">
            <div class="bar-chart-vertical">
              <div class="bar-col"><div class="bar-val">325</div><div class="bar-fill" style="height:32px"></div><div class="bar-label">65+</div></div>
              <div class="bar-col"><div class="bar-val">580</div><div class="bar-fill" style="height:58px"></div><div class="bar-label">45-64</div></div>
              <div class="bar-col"><div class="bar-val">820</div><div class="bar-fill" style="height:82px"></div><div class="bar-label">25-44</div></div>
              <div class="bar-col"><div class="bar-val">708</div><div class="bar-fill" style="height:70px"></div><div class="bar-label">15-24</div></div>
              <div class="bar-col"><div class="bar-val">812</div><div class="bar-fill" style="height:80px"></div><div class="bar-label">0-14</div></div>
            </div>
          </div>
          <div class="inf-body"><span class="badge badge-primary mb-1">Kependudukan</span><h4>Piramida Penduduk 2024</h4><p>Distribusi penduduk berdasarkan kelompok usia. Total <?= fmtNum($desa['penduduk_total']) ?> jiwa.</p></div>
        </div>
        <div class="inf-card" data-cat="ekonomi">
          <div class="inf-visual" style="background:linear-gradient(135deg,#E8820C,#C96A00)">
            <div style="font-size:3rem;">💼</div>
            <div class="inf-chart">
              <div class="inf-bar-label"><span>🌾 Petani</span><span>35%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:35%"></div></div>
              <div class="inf-bar-label"><span>🏪 Pedagang</span><span>14%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:14%"></div></div>
              <div class="inf-bar-label"><span>🏢 PNS</span><span>8%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:8%"></div></div>
            </div>
          </div>
          <div class="inf-body"><span class="badge badge-accent mb-1">Ekonomi</span><h4>Distribusi Mata Pencaharian</h4><p>Petani mendominasi mata pencaharian warga desa.</p></div>
        </div>
        <div class="inf-card" data-cat="pendidikan">
          <div class="inf-visual" style="background:linear-gradient(135deg,#5E8E33,#79B443)">
            <div style="font-size:3rem;">📚</div>
            <div class="inf-chart">
              <div class="inf-bar-label"><span>SMA/Sederajat</span><span>38%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:38%"></div></div>
              <div class="inf-bar-label"><span>SMP/Sederajat</span><span>25%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:25%"></div></div>
              <div class="inf-bar-label"><span>SD/Sederajat</span><span>23%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:23%"></div></div>
            </div>
          </div>
          <div class="inf-body"><span class="badge badge-green mb-1">Pendidikan</span><h4>Tingkat Pendidikan Warga</h4><p>38% warga telah menamatkan pendidikan SMA/Sederajat.</p></div>
        </div>
        <div class="inf-card" data-cat="kesehatan">
          <div class="inf-visual" style="background:linear-gradient(135deg,#C0392B,#e74c3c)">
            <div style="font-size:3rem;">🏥</div>
            <div class="inf-chart">
              <div class="inf-bar-label"><span>💉 Imunisasi</span><span>91%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:91%"></div></div>
              <div class="inf-bar-label"><span>👶 Balita Sehat</span><span>95%</span></div><div class="inf-bar"><div class="inf-bar-fill" style="width:95%"></div></div>
            </div>
          </div>
          <div class="inf-body"><span class="badge mb-1" style="background:rgba(192,57,43,.12);color:var(--red)">Kesehatan</span><h4>Indikator Kesehatan Desa</h4><p>Cakupan imunisasi dan kesehatan ibu anak.</p></div>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div><div class="footer-logo"><?= e($desa['emoji']) ?></div><h4>Desa <?= e($desa['nama']) ?></h4><p>Kecamatan Muaradua, OKU Selatan</p></div>
        <div><h4>Navigasi</h4><ul><li><a href="index.php">Beranda</a></li><li><a href="data.php">Data Desa</a></li><li><a href="login.php">Admin Login</a></li></ul></div>
        <div><h4>Kecamatan</h4><ul><li><a href="/muaradua-web/">← Kecamatan Muaradua</a></li></ul></div>
      </div>
      <div class="footer-bottom"><p>&copy; <?= date('Y') ?> Desa <?= e($desa['nama']) ?> · Kecamatan Muaradua</p></div>
    </div>
  </footer>

  <script src="../../js/main.js"></script>
  <script>
    function filterInf(cat,btn){
      document.querySelectorAll('.filter-tab').forEach(t=>t.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.inf-card').forEach(card=>{
        const c=card.getAttribute('data-cat');
        card.style.display=(cat==='semua'||c===cat)?'block':'none';
      });
    }
  </script>
</body>
</html>
