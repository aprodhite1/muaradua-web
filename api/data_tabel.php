<?php
/**
 * api/data_tabel.php
 * GET    ?desa_id=X             — semua tabel milik desa
 * GET    ?id=X                  — satu tabel (untuk view)
 * POST   {desa_id,kategori,judul,headers[],rows[][]} — tambah
 * DELETE ?id=X                  — hapus
 */
require_once '../config/db.php';
require_once '../config/helpers.php';
header('Content-Type: application/json; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT id,desa_id,kategori,judul,headers,`rows`,created_at FROM data_tabel WHERE id=? LIMIT 1");
        $stmt->execute([(int)$_GET['id']]);
        $row = $stmt->fetch();
        if ($row) { $row['headers'] = json_decode($row['headers']); $row['rows'] = json_decode($row['rows']); }
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
    } else {
        $desa_id = (int)($_GET['desa_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT id,desa_id,kategori,judul,created_at FROM data_tabel WHERE desa_id=? ORDER BY kategori,created_at DESC");
        $stmt->execute([$desa_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    }
    exit;
}

if ($method === 'POST') {
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $desa_id = (int)($body['desa_id'] ?? 0);
    if (!isAdminLoggedIn($desa_id)) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }

    $kategori = trim($body['kategori'] ?? '');
    $judul    = trim($body['judul'] ?? '');
    $headers  = $body['headers'] ?? [];
    $rows     = $body['rows'] ?? [];

    if (!$kategori || !$judul || empty($headers)) {
        echo json_encode(['success'=>false,'message'=>'Kategori, judul, dan minimal 1 kolom wajib diisi']); exit;
    }

    $stmt = $pdo->prepare("INSERT INTO data_tabel (desa_id,kategori,judul,headers,`rows`) VALUES (?,?,?,?,?)");
    $stmt->execute([$desa_id, $kategori, $judul, json_encode($headers, JSON_UNESCAPED_UNICODE), json_encode($rows, JSON_UNESCAPED_UNICODE)]);
    echo json_encode(['success'=>true,'message'=>'Tabel berhasil disimpan','id'=>$pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT desa_id FROM data_tabel WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !isAdminLoggedIn((int)$row['desa_id'])) {
        echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
    }
    $pdo->prepare("DELETE FROM data_tabel WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Tabel berhasil dihapus']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan']);
