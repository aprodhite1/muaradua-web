// ============================================
// MAIN JS - WEBSITE KECAMATAN MUARADUA
// ============================================

// --- MOCK DATA: Daftar Desa ---
const villages = [
  { id: 'muara-dua',       name: 'Muara Dua',       kepala: 'Bapak Syahril',      penduduk: '3.245', luas: '12.5 km²', emoji: '🏘️', color: '#1D4E89' },
  { id: 'gunung-batu',     name: 'Gunung Batu',     kepala: 'Bapak Ruslan',       penduduk: '2.180', luas: '8.3 km²',  emoji: '⛰️', color: '#2E6DB4' },
  { id: 'batu-belang',     name: 'Batu Belang',     kepala: 'Bapak Armansyah',    penduduk: '1.850', luas: '6.7 km²',  emoji: '🌄', color: '#163A66' },
  { id: 'tanjung-jaya',    name: 'Tanjung Jaya',    kepala: 'Bapak Irfan',        penduduk: '2.560', luas: '9.1 km²',  emoji: '🌊', color: '#79B443' },
  { id: 'sukarami',        name: 'Sukarami',        kepala: 'Bapak Junaidi',      penduduk: '1.620', luas: '5.4 km²',  emoji: '🌾', color: '#E8820C' },
  { id: 'sinar-harapan',   name: 'Sinar Harapan',   kepala: 'Bapak Rahmat',       penduduk: '2.090', luas: '7.8 km²',  emoji: '🌟', color: '#5E8E33' },
  { id: 'pasar-muaradua',  name: 'Pasar Muaradua',  kepala: 'Bapak Hermansyah',   penduduk: '4.120', luas: '4.2 km²',  emoji: '🏪', color: '#C96A00' },
  { id: 'padang-bindu',    name: 'Padang Bindu',    kepala: 'Bapak Muslidin',     penduduk: '1.480', luas: '10.2 km²', emoji: '🌿', color: '#1D4E89' },
];

// --- MOCK AUTH DATA (username: password per desa) ---
const adminCredentials = {
  'muara-dua':      { user: 'admin.muaradua',      pass: 'muaradua123',      name: 'Admin Desa Muara Dua' },
  'gunung-batu':    { user: 'admin.gunungbatu',    pass: 'gunungbatu123',    name: 'Admin Desa Gunung Batu' },
  'batu-belang':    { user: 'admin.batabelang',    pass: 'batabelang123',    name: 'Admin Desa Batu Belang' },
  'tanjung-jaya':   { user: 'admin.tanjungjaya',   pass: 'tanjungjaya123',   name: 'Admin Desa Tanjung Jaya' },
  'sukarami':       { user: 'admin.sukarami',      pass: 'sukarami123',      name: 'Admin Desa Sukarami' },
  'sinar-harapan':  { user: 'admin.sinarharapan',  pass: 'sinarharapan123',  name: 'Admin Desa Sinar Harapan' },
  'pasar-muaradua': { user: 'admin.pasarmuaradua', pass: 'pasarmuaradua123', name: 'Admin Pasar Muaradua' },
  'padang-bindu':   { user: 'admin.padangbindu',   pass: 'padangbindu123',   name: 'Admin Desa Padang Bindu' },
};

// --- UTILS ---
function getVillageIdFromPath() {
  const parts = window.location.pathname.split('/');
  const idx = parts.indexOf('muaradua');
  return idx !== -1 ? parts[idx + 1] : null;
}

function getVillageData(id) {
  return villages.find(v => v.id === id);
}

function setActive(navLinks) {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  navLinks.forEach(link => {
    const href = link.getAttribute('href') || '';
    if (href.endsWith(currentPage) || (currentPage === '' && href.endsWith('index.html'))) {
      link.classList.add('active');
    }
  });
}

// --- HAMBURGER MENU ---
function initMobileMenu() {
  const hamburger = document.querySelector('.hamburger');
  const nav = document.querySelector('.village-nav');
  if (!hamburger || !nav) return;
  hamburger.addEventListener('click', () => {
    nav.classList.toggle('open');
    hamburger.textContent = nav.classList.contains('open') ? '✕' : '☰';
  });
}

// --- LOGIN FORM ---
function initLoginForm() {
  const form = document.getElementById('loginForm');
  if (!form) return;

  const villageId = getVillageIdFromPath();
  const alertEl = document.getElementById('loginAlert');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const creds = adminCredentials[villageId];

    if (creds && username === creds.user && password === creds.pass) {
      // Simpan session di localStorage
      localStorage.setItem(`admin_${villageId}`, JSON.stringify({ logged: true, name: creds.name, time: Date.now() }));
      showAlert(alertEl, 'success', '✅ Login berhasil! Mengalihkan ke Dashboard...');
      setTimeout(() => { window.location.href = 'admin.html'; }, 1200);
    } else {
      showAlert(alertEl, 'error', '❌ Username atau password salah. Silakan coba lagi.');
    }
  });
}

