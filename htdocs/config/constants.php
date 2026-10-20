<?php
/**
 * Global Constants and Configuration
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Environment and debugging
define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Upload constraints as specified in Rules.md
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products');
define('UPLOAD_URL_PATH', 'uploads/products/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB max
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Booking hold duration in minutes (anti double-booking)
define('DEFAULT_HOLD_MINUTES', 60);

// Base URL calculation (supports root domain, localhost:8000, or subfolder)
function get_base_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    
    // If running with built-in server or root domain
    if ($dir === '' || $dir === '/') {
        return $protocol . $host;
    }
    return $protocol . $host . $dir;
}

define('BASE_URL', get_base_url());

/**
 * XSS sanitization helper (Rules.md constraint)
 */
function e(?string $data): string {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

/**
 * Currency format helper
 */
function format_rupiah(float|int|string|null $amount): string {
    $num = (float)($amount ?? 0);
    return 'Rp ' . number_format($num, 0, ',', '.');
}

/**
 * URL builder helper
 */
function url(string $path = ''): string {
    $cleanPath = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $cleanPath;
}

/**
 * Asset URL builder
 */
function asset(string $path): string {
    return url(ltrim($path, '/'));
}

/**
 * Safe redirect helper
 */
function redirect(string $path): never {
    header("Location: " . url($path));
    exit;
}

/**
 * Slug generator
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'produk-' . time());
}

/**
 * Session Flash Messages
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'][$type] = $message;
}

function get_flash(string $type): ?string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}
