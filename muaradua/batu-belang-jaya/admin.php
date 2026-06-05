<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

// Proteksi halaman admin
requireAdminPage($desa, '');
$adminNama = $_SESSION['admin_nama'];

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = []; session_destroy();
    header('Location: login.php'); exit;
}

// Ambil data untuk dashboard
$totalPenduduk = $pdo->prepare("SELECT IFNULL(SUM(total),0) FROM penduduk WHERE desa_id=?"); $totalPenduduk->execute([$desa['id']]); $totalPenduduk = $totalPenduduk->fetchColumn();
$totalInfografis = $pdo->prepare("SELECT COUNT(*) FROM infografis WHERE desa_id=?"); $totalInfografis->execute([$desa['id']]); $totalInfografis = $totalInfografis->fetchColumn();
$totalPengumuman = $pdo->prepare("SELECT COUNT(*) FROM pengumuman WHERE desa_id=? AND aktif=1"); $totalPengumuman->execute([$desa['id']]); $totalPengumuman = $totalPengumuman->fetchColumn();
// Data tabel dinamis
$dataTabel = $pdo->prepare("SELECT id,kategori,judul,created_at FROM data_tabel WHERE desa_id=? ORDER BY kategori,created_at DESC"); $dataTabel->execute([$desa['id']]); $dataTabel=$dataTabel->fetchAll();
$totalDataTabel = count($dataTabel);
// Foto desa
$fotoData = $pdo->prepare("SELECT id, judul, filename, urutan FROM foto_desa WHERE desa_id=? ORDER BY urutan ASC, id ASC"); $fotoData->execute([$desa['id']]); $fotoData=$fotoData->fetchAll();
$totalFoto = count($fotoData);

