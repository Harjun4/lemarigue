# Rules.md
## Business Logic & Development Constraints

### 1. Aturan Manajemen Stok Preloved
* Setiap produk preloved adalah barang unik dengan `stock = 1`.
* **Status Produk:**
  * `available`: Barang aktif dan bebas dimasukkan ke keranjang belanja.
  * `booked`: Barang telah diproses checkout oleh seorang pembeli dan sedang dalam tenggat transfer (status sementara).
  * `sold`: Pembayaran telah diverifikasi penjual. Barang tetap tampil di web dengan label *Sold Out* sebagai portofolio/arsip atau disembunyikan sesuai pengaturan.
* **Aturan Lock Timeout (Anti-Rebutan):**
  * Saat pembeli menekan konfirmasi checkout dan diarahkan ke WhatsApp, status produk berubah menjadi `booked` dengan `hold_expires_at = NOW() + 60 menit`.
  * Jika dalam 60 menit penjual tidak menandai pesanan sebagai `verified`, sistem secara dinamis mengembalikan status produk menjadi `available` ketika daftar produk di-load kembali.

### 2. Spesifikasi Alur Pesanan & WhatsApp Contract
* Tautan WhatsApp dibentuk menggunakan protokol resmi: `https://wa.me/{ADMIN_PHONE}?text={ENCODED_MESSAGE}`.
* Format pesan wajib memuat kode pemesanan unik dengan format `#PRL-{YYYYMMDD}-{INDEX}` untuk verifikasi silang cepat di dasbor admin.
* Struktur draf pesan:
  ```text
  Halo Min, saya mau order barang ini:

  Kode Order: #PRL-20261017-004
  Nama: [Nama Pembeli]
  No. HP: [No WhatsApp]
  Alamat: [Alamat Lengkap]

  Barang:
  1. [Nama Barang] - [Ukuran] (Rp [Harga])

  Subtotal: Rp [Nominal]
  Ekspedisi: [JNE/J&T/SiCepat]

  Mohon total ongkir dan rekening pembayarannya ya min. Terima kasih!

  3. Batasan Unggah Berkas (InfinityFree Constraints)
Ukuran maksimal file gambar produk: 2 MB per foto.

Format yang diizinkan hanya: jpg, jpeg, png, webp.

Nama file wajib dienkripsi acak (uniqid('prod_', true) . '.' . $ext) saat disimpan untuk mencegah nama file bentrok atau berisi karakter berbahaya.

Sistem harus me-resize atau mengompresi gambar otomatis menggunakan ekstensi GD Library PHP bawaan sebelum disimpan ke disk.

4. Standar Kode & Keamanan
SQL Injection Prevention: Seluruh query database wajib menggunakan PDO Prepared Statements ($stmt->prepare() dan $stmt->execute()).

Cross-Site Scripting (XSS): Semua data dinamis yang ditampilkan ke layar wajib di-escape menggunakan fungsi pembantu htmlspecialchars($data, ENT_QUOTES, 'UTF-8').

Session Security: Autentikasi admin wajib menerapkan session_regenerate_id(true) setelah login berhasil dan pengecekan otorisasi di setiap halaman admin.


---

```markdown
# Schema.md
## Database Architecture & DDL Specification (MySQL)

### 1. Diagram Relasi Entitas (ERD Overview)
+------------------+         +------------------+
|     products     | 1 --- * |  product_images  |
+------------------+         +------------------+
| 1
|
| *
+------------------+         +------------------+
|   order_items    | * --- 1 |      orders      |
+------------------+         +------------------+

+------------------+         +------------------+
|      admins      |         |     settings     |
+------------------+         +------------------+


### 2. Skrip DDL Lengkap (MySQL / phpMyAdmin)

```sql
-- Nonaktifkan pengecekan foreign key sementara untuk inisialisasi tabel bersih
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `settings`;

-- --------------------------------------------------------
-- 1. Tabel Admins (Pengelola Toko)
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabel Products (Katalog Barang Preloved)
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `category` ENUM('tops', 'bottoms', 'outerwear', 'accessories', 'others') NOT NULL DEFAULT 'tops',
  `size` VARCHAR(30) NOT NULL,
  `dimensions` VARCHAR(100) NULL COMMENT 'Contoh: P: 68cm, L: 52cm',
  `condition_grade` ENUM('like_new', 'good', 'fair') NOT NULL DEFAULT 'good',
  `condition_notes` TEXT NULL COMMENT 'Catatan minus, noda, atau cacat pemakaian',
  `price` DECIMAL(12, 2) NOT NULL,
  `discount_price` DECIMAL(12, 2) NULL,
  `status` ENUM('available', 'booked', 'sold') NOT NULL DEFAULT 'available',
  `hold_expires_at` DATETIME NULL COMMENT 'Batas waktu booking pesanan',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabel Product Images (Galeri Foto Detail)
-- --------------------------------------------------------
CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabel Orders (Pencatatan Pesanan Masuk)
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(25) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `courier` VARCHAR(50) NOT NULL,
  `total_amount` DECIMAL(12, 2) NOT NULL,
  `payment_status` ENUM('waiting_payment', 'verified', 'cancelled') NOT NULL DEFAULT 'waiting_payment',
  `whatsapp_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_order_code` (`order_code`),
  INDEX `idx_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabel Order Items (Rincian Produk dalam Pesanan)
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `price_at_purchase` DECIMAL(12, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabel Settings (Konfigurasi Global Web)
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Data Inisial (Seeder Awal)
-- --------------------------------------------------------
-- Akun Admin Default (Password default: 'admin123' ter-hash via BCRYPT)
INSERT INTO `admins` (`username`, `password_hash`, `name`) 
VALUES ('admin', '$2y$10$eA0q4tqP3mN9f4c.Q5nWeeDq7L4YgXyUvVz4eU3zVwF5Uo.9RKeiS', 'Store Owner');

-- Konfigurasi Pengaturan Toko
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Vintage Thrifthouse'),
('whatsapp_number', '6281234567890'),
('bank_payment_details', 'BCA: 1234567890 a.n Store Owner\nMandiri: 0987654321 a.n Store Owner'),
('booking_hold_duration_minutes', '60');

<ElicitationsGroup message="Pilih langkah teknis berikutnya yang ingin dibuat:">
  <Elicitation label="Buat skrip PHP Native koneksi database & controller CRUD katalog" query="Tolong buatkan kode PHP Native untuk database.php, Product.php, dan skrip upload gambar berfitur resize."/>
  <Elicitation label="Buat file CSS hand-drawn lengkap dan template HTML beranda" query="Tolong buatkan file hand-drawn.css lengkap dengan styling kartu polaroid dan template katalog index.php."/>
</ElicitationsGroup>