// --- PROTECT ADMIN PAGE ---
function protectAdminPage() {
  const body = document.body;
  if (!body || !body.classList.contains('admin-page')) return;
  const villageId = getVillageIdFromPath();
  const session = JSON.parse(localStorage.getItem(`admin_${villageId}`) || '{}');

  if (!session.logged) {
    window.location.href = 'login.html';
    return;
  }

  // Set admin name
  const nameEl = document.getElementById('adminName');
  if (nameEl) nameEl.textContent = session.name || 'Admin';
  const avatarEl = document.getElementById('adminAvatar');
  if (avatarEl) avatarEl.textContent = (session.name || 'A').charAt(6).toUpperCase() || 'A';
}

// --- LOGOUT ---
function initLogout() {
  const logoutBtn = document.getElementById('logoutBtn');
  if (!logoutBtn) return;
  logoutBtn.addEventListener('click', function(e) {
    e.preventDefault();
    const villageId = getVillageIdFromPath();
    localStorage.removeItem(`admin_${villageId}`);
    window.location.href = 'login.html';
  });
}

// --- SHOW ALERT ---
function showAlert(el, type, msg) {
  if (!el) return;
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  el.style.display = 'flex';
}

// --- SIDEBAR ACTIVE LINK ---
function initSidebarActive() {
  const links = document.querySelectorAll('.sidebar-menu a, .village-nav a');
  setActive(links);
}

// --- ADMIN CRUD: Simpan data ke LocalStorage ---
function saveVillageData(villageId, key, data) {
  localStorage.setItem(`data_${villageId}_${key}`, JSON.stringify(data));
}

function loadVillageData(villageId, key, defaultVal) {
  const raw = localStorage.getItem(`data_${villageId}_${key}`);
  return raw ? JSON.parse(raw) : defaultVal;
}

// --- POPULATION TABLE (Data Desa) ---
const defaultPendudukData = [
  { no: 1, rt: 'RT 01', rw: 'RW 01', laki: 210, perempuan: 198, total: 408 },
  { no: 2, rt: 'RT 02', rw: 'RW 01', laki: 185, perempuan: 172, total: 357 },
  { no: 3, rt: 'RT 03', rw: 'RW 02', laki: 230, perempuan: 220, total: 450 },
  { no: 4, rt: 'RT 04', rw: 'RW 02', laki: 165, perempuan: 159, total: 324 },
  { no: 5, rt: 'RT 05', rw: 'RW 03', laki: 192, perempuan: 188, total: 380 },
];

function renderPendudukTable(villageId) {
  const tbody = document.getElementById('pendudukTableBody');
  if (!tbody) return;
  const data = loadVillageData(villageId, 'penduduk', defaultPendudukData);
  tbody.innerHTML = data.map(row => `
    <tr>
      <td>${row.no}</td>
      <td>${row.rt}</td>
      <td>${row.rw}</td>
      <td>${row.laki.toLocaleString('id-ID')}</td>
      <td>${row.perempuan.toLocaleString('id-ID')}</td>
      <td><strong>${row.total.toLocaleString('id-ID')}</strong></td>
    </tr>
  `).join('');
}

// --- ADMIN DATA TABLE MANAGER ---
function initAdminDataManager() {
  const villageId = getVillageIdFromPath();
  if (!villageId) return;
  const form = document.getElementById('addPendudukForm');
  if (!form) return;

  let data = loadVillageData(villageId, 'penduduk', defaultPendudukData);
  renderAdminTable(data, villageId);

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const rt = document.getElementById('inputRT').value;
    const rw = document.getElementById('inputRW').value;
    const laki = parseInt(document.getElementById('inputLaki').value);
    const perempuan = parseInt(document.getElementById('inputPerempuan').value);
    data.push({ no: data.length + 1, rt, rw, laki, perempuan, total: laki + perempuan });
    saveVillageData(villageId, 'penduduk', data);
    renderAdminTable(data, villageId);
    form.reset();
    showAlert(document.getElementById('dataAlert'), 'success', '✅ Data berhasil ditambahkan!');
  });
}

