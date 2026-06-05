<?php
/**
 * api/pengumuman.php
 * GET    ?desa_id=X  → daftar pengumuman aktif
 * POST   { judul, isi, tanggal } → tambah (admin)
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
    $stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE desa_id = ? AND aktif = 1 ORDER BY tanggal DESC");
    $stmt->execute([$desa_id]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

// POST
if ($method === 'POST') {
    requireAdmin();
    $data    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $desa_id = intval($_SESSION['admin_desa_id']);
    $judul   = trim($data['judul']   ?? '');
    $isi     = trim($data['isi']     ?? '');
    $tanggal = trim($data['tanggal'] ?? date('Y-m-d'));

    if (!$judul) { echo json_encode(['success'=>false,'message'=>'Judul wajib diisi.']); exit; }

    $stmt = $pdo->prepare("INSERT INTO pengumuman (desa_id, judul, isi, tanggal, aktif) VALUES (?,?,?,?,1)");
    $stmt->execute([$desa_id, $judul, $isi, $tanggal]);
    echo json_encode(['success' => true, 'message' => 'Pengumuman ditambahkan.', 'id' => $pdo->lastInsertId()]);
    exit;
}

// DELETE
if ($method === 'DELETE') {
    requireAdmin();
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT desa_id FROM pengumuman WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || intval($row['desa_id']) !== intval($_SESSION['admin_desa_id'])) {
        http_response_code(403); echo json_encode(['success'=>false,'message'=>'Akses ditolak.']); exit;
    }
    $pdo->prepare("UPDATE pengumuman SET aktif = 0 WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Pengumuman dihapus.']);
    exit;
}
echo json_encode(['success'=>false,'message'=>'Method tidak didukung.']);
