<?php
/**
 * Debug tool — http://localhost/muaradua-web/api/debug_session.php
 * Akses SETELAH login ke admin
 */
require_once '../config/db.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');
echo json_encode([
    'session_id'       => session_id(),
    'admin_logged_in'  => $_SESSION['admin_logged_in'] ?? null,
    'admin_desa_id'    => $_SESSION['admin_desa_id'] ?? null,
    'admin_nama'       => $_SESSION['admin_nama'] ?? null,
    'all_session'      => $_SESSION,
    'isAdmin_desa1'    => isAdminLoggedIn(1),
    'request_method'   => $_SERVER['REQUEST_METHOD'],
    'content_type'     => $_SERVER['CONTENT_TYPE'] ?? '',
]);
