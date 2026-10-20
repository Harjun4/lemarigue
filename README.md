# Isi Lemarigue (Preloved Hand-Drawn Storefront)

Platform web storefront toko *preloved* mandiri dengan estetika visual *hand-drawn scrapbook*, dirancang khusus untuk inventaris pakaian satuan (*one-of-a-kind*, stok = 1) dan integrasi pesanan langsung via WhatsApp Checkout.

---

## Fitur Utama

- **Katalog & Detail Produk Preloved:**
  - Status ketersediaan langsung: `Available`, `Booked` (hold 60 menit), dan `Sold Out`.
  - Galeri foto multi-sudut (depan, belakang, detail tag/minus).
  - Rincian dimensi fisik (Panjang x Lebar dsb.) dan *grade* kondisi (*Like New*, *Good*, *Fair*).
  - Filter kategori, ukuran, rentang harga, dan kondisi.
- **Cart & WhatsApp Checkout:**
  - Keranjang belanja interaktif berbasis LocalStorage.
  - Form checkout rapi: Nama, WhatsApp, Alamat Pengiriman, dan Kurir.
  - Otomatis membuat *Order Code* dan mendraf pesan WhatsApp siap kirim ke penjual.
- **Admin Dashboard:**
  - CRUD produk dengan multi-upload gambar.
  - Pelacakan & verifikasi pesanan (konfirmasi pembayaran atau batalkan pesanan).
  - Pengaturan nomor WhatsApp toko, informasi rekening pembayaran/QRIS, dan durasi hold booking.
- **Desain & Responsivitas:**
  - Estetika *hand-drawn vintage scrapbook* dengan custom borders, tape accents, dan badge unik.
  - Responsif penuh untuk tampilan smartphone maupun desktop.

---

## Struktur Folder

```
isi-lemarigue/
├── Architecture.md     # Arsitektur sistem & panduan deployment
├── Design.md           # Panduan visual dan style hand-drawn
├── PRD.md              # Product Requirements Document
├── Rules.md            # Aturan teknis pengembangan
├── htdocs/             # Root direktori web (dapat di-upload ke public_html / htdocs web server)
│   ├── assets/         # CSS, JS, dan font assets
│   ├── config/         # Konfigurasi database & konstanta
│   ├── controllers/    # Controller MVC (Catalog, Checkout, Admin, dll.)
│   ├── models/         # Model data (Product, Order, Setting, Admin)
│   ├── uploads/        # Direktori penyimpanan foto produk
│   ├── views/          # Template tampilan PHP (layouts, storefront, admin)
│   ├── index.php       # Entry point / routing utama
│   ├── schema.sql      # Database schema & seed data
│   └── .htaccess       # Konfigurasi rewrite Apache
└── README.md
```

---

## Panduan Instalasi Lokal (XAMPP / Laragon / PHP Built-in Server)

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/Harjun4/lemarigue.git
   cd lemarigue
   ```

2. **Setup Database:**
   - Buat database MySQL baru, misalnya `isi_lemarigue`.
   - Impor file [schema.sql](file:///htdocs/schema.sql) ke database Anda.
   - Atau buat file `htdocs/config/db_config.php` untuk konfigurasi kustom:
     ```php
     <?php
     return [
         'host' => '127.0.0.1',
         'port' => '3306',
         'dbname' => 'isi_lemarigue',
         'user' => 'root',
         'pass' => '',
         'driver' => 'mysql'
     ];
     ```

3. **Jalankan Web Server:**
   - Jika menggunakan PHP Built-in Server:
     ```bash
     cd htdocs
     php -S localhost:8000 index.php
     ```
   - Buka browser di `http://localhost:8000`

4. **Login Admin:**
   - Akses `/admin/login`
   - Default username: `admin`
   - Default password: `admin123`