// Data tabel per menu
$pendudukData   = $pdo->prepare("SELECT * FROM penduduk    WHERE desa_id=? ORDER BY rw,rt"); $pendudukData->execute([$desa['id']]); $pendudukData=$pendudukData->fetchAll();
$infografisData = $pdo->prepare("SELECT * FROM infografis  WHERE desa_id=? ORDER BY created_at DESC"); $infografisData->execute([$desa['id']]); $infografisData=$infografisData->fetchAll();
$pengumumanData = $pdo->prepare("SELECT * FROM pengumuman  WHERE desa_id=? ORDER BY tanggal DESC"); $pengumumanData->execute([$desa['id']]); $pengumumanData=$pengumumanData->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Dashboard Admin – Desa <?= e($desa['nama']) ?></title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <style>
    body{background:var(--bg-light);margin:0;}
    .admin-layout{display:flex;min-height:100vh;}
    .admin-sidebar{width:240px;background:#0F2944;flex-shrink:0;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
    .admin-sidebar-brand{padding:1.5rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:.75rem;}
    .admin-sidebar-brand-icon{font-size:1.5rem;}
    .admin-sidebar-brand h3{color:var(--white);font-size:.95rem;margin:0;}
    .admin-sidebar-brand span{color:rgba(255,255,255,.5);font-size:.75rem;}
    .admin-sidebar-menu{padding:1rem 0;flex:1;}
    .admin-menu-label{padding:.5rem 1.25rem;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);}
    .admin-sidebar-menu a{display:flex;align-items:center;gap:.75rem;padding:.7rem 1.25rem;color:rgba(255,255,255,.7);text-decoration:none;font-size:.875rem;transition:var(--transition);}
    .admin-sidebar-menu a:hover,.admin-sidebar-menu a.active{background:rgba(255,255,255,.1);color:#fff;}
    .admin-sidebar-menu a.active{border-left:3px solid var(--accent);}
    .admin-main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
    .admin-topbar{background:var(--white);border-bottom:1px solid var(--border);padding:.875rem 1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
    .admin-topbar h4{font-size:1rem;margin:0;}
    .admin-topbar-right{display:flex;align-items:center;gap:1rem;}
    .admin-avatar{width:36px;height:36px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;}
    .admin-content{padding:1.75rem;flex:1;overflow-y:auto;}
    .admin-panel{display:none;} .admin-panel.active{display:block;}
    .admin-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:1.75rem;}
    .admin-stat-card{background:var(--white);border-radius:var(--radius-lg);padding:1.25rem 1.5rem;border:1px solid var(--border);display:flex;align-items:center;gap:1rem;}
    .admin-stat-icon{width:48px;height:48px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:1.5rem;}
    .admin-stat-info h5{font-size:.8rem;color:var(--text-light);font-weight:500;margin-bottom:.2rem;}
    .admin-stat-info .val{font-size:1.5rem;font-weight:800;color:var(--text-dark);}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
    .form-group{display:flex;flex-direction:column;gap:.35rem;margin-bottom:0;}
    .form-label{font-size:.8rem;font-weight:600;color:var(--text-mid);}
    .success-msg{background:#D1FAE5;border:1px solid #6EE7B7;color:#065F46;padding:.75rem 1rem;border-radius:var(--radius-sm);font-size:.875rem;margin-bottom:1rem;display:none;}
    @media(max-width:768px){.admin-sidebar{display:none;}.form-grid{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
      <div class="admin-sidebar-brand-icon"><?= e($desa['emoji']) ?></div>
      <div><h3>Dashboard Admin</h3><span>Desa <?= e($desa['nama']) ?></span></div>
    </div>
    <nav class="admin-sidebar-menu">
      <div class="admin-menu-label">Menu Utama</div>
      <a href="#" class="active" onclick="showPanel('dashboard',this)">🏠 Dashboard</a>
      <div class="admin-menu-label">Data Desa</div>
      <a href="#" onclick="showPanel('penduduk',this)">👥 Data Penduduk</a>
      <a href="#" onclick="showPanel('datadesa',this)">📋 Kelola Data Desa</a>
      <div class="admin-menu-label">Konten</div>
      <a href="#" onclick="showPanel('infografis',this)">📈 Kelola Infografis</a>
      <a href="#" onclick="showPanel('pengumuman',this)">📢 Pengumuman</a>
      <a href="#" onclick="showPanel('foto',this)">📷 Foto Desa</a>
      <div class="admin-menu-label">Kuesioner</div>
      <a href="#" onclick="showPanel('kuesioner',this)">📝 Kuesioner Desa</a>
      <div class="admin-menu-label">Akun</div>
      <a href="index.php" target="_blank">🌐 Lihat Website</a>
      <a href="?logout=1" onclick="return confirm('Yakin ingin keluar?')">🚪 Keluar</a>
    </nav>
  </aside>

  <div class="admin-main">
    <!-- TOP BAR -->
    <div class="admin-topbar">
      <h4>Dashboard Admin – Desa <?= e($desa['nama']) ?></h4>
      <div class="admin-topbar-right">
        <a href="index.php" target="_blank" style="font-size:.82rem;color:var(--primary);">🌐 Lihat Website</a>
        <div class="admin-avatar"><?= strtoupper(substr($adminNama,0,1)) ?></div>
        <div><div style="font-size:.85rem;font-weight:600;"><?= e($adminNama) ?></div><div style="font-size:.72rem;color:var(--text-light);"><?= e($_SESSION['admin_username']) ?></div></div>
        <a href="?logout=1" onclick="return confirm('Yakin keluar?')" style="color:var(--accent);font-size:.82rem;">🚪 Keluar</a>
      </div>
    </div>

    <div class="admin-content">

      <!-- DASHBOARD -->
      <div id="panel-dashboard" class="admin-panel active animate-fade-in-up">
        <h3 style="margin-bottom:1.25rem;">🏠 Selamat Datang, <?= e($adminNama) ?>!</h3>
        <div class="admin-stats">
          <div class="admin-stat-card"><div class="admin-stat-icon" style="background:#EEF3FA">👥</div><div class="admin-stat-info"><h5>Total Penduduk</h5><div class="val"><?= fmtNum($totalPenduduk) ?></div></div></div>
          <div class="admin-stat-card"><div class="admin-stat-icon" style="background:#FFF8EE">📈</div><div class="admin-stat-info"><h5>Infografis</h5><div class="val"><?= $totalInfografis ?></div></div></div>
          <div class="admin-stat-card"><div class="admin-stat-icon" style="background:#F0FFF4">📢</div><div class="admin-stat-info"><h5>Pengumuman Aktif</h5><div class="val"><?= $totalPengumuman ?></div></div></div>
          <div class="admin-stat-card"><div class="admin-stat-icon" style="background:#F5F0FF">📋</div><div class="admin-stat-info"><h5>Tabel Data Desa</h5><div class="val"><?= $totalDataTabel ?></div></div></div>
          <div class="admin-stat-card"><div class="admin-stat-icon" style="background:#FFF0F8">📷</div><div class="admin-stat-info"><h5>Foto Desa</h5><div class="val"><?= $totalFoto ?></div></div></div>
        </div>
        <div class="admin-stats" style="margin-bottom:1.75rem;">
          <div class="admin-stat-card"><div class="admin-stat-icon" style="background:#E8F5E9">📝</div><div class="admin-stat-info"><h5>Kuesioner <?= date('Y') ?></h5><div class="val"><?php
$cekKues = $pdo->prepare("SELECT status FROM kuesioner WHERE desa_id=? AND tahun=? LIMIT 1");
$cekKues->execute([$desa['id'], date('Y')]);
$kuesStatus = $cekKues->fetchColumn();
if ($kuesStatus === 'selesai') echo '<span style="color:#059669;font-size:.9rem;">✅ Selesai</span>';
elseif ($kuesStatus === 'draft') echo '<span style="color:#D97706;font-size:.9rem;">✏️ Draft</span>';
else echo '<span style="color:#aaa;font-size:.85rem;">Belum</span>';
?></div></div></div>
        </div><!-- /admin-stats kuesioner -->
        <div class="card">
          <div class="card-header">📋 Aksi Cepat</div>
          <div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap;">
            <button class="btn" onclick="showPanel('penduduk',null)">👥 Kelola Penduduk</button>
            <button class="btn btn-outline" onclick="showPanel('infografis',null)">📈 Tambah Infografis</button>
            <button class="btn btn-outline" onclick="showPanel('pengumuman',null)">📢 Tambah Pengumuman</button>
            <button class="btn btn-outline" onclick="showPanel('foto',null)">📷 Upload Foto</button>
            <button class="btn btn-outline" onclick="showPanel('kuesioner',null)">📝 Isi Kuesioner</button>
            <a href="data.php" class="btn btn-outline" target="_blank">📊 Lihat Data Publik</a>
          </div>
        </div>
      </div>

      <!-- PENDUDUK -->
      <div id="panel-penduduk" class="admin-panel animate-fade-in-up">
        <h3 style="margin-bottom:1.25rem;">👥 Kelola Data Penduduk</h3>
        <div id="msg-penduduk" class="success-msg"></div>
        <div class="card mb-3">
          <div class="card-header">➕ Tambah Data Penduduk</div>
          <div class="card-body">
            <form id="formPenduduk">
              <input type="hidden" name="desa_id" value="<?= (int)$desa['id'] ?>"/>
              <div class="form-grid">
                <div class="form-group"><label class="form-label">Nomor RT</label><input name="rt" class="form-input" placeholder="RT 01" required/></div>
                <div class="form-group"><label class="form-label">Nomor RW</label><input name="rw" class="form-input" placeholder="RW 01" required/></div>
                <div class="form-group"><label class="form-label">Laki-laki</label><input type="number" name="laki_laki" class="form-input" min="0" placeholder="0" required/></div>
                <div class="form-group"><label class="form-label">Perempuan</label><input type="number" name="perempuan" class="form-input" min="0" placeholder="0" required/></div>
              </div>
              <button type="submit" class="btn mt-3">➕ Tambah Data</button>
            </form>
          </div>
        </div>
        <div class="card">
          <div class="card-header">📋 Daftar Data Penduduk</div>
          <div class="table-wrapper">
            <table id="tablePenduduk">
              <thead><tr><th>No</th><th>RT</th><th>RW</th><th>Laki-laki</th><th>Perempuan</th><th>Total</th><th>Aksi</th></tr></thead>
              <tbody>
                <?php foreach($pendudukData as $i=>$r): ?>
                <tr id="row-p-<?= $r['id'] ?>">
                  <td><?= $i+1 ?></td><td><?= e($r['rt']) ?></td><td><?= e($r['rw']) ?></td>
                  <td><?= fmtNum($r['laki_laki']) ?></td><td><?= fmtNum($r['perempuan']) ?></td><td><strong><?= fmtNum($r['total']) ?></strong></td>
                  <td><button class="btn btn-danger btn-sm" onclick="hapusPenduduk(<?= $r['id'] ?>)">Hapus</button></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- INFOGRAFIS -->
      <div id="panel-infografis" class="admin-panel animate-fade-in-up">
        <h3 style="margin-bottom:1.25rem;">📈 Kelola Infografis</h3>
        <div id="msg-infografis" class="success-msg"></div>
        <div class="card mb-3">
          <div class="card-header">➕ Tambah Infografis Baru</div>
          <div class="card-body">
            <form id="formInfografis">
              <div class="form-grid">
                <div class="form-group"><label class="form-label">Judul Infografis</label><input name="judul" class="form-input" placeholder="Judul infografis" required/></div>
                <div class="form-group"><label class="form-label">Kategori</label>
                  <select name="kategori" class="form-input" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option>Kependudukan</option><option>Pendidikan</option><option>Ekonomi</option>
                    <option>Kesehatan</option><option>Infrastruktur</option><option>Pertanian</option><option>Sosial</option>
                  </select>
                </div>
                <div class="form-group"><label class="form-label">Emoji / Ikon</label><input name="emoji" class="form-input" placeholder="📊" maxlength="5"/></div>
                <div class="form-group"><label class="form-label">Warna BG</label>
                  <select name="warna_bg" class="form-input">
                    <option value="#EEF3FA">Biru Muda</option><option value="#FFF8EE">Oranye Muda</option>
                    <option value="#F0FFF4">Hijau Muda</option><option value="#FFF0F0">Merah Muda</option><option value="#F5F0FF">Ungu Muda</option>
                  </select>
                </div>
              </div>
              <div class="form-group mt-2"><label class="form-label">Deskripsi Singkat</label><textarea name="deskripsi" class="form-input" rows="3" placeholder="Deskripsi singkat infografis ini..."></textarea></div>
              <button type="submit" class="btn mt-3">➕ Tambah Infografis</button>
            </form>
          </div>
        </div>
        <div class="card">
          <div class="card-header flex-between"><span>📋 Daftar Infografis</span><a href="infografis.php" target="_blank" class="btn btn-outline btn-sm">Lihat Publik →</a></div>
          <div class="table-wrapper">
            <table id="tableInfografis">
              <thead><tr><th>Ikon</th><th>Judul</th><th>Kategori</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
              <tbody>
                <?php foreach($infografisData as $r): ?>
                <tr id="row-i-<?= $r['id'] ?>">
                  <td style="font-size:1.5rem"><?= e($r['emoji']) ?></td>
                  <td><?= e($r['judul']) ?></td>
                  <td><span class="badge badge-primary"><?= e($r['kategori']) ?></span></td>
                  <td style="font-size:.82rem;color:var(--text-light)"><?= e(substr($r['deskripsi'],0,60)) ?>...</td>
                  <td><button class="btn btn-danger btn-sm" onclick="hapusInfografis(<?= $r['id'] ?>)">Hapus</button></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- PENGUMUMAN -->
      <div id="panel-pengumuman" class="admin-panel animate-fade-in-up">
        <h3 style="margin-bottom:1.25rem;">📢 Kelola Pengumuman</h3>
        <div id="msg-pengumuman" class="success-msg"></div>
        <div class="card mb-3">
          <div class="card-header">➕ Tambah Pengumuman</div>
          <div class="card-body">
            <form id="formPengumuman">
              <div class="form-group mb-3"><label class="form-label">Judul Pengumuman</label><input name="judul" class="form-input" placeholder="Judul pengumuman" required/></div>
              <div class="form-group mb-3"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-input" value="<?= date('Y-m-d') ?>" required/></div>
              <div class="form-group mb-3"><label class="form-label">Isi Pengumuman</label><textarea name="isi" class="form-input" rows="4" placeholder="Isi pengumuman..."></textarea></div>
              <button type="submit" class="btn">📢 Tambah Pengumuman</button>
            </form>
          </div>
        </div>
        <div class="card">
          <div class="card-header">📋 Daftar Pengumuman</div>
          <div class="table-wrapper">
            <table id="tablePengumuman">
              <thead><tr><th>Judul</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
              <tbody>
                <?php foreach($pengumumanData as $r): ?>
                <tr id="row-pg-<?= $r['id'] ?>">
                  <td><?= e($r['judul']) ?></td>
                  <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                  <td><?= $r['aktif']?'<span class="badge badge-green">Aktif</span>':'<span class="badge">Nonaktif</span>' ?></td>
                  <td><button class="btn btn-danger btn-sm" onclick="hapusPengumuman(<?= $r['id'] ?>)">Hapus</button></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- DATA DESA (Table Builder) -->
      <div id="panel-datadesa" class="admin-panel animate-fade-in-up">
        <h3 style="margin-bottom:1.25rem;">📋 Kelola Data Desa</h3>
        <div id="msg-datadesa" class="success-msg"></div>

        <!-- FORM TAMBAH -->
        <div class="card mb-3">
          <div class="card-header">➕ Tambah Tabel Data Baru</div>
          <div class="card-body">
            <form id="formDataDesa" onsubmit="submitDataDesa(event)">
              <input type="hidden" name="desa_id" value="<?= (int)$desa['id'] ?>"/>
              <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                <div class="form-group">
                  <label class="form-label">Kategori</label>
                  <select name="kategori" id="inputKategori" class="form-input" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option>Kependudukan</option><option>Pendidikan</option>
                    <option>Ekonomi</option><option>Kesehatan</option>
                    <option>Infrastruktur</option><option>Pertanian</option>
                    <option>Sosial &amp; Budaya</option><option>Lainnya</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Judul Tabel</label>
                  <input name="judul" id="inputJudul" class="form-input" placeholder="Contoh: Data Penduduk per RT/RW" required/>
                </div>
              </div>

              <!-- TABLE BUILDER -->
              <div style="margin-top:1.25rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
                  <label class="form-label" style="margin:0;">Buat Tabel (tambah/hapus baris &amp; kolom)</label>
                  <div style="display:flex;gap:.5rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="builderAddCol()">+ Kolom</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="builderAddRow()">+ Baris</button>
                  </div>
                </div>
                <div style="overflow-x:auto;">
                  <table id="tableBuilder" style="border-collapse:collapse;min-width:100%;">
                    <thead>
                      <tr id="builderHeader">
                        <th style="background:var(--bg-section);padding:.5rem;border:1px solid var(--border);font-size:.8rem;">
                          <input class="form-input col-header" placeholder="Kolom 1" style="min-width:100px;padding:.3rem .5rem;font-size:.8rem;"/>
                          <button type="button" onclick="builderRemoveCol(this)" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:.9rem;margin-left:.25rem;">✕</button>
                        </th>
                        <th style="background:var(--bg-section);padding:.5rem;border:1px solid var(--border);font-size:.8rem;">
                          <input class="form-input col-header" placeholder="Kolom 2" style="min-width:100px;padding:.3rem .5rem;font-size:.8rem;"/>
                          <button type="button" onclick="builderRemoveCol(this)" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:.9rem;margin-left:.25rem;">✕</button>
                        </th>
                        <th style="background:var(--primary);color:#fff;padding:.5rem .4rem;border:1px solid var(--border);width:40px;text-align:center;font-size:.75rem;">Hapus</th>
                      </tr>
                    </thead>
                    <tbody id="builderBody">
                      <tr class="builder-row">
                        <td style="padding:.35rem;border:1px solid var(--border);"><input class="form-input row-cell" placeholder="-" style="padding:.3rem .5rem;font-size:.85rem;min-width:100px;"/></td>
                        <td style="padding:.35rem;border:1px solid var(--border);"><input class="form-input row-cell" placeholder="-" style="padding:.3rem .5rem;font-size:.85rem;min-width:100px;"/></td>
                        <td style="padding:.35rem;border:1px solid var(--border);text-align:center;"><button type="button" onclick="builderRemoveRow(this)" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:1.1rem;">✕</button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <button type="submit" class="btn mt-3">💾 Simpan Tabel</button>
            </form>
          </div>
        </div>

        <!-- DAFTAR TABEL YANG ADA -->
        <div class="card">
          <div class="card-header flex-between">
            <span>📊 Daftar Tabel Data Desa</span>
            <a href="data.php" target="_blank" class="btn btn-outline btn-sm">Lihat Publik →</a>
          </div>
          <div class="table-wrapper"><table id="tableDataDesa">
            <thead><tr><th>No</th><th>Kategori</th><th>Judul</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php if(empty($dataTabel)): ?>
              <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-light)">Belum ada tabel. Tambahkan menggunakan form di atas.</td></tr>
              <?php else: foreach($dataTabel as $i=>$t): ?>
              <tr id="row-dt-<?= $t['id'] ?>">
                <td><?= $i+1 ?></td>
                <td><span class="badge badge-primary"><?= e($t['kategori']) ?></span></td>
                <td><?= e($t['judul']) ?></td>
                <td style="font-size:.8rem;color:var(--text-light)"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                <td><button class="btn btn-danger btn-sm" onclick="hapusDataTabel(<?= $t['id'] ?>)">Hapus</button></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table></div>
        </div>
      </div><!-- /panel-datadesa -->

      <!-- FOTO DESA -->
      <div id="panel-foto" class="admin-panel animate-fade-in-up">
        <h3 style="margin-bottom:1.25rem;">📷 Kelola Foto Desa</h3>
        <div id="msg-foto" class="success-msg"></div>

        <!-- Upload Form -->
        <div class="card mb-3">
          <div class="card-header">⬆️ Upload Foto Baru</div>
          <div class="card-body">
            <form id="formFoto" enctype="multipart/form-data">
              <input type="hidden" name="desa_id" value="<?= (int)$desa['id'] ?>"/>
              <div class="form-grid" style="grid-template-columns:1fr 1fr;">
                <div class="form-group">
                  <label class="form-label">Judul Foto (opsional)</label>
                  <input name="judul" class="form-input" placeholder="Contoh: Sawah di Musim Panen"/>
                </div>
                <div class="form-group">
                  <label class="form-label">Pilih Foto (jpg/png/webp, maks 5MB)</label>
                  <input type="file" name="foto" class="form-input" accept="image/*" required id="inputFoto"/>
                </div>
              </div>
              <!-- Preview -->
              <div id="fotoPreviewWrap" style="display:none;margin-top:.75rem;">
                <img id="fotoPreview" src="" alt="Preview" style="max-height:200px;border-radius:8px;border:1px solid var(--border);"/>
              </div>
              <button type="submit" class="btn mt-3">📷 Upload Foto</button>
            </form>
          </div>
        </div>

        <!-- Daftar Foto -->
        <div class="card">
          <div class="card-header flex-between">
            <span>🖼️ Daftar Foto (<?= $totalFoto ?>) — geser untuk melihat semua</span>
            <a href="index.php#foto-desa" target="_blank" class="btn btn-outline btn-sm">Lihat Publik →</a>
          </div>
          <div class="card-body">
            <?php if(empty($fotoData)): ?>
            <p style="text-align:center;color:var(--text-light);padding:2rem;">Belum ada foto. Upload foto pertama desa Anda!</p>
            <?php else: ?>
            <div id="fotoGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
              <?php foreach($fotoData as $f): ?>
              <div id="foto-card-<?= $f['id'] ?>" style="background:var(--bg-section);border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--border);">
                <img src="../../uploads/foto/<?= e($f['filename']) ?>" alt="<?= e($f['judul']) ?>" style="width:100%;height:150px;object-fit:cover;display:block;"/>
                <div style="padding:.6rem .75rem;">
                  <div style="font-size:.82rem;font-weight:600;color:var(--text-dark);margin-bottom:.4rem;"><?= e($f['judul'] ?: '(tanpa judul)') ?></div>
                  <button class="btn btn-danger btn-sm" onclick="hapusFoto(<?= $f['id'] ?>)">🗑️ Hapus</button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div><!-- /panel-foto -->

      <?php include __DIR__ . '/panel_kuesioner.php'; ?>

    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script>
const DESA_ID = <?= (int)$desa['id'] ?>;
const BASE = '/muaradua-web';


function showPanel(id, el) {
  document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
  const panel = document.getElementById('panel-' + id);
  if (panel) panel.classList.add('active');
  document.querySelectorAll('.admin-sidebar-menu a').forEach(a => a.classList.remove('active'));
  if (el) el.classList.add('active');
  // Sync sidebar active link by panel id
  const sideLink = document.querySelector(`.admin-sidebar-menu a[onclick*="'${id}'"]`);
  if (sideLink) sideLink.classList.add('active');
  try { sessionStorage.setItem('activePanel', id); } catch(e) {}
  return false;
}

// Auto-restore panel setelah redirect (misal: ?ktahun=)
(function() {
  // Jika URL memiliki ?ktahun= → aktifkan panel kuesioner
  const urlParams = new URLSearchParams(window.location.search);
  let panelToShow = urlParams.has('ktahun') ? 'kuesioner' : null;
  if (!panelToShow) {
    try { panelToShow = sessionStorage.getItem('activePanel'); } catch(e) {}
  }
  if (panelToShow && panelToShow !== 'dashboard') {
    showPanel(panelToShow, null);
  }
  // Clear setelah dipakai
  try { sessionStorage.removeItem('activePanel'); } catch(e) {}
})();


function showMsg(id, txt, ok=true) {
  const el = document.getElementById('msg-' + id);
  el.style.display = 'block';
  el.style.background = ok ? '#D1FAE5' : '#FEF2F2';
  el.style.color = ok ? '#065F46' : '#B91C1C';
  el.style.borderColor = ok ? '#6EE7B7' : '#FECACA';
  el.textContent = (ok ? '✅ ' : '⚠️ ') + txt;
  setTimeout(() => { el.style.display = 'none'; }, 4000);
}

// --- PENDUDUK ---
document.getElementById('formPenduduk').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = Object.fromEntries(new FormData(e.target));
  const res = await fetch(BASE + '/api/penduduk.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify(fd) });
  const data = await res.json();
  showMsg('penduduk', data.message, data.success);
  if (data.success) { e.target.reset(); location.reload(); }
});

async function hapusPenduduk(id) {
  if (!confirm('Hapus data ini?')) return;
  const res = await fetch(BASE + '/api/penduduk.php?id=' + id, { method:'DELETE', credentials:'same-origin' });
  const data = await res.json();
  showMsg('penduduk', data.message, data.success);
  if (data.success) { document.getElementById('row-p-' + id)?.remove(); }
}

// --- INFOGRAFIS ---
document.getElementById('formInfografis').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = Object.fromEntries(new FormData(e.target));
  const res = await fetch(BASE + '/api/infografis.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify(fd) });
  const data = await res.json();
  showMsg('infografis', data.message, data.success);
  if (data.success) { e.target.reset(); location.reload(); }
});

async function hapusInfografis(id) {
  if (!confirm('Hapus infografis ini?')) return;
  const res = await fetch(BASE + '/api/infografis.php?id=' + id, { method:'DELETE', credentials:'same-origin' });
  const data = await res.json();
  showMsg('infografis', data.message, data.success);
  if (data.success) { document.getElementById('row-i-' + id)?.remove(); }
}

// --- PENGUMUMAN ---
document.getElementById('formPengumuman').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = Object.fromEntries(new FormData(e.target));
  const res = await fetch(BASE + '/api/pengumuman.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify(fd) });
  const data = await res.json();
  showMsg('pengumuman', data.message, data.success);
  if (data.success) { e.target.reset(); location.reload(); }
});

