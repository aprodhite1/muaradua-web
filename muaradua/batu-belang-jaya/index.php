<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

// Ambil pengumuman dari DB
$stmt_peng = $pdo->prepare("SELECT * FROM pengumuman WHERE desa_id = ? AND aktif = 1 ORDER BY tanggal DESC LIMIT 5");
$stmt_peng->execute([$desa['id']]);
$pengumuman = $stmt_peng->fetchAll();

// Ambil foto desa
$stmt_foto = $pdo->prepare("SELECT id, judul, filename FROM foto_desa WHERE desa_id = ? ORDER BY urutan ASC, id ASC");
$stmt_foto->execute([$desa['id']]);
$fotos = $stmt_foto->fetchAll();

$base = desaBaseUrl($slug);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Desa <?= e($desa['nama']) ?> – Kecamatan Muaradua</title>
  <meta name="description" content="Website resmi Desa <?= e($desa['nama']) ?>, Kecamatan Muaradua, OKU Selatan." />
  <link rel="stylesheet" href="../../css/style.css" />
  <style>
    body { background: var(--bg-light); }
    /* ===== VILLAGE HERO – foto pemandangan ===== */
    .village-hero {
      background: url('../../css/desa_header.jpg') center center / cover no-repeat;
      color: var(--white);
      padding: 4rem 0 6rem;
      position: relative;
      overflow: hidden;
    }
    /* Overlay gradasi warna khas tiap desa */
    .village-hero::before {
      content: '';
      position: absolute; inset: 0;
      background: <?= e(
        preg_replace(
          '/linear-gradient\(135deg,([^,]+),([^)]+)\)/',
          'linear-gradient(135deg, $1cc 0%, $2aa 100%)',
          $desa['color_gradient']
        )
      ) ?>;
      mix-blend-mode: multiply;
    }
    /* Gradasi gelap tambahan di bawah agar konten terbaca */
    .village-hero::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.25) 0%,
        rgba(0,0,0,0.10) 40%,
        rgba(0,0,0,0.35) 100%
      );
      pointer-events: none;
    }
    .village-hero-inner { position:relative; z-index:1; display:flex; align-items:center; gap:2rem; }
    .village-hero-icon {
      font-size: 5rem;
      flex-shrink: 0;
      filter: drop-shadow(0 4px 16px rgba(0,0,0,0.4));
    }
    .village-hero-text h1 { color:var(--white); margin-bottom:.5rem; text-shadow: 0 2px 12px rgba(0,0,0,0.4); }
    .village-hero-text p { color:rgba(255,255,255,.92); text-shadow: 0 1px 6px rgba(0,0,0,0.3); }
    .village-hero-wave { position:absolute; bottom:-2px; left:0; width:100%; line-height:0; z-index:2; }
    .quick-stats { margin-top:-3rem; position:relative; z-index:2; margin-bottom:2rem; }
    .update-item { display:flex; gap:1rem; align-items:flex-start; padding:1rem 0; border-bottom:1px solid var(--border); }
    .update-item:last-child { border-bottom:none; }
    .update-dot { width:10px; height:10px; border-radius:50%; background:var(--primary); margin-top:.4rem; flex-shrink:0; }
    .profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .profile-item { padding:1rem; background:var(--bg-section); border-radius:var(--radius-sm); }
    .profile-item label { font-size:.75rem; color:var(--text-light); font-weight:600; text-transform:uppercase; letter-spacing:.06em; }
    .profile-item p { font-size:.95rem; font-weight:600; color:var(--text-dark); margin-top:.25rem; }

    /* ===== FOTO GALLERY ===== */
    .foto-section { padding: 3rem 0; background: var(--white); }
    .foto-section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
    .foto-carousel-wrap { position:relative; overflow:hidden; border-radius: var(--radius-lg); }
    .foto-carousel {
      display: flex;
      gap: 1rem;
      cursor: grab;
      user-select: none;
      scroll-snap-type: x mandatory;
      overflow-x: scroll;
      scroll-behavior: smooth;
      scrollbar-width: none;
      -ms-overflow-style: none;
      padding-bottom: .5rem;
    }
    .foto-carousel::-webkit-scrollbar { display: none; }
    .foto-carousel.grabbing { cursor: grabbing; scroll-behavior: auto; }
    .foto-slide {
      flex: 0 0 calc(33.333% - .667rem);
      scroll-snap-align: start;
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border);
      background: var(--bg-section);
      transition: transform .2s;
    }
    .foto-slide:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .foto-slide img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      display: block;
    }
    .foto-slide-caption {
      padding: .75rem 1rem;
      font-size: .85rem;
      font-weight: 600;
      color: var(--text-dark);
      background: var(--white);
    }
    .foto-nav {
      display: flex;
      justify-content: center;
      gap: .75rem;
      margin-top: 1rem;
    }
    .foto-nav-btn {
      width: 40px; height: 40px;
      border-radius: 50%;
      border: 2px solid var(--border);
      background: var(--white);
      cursor: pointer;
      display: flex; align-items:center; justify-content:center;
      font-size: 1.1rem;
      transition: var(--transition);
      box-shadow: var(--shadow-sm);
    }
    .foto-nav-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .foto-empty {
      text-align:center; padding:3rem 1rem;
      color: var(--text-light);
      background: var(--bg-section);
      border-radius: var(--radius-md);
      border: 2px dashed var(--border);
    }
    .foto-empty .foto-empty-icon { font-size:3rem; margin-bottom:.75rem; }
    @media(max-width:768px){
      .foto-slide { flex: 0 0 calc(50% - .5rem); }
    }
    @media(max-width:480px){
      .foto-slide { flex: 0 0 calc(85% - .5rem); }
    }
    @media(max-width:600px){ .village-hero-inner{flex-direction:column;text-align:center;} .profile-grid{grid-template-columns:1fr;} }
  </style>
