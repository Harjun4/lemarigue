<?php
/**
 * Admin Product Edit View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Edit Produk: ' . $product['name'];
require_once __DIR__ . '/../../layouts/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; padding-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 2.2rem; margin-bottom: 0.15rem;">Edit Barang Preloved</h1>
            <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
                Perbarui detail pakaian atau kelola galeri foto.
            </p>
        </div>
        <a href="<?= url('admin/products') ?>" class="btn-sketch btn-sketch-sm">&larr; Kembali ke Daftar</a>
    </div>

    <!-- Existing Photos Gallery Section -->
    <div class="scrapbook-card" style="background-color: var(--color-paper-light); margin-bottom: 2rem; padding: 1.5rem;">
        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Foto Produk Saat Ini:</h3>
        <p style="font-size: 0.85rem; color: var(--color-ink-muted); margin-bottom: 1rem;">
            Foto pertama atau yang ditandai bintang adalah foto utama yang muncul di katalog depan.
        </p>

        <?php if (empty($product['images'])): ?>
            <p style="font-style: italic; color: var(--color-ink-muted);">Belum ada foto yang diunggah untuk pakaian ini.</p>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem;">
                <?php foreach ($product['images'] as $img): ?>
                    <?php $imgUrl = asset($img['image_path']); ?>
                    <div style="border: var(--border-ink); border-radius: 6px; overflow: hidden; background-color: #FFFFFF; box-shadow: 2px 2px 0 var(--color-ink); display: flex; flex-direction: column;">
                        <div style="width: 100%; aspect-ratio: 1/1; position: relative;">
                            <img src="<?= $imgUrl ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php if ($img['is_primary']): ?>
                                <span style="position: absolute; top: 4px; left: 4px; background-color: var(--color-highlighter); border: 1px solid var(--color-ink); border-radius: 3px; font-size: 0.7rem; font-weight: bold; padding: 1px 4px;">
                                    Utama
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 0.4rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #DDD8CE; background-color: var(--color-paper);">
                            <?php if (!$img['is_primary']): ?>
                                <form method="POST" action="<?= url('admin/products/' . $product['id'] . '/image/' . $img['id'] . '/primary') ?>">
                                    <button type="submit" class="btn-sketch btn-sketch-sm" style="font-size: 0.75rem; padding: 2px 6px;" title="Jadikan Foto Utama">
                                        Set Utama
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; font-weight: 600; color: var(--color-sage);">Foto Utama</span>
                            <?php endif; ?>

                            <form method="POST" action="<?= url('admin/products/' . $product['id'] . '/image/' . $img['id'] . '/delete') ?>" onsubmit="return confirm('Hapus foto ini?');">
                                <button type="submit" class="btn-sketch btn-sketch-sm btn-sketch-terracotta" style="font-size: 0.75rem; padding: 2px 6px;" title="Hapus Foto">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Details Edit Form -->
    <div class="scrapbook-card washi-tape washi-tape-angle-right" style="background-color: var(--color-paper-light); padding: 2rem;">
        <form method="POST" action="<?= url('admin/products/edit/' . $product['id']) ?>" enctype="multipart/form-data">
            <!-- Product Name -->
            <div class="form-group">
                <label for="productName" class="form-label">Nama Barang Preloved:*</label>
                <input type="text" id="productName" name="name" required value="<?= e($product['name']) ?>" class="input-sketch">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Category -->
                <div class="form-group">
                    <label for="productCategory" class="form-label">Kategori Pakaian:*</label>
                    <select id="productCategory" name="category" required class="input-sketch" style="cursor: pointer;">
                        <option value="tops" <?= ($product['category'] === 'tops') ? 'selected' : '' ?>>Atasan (Tops)</option>
                        <option value="bottoms" <?= ($product['category'] === 'bottoms') ? 'selected' : '' ?>>Bawahan (Bottoms)</option>
                        <option value="outerwear" <?= ($product['category'] === 'outerwear') ? 'selected' : '' ?>>Outerwear (Jaket/Cardigan)</option>
                        <option value="accessories" <?= ($product['category'] === 'accessories') ? 'selected' : '' ?>>Aksesori & Tas</option>
                        <option value="others" <?= ($product['category'] === 'others') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <!-- Tag Size -->
                <div class="form-group">
                    <label for="productSize" class="form-label">Ukuran Tag:*</label>
                    <input type="text" id="productSize" name="size" required value="<?= e($product['size']) ?>" class="input-sketch">
                </div>
            </div>

            <!-- Dimensions -->
            <div class="form-group">
                <label for="productDimensions" class="form-label">Dimensi Nyata (cm):</label>
                <input type="text" id="productDimensions" name="dimensions" value="<?= e($product['dimensions']) ?>" placeholder="Contoh: Panjang: 68 cm, Lebar Dada: 54 cm" class="input-sketch">
            </div>

            <!-- Condition Grade -->
            <div class="form-group">
                <label for="conditionGrade" class="form-label">Grade Kondisi Fisik:*</label>
                <select id="conditionGrade" name="condition_grade" required class="input-sketch" style="cursor: pointer;">
                    <option value="like_new" <?= ($product['condition_grade'] === 'like_new') ? 'selected' : '' ?>>Like New (Hampir seperti baru, tanpa minus)</option>
                    <option value="good" <?= ($product['condition_grade'] === 'good') ? 'selected' : '' ?>>Good (Terawat, layak pakai, minus pemakaian wajar)</option>
                    <option value="fair" <?= ($product['condition_grade'] === 'fair') ? 'selected' : '' ?>>Fair (Ada noda/minus tertentu, sudah dicatat jelas)</option>
                </select>
            </div>

            <!-- Condition Notes -->
            <div class="form-group">
                <label for="conditionNotes" class="form-label">Catatan Kejujuran Fisik & Minus:</label>
                <textarea id="conditionNotes" name="condition_notes" class="input-sketch"><?= e($product['condition_notes']) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <!-- Price -->
                <div class="form-group">
                    <label for="productPrice" class="form-label">Harga Normal (Rp):*</label>
                    <input type="number" id="productPrice" name="price" required min="1000" step="1000" value="<?= (int)$product['price'] ?>" class="input-sketch">
                </div>

                <!-- Discount Price -->
                <div class="form-group">
                    <label for="productDiscountPrice" class="form-label">Harga Diskon (Rp, Opsional):</label>
                    <input type="number" id="productDiscountPrice" name="discount_price" min="0" step="1000" value="<?= $product['discount_price'] ? (int)$product['discount_price'] : '' ?>" class="input-sketch">
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label for="productStatus" class="form-label">Status Ketersediaan Barang:*</label>
                <select id="productStatus" name="status" class="input-sketch" style="cursor: pointer;">
                    <option value="available" <?= ($product['status'] === 'available') ? 'selected' : '' ?>>Available (Bebas Dibeli)</option>
                    <option value="booked" <?= ($product['status'] === 'booked') ? 'selected' : '' ?>>Booked (Sedang Di-hold)</option>
                    <option value="sold" <?= ($product['status'] === 'sold') ? 'selected' : '' ?>>Sold Out (Arsip/Terjual)</option>
                </select>
            </div>

            <!-- Additional Image Upload -->
            <div class="form-group" style="margin-top: 1.5rem; padding: 1.25rem; border: 2px dashed var(--color-ink); border-radius: 8px; background-color: #FFFFFF;">
                <label for="productAddImages" class="form-label" style="font-size: 1.05rem;">
                    Tambah Foto Baru ke Galeri Produk:
                </label>
                <input type="file" id="productAddImages" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="input-sketch" style="padding: 0.5rem;">
                <small style="color: var(--color-ink-muted); font-size: 0.8rem; display: block; margin-top: 0.35rem;">
                    Maksimal 2 MB per foto. Format yang diizinkan: JPG, PNG, WebP.
                </small>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="<?= url('admin/products') ?>" class="btn-sketch">Batal</a>
                <button type="submit" class="btn-sketch btn-sketch-primary" style="font-size: 1.25rem; padding: 0.75rem 2rem;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