async function hapusPengumuman(id) {
  if (!confirm('Hapus pengumuman ini?')) return;
  const res = await fetch(BASE + '/api/pengumuman.php?id=' + id, { method:'DELETE', credentials:'same-origin' });
  const data = await res.json();
  showMsg('pengumuman', data.message, data.success);
  if (data.success) { document.getElementById('row-pg-' + id)?.remove(); }
}

// --- DATA DESA TABLE BUILDER ---
function builderGetColCount() {
  return document.querySelectorAll('#builderHeader .col-header').length;
}

function builderAddCol() {
  const n = builderGetColCount() + 1;
  const headerRow = document.getElementById('builderHeader');
  const delTh = headerRow.lastElementChild;
  const th = document.createElement('th');
  th.style.cssText = 'background:var(--bg-section);padding:.5rem;border:1px solid var(--border);font-size:.8rem;';
  th.innerHTML = `<input class="form-input col-header" placeholder="Kolom ${n}" style="min-width:100px;padding:.3rem .5rem;font-size:.8rem;"/><button type="button" onclick="builderRemoveCol(this)" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:.9rem;margin-left:.25rem;">✕</button>`;
  headerRow.insertBefore(th, delTh);
  document.querySelectorAll('#builderBody .builder-row').forEach(row => {
    const delTd = row.lastElementChild;
    const td = document.createElement('td');
    td.style.cssText = 'padding:.35rem;border:1px solid var(--border);';
    td.innerHTML = '<input class="form-input row-cell" placeholder="-" style="padding:.3rem .5rem;font-size:.85rem;min-width:100px;"/>';
    row.insertBefore(td, delTd);
  });
}