</head>
<body>

<?php $currentPage = 'beranda'; require '_navbar.php'; ?>

  <!-- HERO -->
  <section class="village-hero">
    <div class="container">
      <div class="village-hero-inner animate-fade-in-up">
        <div class="village-hero-icon"><?= e($desa['emoji']) ?></div>
        <div class="village-hero-text">
          <div class="eyebrow" style="font-size:.8rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;opacity:.8;margin-bottom:.5rem;">🏛️ Kecamatan Muaradua · OKU Selatan</div>
          <h1>Desa <?= e($desa['nama']) ?></h1>
          <p><?= e($desa['deskripsi'] ?: 'Selamat datang di portal informasi resmi Desa ' . $desa['nama']) ?></p>
          <div style="margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="data.php" class="btn btn-ghost">📊 Data Desa</a>
            <a href="infografis.php" class="btn" style="background:var(--accent);color:#fff;">📈 Infografis</a>
            <a href="kuesioner.php" class="btn btn-ghost">📋 Profil Desa</a>
            <a href="#foto-desa" class="btn btn-ghost">📷 Foto Desa</a>
          </div>
        </div>
      </div>
    </div>
    <div class="village-hero-wave">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 80" preserveAspectRatio="none">
        <path fill="#F4F7FB" d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z"/>
      </svg>
    </div>
  </section>

  <!-- STATISTIK CEPAT -->
  <div class="container quick-stats animate-fade-in-up delay-2">
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon blue">👥</div><div class="stat-info"><h4>Total Penduduk</h4><div class="value"><?= fmtNum($desa['penduduk_total']) ?></div><div class="sub">jiwa (2024)</div></div></div>
      <div class="stat-card"><div class="stat-icon orange">🏠</div><div class="stat-info"><h4>Kepala Keluarga</h4><div class="value"><?= fmtNum($desa['jumlah_kk']) ?></div><div class="sub">KK terdaftar</div></div></div>
      <div class="stat-card"><div class="stat-icon green">📐</div><div class="stat-info"><h4>Luas Wilayah</h4><div class="value"><?= e($desa['luas_wilayah']) ?></div><div class="sub">luas total desa</div></div></div>
      <div class="stat-card"><div class="stat-icon red">🏘️</div><div class="stat-info"><h4>Wilayah</h4><div class="value"><?= (int)$desa['jumlah_rt'] ?> RT</div><div class="sub"><?= (int)$desa['jumlah_rw'] ?> RW</div></div></div>
    </div>
  </div>

  <!-- KONTEN UTAMA -->
  <div class="container" style="padding-bottom:3rem;">
    <div class="grid-2 mt-4">

      <!-- Profil Desa -->
      <div class="card animate-fade-in-up delay-1">
        <div class="card-header">🏛️ Profil Desa</div>
        <div class="card-body">
          <div class="profile-grid">
            <div class="profile-item"><label>Nama Desa</label><p><?= e($desa['nama']) ?></p></div>
            <div class="profile-item"><label>Kecamatan</label><p>Muaradua</p></div>
            <div class="profile-item"><label>Kabupaten</label><p>OKU Selatan</p></div>
            <div class="profile-item"><label>Provinsi</label><p>Sumatera Selatan</p></div>
            <div class="profile-item"><label>Kepala Desa</label><p><?= e($desa['kepala_desa']) ?></p></div>
            <div class="profile-item"><label>Kode Pos</label><p><?= e($desa['kode_pos']) ?></p></div>
            <div class="profile-item"><label>Jumlah RT</label><p><?= (int)$desa['jumlah_rt'] ?> RT</p></div>
            <div class="profile-item"><label>Jumlah RW</label><p><?= (int)$desa['jumlah_rw'] ?> RW</p></div>
          </div>
          <a href="data.php" class="btn btn-outline mt-3 w-full" style="justify-content:center;">Lihat Data Lengkap →</a>
        </div>
      </div>

      <!-- Pengumuman dari DB -->
      <div class="card animate-fade-in-up delay-2">
        <div class="card-header">📢 Pengumuman Terbaru</div>
        <div class="card-body">
          <?php if (empty($pengumuman)): ?>
            <p class="text-light" style="text-align:center;padding:1rem;">Belum ada pengumuman.</p>
          <?php else: ?>
            <?php foreach ($pengumuman as $i => $p): ?>
            <div class="update-item">
              <div class="update-dot" style="background:<?= $i===0?'var(--accent)':($i===1?'var(--green)':'var(--primary)') ?>"></div>
              <div>
                <h4 style="font-size:.95rem;margin-bottom:.2rem;"><?= e($p['judul']) ?></h4>
                <span style="font-size:.8rem;color:var(--text-light)">📅 <?= date('d F Y', strtotime($p['tanggal'])) ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Potensi Desa -->
    <div class="card mt-4 animate-fade-in-up delay-3">
      <div class="card-header">🌿 Potensi Desa</div>
      <div class="card-body">
        <div class="grid-3">
          <div style="text-align:center;padding:1.25rem;background:var(--bg-section);border-radius:var(--radius-sm);">
            <div style="font-size:2.5rem;margin-bottom:.5rem;">🌾</div><h4>Pertanian</h4>
            <p style="font-size:.85rem;">Padi, jagung, dan sayuran sebagai komoditas utama</p>
          </div>
          <div style="text-align:center;padding:1.25rem;background:var(--bg-section);border-radius:var(--radius-sm);">
            <div style="font-size:2.5rem;margin-bottom:.5rem;">🐟</div><h4>Perikanan</h4>
            <p style="font-size:.85rem;">Budidaya ikan air tawar dan hasil sungai</p>
          </div>
          <div style="text-align:center;padding:1.25rem;background:var(--bg-section);border-radius:var(--radius-sm);">
            <div style="font-size:2.5rem;margin-bottom:.5rem;">🏔️</div><h4>Wisata Alam</h4>
            <p style="font-size:.85rem;">Potensi wisata alam sungai dan perbukitan</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== FOTO DESA ===== -->
  <section class="foto-section" id="foto-desa">
    <div class="container">
      <div class="foto-section-header">
        <div>
          <div class="eyebrow">📷 Galeri Foto</div>
          <h2 style="margin-top:.25rem;">Foto-Foto Desa <?= e($desa['nama']) ?></h2>
        </div>
        <?php if (isAdminLoggedIn((int)$desa['id'])): ?>
        <a href="admin.php" class="btn btn-outline btn-sm">⚙️ Kelola Foto</a>
        <?php endif; ?>
      </div>

      <?php if (empty($fotos)): ?>
        <div class="foto-empty">
          <div class="foto-empty-icon">🏞️</div>
          <h4>Belum Ada Foto</h4>
          <p>Foto-foto desa akan ditampilkan di sini setelah admin mengunggahnya.</p>
        </div>
      <?php else: ?>
        <div class="foto-carousel-wrap">
          <div class="foto-carousel" id="fotoCarousel">
            <?php foreach($fotos as $foto): ?>
            <div class="foto-slide">
              <img src="../../uploads/foto/<?= e($foto['filename']) ?>"
                   alt="<?= e($foto['judul'] ?: 'Foto Desa ' . $desa['nama']) ?>"
                   draggable="false" />
              <?php if ($foto['judul']): ?>
              <div class="foto-slide-caption"><?= e($foto['judul']) ?></div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="foto-nav">
          <button class="foto-nav-btn" id="fotoPrev" title="Sebelumnya">←</button>
          <button class="foto-nav-btn" id="fotoNext" title="Berikutnya">→</button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
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
            <li><a href="#foto-desa">Foto Desa</a></li>
            <li><a href="login.php">Admin Login</a></li>
          </ul>
        </div>
        <div>
          <h4>Kecamatan</h4>
          <ul><li><a href="/muaradua-web/">← Kecamatan Muaradua</a></li></ul>
        </div>
      </div>
      <div class="footer-bottom"><p>&copy; <?= date('Y') ?> Desa <?= e($desa['nama']) ?> · Kecamatan Muaradua</p></div>
    </div>
  </footer>

  <script src="../../js/main.js"></script>
  <script>
    // ===== FOTO CAROUSEL DRAG =====
    (function(){
      const carousel = document.getElementById('fotoCarousel');
      if (!carousel) return;

      let isDown = false, startX, scrollLeft;

      carousel.addEventListener('mousedown', e => {
        isDown = true;
        carousel.classList.add('grabbing');
        startX = e.pageX - carousel.offsetLeft;
        scrollLeft = carousel.scrollLeft;
      });
      carousel.addEventListener('mouseleave', () => { isDown = false; carousel.classList.remove('grabbing'); });
      carousel.addEventListener('mouseup', () => { isDown = false; carousel.classList.remove('grabbing'); });
      carousel.addEventListener('mousemove', e => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - carousel.offsetLeft;
        const walk = (x - startX) * 1.5;
        carousel.scrollLeft = scrollLeft - walk;
      });

      // Touch support
      let touchStartX = 0, touchScrollLeft = 0;
      carousel.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].pageX;
        touchScrollLeft = carousel.scrollLeft;
      }, {passive:true});
      carousel.addEventListener('touchmove', e => {
        const dx = touchStartX - e.touches[0].pageX;
        carousel.scrollLeft = touchScrollLeft + dx;
      }, {passive:true});

      // Nav buttons
      const slideWidth = () => {
        const slide = carousel.querySelector('.foto-slide');
        return slide ? slide.offsetWidth + 16 : 320;
      };
      const prev = document.getElementById('fotoPrev');
      const next = document.getElementById('fotoNext');
      if (prev) prev.addEventListener('click', () => {
        carousel.style.scrollBehavior = 'smooth';
        carousel.scrollLeft -= slideWidth() * 2;
      });
      if (next) next.addEventListener('click', () => {
        carousel.style.scrollBehavior = 'smooth';
        carousel.scrollLeft += slideWidth() * 2;
      });
    })();
  </script>
</body>
</html>
