<?php
/**
 * api/pendidikan.php
 * GET ?desa_id=X   — Ambil data pendidikan
 * POST             — Tambah data (admin only)
 * DELETE ?id=X     — Hapus data (admin only)
 */
require_once '../config/db.php';
require_once '../config/helpers.php';

header('Content-Type: application/json; charset=UTF-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $desa_id = (int)($_GET['desa_id'] ?? 0);
    if (!$desa_id) { echo json_encode(['success'=>false,'message'=>'desa_id diperlukan']); exit; }
    $stmt = $pdo->prepare("SELECT * FROM pendidikan WHERE desa_id=? ORDER BY urutan");
    $stmt->execute([$desa_id]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $desa_id    = (int)($body['desa_id'] ?? 0);
    $tingkat    = trim($body['tingkat'] ?? '');
    $laki_laki  = (int)($body['laki_laki'] ?? 0);
    $perempuan  = (int)($body['perempuan'] ?? 0);
    $urutan     = (int)($body['urutan'] ?? 99);

    if (!isAdminLoggedIn($desa_id)) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
    if (!$tingkat) { echo json_encode(['success'=>false,'message'=>'Tingkat pendidikan harus diisi']); exit; }

    $stmt = $pdo->prepare("INSERT INTO pendidikan (desa_id,tingkat,laki_laki,perempuan,urutan) VALUES (?,?,?,?,?)");
    $stmt->execute([$desa_id, $tingkat, $laki_laki, $perempuan, $urutan]);
    echo json_encode(['success'=>true,'message'=>'Data pendidikan berhasil ditambahkan','id'=>$pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID diperlukan']); exit; }
    // Cek kepemilikan
    $stmt = $pdo->prepare("SELECT desa_id FROM pendidikan WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !isAdminLoggedIn((int)$row['desa_id'])) {
        echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
    }
    $pdo->prepare("DELETE FROM pendidikan WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Data berhasil dihapus']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan']);