function renderAdminTable(data, villageId) {
  const tbody = document.getElementById('adminPendudukBody');
  if (!tbody) return;
  tbody.innerHTML = data.map((row, i) => `
    <tr>
      <td>${row.no}</td>
      <td>${row.rt}</td>
      <td>${row.rw}</td>
      <td>${row.laki.toLocaleString('id-ID')}</td>
      <td>${row.perempuan.toLocaleString('id-ID')}</td>
      <td><strong>${row.total.toLocaleString('id-ID')}</strong></td>
      <td>
        <button class="btn btn-primary" style="padding:0.3rem 0.75rem;font-size:0.78rem;" onclick="deleteRow(${i})">Hapus</button>
      </td>
    </tr>
  `).join('');
}

function deleteRow(index) {
  const villageId = getVillageIdFromPath();
  let data = loadVillageData(villageId, 'penduduk', defaultPendudukData);
  data.splice(index, 1);
  data = data.map((r, i) => ({ ...r, no: i + 1 }));
  saveVillageData(villageId, 'penduduk', data);
  renderAdminTable(data, villageId);
  showAlert(document.getElementById('dataAlert'), 'success', '✅ Data berhasil dihapus.');
}

// --- INFOGRAFIS ADMIN MANAGER ---
const defaultInfografis = [
  { id: 1, judul: 'Komposisi Penduduk', kategori: 'Kependudukan', emoji: '👥', desc: 'Sebaran penduduk berdasarkan usia dan jenis kelamin.', color: '#EEF3FA' },
  { id: 2, judul: 'Tingkat Pendidikan', kategori: 'Pendidikan',   emoji: '📚', desc: 'Profil tingkat pendidikan warga desa.',              color: '#FFF8EE' },
  { id: 3, judul: 'Mata Pencaharian',   kategori: 'Ekonomi',      emoji: '💼', desc: 'Distribusi pekerjaan utama masyarakat desa.',        color: '#F0FFF4' },
  { id: 4, judul: 'Kesehatan Warga',    kategori: 'Kesehatan',    emoji: '🏥', desc: 'Data aksesibilitas layanan kesehatan.',              color: '#FFF0F0' },
];

function renderInfografis(villageId) {
  const grid = document.getElementById('infografisGrid');
  if (!grid) return;
  const data = loadVillageData(villageId, 'infografis', defaultInfografis);
  grid.innerHTML = data.map(item => `
    <div class="infografis-card animate-fade-in-up">
      <div class="infografis-thumb" style="background:${item.color}">${item.emoji}</div>
      <div class="infografis-caption">
        <span class="badge badge-primary mb-1">${item.kategori}</span>
        <h4>${item.judul}</h4>
        <p>${item.desc}</p>
      </div>
    </div>
  `).join('');
}

function initAdminInfografis() {
  const villageId = getVillageIdFromPath();
  if (!villageId) return;
  const form = document.getElementById('addInfografisForm');
  if (!form) return;

  let data = loadVillageData(villageId, 'infografis', defaultInfografis);
  renderAdminInfografisTable(data);

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const judul = document.getElementById('infJudul').value;
    const kategori = document.getElementById('infKategori').value;
    const emoji = document.getElementById('infEmoji').value || '📊';
    const desc = document.getElementById('infDesc').value;
    data.push({ id: Date.now(), judul, kategori, emoji, desc, color: '#F4F7FB' });
    saveVillageData(villageId, 'infografis', data);
    renderAdminInfografisTable(data);
    form.reset();
    showAlert(document.getElementById('infAlert'), 'success', '✅ Infografis berhasil ditambahkan!');
  });
}

function renderAdminInfografisTable(data) {
  const tbody = document.getElementById('adminInfografisBody');
  if (!tbody) return;
  tbody.innerHTML = data.map((item, i) => `
    <tr>
      <td>${item.emoji}</td>
      <td>${item.judul}</td>
      <td><span class="badge badge-primary">${item.kategori}</span></td>
      <td style="max-width:200px;font-size:0.8rem">${item.desc}</td>
      <td>
        <button class="btn btn-primary" style="padding:0.3rem 0.75rem;font-size:0.78rem;" onclick="deleteInfografis(${i})">Hapus</button>
      </td>
    </tr>
  `).join('');
}

function deleteInfografis(index) {
  const villageId = getVillageIdFromPath();
  let data = loadVillageData(villageId, 'infografis', defaultInfografis);
  data.splice(index, 1);
  saveVillageData(villageId, 'infografis', data);
  renderAdminInfografisTable(data);
  showAlert(document.getElementById('infAlert'), 'success', '✅ Infografis berhasil dihapus.');
}

// --- INIT ALL ---
document.addEventListener('DOMContentLoaded', function () {
  initMobileMenu();
  initSidebarActive();
  protectAdminPage();
  initLoginForm();
  initLogout();
  const villageId = getVillageIdFromPath();
  if (villageId) {
    renderPendudukTable(villageId);
    renderInfografis(villageId);
    initAdminDataManager();
    initAdminInfografis();
  }
});
