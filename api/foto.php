<?php
/**
 * api/foto.php
 * CRUD foto desa — hanya admin yang sedang login bisa upload/hapus
 */
require_once '../config/db.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// ---------- GET: ambil daftar foto ----------
if ($method === 'GET') {
    $desa_id = (int)($_GET['desa_id'] ?? 0);
    if (!$desa_id) { echo json_encode(['success'=>false,'message'=>'desa_id diperlukan']); exit; }
    $stmt = $pdo->prepare("SELECT id, judul, filename, urutan, created_at FROM foto_desa WHERE desa_id=? ORDER BY urutan ASC, id ASC");
    $stmt->execute([$desa_id]);
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    exit;
}

// ---------- POST: upload foto ----------
if ($method === 'POST') {
    $desa_id = (int)($_POST['desa_id'] ?? 0);
    $judul   = trim($_POST['judul'] ?? '');

    if (!isAdminLoggedIn($desa_id)) {
        echo json_encode(['success'=>false,'message'=>'Tidak terautentikasi']); exit;
    }
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success'=>false,'message'=>'Upload file gagal']); exit;
    }

    $file    = $_FILES['foto'];
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    $mime    = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        echo json_encode(['success'=>false,'message'=>'Format file tidak didukung (jpg/png/webp/gif)']); exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 5MB']); exit;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'desa_' . $desa_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    $destDir  = dirname(__DIR__) . '/uploads/foto/';
    $destPath = $destDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file']); exit;
    }

    // Urutan: ambil max+1
    $maxUrutan = $pdo->prepare("SELECT IFNULL(MAX(urutan),0)+1 FROM foto_desa WHERE desa_id=?");
    $maxUrutan->execute([$desa_id]);
    $urutan = (int)$maxUrutan->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO foto_desa (desa_id, judul, filename, urutan) VALUES (?,?,?,?)");
    $stmt->execute([$desa_id, $judul, $filename, $urutan]);
    $newId = $pdo->lastInsertId();

    echo json_encode(['success'=>true,'message'=>'Foto berhasil diupload','id'=>$newId,'filename'=>$filename]);
    exit;
}

// ---------- DELETE: hapus foto ----------
if ($method === 'DELETE') {
    $id      = (int)($_GET['id'] ?? 0);
    $desa_id = (int)($_GET['desa_id'] ?? 0);

    if (!isAdminLoggedIn($desa_id)) {
        echo json_encode(['success'=>false,'message'=>'Tidak terautentikasi']); exit;
    }

    $stmt = $pdo->prepare("SELECT filename, desa_id FROM foto_desa WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || (int)$row['desa_id'] !== $desa_id) {
        echo json_encode(['success'=>false,'message'=>'Foto tidak ditemukan']); exit;
    }

    // Hapus file fisik
    $filePath = dirname(__DIR__) . '/uploads/foto/' . $row['filename'];
    if (file_exists($filePath)) { @unlink($filePath); }

    $pdo->prepare("DELETE FROM foto_desa WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Foto berhasil dihapus']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Method tidak didukung']);