function builderRemoveCol(btn) {
  if (builderGetColCount() <= 1) { alert('Minimal 1 kolom.'); return; }
  const th = btn.closest('th');
  const idx = Array.from(th.parentElement.children).indexOf(th);
  th.remove();
  document.querySelectorAll('#builderBody .builder-row').forEach(row => {
    row.children[idx]?.remove();
  });
}

function builderAddRow() {
  const cols = builderGetColCount();
  const tbody = document.getElementById('builderBody');
  const tr = document.createElement('tr');
  tr.className = 'builder-row';
  let html = '';
  for (let i = 0; i < cols; i++) {
    html += '<td style="padding:.35rem;border:1px solid var(--border);"><input class="form-input row-cell" placeholder="-" style="padding:.3rem .5rem;font-size:.85rem;min-width:100px;"/></td>';
  }
  html += '<td style="padding:.35rem;border:1px solid var(--border);text-align:center;"><button type="button" onclick="builderRemoveRow(this)" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:1.1rem;">✕</button></td>';
  tr.innerHTML = html;
  tbody.appendChild(tr);
}

function builderRemoveRow(btn) {
  const tr = btn.closest('tr');
  if (document.querySelectorAll('#builderBody .builder-row').length <= 1) { alert('Minimal 1 baris.'); return; }
  tr.remove();
}

