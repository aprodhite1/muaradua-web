<?php
/**
 * panel_kuesioner.php — panel admin kuesioner desa (Word-style)
 * Di-include dari admin.php masing-masing desa
 * Variabel yang tersedia: $pdo, $desa (array)
 */
$soal = require dirname(__DIR__, 2) . '/config/kuesioner_soal.php';
$tahunAktif = (int)(isset($_GET['ktahun']) ? $_GET['ktahun'] : date('Y'));

// Ambil daftar tahun yang sudah ada
$stTahun = $pdo->prepare("SELECT tahun, status FROM kuesioner WHERE desa_id=? ORDER BY tahun DESC");
$stTahun->execute([$desa['id']]);
$tahunList = $stTahun->fetchAll();

// Ambil data kuesioner tahun aktif
$stK = $pdo->prepare("SELECT * FROM kuesioner WHERE desa_id=? AND tahun=? LIMIT 1");
$stK->execute([$desa['id'], $tahunAktif]);
$kHeader = $stK->fetch() ?: null;

$nilai = [];
if ($kHeader) {
    $stN = $pdo->prepare("SELECT kode, nilai FROM kuesioner_nilai WHERE kuesioner_id=?");
    $stN->execute([$kHeader['id']]);
    foreach ($stN->fetchAll() as $r) $nilai[$r['kode']] = $r['nilai'];
}
$get  = fn($k) => htmlspecialchars($nilai[$k] ?? '', ENT_QUOTES);
$getN = fn($k) => $nilai[$k] !== null && $nilai[$k] !== '' ? $nilai[$k] : '';
?>
<style>
/* ====== KUESIONER PANEL ====== */
#panel-kuesioner { font-family: Arial, sans-serif; }
.kues-wrap { background:#e8ecf0; padding:1.5rem; }
.kues-toolbar-bar {
  display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
  background:#fff; padding:.85rem 1.25rem; border-radius:8px;
  box-shadow:0 1px 4px rgba(0,0,0,.1); margin-bottom:1.25rem;
}
.kues-toolbar-bar h3 { margin:0; font-size:1rem; color:#1a3a5c; }
.kues-year-tabs { display:flex; gap:.4rem; flex-wrap:wrap; }
.kues-year-tab {
  padding:.25rem .75rem; border-radius:20px; font-size:.8rem; font-weight:600;
  cursor:pointer; border:2px solid #1a3a5c; color:#1a3a5c;
  background:#fff; text-decoration:none; transition:.15s;
}
.kues-year-tab:hover, .kues-year-tab.active { background:#1a3a5c; color:#fff; }
.kues-year-tab.selesai { border-color:#059669; color:#059669; }
.kues-year-tab.selesai.active, .kues-year-tab.selesai:hover { background:#059669; color:#fff; }

/* Kertas Word */
.kues-paper {
  background:#fff; max-width:820px; margin:0 auto;
  padding:2cm 2.5cm; box-shadow:0 2px 20px rgba(0,0,0,.18);
  font-family:'Times New Roman', Times, serif; font-size:12pt;
  color:#000; line-height:1.5;
}
/* Kop surat */
.kues-kop { border-bottom:4px double #000; padding-bottom:8px; margin-bottom:14px; text-align:center; }
.kues-kop-logo { font-size:2.5rem; line-height:1; }
.kues-kop h1 { font-size:13pt; margin:4px 0 2px; text-transform:uppercase; font-weight:700; }
.kues-kop p  { font-size:9.5pt; margin:0; }
/* Judul */
.kues-judul { text-align:center; margin:14px 0 18px; }
.kues-judul h2 { font-size:14pt; font-weight:700; text-transform:uppercase; margin:0 0 3px; text-decoration:underline; }
.kues-judul .sub { font-size:10pt; color:#333; }
/* Section */
.kues-section { margin-bottom:16px; }
.kues-sec-head {
  background:#1a3a5c; color:#fff; font-size:11pt; font-weight:700;
  padding:4px 10px; margin-bottom:8px;
}
/* Sub-section header */
.kues-subsec-head {
  background:#e8f0f8; color:#1a3a5c; font-size:10.5pt; font-weight:700;
  padding:3px 8px; margin:10px 0 4px; border-left:4px solid #1a3a5c;
}
/* Tabel isian */
.kues-tbl { width:100%; border-collapse:collapse; font-size:11pt; }
.kues-tbl td { padding:3px 4px; vertical-align:middle; }
.kues-tbl .td-no   { width:5%; text-align:center; }
.kues-tbl .td-lbl  { width:44%; font-weight:normal; }
.kues-tbl .td-sep  { width:2%; text-align:center; }
.kues-tbl .td-val  { width:49%; }
/* Input bergaris bawah */
.kf-input {
  border:none; border-bottom:1px solid #555; width:100%;
  font-family:'Times New Roman',Times,serif; font-size:11pt;
  padding:1px 4px; background:transparent; color:#000; outline:none;
}
.kf-input:focus { border-bottom-color:#1a3a5c; background:#f5f8ff; }
.kf-input[type=number] { width:120px; }
.kf-select {
  border:none; border-bottom:1px solid #555;
  font-family:'Times New Roman',Times,serif; font-size:11pt;
  background:transparent; color:#000; outline:none;
  cursor:pointer; padding:1px 2px; max-width:100%;
}
.kf-textarea {
  width:100%; border:1px solid #999; font-family:'Times New Roman',Times,serif;
  font-size:11pt; padding:4px 6px; resize:vertical; min-height:56px;
  margin-top:2px; background:#fffef5;
}
/* Tanda tangan */
.kues-ttd { display:grid; grid-template-columns:1fr 1fr; gap:2cm; margin-top:1.5cm; }
.kues-ttd-box { text-align:center; font-size:11pt; }
.kues-ttd-line { border-bottom:1px solid #333; margin:2.5cm .5cm .3cm; }
/* Status badge */
.kbadge { display:inline-block; padding:.2rem .65rem; border-radius:12px; font-size:.75rem; font-weight:700; font-family:Arial,sans-serif; }
.kbadge-draft    { background:#FEF9C3; color:#854D0E; }
.kbadge-selesai  { background:#D1FAE5; color:#065F46; }
/* New year form inline */
.kues-add-year-form { display:flex; align-items:center; gap:.4rem; }
.kues-add-year-form input[type=number] {
  width:80px; border:1.5px solid #1a3a5c; border-radius:6px;
  padding:.28rem .5rem; font-size:.83rem; outline:none;
}
.kues-add-year-form input[type=number]:focus { border-color:#0F2944; box-shadow:0 0 0 2px rgba(29,78,137,.15); }
/* Print */
@media print {
  .admin-sidebar,.admin-topbar,.kues-toolbar-bar,.kbtn { display:none!important; }
  .kues-paper { box-shadow:none; padding:1.5cm; margin:0; }
  .kues-wrap { background:#fff; padding:0; }
  .kf-input,.kf-select { border-bottom-color:#000; }
}
@media (max-width:700px) {
  .kues-paper { padding:.75rem; }
  .kues-ttd { grid-template-columns:1fr; gap:1rem; }
  .kf-input[type=number] { width:90px; }
}
</style>

<div id="panel-kuesioner" class="admin-panel animate-fade-in-up">

  <!-- TOOLBAR -->
  <div class="kues-toolbar-bar">
    <h3>📝 Kuesioner Desa</h3>

    <!-- Pilih tahun -->
    <div class="kues-year-tabs">
      <?php foreach ($tahunList as $tl): ?>
      <a href="?ktahun=<?= $tl['tahun'] ?>"
         onclick="event.preventDefault(); showPanel('kuesioner',null); window.location='?ktahun=<?= $tl['tahun'] ?>';"
         class="kues-year-tab <?= $tl['status'] ?> <?= $tl['tahun']==$tahunAktif?'active':'' ?>">
        <?= $tl['tahun'] ?>
        <?= $tl['status']==='selesai'?' ✔':'  ✏' ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Tahun baru -->
    <div style="margin-left:auto;display:flex;gap:.5rem;align-items:center;">
      <div class="kues-add-year-form">
        <input type="number" id="inputTahunBaru" value="<?= date('Y') ?>"
               min="2020" max="2099" placeholder="Tahun"/>
        <button class="btn btn-outline btn-sm" onclick="bukaKuesionerBaru()">＋ Tahun Baru</button>
      </div>
      <button class="btn btn-outline btn-sm" onclick="window.print()">🖨️ Cetak</button>
      <?php if($kHeader): ?>
      <button class="btn btn-danger btn-sm"
              onclick="if(confirm('Hapus kuesioner tahun <?=$tahunAktif?>?')) hapusKuesioner(<?=$tahunAktif?>)">🗑️ Hapus</button>
      <?php endif; ?>
    </div>
  </div>

  <div id="msg-kuesioner" class="success-msg" style="max-width:820px;margin:0 auto .75rem;font-family:Arial,sans-serif;"></div>

  <!-- KERTAS KUESIONER -->
  <div class="kues-wrap">
  <div class="kues-paper">

    <!-- KOP SURAT -->
    <div class="kues-kop">
      <div class="kues-kop-logo">🏛️</div>
      <h1>Pemerintah Desa <?= e($desa['nama']) ?></h1>
      <p>Kecamatan Muaradua, Kabupaten OKU Selatan, Provinsi Sumatera Selatan</p>
      <p><?= $kHeader ? ('Telp: '.e($nilai['no_telp'] ?? '-').' | Email: '.e($nilai['email_desa'] ?? '-')) : '&nbsp;' ?></p>
    </div>

    <!-- JUDUL -->
    <div class="kues-judul">
      <h2>KUESIONER DESA <?= $tahunAktif ?></h2>
      <div class="sub">Pendataan Potensi dan Kondisi Desa Tahun <?= $tahunAktif ?></div>
      <?php if($kHeader): ?>
      <div style="margin-top:6px;">
        <span class="kbadge <?= 'kbadge-'.$kHeader['status'] ?>">
          <?= $kHeader['status']==='selesai' ? '✅ Telah Diselesaikan' : '✏️ Draft — Belum Final' ?>
        </span>
      </div>
      <?php endif; ?>
    </div>

    <form id="formKuesioner">
    <input type="hidden" name="desa_id" value="<?= (int)$desa['id'] ?>"/>
    <input type="hidden" name="tahun"   value="<?= $tahunAktif ?>"/>

    <?php
    $no = 1;
    foreach ($soal as $bagian => $pertanyaan):
    ?>
    <div class="kues-section">
      <div class="kues-sec-head"><?= htmlspecialchars($bagian) ?></div>
      <table class="kues-tbl">
      <?php foreach ($pertanyaan as $p):
        $k    = $p['kode'];
        $tipe = $p['tipe'];
        $val  = $nilai[$k] ?? '';
        // Sub-section header (optional key 'sub')
        if (!empty($p['sub'])): ?>
      </table>
      <div class="kues-subsec-head"><?= htmlspecialchars($p['sub']) ?></div>
      <table class="kues-tbl">
      <?php endif; ?>
      <tr>
        <td class="td-no"><?= $no++ ?>.</td>
        <td class="td-lbl"><?= htmlspecialchars($p['label']) ?></td>
        <td class="td-sep">:</td>
        <td class="td-val">
          <?php if ($tipe === 'text'): ?>
            <input class="kf-input" type="text" name="nilai[<?=$k?>]"
                   value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" placeholder="<?= htmlspecialchars($p['label']) ?>"/>
          <?php elseif ($tipe === 'number'): ?>
            <input class="kf-input" type="number" name="nilai[<?=$k?>]"
                   value="<?= $val !== '' ? (int)$val : '' ?>" placeholder="0" min="0"/>
          <?php elseif ($tipe === 'select'): ?>
            <select class="kf-select" name="nilai[<?=$k?>]">
              <option value="">— Pilih —</option>
              <?php foreach ($p['opsi'] as $opt): ?>
              <option value="<?= htmlspecialchars($opt, ENT_QUOTES) ?>" <?= $val===$opt?'selected':'' ?>>
                <?= htmlspecialchars($opt) ?>
              </option>
              <?php endforeach; ?>
            </select>
          <?php elseif ($tipe === 'textarea'): ?>
            <textarea class="kf-textarea" name="nilai[<?=$k?>]"
                      placeholder="<?= htmlspecialchars($p['label']) ?>"><?= htmlspecialchars($val, ENT_QUOTES) ?></textarea>
          <?php elseif ($tipe === 'date'): ?>
            <input class="kf-input" type="date" name="nilai[<?=$k?>]" value="<?= htmlspecialchars($val, ENT_QUOTES) ?>"/>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </table>
    </div>
    <?php endforeach; ?>

    <!-- IDENTITAS PENGISI -->
    <div class="kues-section">
      <div class="kues-sec-head">IX. IDENTITAS PENGISI KUESIONER</div>
      <table class="kues-tbl">
        <tr>
          <td class="td-no">1.</td><td class="td-lbl">Nama Pengisi</td><td class="td-sep">:</td>
          <td class="td-val"><input class="kf-input" type="text" name="pengisi_nama"
            value="<?= htmlspecialchars($kHeader['pengisi_nama'] ?? '', ENT_QUOTES) ?>" placeholder="Nama Lengkap"/></td>
        </tr>
        <tr>
          <td class="td-no">2.</td><td class="td-lbl">Jabatan</td><td class="td-sep">:</td>
          <td class="td-val"><input class="kf-input" type="text" name="pengisi_jabatan"
            value="<?= htmlspecialchars($kHeader['pengisi_jabatan'] ?? '', ENT_QUOTES) ?>" placeholder="Jabatan"/></td>
        </tr>
        <tr>
          <td class="td-no">3.</td><td class="td-lbl">Tanggal Pengisian</td><td class="td-sep">:</td>
          <td class="td-val"><input class="kf-input" type="date" name="tanggal_pengisian"
            value="<?= htmlspecialchars($kHeader['tanggal_pengisian'] ?? date('Y-m-d'), ENT_QUOTES) ?>"/></td>
        </tr>
        <tr>
          <td class="td-no">4.</td><td class="td-lbl">Status Pengisian</td><td class="td-sep">:</td>
          <td class="td-val">
            <select class="kf-select" name="status">
              <option value="draft"   <?= ($kHeader['status'] ?? '')!=='selesai'?'selected':'' ?>>✏️ Draft (masih bisa diedit)</option>
              <option value="selesai" <?= ($kHeader['status'] ?? '')==='selesai'?'selected':'' ?>>✅ Selesai (siap tampil di website)</option>
            </select>
          </td>
        </tr>
      </table>
    </div>

    <!-- Tombol simpan -->
    <div style="text-align:center;margin:1.5rem 0 1rem;font-family:Arial,sans-serif;">
      <button type="button" class="btn kbtn" id="btnSimpan" onclick="simpanKuesioner()" style="padding:.6rem 2rem;font-size:1rem;">
        💾 Simpan Kuesioner <?= $tahunAktif ?>
      </button>
    </div>

    <!-- TANDA TANGAN -->
    <div class="kues-ttd">
      <div class="kues-ttd-box">
        <p>Mengetahui,</p>
        <p>Kepala Desa <?= e($desa['nama']) ?></p>
        <div class="kues-ttd-line"></div>
        <strong><?= e($desa['nama_kepala_desa'] ?? '( ................... )') ?></strong>
      </div>
      <div class="kues-ttd-box">
        <p><?= $kHeader ? htmlspecialchars($nilai['batas_utara']??'', ENT_QUOTES) : '&nbsp;' ?>,
           <?= date('d') ?> <?= ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][(int)date('m')] ?> <?= $tahunAktif ?></p>
        <p>Pengisi Kuesioner,</p>
        <div class="kues-ttd-line"></div>
        <strong id="ttdNamaPengisi"><?= htmlspecialchars($kHeader['pengisi_nama'] ?? '( ................... )', ENT_QUOTES) ?></strong>
      </div>
    </div>

    </form>
  </div><!-- .kues-paper -->
  </div><!-- .kues-wrap -->

</div><!-- #panel-kuesioner -->

<script>
// Update nama di tanda tangan saat diketik
document.querySelector('[name="pengisi_nama"]')?.addEventListener('input', function() {
  document.getElementById('ttdNamaPengisi').textContent = this.value || '( ................... )';
});

async function simpanKuesioner() {
  const btn = document.getElementById('btnSimpan');
  btn.disabled = true; btn.textContent = '⏳ Menyimpan...';

  const form = document.getElementById('formKuesioner');
  const fd   = new FormData(form);
  const payload = { nilai: {} };
  for (const [k, v] of fd.entries()) {
    if (k.startsWith('nilai[')) {
      payload.nilai[k.slice(6, -1)] = v;
    } else {
      payload[k] = v;
    }
  }

  try {
    const r = await fetch(BASE + '/api/kuesioner.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    showMsg('kuesioner', d.message, d.success);
    if (d.success && payload.status === 'selesai') {
      setTimeout(() => location.reload(), 1200);
    } else if (d.success) {
      // reload to refresh year tab list (new year creation)
      const currentTahun = payload.tahun;
      setTimeout(() => { window.location.href = '?ktahun=' + currentTahun; }, 900);
    }
  } catch(e) {
    showMsg('kuesioner', 'Gagal: ' + e.message, false);
  } finally {
    btn.disabled = false;
    btn.textContent = '💾 Simpan Kuesioner <?= $tahunAktif ?>';
  }
}

/**
 * FIX: bukaKuesionerBaru — langsung redirect ke URL baru dengan ktahun,
 * tanpa mencoba showPanel dulu (yang menyebabkan link hilang).
 * Panel 'kuesioner' akan aktif karena admin.php membaca ?ktahun= dan
 * menyimpan panel aktif via sessionStorage.
 */
function bukaKuesionerBaru() {
  const t = parseInt(document.getElementById('inputTahunBaru').value);
  if (!t || t < 2020 || t > 2099) { alert('Masukkan tahun yang valid (2020–2099).'); return; }

  // Cek apakah tahun sudah ada di daftar
  const existing = document.querySelectorAll('.kues-year-tab');
  for (const el of existing) {
    if (el.textContent.trim().startsWith(String(t))) {
      alert('Tahun ' + t + ' sudah ada. Klik tab tahun tersebut untuk membukanya.');
      return;
    }
  }

  // Simpan panel aktif di sessionStorage agar setelah redirect tetap di kuesioner
  try { sessionStorage.setItem('activePanel', 'kuesioner'); } catch(e) {}
  window.location.href = window.location.pathname + '?ktahun=' + t;
}

async function hapusKuesioner(tahun) {
  const r = await fetch(BASE + '/api/kuesioner.php?desa_id=<?= (int)$desa['id'] ?>&tahun=' + tahun,
    { method: 'DELETE', credentials: 'same-origin' });
  const d = await r.json();
  showMsg('kuesioner', d.message, d.success);
  if (d.success) setTimeout(() => location.href = '?ktahun=' + new Date().getFullYear(), 1000);
}
</script>
