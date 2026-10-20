# Architecture.md
## System Architecture & Technical Specifications

### 1. Diagram Arsitektur Sistem

+-------------------------------------------------------------------------+
|                              KLIEN / BROWSER                            |
|  - HTML5 Semantik + CSS3 Hand-Drawn Design + Vanilla JS                 |
|  - LocalStorage (State Keranjang Sementara)                             |
+--------------------+--------------------------------+-------------------+
|                                |
(HTTP Request / Form Submit)       (Direct WhatsApp Link)
|                                |
v                                v
+------------------------------------+   +--------------------------------+
|       INFINITYFREE APACHE SERVER   |   |        APLIKASI WHATSAPP       |
|  - PHP 8.x Engine                  |   |  (Pesan Terformat + Bukti TF)  |
|  - Routing: .htaccess (Clean URLs) |   +--------------------------------+
|  - Session Auth (Admin Panel)      |
|  - Local File Storage:             |
|    htdocs/uploads/products/        |
+--------------------+---------------+
|
(PDO Database Driver)
|
v
+------------------------------------+
|         MYSQL DATABASE             |
|  - Relasi Produk, Order, & Admin   |
+------------------------------------+


### 2. Struktur Direktori Proyek (Root: `htdocs/`)
htdocs/
├── .htaccess                  # Konfigurasi Apache rewrite & proteksi file
├── config/
│   ├── database.php           # Koneksi PDO MySQL
│   └── constants.php          # Variabel global toko & konfigurasi path
├── controllers/
│   ├── CartController.php     # Validasi ketersediaan keranjang
│   ├── OrderController.php    # Logika pembuatan order & generator WA
│   └── AdminController.php    # Logika login & manajemen katalog
├── models/
│   ├── Product.php            # Query database entitas produk
│   └── Order.php              # Query database entitas pesanan
├── views/
│   ├── layouts/
│   │   ├── header.php         # Bagian head, CSS hand-drawn, navigasi
│   │   └── footer.php         # Penutup tag, modal keranjang, script JS
│   ├── storefront/
│   │   ├── index.php          # Halaman beranda & katalog produk
│   │   ├── detail.php         # Halaman rincian produk
│   │   └── checkout.php       # Modal/halaman nota checkout
│   └── admin/
│       ├── login.php          # Halaman login penjual
│       ├── dashboard.php      # Ringkasan penjualan
│       ├── products/          # Form tambah, edit, list produk
│       └── orders/            # List order & aksi verifikasi status
├── assets/
│   ├── css/
│   │   ├── hand-drawn.css     # Styling komponen organik, polaroid, stempel
│   │   └── main.css           # Layout utility grid & reset CSS
│   ├── js/
│   │   ├── cart.js            # Manajemen keranjang di LocalStorage
│   │   └── main.js            # Interaktivitas UI & toggle modal
│   └── images/                # Ikon coretan (doodles), tekstur kertas
├── uploads/
│   └── products/              # Folder penyimpanan foto produk unggahan
└── index.php                  # Router utama (entry point)


### 3. Strategi Deployment InfinityFree
* **Database Access:** Menggunakan host MySQL bawaan InfinityFree (misal: `sqlXXX.infinityfree.com`), username, password, dan nama DB yang tertera di vPanel.
* **File Permissions:** Folder `uploads/products/` disetel dengan izin tulis (`chmod 755` atau `777`) agar skrip PHP dapat memindahkan file yang diunggah (`move_uploaded_file`).
* **Proteksi File:** File `.htaccess` dikonfigurasi untuk membatasi eksekusi skrip berbahaya di dalam folder `/uploads/`.