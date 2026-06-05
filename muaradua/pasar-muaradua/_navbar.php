<?php
/**
 * _navbar.php — Navbar terpusat untuk semua halaman publik desa.
 * Variabel yang dibutuhkan: $desa (array), $currentPage (string: 'beranda'|'data'|'infografis'|'kuesioner')
 * Di-include setelah $desa tersedia.
 */
$_navPage = $currentPage ?? '';
?>
<header class="village-header">
  <div class="container">
    <a href="index.php" class="village-header-brand">
      <div class="village-header-logo"><?= e($desa['emoji']) ?></div>
      <div>
        <div class="village-header-title">Desa <?= e($desa['nama']) ?></div>
        <div class="village-header-sub">Kecamatan Muaradua, OKU Selatan</div>
      </div>
    </a>
    <button class="hamburger" id="hamburgerBtn">☰</button>
    <nav class="village-nav" id="villageNav">
      <a href="index.php"    <?= $_navPage==='beranda'    ?'class="active"':'' ?>>🏠 Beranda</a>
      <a href="data.php"     <?= $_navPage==='data'       ?'class="active"':'' ?>>📊 Data Desa</a>
      <a href="infografis.php" <?= $_navPage==='infografis'?'class="active"':'' ?>>📈 Infografis</a>
      <a href="kuesioner.php" <?= $_navPage==='kuesioner'  ?'class="active"':'' ?>>📋 Kuesioner Desa</a>
      <a href="login.php" class="btn-login<?= $_navPage==='admin'?' active':'' ?>">🔐 Admin</a>
    </nav>
  </div>
</header>
<script>
// Hamburger toggle — bisa dipanggil berkali-kali tanpa konflik
(function(){
  var btn = document.getElementById('hamburgerBtn');
  var nav = document.getElementById('villageNav');
  if (btn && nav) {
    btn.addEventListener('click', function(){ nav.classList.toggle('open'); });
  }
})();
</script>
