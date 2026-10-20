# Design.md
## Design System & UI/UX Guidelines: Vintage Thrift Diary

### 1. Konsep Estetika
Gaya visual mengusung tema **"Hand-Drawn Vintage Scrapbook"**. Tampilan web didesain layaknya buku catatan harian toko barang antik dengan tekstur kertas, garis tepi sketsa yang sedikit tidak simetris (*wobbly lines*), aksen pita perekat (*washi tape*), dan stempel tinta.

### 2. Palet Warna (Color Palette)
* **Paper Canvas (Latar Belakang Utama):** `#FAF6EE` (Krem kertas pudar alami)
* **Drawing Ink (Teks Utama & Garis Kontur):** `#242424` (Arang pekat, bukan hitam pekat `#000`)
* **Muted Cream (Kartu & Wadah):** `#FFFFFF` / `#FFFDF9`
* **Highlighter Accent (Tombol & Aksen Diskon):** `#F3D361` (Kuning stabilo vintage)
* **Terracotta Red (Label Sale & Sold Out):** `#C85A48`
* **Sage Green (Label Like New & Sukses):** `#5B8A72`
* **Washi Tape Yellow:** `rgba(244, 223, 154, 0.75)`

### 3. Tipografi
* **Display / Headings (Kesan Tulisan Tangan):**
  * Font: `'Caveat'`, `'Patrick Hand'`, atau `'Gochi Hand'` (Google Fonts).
  * Digunakan untuk: Judul toko, header bagian, badge stempel, dan aksen harga coret.
* **Body / Data Transaksi (Keterbacaan Tinggi):**
  * Font: `'Plus Jakarta Sans'`, `'Nunito'`, atau font sans-serif sistem.
  * Digunakan untuk: Rincian ukuran pakaian, nominal harga, input formulir, dan teks alamat checkout.

### 4. Komponen UI Khas
* **Polaroid Product Card:**
  * Bingkai foto berwarna putih gading dengan padding bawah tebal khas polaroid.
  * Sudut kartu sedikit miring acak menggunakan CSS (`transform: rotate(-0.8deg)` atau `1deg`).
  * Aksen *washi tape* di bagian atas kartu menggunakan pseudo-element `::before`.
* **Wobbly Borders & Sketch Shadow:**
  * Kontur border: `border: 2px solid #242424`.
  * Kelengkungan asimetris: `border-radius: 255px 15px 225px 15px / 15px 225px 15px 255px`.
  * Bayangan sketsa: `box-shadow: 4px 5px 0px #242424` (tanpa blur).
* **Stamp Badge (Stempel Kondisi Barang):**
  * Tampilan berbentuk lingkaran atau kapsul lonjong dengan garis tepi putus-putus atau ganda, dirotasi -5 derajat untuk menunjukkan grade pakaian.
* **Receipt-Style Checkout Modal:**
  * Wadah formulir checkout menyerupai struk belanja fisik dengan gerigi zigzag di bagian bawah (dihasilkan via CSS `radial-gradient` atau `clip-path`).

### 5. Prinsip Responsif (Mobile-First)
* Pengguna preloved mayoritas mengakses via smartphone:
  * Grid katalog: 2 kolom pada layar ponsel (`grid-cols-2`), 3–4 kolom pada desktop.
  * Tombol keranjang melayang (*floating sketch cart button*) selalu terlihat di pojok kanan bawah ponsel.
  * Form checkout dioptimalkan untuk input sentuh dengan padding tombol minimal 48px.