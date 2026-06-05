<?php
/**
 * api/penduduk.php
 * GET    ?desa_id=X  → ambil data penduduk
 * POST   { desa_id, rt, rw, laki_laki, perempuan } → tambah
 * DELETE ?id=X       → hapus baris
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// --- GET ---
if ($method === 'GET') {
    $desa_id = intval($_GET['desa_id'] ?? 0);
    if (!$desa_id) {
        echo json_encode(['success' => false, 'message' => 'desa_id wajib diisi.']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM penduduk WHERE desa_id = ? ORDER BY rw, rt");
    $stmt->execute([$desa_id]);
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// --- AUTH CHECK (POST/DELETE hanya untuk admin) ---
function requireAdmin() {
    if (empty($_SESSION['admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Anda harus login sebagai admin.']);
        exit;
    }
}

// --- POST ---
if ($method === 'POST') {
    requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $desa_id   = intval($data['desa_id']   ?? $_SESSION['admin_desa_id'] ?? 0);
    $rt        = trim($data['rt']        ?? '');
    $rw        = trim($data['rw']        ?? '');
    $laki      = intval($data['laki_laki']  ?? 0);
    $perempuan = intval($data['perempuan']   ?? 0);

    // Validasi desa_id sesuai session admin
    if ($desa_id !== intval($_SESSION['admin_desa_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak: bukan desa Anda.']);
        exit;
    }
    if (!$rt || !$rw || $laki < 0 || $perempuan < 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap atau tidak valid.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO penduduk (desa_id, rt, rw, laki_laki, perempuan) VALUES (?,?,?,?,?)");
    $stmt->execute([$desa_id, $rt, $rw, $laki, $perempuan]);
    $newId = $pdo->lastInsertId();

    // Perbarui total di tabel desa
    $pdo->prepare("UPDATE desa SET penduduk_total = (SELECT IFNULL(SUM(total),0) FROM penduduk WHERE desa_id = ?), jumlah_kk = FLOOR(penduduk_total/3.6) WHERE id = ?")->execute([$desa_id, $desa_id]);

    echo json_encode(['success' => true, 'message' => 'Data penduduk berhasil ditambahkan.', 'id' => $newId]);
    exit;
}

// --- DELETE ---
if ($method === 'DELETE') {
    requireAdmin();
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
        exit;
    }
    // Pastikan baris milik desa admin
    $stmt = $pdo->prepare("SELECT desa_id FROM penduduk WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || intval($row['desa_id']) !== intval($_SESSION['admin_desa_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
        exit;
    }
    $pdo->prepare("DELETE FROM penduduk WHERE id = ?")->execute([$id]);

    // Update total penduduk desa
    $desa_id = $_SESSION['admin_desa_id'];
    $pdo->prepare("UPDATE desa SET penduduk_total = (SELECT IFNULL(SUM(total),0) FROM penduduk WHERE desa_id = ?) WHERE id = ?")->execute([$desa_id, $desa_id]);

    echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method tidak didukung.']);