async function submitDataDesa(e) {
  e.preventDefault();
  const desa_id = DESA_ID;
  const kategori = document.getElementById('inputKategori').value;
  const judul    = document.getElementById('inputJudul').value.trim();
  const headers  = Array.from(document.querySelectorAll('#builderHeader .col-header')).map(i => i.value.trim() || '—');
  const rows     = Array.from(document.querySelectorAll('#builderBody .builder-row')).map(tr =>
    Array.from(tr.querySelectorAll('.row-cell')).map(i => i.value.trim() || '—')
  );
  if (!kategori || !judul) { showMsg('datadesa','Kategori dan judul wajib diisi',false); return; }
  const res  = await fetch(BASE + '/api/data_tabel.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify({desa_id,kategori,judul,headers,rows}) });
  const data = await res.json();
  showMsg('datadesa', data.message, data.success);
  if (data.success) { location.reload(); }
}

async function hapusDataTabel(id) {
  if (!confirm('Hapus tabel ini?')) return;
  const res  = await fetch(BASE + '/api/data_tabel.php?id=' + id, { method:'DELETE', credentials:'same-origin' });
  const data = await res.json();
  showMsg('datadesa', data.message, data.success);
  if (data.success) { document.getElementById('row-dt-' + id)?.remove(); }
}

// --- FOTO DESA ---
document.getElementById('inputFoto')?.addEventListener('change', function(){
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('fotoPreview').src = e.target.result;
    document.getElementById('fotoPreviewWrap').style.display = 'block';
  };
  reader.readAsDataURL(file);
});

document.getElementById('formFoto')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const btn = e.target.querySelector('[type=submit]');
  btn.disabled = true; btn.textContent = '⏳ Mengupload...';
  try {
    const res = await fetch(BASE + '/api/foto.php', { method:'POST', credentials:'same-origin', body: fd });
    const data = await res.json();
    showMsg('foto', data.message, data.success);
    if (data.success) { e.target.reset(); document.getElementById('fotoPreviewWrap').style.display='none'; location.reload(); }
  } catch(err) {
    showMsg('foto', 'Gagal upload: ' + err.message, false);
  } finally {
    btn.disabled = false; btn.textContent = '📷 Upload Foto';
  }
});

async function hapusFoto(id) {
  if (!confirm('Hapus foto ini? Tindakan tidak dapat dibatalkan.')) return;
  const res  = await fetch(BASE + '/api/foto.php?id=' + id + '&desa_id=' + DESA_ID, { method:'DELETE', credentials:'same-origin' });
  const data = await res.json();
  showMsg('foto', data.message, data.success);
  if (data.success) { document.getElementById('foto-card-' + id)?.remove(); }
}
</script>

</body>
</html>
