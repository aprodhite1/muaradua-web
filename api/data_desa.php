<?php
/**
 * api/data_desa.php
 * GET ?desa_id=X&tabel=pendidikan|ekonomi|kesehatan|infrastruktur|pertanian|sosial
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$desa_id = intval($_GET['desa_id'] ?? 0);
$tabel   = $_GET['tabel'] ?? '';

$allowed = ['pendidikan','ekonomi','kesehatan','infrastruktur','pertanian','sosial_budaya'];

if (!$desa_id || !in_array($tabel, $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Parameter tidak valid.']); exit;
}

$stmt = $pdo->prepare("SELECT * FROM $tabel WHERE desa_id = ?");
$stmt->execute([$desa_id]);
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
