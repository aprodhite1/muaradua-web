<?php
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$slug = getSlugFromPath();
$desa = getDesaBySlug($pdo, $slug);
if (!$desa) { http_response_code(404); die('<h2>Desa tidak ditemukan.</h2>'); }

// Jika sudah login, redirect ke admin
if (isAdminLoggedIn((int)$desa['id'])) {
    header('Location: admin.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT a.*, d.slug AS desa_slug, d.nama AS desa_nama, d.id AS desa_id
        FROM admin_desa a JOIN desa d ON a.desa_id = d.id
        WHERE a.username = ? AND d.slug = ? LIMIT 1
    ");
    $stmt->execute([$username, $slug]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        $error = 'Username atau password salah. Silakan coba lagi.';
    } else {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        $_SESSION['admin_username']  = $admin['username'];
        $_SESSION['admin_nama']      = $admin['nama_lengkap'];
        $_SESSION['admin_desa_id']   = $admin['desa_id'];
        $_SESSION['admin_desa_slug'] = $admin['desa_slug'];
        $_SESSION['admin_desa_nama'] = $admin['desa_nama'];
        header('Location: admin.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Login Admin – Desa <?= e($desa['nama']) ?></title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <style>
    body{background:<?= e($desa['color_gradient']) ?>;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;}
    .login-back{color:rgba(255,255,255,.8);text-decoration:none;font-size:.875rem;margin-bottom:1.5rem;align-self:flex-start;margin-left:auto;margin-right:auto;max-width:440px;width:100%;}
    .login-back:hover{color:#fff;}
    .login-card{background:var(--white);border-radius:var(--radius-xl);padding:2.5rem 2.5rem 2rem;box-shadow:0 20px 60px rgba(0,0,0,.25);max-width:440px;width:100%;}
    .login-icon{width:72px;height:72px;border-radius:var(--radius-lg);background:<?= e($desa['color_gradient']) ?>;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.25rem;}
    .login-card h2{text-align:center;margin-bottom:.25rem;}
    .login-card p{text-align:center;color:var(--text-light);font-size:.875rem;margin-bottom:1.75rem;}
    .error-box{background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C;padding:.85rem 1rem;border-radius:var(--radius-sm);font-size:.875rem;margin-bottom:1.25rem;}
    .hint-box{background:var(--bg-section);border-radius:var(--radius-sm);padding:1rem;font-size:.82rem;margin-top:1.25rem;border:1px solid var(--border);}
    .hint-box code{background:var(--white);padding:.1em .4em;border-radius:4px;font-size:.85em;border:1px solid var(--border);}
    .footer-login{color:rgba(255,255,255,.6);font-size:.78rem;margin-top:1.5rem;text-align:center;}
  </style>
</head>
<body>
  <a href="index.php" class="login-back">← Kembali ke Beranda Desa</a>
  <div class="login-card">
    <div class="login-icon">🔐</div>
    <h2>Login Admin Desa</h2>
    <p>Portal pengelolaan data <strong>Desa <?= e($desa['nama']) ?></strong></p>

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="username">👤 Username Admin</label>
        <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username" value="<?= e($_POST['username']??'') ?>" required autocomplete="username"/>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">🔑 Password</label>
        <div style="position:relative;">
          <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password" required autocomplete="current-password" style="padding-right:3rem;"/>
          <button type="button" onclick="togglePw()" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1.1rem;" title="Tampilkan password">👁️</button>
        </div>
      </div>
      <button type="submit" class="btn" style="width:100%;justify-content:center;font-size:1rem;padding:.85rem;background:<?= e($desa['color_gradient']) ?>;color:#fff;margin-top:.5rem;">🚀 Masuk ke Dashboard Admin</button>
    </form>

    <div class="hint-box">
      <strong>🔑 Akun Demo:</strong><br/>
      Username: <code>admin.<?= e(str_replace('-','',$slug)) ?></code><br/>
      Password: <code><?= e(str_replace('-','',$slug)) ?>123</code><br/>
      <em style="color:var(--text-light);">Setiap desa memiliki akun yang berbeda.</em>
    </div>
  </div>
  <div class="footer-login">&copy; <?= date('Y') ?> Kecamatan Muaradua · OKU Selatan</div>

  <script>function togglePw(){const i=document.getElementById('password');i.type=i.type==='password'?'text':'password';}</script>
</body>
</html>
