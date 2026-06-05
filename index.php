<?php
require_once 'config/db.php';
require_once 'config/helpers.php';

$stmt  = $pdo->query("SELECT * FROM desa ORDER BY nama ASC");
$desas = $stmt->fetchAll();
$totalPenduduk = $pdo->query("SELECT IFNULL(SUM(penduduk_total),0) FROM desa")->fetchColumn();
$totalDesa     = count($desas);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kecamatan Muaradua – Portal Informasi Desa</title>
  <meta name="description" content="Portal informasi terpadu desa-desa di Kecamatan Muaradua, Kabupaten OKU Selatan, Sumatera Selatan."/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
</head>
<body>

  <!-- ===================== HEADER ===================== -->
  <div class="kec-topbar">
    <div class="container">
      <span>🏛️ Pemerintah Kecamatan Muaradua</span>
      <span class="kec-topbar-sep">|</span>
      <span>Kabupaten OKU Selatan, Sumatera Selatan</span>
    </div>
  </div>

  <header class="kec-header">
    <div class="container">
      <a href="index.php" class="kec-brand">
        <div class="kec-brand-icon">🏛️</div>
        <div>
          <div class="kec-brand-title">Kecamatan Muaradua</div>
          <div class="kec-brand-sub">Kabupaten OKU Selatan, Sumatera Selatan</div>
        </div>
      </a>
      <nav class="kec-nav">
        <a href="index.php" class="active">Beranda</a>
        <a href="#desa">Desa</a>
        <a href="#tentang">Tentang</a>
        <a href="#layanan">Layanan</a>
      </nav>
      <button class="hamburger" id="hamburgerBtn" onclick="document.getElementById('kecNav').classList.toggle('open')">☰</button>
    </div>
  </header>

  <!-- ===================== HERO ===================== -->
  <section class="hero">
    <div class="container hero-content animate-fade-in-up">
      <div class="eyebrow">🌐 Website Resmi Kecamatan</div>
      <h1>Selamat Datang di<br/>Kecamatan Muaradua</h1>
      <p style="color:rgba(255,255,255,.85);font-size:1.1rem;max-width:560px;margin-top:1rem;">
        Portal informasi terpadu kependudukan, data desa, dan layanan publik seluruh desa di wilayah Kecamatan Muaradua.
      </p>
      <div class="hero-actions animate-fade-in-up delay-2">
        <a href="#desa" class="btn btn-ghost">📍 Lihat Semua Desa</a>
        <a href="#tentang" class="btn btn-accent">ℹ️ Tentang Kecamatan</a>
      </div>
    </div>
    <div class="hero-stats animate-fade-in-up delay-3">
      <div class="container">
        <div class="hero-stats-inner">
          <div class="hero-stat-item">
            <div class="hero-stat-val"><?= $totalDesa ?></div>
            <div class="hero-stat-lbl">Desa / Kelurahan</div>
          </div>
          <div class="hero-stat-sep"></div>
          <div class="hero-stat-item">
            <div class="hero-stat-val"><?= fmtNum($totalPenduduk) ?></div>
            <div class="hero-stat-lbl">Total Penduduk</div>
          </div>
          <div class="hero-stat-sep"></div>
          <div class="hero-stat-item">
            <div class="hero-stat-val">64,2 km²</div>
            <div class="hero-stat-lbl">Luas Wilayah</div>
          </div>
          <div class="hero-stat-sep"></div>
          <div class="hero-stat-item">
            <div class="hero-stat-val"><?= date('Y') ?></div>
            <div class="hero-stat-lbl">Data Terkini</div>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-wave">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 70" preserveAspectRatio="none">
        <path fill="#F4F7FB" d="M0,35 C360,70 1080,0 1440,35 L1440,70 L0,70 Z"/>
      </svg>
    </div>
  </section>

  <!-- ===================== DESA GRID ===================== -->
  <section id="desa" style="padding:4rem 0;">
    <div class="container">
      <div class="section-header">
        <div class="eyebrow">🏘️ Daftar Desa</div>
        <h2>Pilih Desa yang Ingin Anda Kunjungi</h2>
        <p>Klik salah satu desa di bawah untuk melihat informasi, data kependudukan, dan infografis.</p>
      </div>

      <!-- Search Box -->
      <div class="kec-search">
        <span class="kec-search-icon">🔍</span>
        <input type="text" id="searchDesa" class="kec-search-input" placeholder="Cari nama desa..." oninput="filterDesa(this.value)"/>
      </div>

      <!-- Village Grid -->
      <div class="kec-village-grid" id="desaGrid">
        <?php foreach($desas as $d): ?>
        <a href="muaradua/<?= e($d['slug']) ?>/index.php" class="kec-village-card" data-nama="<?= strtolower(e($d['nama'])) ?>">
          <div class="kec-village-card-top" style="background:<?= e($d['color_gradient']) ?>">
            <div class="kec-village-card-emoji"><?= e($d['emoji']) ?></div>
          </div>
          <div class="kec-village-card-body">
            <h3 class="kec-village-card-name"><?= e($d['nama']) ?></h3>
            <div class="kec-village-card-meta">
              <span>👤 <?= e($d['kepala_desa']) ?></span>
            </div>
            <div class="kec-village-card-meta">
              <span>📐 <?= e($d['luas_wilayah']) ?></span>
              <span>🏘️ <?= (int)$d['jumlah_rt'] ?> RT / <?= (int)$d['jumlah_rw'] ?> RW</span>
            </div>
            <div class="kec-village-card-stat">👥 <?= fmtNum($d['penduduk_total']) ?> Jiwa</div>
          </div>
          <div class="kec-village-card-footer">Kunjungi Desa →</div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ===================== TENTANG ===================== -->
  <section id="tentang" style="background:var(--white);padding:4.5rem 0;">
    <div class="container">
      <div class="grid-2" style="gap:3rem;align-items:center;">
        <div class="animate-fade-in-up">
          <div class="eyebrow">🏛️ Tentang Kecamatan</div>
          <h2 style="margin-bottom:1rem;">Kecamatan Muaradua, OKU Selatan</h2>
          <p style="margin-bottom:1rem;">Kecamatan Muaradua adalah pusat pemerintahan Kabupaten Ogan Komering Ulu (OKU) Selatan, Provinsi Sumatera Selatan, dengan potensi pertanian dan sumber daya alam yang melimpah.</p>
          <p>Dengan <?= $totalDesa ?> desa yang tersebar di wilayah seluas 64,2 km², kecamatan ini terus berkembang dalam peningkatan kesejahteraan masyarakat.</p>
          <div class="stats-grid" style="margin-top:1.75rem;grid-template-columns:1fr 1fr 1fr;">
            <div class="stat-card" style="flex-direction:column;gap:.5rem;text-align:center;">
              <div style="font-size:2rem;">🏘️</div>
              <div style="font-size:1.5rem;font-weight:800;color:var(--primary)"><?= $totalDesa ?></div>
              <div style="font-size:.8rem;color:var(--text-light)">Desa</div>
            </div>
            <div class="stat-card" style="flex-direction:column;gap:.5rem;text-align:center;">
              <div style="font-size:2rem;">👥</div>
              <div style="font-size:1.5rem;font-weight:800;color:var(--primary)"><?= fmtNum($totalPenduduk) ?></div>
              <div style="font-size:.8rem;color:var(--text-light)">Penduduk</div>
            </div>
            <div class="stat-card" style="flex-direction:column;gap:.5rem;text-align:center;">
              <div style="font-size:2rem;">📐</div>
              <div style="font-size:1.5rem;font-weight:800;color:var(--primary)">64,2</div>
              <div style="font-size:.8rem;color:var(--text-light)">km² Luas</div>
            </div>
          </div>
        </div>
        <div style="text-align:center;font-size:7rem;filter:drop-shadow(0 12px 32px rgba(29,78,137,.15))">🏔️</div>
      </div>
    </div>
  </section>

  <!-- ===================== LAYANAN ===================== -->
  <section id="layanan" style="padding:4.5rem 0;">
    <div class="container">
      <div class="section-header">
        <div class="eyebrow">📋 Layanan</div>
        <h2>Layanan Informasi Publik</h2>
        <p>Akses berbagai informasi dan layanan publik yang tersedia di Kecamatan Muaradua.</p>
      </div>
      <div class="grid-4">
        <div class="kec-service-card">
          <div class="kec-service-icon" style="background:rgba(29,78,137,.1);color:var(--primary)">📊</div>
          <h4>Data Kependudukan</h4>
          <p>Statistik penduduk, KK, dan demografi per desa</p>
        </div>
        <div class="kec-service-card">
          <div class="kec-service-icon" style="background:rgba(121,180,67,.1);color:var(--green-dark)">🌾</div>
          <h4>Potensi Desa</h4>
          <p>Informasi potensi pertanian, ekonomi, dan wisata</p>
        </div>
        <div class="kec-service-card">
          <div class="kec-service-icon" style="background:rgba(232,130,12,.1);color:var(--accent-dark)">📈</div>
          <h4>Infografis</h4>
          <p>Visualisasi data desa dalam format yang mudah dipahami</p>
        </div>
        <div class="kec-service-card">
          <div class="kec-service-icon" style="background:rgba(192,57,43,.1);color:var(--red)">🏥</div>
          <h4>Layanan Kesehatan</h4>
          <p>Data fasilitas dan layanan kesehatan tiap desa</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== FOOTER ===================== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-logo">🏛️</div>
          <h4>Kecamatan Muaradua</h4>
          <p>Kabupaten OKU Selatan<br/>Sumatera Selatan, Indonesia</p>
          <p style="margin-top:.5rem;">Jl. Merdeka No. 1, Muaradua 32211</p>
        </div>
        <div>
          <h4>Desa</h4>
          <ul>
            <?php foreach($desas as $d): ?>
            <li><a href="muaradua/<?= e($d['slug']) ?>/index.php"><?= e($d['emoji']) ?> <?= e($d['nama']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div>
          <h4>Tautan</h4>
          <ul>
            <li><a href="index.php">Beranda</a></li>
            <li><a href="#desa">Pilih Desa</a></li>
            <li><a href="#tentang">Tentang Kecamatan</a></li>
            <li><a href="https://www.okuselatankab.go.id" target="_blank" rel="noopener">OKU Selatan ↗</a></li>
            <li><a href="https://sumsel.bps.go.id" target="_blank" rel="noopener">BPS Sumsel ↗</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Kecamatan Muaradua &middot; Seluruh hak dilindungi</p>
      </div>
    </div>
  </footer>

  <script>
    function filterDesa(q) {
      const val = q.toLowerCase();
      document.querySelectorAll('.kec-village-card').forEach(c => {
        c.style.display = c.dataset.nama.includes(val) ? '' : 'none';
      });
    }
    // Hamburger mobile nav
    document.getElementById('hamburgerBtn').addEventListener('click', function() {
      document.querySelector('.kec-nav').classList.toggle('open');
    });
  </script>
</body>
</html>
