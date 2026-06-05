<?php
/**
 * api/infografis.php
 * GET    ?desa_id=X  → daftar infografis publik
 * POST   { desa_id, judul, kategori, emoji, deskripsi } → tambah (admin)
 * DELETE ?id=X       → hapus (admin)
 */
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

function requireAdmin() {
    if (empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Login admin diperlukan.']);
        exit;
    }
}

// GET
if ($method === 'GET') {
    $desa_id = intval($_GET['desa_id'] ?? 0);
    if (!$desa_id) { echo json_encode(['success'=>false,'message'=>'desa_id wajib.']); exit; }
    $stmt = $pdo->prepare("SELECT * FROM infografis WHERE desa_id = ? ORDER BY created_at DESC");
    $stmt->execute([$desa_id]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

// POST
if ($method === 'POST') {
    requireAdmin();
    $data      = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $desa_id   = intval($_SESSION['admin_desa_id']);
    $judul     = trim($data['judul']     ?? '');
    $kategori  = trim($data['kategori']  ?? '');
    $emoji     = trim($data['emoji']     ?? '📊');
    $deskripsi = trim($data['deskripsi'] ?? '');
    $warna_bg  = trim($data['warna_bg']  ?? '#EEF3FA');

    if (!$judul || !$kategori) { echo json_encode(['success'=>false,'message'=>'Judul dan kategori wajib.']); exit; }

    $stmt = $pdo->prepare("INSERT INTO infografis (desa_id, judul, kategori, emoji, deskripsi, warna_bg) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$desa_id, $judul, $kategori, $emoji, $deskripsi, $warna_bg]);
    echo json_encode(['success' => true, 'message' => 'Infografis ditambahkan.', 'id' => $pdo->lastInsertId()]);
    exit;
}

// DELETE
if ($method === 'DELETE') {
    requireAdmin();
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT desa_id FROM infografis WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || intval($row['desa_id']) !== intval($_SESSION['admin_desa_id'])) {
        http_response_code(403); echo json_encode(['success'=>false,'message'=>'Akses ditolak.']); exit;
    }
    $pdo->prepare("DELETE FROM infografis WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Infografis dihapus.']);
    exit;
}
echo json_encode(['success'=>false,'message'=>'Method tidak didukung.']);
