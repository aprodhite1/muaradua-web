<?php
/**
 * api/auth.php
 * Handles login & logout admin desa
 * POST { username, password, desa_slug } → JSON response
 * GET  ?action=logout                    → redirect to login
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// --- LOGOUT ---
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    $desa_slug = $_GET['desa'] ?? '';
    $_SESSION = [];
    session_destroy();
    header('Location: ../muaradua/' . $desa_slug . '/login.php');
    exit;
}

// --- LOGIN ---
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST; // fallback form POST
    }

    $username  = trim($data['username'] ?? '');
    $password  = $data['password'] ?? '';
    $desa_slug = trim($data['desa_slug'] ?? '');

    if (empty($username) || empty($password) || empty($desa_slug)) {
        echo json_encode(['success' => false, 'message' => 'Username, password, dan desa wajib diisi.']);
        exit;
    }

    // Cari admin berdasarkan username dan pastikan desa_slug cocok
    $stmt = $pdo->prepare("
        SELECT a.*, d.slug AS desa_slug, d.nama AS desa_nama, d.id AS desa_id
        FROM admin_desa a
        JOIN desa d ON a.desa_id = d.id
        WHERE a.username = ? AND d.slug = ?
        LIMIT 1
    ");
    $stmt->execute([$username, $desa_slug]);
    $admin = $stmt->fetch();

    if (!$admin) {
        echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan untuk desa ini.']);
        exit;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Password salah. Silakan coba lagi.']);
        exit;
    }

    // Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id']        = $admin['id'];
    $_SESSION['admin_username']  = $admin['username'];
    $_SESSION['admin_nama']      = $admin['nama_lengkap'];
    $_SESSION['admin_desa_id']   = $admin['desa_id'];
    $_SESSION['admin_desa_slug'] = $admin['desa_slug'];
    $_SESSION['admin_desa_nama'] = $admin['desa_nama'];

    echo json_encode([
        'success'    => true,
        'message'    => 'Login berhasil!',
        'nama'       => $admin['nama_lengkap'],
        'desa_nama'  => $admin['desa_nama'],
        'desa_slug'  => $admin['desa_slug'],
        'redirect'   => '/muaradua-web/muaradua/' . $admin['desa_slug'] . '/admin.php'
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method tidak didukung.']);
