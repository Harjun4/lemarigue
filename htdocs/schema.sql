-- Database Schema for Preloved Hand-Drawn Storefront (isi-lemarigue)
-- Target: MySQL 8.x / MariaDB / InfinityFree Apache Server

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `settings`;

-- 1. Admins Table
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Products Table (stock = 1 per unique preloved item)
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `category` ENUM('tops', 'bottoms', 'outerwear', 'accessories', 'others') NOT NULL DEFAULT 'tops',
  `size` VARCHAR(30) NOT NULL,
  `dimensions` VARCHAR(100) NULL COMMENT 'Contoh: P: 68cm, L: 52cm',
  `condition_grade` ENUM('like_new', 'good', 'fair') NOT NULL DEFAULT 'good',
  `condition_notes` TEXT NULL COMMENT 'Catatan minus, noda, atau kondisi pemakaian',
  `price` DECIMAL(12, 2) NOT NULL,
  `discount_price` DECIMAL(12, 2) NULL,
  `status` ENUM('available', 'booked', 'sold') NOT NULL DEFAULT 'available',
  `hold_expires_at` DATETIME NULL COMMENT 'Batas waktu booking pesanan (60 menit)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Product Images Table
CREATE TABLE `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Orders Table
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

-- 5. Order Items Table
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `price_at_purchase` DECIMAL(12, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Settings Table
CREATE TABLE `settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed Data: Admin (username: admin, password: admin123)
-- Hash generated using password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admins` (`username`, `password_hash`, `name`) 
VALUES ('admin', '$2y$12$N/GTKpngAW7EpOOTzmZ5durG0MbA3hTgO9A5608axJ0n//5U43epS', 'Pemilik Lemari');

-- Seed Data: Store Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'Isi Lemarigue Preloved'),
('store_tagline', 'Koleksi pakaian preloved pilihan dengan cerita personal.'),
('whatsapp_number', '6281234567890'),
('bank_payment_details', 'BCA: 8735019281 a.n Juna Preloved\nMandiri: 137001928374 a.n Juna Preloved\nQRIS: Hubungi WhatsApp untuk barcode'),
('booking_hold_duration_minutes', '60'),
('shipping_note', 'Pengiriman dari Jakarta Selatan setiap Senin sampai Sabtu pukul 15.00 WIB.');

-- Seed Data: Sample Initial Products
INSERT INTO `products` (`name`, `slug`, `category`, `size`, `dimensions`, `condition_grade`, `condition_notes`, `price`, `discount_price`, `status`) VALUES
('Jaket Denim Vintage Oversized 90s', 'jaket-denim-vintage-oversized-90s', 'outerwear', 'L', 'Panjang: 68 cm, Lebar Dada: 58 cm', 'good', 'Bahan denim tebal original. Ada sedikit fading alami di ujung lengan kiri, kancing besi lengkap.', 175000.00, 150000.00, 'available'),
('Kemeja Flanel Kotak Retro Earth Tone', 'kemeja-flanel-kotak-retro-earth-tone', 'tops', 'M', 'Panjang: 70 cm, Lebar Dada: 52 cm', 'like_new', 'Bahan katun flanel lembut, warna masih sangat pekat, tidak ada sobek atau noda. Tag utuh.', 95000.00, NULL, 'available'),
('Celana Corduroy Cokelat Hangat', 'celana-corduroy-cokelat-hangat', 'bottoms', 'L (32-33)', 'Panjang: 98 cm, Lingkar Pinggang: 84 cm', 'good', 'Tekstur corduroy masih empuk, resleting YKK lancar, saku depan dan belakang aman.', 135000.00, 120000.00, 'available'),
('Cardigan Rajut Wool Cream Vintage', 'cardigan-rajut-wool-cream-vintage', 'outerwear', 'Free Size', 'Panjang: 62 cm, Lebar Dada: 56 cm', 'fair', 'Rajutan hangat khas vintage Jepang. Ada repair kecil tak kasat mata di bagian saku dalam.', 85000.00, NULL, 'available'),
('Tas Bahu Kanvas Daily Sling Pocket', 'tas-bahu-kanvas-daily-sling-pocket', 'accessories', 'All Size', 'Panjang: 32 cm, Tinggi: 25 cm, Lebar: 8 cm', 'like_new', 'Kanvas tebal kokoh, strap bisa disesuaikan, resleting bersih dan fungsional.', 65000.00, NULL, 'available');

-- Seed Data: Product Images (Sample images)
INSERT INTO `product_images` (`product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(1, 'assets/images/sample-denim.svg', 1, 0),
(2, 'assets/images/sample-flannel.svg', 1, 0),
(3, 'assets/images/sample-corduroy.svg', 1, 0),
(4, 'assets/images/sample-cardigan.svg', 1, 0),
(5, 'assets/images/sample-bag.svg', 1, 0);
