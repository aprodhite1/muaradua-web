<?php
/**
 * api/desa.php
 * GET /api/desa.php             → semua desa
 * GET /api/desa.php?slug=xxx    → detail satu desa
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$slug = $_GET['slug'] ?? null;

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM desa WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $desa = $stmt->fetch();
    if (!$desa) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Desa tidak ditemukan.']);
    } else {
        echo json_encode(['success' => true, 'data' => $desa]);
    }
} else {
    $stmt = $pdo->query("SELECT * FROM desa ORDER BY nama ASC");
    $list = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $list]);
}
