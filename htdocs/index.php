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
