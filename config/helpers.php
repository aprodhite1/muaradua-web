<?php
/**
 * config/helpers.php
 * Helper functions & session utilities
 */

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ambil info desa dari DB berdasarkan slug URL
 */
function getDesaBySlug(PDO $pdo, string $slug): ?array {
    $stmt = $pdo->prepare("SELECT * FROM desa WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/**
 * Cek apakah admin sudah login untuk desa ini
 */
function isAdminLoggedIn(int $desa_id): bool {
    return !empty($_SESSION['admin_logged_in'])
        && intval($_SESSION['admin_desa_id']) === $desa_id;
}

/**
 * Proteksi halaman admin — redirect ke login jika belum login
 */
function requireAdminPage(array $desa, string $base_url): void {
    if (!isAdminLoggedIn((int)$desa['id'])) {
        header('Location: ' . $base_url . '/login.php');
        exit;
    }
}

/**
 * Escape HTML output
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke format Indonesia (1.234)
 */
function fmtNum($n): string {
    return number_format((int)$n, 0, ',', '.');
}

/**
 * Ambil slug desa dari URL path
 * URL pattern: /muaradua-web/muaradua/{slug}/halaman.php
 */
function getSlugFromPath(): string {
    $parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $idx = array_search('muaradua', $parts);
    return ($idx !== false && isset($parts[$idx + 1])) ? $parts[$idx + 1] : '';
}

/**
 * Base URL desa untuk dipakai di link navigasi
 */
function desaBaseUrl(string $slug): string {
    return '/muaradua-web/muaradua/' . $slug;
}
