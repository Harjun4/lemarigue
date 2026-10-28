<?php
/**
 * Main Front Controller & Router
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Setting.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/AdminController.php';

// Parse Request Path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove script folder if hosted in subfolder
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($scriptDir !== '' && str_starts_with($path, $scriptDir)) {
    $path = substr($path, strlen($scriptDir));
}

$path = '/' . trim($path, '/');

// Allow query param override e.g. ?route=admin
if (isset($_GET['route'])) {
    $path = '/' . trim($_GET['route'], '/');
}

// --------------------------------------------------------------------------
// Public Storefront Routes
// --------------------------------------------------------------------------

try {
    // Home & Catalog
if ($path === '/' || $path === '') {
    $productModel = new Product();
    $filters = [
        'category' => $_GET['category'] ?? '',
        'condition_grade' => $_GET['grade'] ?? '',
        'status' => $_GET['status'] ?? '',
        'search' => $_GET['q'] ?? '',
    ];
    $products = $productModel->getAll($filters);
    require_once ROOT_PATH . '/views/storefront/index.php';
    exit;
}

// Product Detail: /product/{slug}
if (preg_match('#^/product/([a-zA-Z0-9\-_]+)$#', $path, $matches)) {
    $slug = $matches[1];
    $productModel = new Product();
    $product = $productModel->getBySlug($slug);
    if (!$product) {
        set_flash('error', 'Produk yang Anda cari tidak ditemukan atau telah dihapus.');
        redirect('');
    }
    require_once ROOT_PATH . '/views/storefront/detail.php';
    exit;
}

// Checkout Page: /checkout
if ($path === '/checkout') {
    $orderController = new OrderController();
    $orderController->checkout();
    exit;
}

// Process Checkout Form: /order/process
if ($path === '/order/process') {
    $orderController = new OrderController();
    $orderController->process();
    exit;
}

// Order Confirmation Receipt: /order/success/{order_code}
if (preg_match('#^/order/success/(.+)$#', $path, $matches)) {
    $orderCode = urldecode($matches[1]);
    $orderController = new OrderController();
    $orderController->success($orderCode);
    exit;
}

// Cart Validation API: /api/cart/validate
if ($path === '/api/cart/validate') {
    $cartController = new CartController();
    $cartController->validate();
    exit;
}

// --------------------------------------------------------------------------
// Admin Panel Routes
// --------------------------------------------------------------------------

$adminController = new AdminController();

// Admin Dashboard: /admin
if ($path === '/admin') {
    $adminController->dashboard();
    exit;
}

// Admin Login & Logout: /admin/login, /admin/logout
if ($path === '/admin/login') {
    $adminController->login();
    exit;
}

if ($path === '/admin/logout') {
    $adminController->logout();
    exit;
}

// Admin Products List: /admin/products
if ($path === '/admin/products') {
    $adminController->products();
    exit;
}

// Admin Product Create: /admin/products/create
if ($path === '/admin/products/create') {
    $adminController->productCreate();
    exit;
}

// Admin Product Edit: /admin/products/edit/{id}
if (preg_match('#^/admin/products/edit/(\d+)$#', $path, $matches)) {
    $id = (int)$matches[1];
    $adminController->productEdit($id);
    exit;
}

// Admin Product Delete: /admin/products/delete/{id}
if (preg_match('#^/admin/products/delete/(\d+)$#', $path, $matches)) {
    $id = (int)$matches[1];
    $adminController->productDelete($id);
    exit;
}

// Admin Product Image Primary: /admin/products/{pid}/image/{imgId}/primary
if (preg_match('#^/admin/products/(\d+)/image/(\d+)/primary$#', $path, $matches)) {
    $pid = (int)$matches[1];
    $imgId = (int)$matches[2];
    $adminController->productImagePrimary($pid, $imgId);
    exit;
}

// Admin Product Image Delete: /admin/products/{pid}/image/{imgId}/delete
if (preg_match('#^/admin/products/(\d+)/image/(\d+)/delete$#', $path, $matches)) {
    $pid = (int)$matches[1];
    $imgId = (int)$matches[2];
    $adminController->productImageDelete($pid, $imgId);
    exit;
}

// Admin Orders List: /admin/orders
if ($path === '/admin/orders') {
    $adminController->orders();
    exit;
}

// Admin Order Detail: /admin/orders/detail/{id}
if (preg_match('#^/admin/orders/detail/(\d+)$#', $path, $matches)) {
    $id = (int)$matches[1];
    $adminController->orderDetail($id);
    exit;
}

// Admin Order Status Update: /admin/orders/{id}/status
if (preg_match('#^/admin/orders/(\d+)/status$#', $path, $matches)) {
    $id = (int)$matches[1];
    $adminController->orderUpdateStatus($id);
    exit;
}

// Admin Store Settings: /admin/settings
if ($path === '/admin/settings') {
    $adminController->settings();
    exit;
}

// 404 Not Found Fallback
http_response_code(404);
$pageTitle = 'Halaman Tidak Ditemukan';
require_once ROOT_PATH . '/views/layouts/header.php';
?>
<div class="scrapbook-card washi-tape" style="max-width: 500px; margin: 4rem auto; text-align: center; padding: 3rem 1.5rem;">
    <h1 style="font-size: 3rem; margin-bottom: 0.5rem;">404</h1>
    <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Halaman Tidak Ditemukan</h2>
    <p style="color: var(--color-ink-muted); margin-bottom: 1.5rem;">Halaman atau rak pakaian yang Anda cari tidak ada atau telah dipindahkan.</p>
    <a href="<?= url('') ?>" class="btn-sketch btn-sketch-primary">Kembali ke Katalog Utama</a>
</div>
<?php
require_once ROOT_PATH . '/views/layouts/footer.php';
} catch (Throwable $e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Setup / Database Notice</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #faf7f2; padding: 40px 20px; color: #2d3748; }
            .box { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border: 2px solid #2d3748; border-radius: 12px; box-shadow: 6px 6px 0 #2d3748; }
            h2 { margin-top: 0; color: #b91c1c; font-size: 1.5rem; }
            code { background: #edf2f7; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; word-break: break-all; }
            .msg { background: #fee2e2; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 16px 0; font-family: monospace; font-size: 0.95em; color: #7f1d1d; }
            ul { line-height: 1.6; padding-left: 20px; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>⚠️ Terjadi Kendala Sistem / Database</h2>
            <p>Aplikasi toko tidak dapat berjalan karena alasan berikut:</p>
            <div class="msg"><?= htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?></div>
            <p><strong>Langkah Perbaikan:</strong></p>
            <ul>
                <li>Pastikan file <code>config/db_config.php</code> sudah diisi dengan data MySQL yang benar dari panel InfinityFree (Host, Nama DB, Username, Password).</li>
                <li>Pastikan file <code>schema.sql</code> sudah berhasil di-import di menu phpMyAdmin.</li>
            </ul>
        </div>
    </body>
    </html>
    <?php
    exit;
}
