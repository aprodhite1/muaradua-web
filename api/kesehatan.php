<?php
/**
 * api/kesehatan.php
 * GET ?desa_id=X   — Ambil data kesehatan
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
    $stmt = $pdo->prepare("SELECT * FROM kesehatan WHERE desa_id=?");
    $stmt->execute([$desa_id]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $desa_id    = (int)($body['desa_id'] ?? 0);
    $fasilitas  = trim($body['fasilitas'] ?? '');
    $jumlah     = trim($body['jumlah'] ?? '');
    $keterangan = trim($body['keterangan'] ?? '');

    if (!isAdminLoggedIn($desa_id)) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
    if (!$fasilitas || !$jumlah) { echo json_encode(['success'=>false,'message'=>'Fasilitas dan jumlah harus diisi']); exit; }

    $stmt = $pdo->prepare("INSERT INTO kesehatan (desa_id,fasilitas,jumlah,keterangan) VALUES (?,?,?,?)");
    $stmt->execute([$desa_id, $fasilitas, $jumlah, $keterangan]);
    echo json_encode(['success'=>true,'message'=>'Data kesehatan berhasil ditambahkan','id'=>$pdo->lastInsertId()]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare("SELECT desa_id FROM kesehatan WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !isAdminLoggedIn((int)$row['desa_id'])) {
        echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
    }
    $pdo->prepare("DELETE FROM kesehatan WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Data berhasil dihapus']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Method tidak diizinkan']);
