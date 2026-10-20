<?php
/**
 * Admin Product Creation View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Tambah Barang Preloved Baru';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div style="max-width: 760px; margin: 0 auto; padding-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 2.2rem; margin-bottom: 0.15rem;">Tambah Pakaian Baru</h1>
            <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
                Catat barang preloved dengan ukuran nyata dan kondisi transparan.
            </p>
        </div>
        <a href="<?= url('admin/products') ?>" class="btn-sketch btn-sketch-sm">&larr; Batal & Kembali</a>
    </div>

    <div class="scrapbook-card washi-tape washi-tape-angle-left" style="background-color: var(--color-paper-light); padding: 2rem;">
        <form method="POST" action="<?= url('admin/products/create') ?>" enctype="multipart/form-data">
            <!-- Product Name -->
            <div class="form-group">
                <label for="productName" class="form-label">Nama Barang Preloved:*</label>
                <input type="text" id="productName" name="name" required placeholder="Contoh: Jaket Denim Vintage Oversized 90s" class="input-sketch">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Category -->
                <div class="form-group">
                    <label for="productCategory" class="form-label">Kategori Pakaian:*</label>
                    <select id="productCategory" name="category" required class="input-sketch" style="cursor: pointer;">
                        <option value="tops">Atasan (Tops)</option>
                        <option value="bottoms">Bawahan (Bottoms)</option>
                        <option value="outerwear">Outerwear (Jaket/Cardigan)</option>
                        <option value="accessories">Aksesori & Tas</option>
                        <option value="others">Lainnya</option>
                    </select>
                </div>

                <!-- Tag Size -->
                <div class="form-group">
                    <label for="productSize" class="form-label">Ukuran Tag:*</label>
                    <input type="text" id="productSize" name="size" required placeholder="Contoh: L, M, 32, Free Size" class="input-sketch">
                </div>
            </div>

            <!-- Dimensions -->
            <div class="form-group">
                <label for="productDimensions" class="form-label">Dimensi Nyata (cm):</label>
                <input type="text" id="productDimensions" name="dimensions" placeholder="Contoh: Panjang: 68 cm, Lebar Dada: 54 cm" class="input-sketch">
                <small style="color: var(--color-ink-muted); font-size: 0.8rem;">Penting untuk baju thrift agar pembeli tahu ukuran pastinya tanpa mengira-ngira.</small>
            </div>

            <!-- Condition Grade -->
            <div class="form-group">
                <label for="conditionGrade" class="form-label">Grade Kondisi Fisik:*</label>
                <select id="conditionGrade" name="condition_grade" required class="input-sketch" style="cursor: pointer;">
                    <option value="like_new">Like New (Hampir seperti baru, tanpa minus)</option>
                    <option value="good" selected>Good (Terawat, layak pakai, minus pemakaian wajar)</option>
                    <option value="fair">Fair (Ada noda/minus tertentu, sudah dicatat jelas)</option>
                </select>
            </div>

            <!-- Condition Notes -->
            <div class="form-group">
                <label for="conditionNotes" class="form-label">Catatan Kejujuran Fisik & Minus:</label>
                <textarea id="conditionNotes" name="condition_notes" placeholder="Tuliskan catatan minus, noda, fading warna, atau kondisi kancing/resleting secara jujur..." class="input-sketch"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Price -->
                <div class="form-group">
                    <label for="productPrice" class="form-label">Harga Normal (Rp):*</label>
                    <input type="number" id="productPrice" name="price" required min="1000" step="1000" placeholder="150000" class="input-sketch">
                </div>

                <!-- Discount Price -->
                <div class="form-group">
                    <label for="productDiscountPrice" class="form-label">Harga Diskon (Rp, Opsional):</label>
                    <input type="number" id="productDiscountPrice" name="discount_price" min="0" step="1000" placeholder="Kosongkan jika harga normal" class="input-sketch">
                </div>
            </div>

            <!-- Initial Status -->
            <div class="form-group">
                <label for="productStatus" class="form-label">Status Awal Barang:*</label>
                <select id="productStatus" name="status" class="input-sketch" style="cursor: pointer;">
                    <option value="available" selected>Available (Siap Dijual di Katalog)</option>
                    <option value="booked">Booked (Sedang Di-hold)</option>
                    <option value="sold">Sold Out (Arsip/Terjual)</option>
                </select>
            </div>

            <!-- Image Upload -->
            <div class="form-group" style="margin-top: 1.5rem; padding: 1.25rem; border: 2px dashed var(--color-ink); border-radius: 8px; background-color: #FFFFFF;">
                <label for="productImages" class="form-label" style="font-size: 1.05rem;">
                    Unggah Foto Produk (Bisa Pilih Banyak Foto Sekaligus):
                </label>
                <input type="file" id="productImages" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="input-sketch" style="padding: 0.5rem;">
                <small style="color: var(--color-ink-muted); font-size: 0.8rem; display: block; margin-top: 0.35rem;">
                    Maksimal 2 MB per foto. Format yang diizinkan: JPG, PNG, WebP. Sistem otomatis mengompresi dan me-resize foto via pustaka GD.
                </small>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="<?= url('admin/products') ?>" class="btn-sketch">Batal</a>
                <button type="submit" class="btn-sketch btn-sketch-primary" style="font-size: 1.25rem; padding: 0.75rem 2rem;">
                    Simpan Barang ke Katalog
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
