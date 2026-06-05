<?php
/**
 * api/ekonomi.php
 * GET ?desa_id=X   — Ambil data ekonomi
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
    $stmt = $pdo->prepare("SELECT * FROM ekonomi WHERE desa_id=? ORDER BY jumlah DESC");
    $stmt->execute([$desa_id]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $desa_id         = (int)($body['desa_id'] ?? 0);
    $mata_pencaharian = trim($body['mata_pencaharian'] ?? '');
    $jumlah          = (int)($body['jumlah'] ?? 0);
    $persentase      = (float)($body['persentase'] ?? 0);
    $emoji           = trim($body['emoji'] ?? '💼');

    if (!isAdminLoggedIn($desa_id)) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
    if (!$mata_pencaharian) { echo json_encode(['success'=>false,'message'=>'Mata pencaharian harus diisi']); exit; }

    $stmt = $pdo->prepare("INSERT INTO ekonomi (desa_id,mata_pencaharian,jumlah,persentase,emoji) VALUES (?,?,?,?,?)");
    $stmt->execute([$desa_id, $mata_pencaharian, $jumlah, $persentase, $emoji]);
    echo json_encode(['success'=>true,'message'=>'Data ekonomi berhasil ditambahkan','id'=>$pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare("SELECT desa_id FROM ekonomi WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !isAdminLoggedIn((int)$row['desa_id'])) {
        echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
    }
    $pdo->prepare("DELETE FROM ekonomi WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Data berhasil dihapus']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan']);
