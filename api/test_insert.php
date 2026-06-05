<?php
/**
 * Test insert data_tabel langsung via PHP (bypass JS form)
 * http://localhost/muaradua-web/api/test_insert.php
 */
require_once '../config/db.php';
require_once '../config/helpers.php';

header('Content-Type: application/json; charset=UTF-8');

// Simulasi session login (untuk test)
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_desa_id']   = 1;

$desa_id  = 1;
$kategori = 'Pendidikan';
$judul    = 'Data Tingkat Pendidikan Warga (Test)';
$headers  = ['Tingkat Pendidikan', 'Laki-laki', 'Perempuan', 'Total'];
$rows     = [
    ['Tidak/Belum Sekolah', '85', '91', '176'],
    ['Tamat SD/Sederajat',  '320', '298', '618'],
    ['SMP/Sederajat',       '210', '195', '405'],
    ['SMA/SMK/Sederajat',   '185', '176', '361'],
    ['Diploma/Sarjana',     '45', '52', '97'],
];

try {
    $stmt = $pdo->prepare("INSERT INTO data_tabel (desa_id,kategori,judul,headers,rows) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $desa_id, $kategori, $judul,
        json_encode($headers, JSON_UNESCAPED_UNICODE),
        json_encode($rows, JSON_UNESCAPED_UNICODE)
    ]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success'=>true,'message'=>"Data berhasil diinsert! ID=$id",'id'=>$id]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
