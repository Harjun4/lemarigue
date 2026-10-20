# PRD.md
## Product Requirements Document: Preloved Hand-Drawn Storefront

### 1. Ringkasan Eksekutif & Latar Belakang
Platform web toko *preloved* mandiri yang dirancang khusus untuk memfasilitasi penjualan pakaian atau barang bekas berkualitas. Berbeda dari toko retail massal, sistem ini menangani inventaris satuan (*one-of-a-kind*, stok = 1), mengusung estetika visual *hand-drawn vintage scrapbook*, serta memanfaatkan integrasi WhatsApp Checkout sebagai gerbang konfirmasi transaksi dan pembayaran tanpa biaya langganan payment gateway pihak ketiga.

### 2. Tujuan Produk & Target Pengguna
* **Tujuan:**
  * Memberikan transparansi kondisi fisik barang preloved (detail minus, tag, ukuran nyata).
  * Mencegah insiden *double-booking* atau rebutan barang melalui mekanisme penguncian pesanan berbatas waktu.
  * Mempermudah penjual memverifikasi pesanan masuk dan mengelola status inventaris secara terpusat.
* **Target Pengguna:**
  * **Pembeli (Customer):** Pecinta *thrifting* yang mencari pakaian unik bernilai personal dengan proses transaksi langsung yang ramah via WhatsApp.
  * **Penjual (Admin):** Pemilik toko preloved yang membutuhkan alat manajemen katalog, stok, dan pencatatan order tanpa infrastruktur rumit.

### 3. Lingkup Fitur (Feature Scope)
#### Sisi Pembeli (Public Storefront)
* **Katalog Produk & Penyaringan:**
  * Penelusuran berbasis kategori pakaian (Atasan, Bawahan, Outer, Aksesori).
  * Filter berdasarkan ukuran (S, M, L, XL, Free Size), rentang harga, dan *Grade* kondisi (Like New, Good, Fair).
  * Indikator status produk *real-time*: `Available`, `Booked` (sementara), dan `Sold Out`.
* **Detail Produk:**
  * Galeri foto multi-sudut (tampak depan, belakang, tag kerah, dan sorotan minus kain).
  * Rincian dimensi fisik akurat (Panjang x Lebar dalam cm).
  * Deskripsi detail catatan pemakaian sebelumnya.
* **Keranjang Belanja (Cart Drawer):**
  * Penampungan item pesanan berbasis *Browser Local Storage*.
  * Peringatan otomatis jika item di keranjang telah dipesan oleh pengguna lain.
* **Checkout Form & Generator WhatsApp:**
  * Form input data diri minimalis: Nama Penerima, Nomor WhatsApp, Alamat Lengkap, Ekspedisi Pilihan, Catatan Tambahan.
  * Pembuatan kode unik transaksi (*Order Code*).
  * Redirect otomatis membuka aplikasi WhatsApp dengan draf pesan pesanan yang terformat rapi.

#### Sisi Penjual (Admin Dashboard)
* **Autentikasi Aman:** Login tunggal berbasis sesi PHP dengan proteksi kata sandi (*bcrypt*).
* **Manajemen Produk (CRUD):**
  * Tambah barang baru dengan unggah banyak foto sekaligus ke folder server.
  * Atur harga normal, harga diskon, kategori, kondisi, dan dimensi barang.
  * Pengubahan manual status ketersediaan barang (`Available`, `Booked`, `Sold`).
* **Manajemen Pesanan (Order Tracking):**
  * Daftar pesanan masuk beserta rincian barang, total bayar, dan data pengiriman.
  * Fitur verifikasi pembayaran: mengubah status dari `Waiting Payment` menjadi `Paid/Verified` (yang otomatis mengubah status barang menjadi `Sold`).
  * Fitur pembatalan pesanan untuk mengembalikan status barang menjadi `Available`.
* **Pengaturan Toko:**
  * Konfigurasi nomor WhatsApp penerima order, rekening bank transfer/QRIS, dan biaya flat ongkir/kebijakan toko.

### 4. Batasan Teknis & Asumsi
* Sistem dijalankan di lingkungan hosting gratis **InfinityFree** (PHP 8.x + MySQL).
* Upload aset foto disimpan langsung pada direktori lokal server (`/uploads/products/`).
* Tidak menggunakan layanan cron job eksternal kompleks; validasi kedaluwarsa pesanan (*expiry checkout*) dihitung secara dinamis saat katalog atau order diakses.