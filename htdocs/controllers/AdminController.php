<?php
/**
 * Admin Controller
 * Manages seller authentication, catalog CRUD, image uploads with GD compression, order tracking, and store settings.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Setting.php';

class AdminController {
    private Product $productModel;
    private Order $orderModel;
    private Setting $settingModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->settingModel = new Setting();
    }

    /**
     * Guard that ensures an active authorized admin session.
     */
    private function requireAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['admin_logged_in']) || empty($_SESSION['admin_user'])) {
            set_flash('error', 'Silakan masuk terlebih dahulu untuk mengakses panel admin.');
            redirect('admin/login');
        }
    }

    /**
     * Admin login view and submission.
     */
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['admin_logged_in'])) {
            redirect('admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                set_flash('error', 'Mohon isi nama pengguna dan kata sandi.');
                require_once ROOT_PATH . '/views/admin/login.php';
                return;
            }

            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];

                set_flash('success', 'Selamat datang kembali, ' . $admin['name'] . '!');
                redirect('admin');
            } else {
                set_flash('error', 'Nama pengguna atau kata sandi tidak cocok.');
            }
        }

        require_once ROOT_PATH . '/views/admin/login.php';
    }

    /**
     * Admin logout.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        redirect('admin/login');
    }

    /**
     * Admin Dashboard view.
     */
    public function dashboard(): void {
        $this->requireAuth();

        $orderStats = $this->orderModel->getDashboardStats();
        $inventoryStats = $this->productModel->getInventoryStats();
        $recentOrders = $this->orderModel->getAll(['limit' => 5]);

        require_once ROOT_PATH . '/views/admin/dashboard.php';
    }

    /**
     * Products List view.
     */
    public function products(): void {
        $this->requireAuth();

        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $products = $this->productModel->getAll([
            'category' => $category,
            'status' => $status,
            'search' => $search,
        ]);

        require_once ROOT_PATH . '/views/admin/products/index.php';
    }

    /**
     * Product Creation.
     */
    public function productCreate(): void {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $category = $_POST['category'] ?? 'tops';
            $size = trim($_POST['size'] ?? '');
            $dimensions = trim($_POST['dimensions'] ?? '');
            $conditionGrade = $_POST['condition_grade'] ?? 'good';
            $conditionNotes = trim($_POST['condition_notes'] ?? '');
            $price = (float)str_replace(['.', ','], ['', '.'], $_POST['price'] ?? 0);
            $discountPrice = !empty($_POST['discount_price']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['discount_price']) : null;
            $status = $_POST['status'] ?? 'available';

            if (empty($name) || empty($size) || $price <= 0) {
                set_flash('error', 'Nama barang, ukuran, dan harga normal wajib diisi.');
                require_once ROOT_PATH . '/views/admin/products/create.php';
                return;
            }

            $slug = slugify($name);
            // Ensure unique slug
            $existing = $this->productModel->getBySlug($slug);
            if ($existing) {
                $slug .= '-' . time();
            }

            $productId = $this->productModel->create([
                'name' => $name,
                'slug' => $slug,
                'category' => $category,
                'size' => $size,
                'dimensions' => $dimensions,
                'condition_grade' => $conditionGrade,
                'condition_notes' => $conditionNotes,
                'price' => $price,
                'discount_price' => $discountPrice,
                'status' => $status,
            ]);

            // Handle multi-image upload
            if (!empty($_FILES['images']['name'][0])) {
                $this->handleImageUploads($productId, $_FILES['images']);
            }

            set_flash('success', 'Barang preloved berhasil ditambahkan ke katalog.');
            redirect('admin/products');
        }

        require_once ROOT_PATH . '/views/admin/products/create.php';
    }

    /**
     * Product Editing.
     */
    public function productEdit(int $id): void {
        $this->requireAuth();

        $product = $this->productModel->getById($id);
        if (!$product) {
            set_flash('error', 'Produk tidak ditemukan.');
            redirect('admin/products');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $category = $_POST['category'] ?? 'tops';
            $size = trim($_POST['size'] ?? '');
            $dimensions = trim($_POST['dimensions'] ?? '');
            $conditionGrade = $_POST['condition_grade'] ?? 'good';
            $conditionNotes = trim($_POST['condition_notes'] ?? '');
            $price = (float)str_replace(['.', ','], ['', '.'], $_POST['price'] ?? 0);
            $discountPrice = !empty($_POST['discount_price']) ? (float)str_replace(['.', ','], ['', '.'], $_POST['discount_price']) : null;
            $status = $_POST['status'] ?? 'available';

            if (empty($name) || empty($size) || $price <= 0) {
                set_flash('error', 'Nama barang, ukuran, dan harga normal wajib diisi.');
                require_once ROOT_PATH . '/views/admin/products/edit.php';
                return;
            }

            $slug = slugify($name);
            // If name changed, ensure unique slug
            if ($slug !== $product['slug']) {
                $existing = $this->productModel->getBySlug($slug);
                if ($existing && $existing['id'] != $id) {
                    $slug .= '-' . time();
                }
            } else {
                $slug = $product['slug'];
            }

            $this->productModel->update($id, [
                'name' => $name,
                'slug' => $slug,
                'category' => $category,
                'size' => $size,
                'dimensions' => $dimensions,
                'condition_grade' => $conditionGrade,
                'condition_notes' => $conditionNotes,
                'price' => $price,
                'discount_price' => $discountPrice,
                'status' => $status,
            ]);

            // Handle additional images if provided
            if (!empty($_FILES['images']['name'][0])) {
                $this->handleImageUploads($id, $_FILES['images']);
            }

            set_flash('success', 'Rincian produk berhasil diperbarui.');
            redirect('admin/products');
        }

        require_once ROOT_PATH . '/views/admin/products/edit.php';
    }

    /**
     * Product Deletion.
     */
    public function productDelete(int $id): void {
        $this->requireAuth();
        $this->productModel->delete($id);
        set_flash('success', 'Produk berhasil dihapus dari inventaris.');
        redirect('admin/products');
    }

    /**
     * Product Image Deletion.
     */
    public function productImageDelete(int $productId, int $imageId): void {
        $this->requireAuth();
        $this->productModel->deleteImage($imageId);
        set_flash('success', 'Foto produk berhasil dihapus.');
        redirect('admin/products/edit/' . $productId);
    }

    /**
     * Set Primary Product Image.
     */
    public function productImagePrimary(int $productId, int $imageId): void {
        $this->requireAuth();
        $this->productModel->setPrimaryImage($productId, $imageId);
        set_flash('success', 'Foto utama produk berhasil diubah.');
        redirect('admin/products/edit/' . $productId);
    }

    /**
     * Handles secure photo uploads, extension check, random filename, and GD resizing.
     */
    private function handleImageUploads(int $productId, array $files): void {
        if (!is_dir(UPLOAD_DIR)) {
            @mkdir(UPLOAD_DIR, 0755, true);
        }

        $count = count($files['name']);
        $existingImages = $this->productModel->getImages($productId);
        $hasPrimary = !empty($existingImages);

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmpName = $files['tmp_name'][$i];
            $origName = $files['name'][$i];
            $size = $files['size'][$i];

            if ($size > MAX_UPLOAD_SIZE) {
                continue; // Skip files larger than 2MB as per Rules.md
            }

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            // Secure unique random filename as specified in Rules.md: uniqid('prod_', true) . '.' . $ext
            $newFileName = uniqid('prod_', true) . '.' . $ext;
            $destination = UPLOAD_DIR . DIRECTORY_SEPARATOR . $newFileName;

            // Resize and compress with GD if available, else move_uploaded_file
            $uploaded = false;
            if (extension_loaded('gd') && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $uploaded = $this->resizeImageWithGd($tmpName, $destination, $ext, 1200);
            }

            if (!$uploaded) {
                $uploaded = move_uploaded_file($tmpName, $destination);
            }

            if ($uploaded) {
                $dbPath = UPLOAD_URL_PATH . $newFileName;
                $isPrimary = !$hasPrimary && ($i === 0);
                $this->productModel->addImage($productId, $dbPath, $isPrimary, $i);
                if ($isPrimary) {
                    $hasPrimary = true;
                }
            }
        }
    }

    /**
     * Resizes and compresses image via GD library.
     */
    private function resizeImageWithGd(string $sourceFile, string $targetFile, string $ext, int $maxWidth = 1200): bool {
        $info = @getimagesize($sourceFile);
        if (!$info) {
            return false;
        }

        [$origWidth, $origHeight, $type] = $info;

        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourceFile),
            IMAGETYPE_PNG => @imagecreatefrompng($sourceFile),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourceFile) : null,
            default => null,
        };

        if (!$sourceImage) {
            return false;
        }

        $newWidth = $origWidth;
        $newHeight = $origHeight;

        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int)round(($origHeight / $origWidth) * $maxWidth);
        }

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and WebP
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($canvas, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        $saved = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($canvas, $targetFile, 85),
            IMAGETYPE_PNG => imagepng($canvas, $targetFile, 8),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($canvas, $targetFile, 85) : false,
            default => false,
        };

        imagedestroy($canvas);
        imagedestroy($sourceImage);

        return (bool)$saved;
    }

    /**
     * Orders List view.
     */
    public function orders(): void {
        $this->requireAuth();

        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $orders = $this->orderModel->getAll([
            'payment_status' => $status,
            'search' => $search,
        ]);

        require_once ROOT_PATH . '/views/admin/orders/index.php';
    }

    /**
     * Order Detail view.
     */
    public function orderDetail(int $id): void {
        $this->requireAuth();

        $order = $this->orderModel->getById($id);
        if (!$order) {
            set_flash('error', 'Pesanan tidak ditemukan.');
            redirect('admin/orders');
        }

        require_once ROOT_PATH . '/views/admin/orders/detail.php';
    }

    /**
     * Update Order Status (Verify Payment -> Sold / Cancel -> Available).
     */
    public function orderUpdateStatus(int $id): void {
        $this->requireAuth();

        $status = $_POST['payment_status'] ?? '';
        if ($this->orderModel->updateStatus($id, $status)) {
            $label = match ($status) {
                'verified' => 'Pesanan berhasil diverifikasi dan stok barang otomatis ditandai Sold Out.',
                'cancelled' => 'Pesanan dibatalkan dan barang otomatis dikembalikan ke katalog Available.',
                default => 'Status pesanan berhasil diperbarui.',
            };
            set_flash('success', $label);
        } else {
            set_flash('error', 'Gagal memperbarui status pesanan.');
        }

        redirect('admin/orders/detail/' . $id);
    }

    /**
     * Store Settings Management view & submit.
     */
    public function settings(): void {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fields = [
                'store_name',
                'store_tagline',
                'whatsapp_number',
                'bank_payment_details',
                'booking_hold_duration_minutes',
                'shipping_note',
            ];

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $this->settingModel->set($field, trim($_POST[$field]));
                }
            }

            set_flash('success', 'Pengaturan toko berhasil disimpan.');
            redirect('admin/settings');
        }

        $settings = $this->settingModel->getAll();
        require_once ROOT_PATH . '/views/admin/settings.php';
    }
}
