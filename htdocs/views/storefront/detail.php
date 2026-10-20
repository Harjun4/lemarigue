<?php
/**
 * Product Detail View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = $product['name'];
$pageDescription = $product['condition_notes'] ? substr($product['condition_notes'], 0, 150) : $product['name'];
require_once __DIR__ . '/../layouts/header.php';

$isAvailable = ($product['status'] === 'available');
$isBooked = ($product['status'] === 'booked');
$isSold = ($product['status'] === 'sold');

$price = (float)$product['price'];
$discountPrice = !empty($product['discount_price']) ? (float)$product['discount_price'] : null;
$effectivePrice = $discountPrice ?? $price;

$images = $product['images'];
$primaryImage = !empty($images[0]['image_path']) ? asset($images[0]['image_path']) : asset('assets/images/placeholder.svg');

$gradeLabel = match ($product['condition_grade']) {
    'like_new' => 'Like New (Hampir Seperti Baru)',
    'fair' => 'Fair (Minus Pemakaian / Vintage Tertera)',
    default => 'Good (Kondisi Terawat & Bagus)',
};

$gradeBadgeClass = match ($product['condition_grade']) {
    'like_new' => 'stamp-badge-likenew',
    'fair' => 'stamp-badge-fair',
    default => 'stamp-badge-good',
};
?>

<!-- Breadcrumbs -->
<nav aria-label="Breadcrumb" style="margin-bottom: 1.5rem; font-size: 0.9rem;">
    <ol style="display: flex; gap: 0.5rem; list-style: none; flex-wrap: wrap;">
        <li><a href="<?= url('') ?>" style="text-decoration: underline;">Katalog</a></li>
        <li aria-hidden="true">&rsaquo;</li>
        <li><a href="<?= url('?category=' . urlencode($product['category'])) ?>" style="text-decoration: underline; text-transform: capitalize;"><?= e($product['category']) ?></a></li>
        <li aria-hidden="true">&rsaquo;</li>
        <li aria-current="page" style="color: var(--color-ink-muted); font-weight: 600;"><?= e($product['name']) ?></li>
    </ol>
</nav>

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem;" class="md:grid-cols-2">
    <!-- Left Column: Photo Gallery -->
    <div>
        <div class="scrapbook-card washi-tape" style="padding: 1rem; background-color: var(--color-paper-light);">
            <div style="width: 100%; aspect-ratio: 1/1; background-color: #F0ECE1; border: var(--border-ink); border-radius: 6px; overflow: hidden; position: relative;">
                <img id="mainGalleryImage" src="<?= $primaryImage ?>" alt="<?= e($product['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                
                <?php if ($isSold): ?>
                    <span class="stamp-badge stamp-badge-sold photo-stamp" style="font-size: 1.25rem;">TERJUAL</span>
                <?php elseif ($isBooked): ?>
                    <span class="stamp-badge stamp-badge-booked photo-stamp" style="font-size: 1.25rem;">DIBOOKING</span>
                <?php endif; ?>
            </div>

            <!-- Thumbnails if multiple images exist -->
            <?php if (count($images) > 1): ?>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
                    <?php foreach ($images as $idx => $img): ?>
                        <?php $thumbUrl = asset($img['image_path']); ?>
                        <button type="button" 
                                class="js-gallery-thumb" 
                                data-full="<?= $thumbUrl ?>"
                                style="width: 70px; height: 70px; border: 2px solid <?= ($idx === 0) ? 'var(--color-highlighter)' : 'var(--color-ink)' ?>; border-radius: 4px; overflow: hidden; padding: 0; background: none; cursor: pointer; flex-shrink: 0;"
                                aria-label="Lihat foto nomor <?= $idx + 1 ?>">
                            <img src="<?= $thumbUrl ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Specs & Order Action -->
    <div>
        <div class="scrapbook-card washi-tape washi-tape-angle-right" style="padding: 2rem 1.5rem; background-color: var(--color-paper-light);">
            <!-- Status and Grade Stamps -->
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <span class="stamp-badge <?= $gradeBadgeClass ?>">
                    <?= $gradeLabel ?>
                </span>
                <?php if ($isAvailable): ?>
                    <span class="stamp-badge stamp-badge-available">Tersedia (1 Pcs)</span>
                <?php elseif ($isBooked): ?>
                    <span class="stamp-badge stamp-badge-booked">Sedang Dibooking (60 Menit)</span>
                <?php else: ?>
                    <span class="stamp-badge stamp-badge-sold">Sudah Terjual</span>
                <?php endif; ?>
            </div>

            <!-- Product Title -->
            <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); line-height: 1.15; margin-bottom: 0.75rem;">
                <?= e($product['name']) ?>
            </h1>

            <!-- Pricing -->
            <div style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 2px dashed var(--color-ink);">
                <span style="font-size: 2rem; font-weight: 700; color: var(--color-ink);">
                    <?= format_rupiah($effectivePrice) ?>
                </span>
                <?php if ($discountPrice && $discountPrice < $price): ?>
                    <span style="font-family: var(--font-hand); font-size: 1.4rem; color: var(--color-terracotta); text-decoration: line-through;">
                        <?= format_rupiah($price) ?>
                    </span>
                    <span style="background-color: var(--color-terracotta); color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                        HEMAT <?= format_rupiah($price - $discountPrice) ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Dimensions and Specs -->
            <div style="background-color: #FFFFFF; border: var(--border-ink); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 2px 2px 0 var(--color-ink);">
                <h3 style="font-size: 1.4rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>[Rincian Fisik Barang]</span>
                </h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.45rem; font-size: 0.95rem;">
                    <li><strong>Ukuran Tag:</strong> <?= e($product['size']) ?></li>
                    <?php if (!empty($product['dimensions'])): ?>
                        <li><strong>Dimensi Nyata:</strong> <?= e($product['dimensions']) ?></li>
                    <?php endif; ?>
                    <li><strong>Kategori:</strong> <span style="text-transform: capitalize;"><?= e($product['category']) ?></span></li>
                </ul>
            </div>

            <!-- Honest Usage Notes -->
            <?php if (!empty($product['condition_notes'])): ?>
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-family: var(--font-hand); font-size: 1.35rem; margin-bottom: 0.35rem;">
                        Catatan Kejujuran Fisik & Minus:
                    </h4>
                    <p style="color: var(--color-ink); font-size: 0.95rem; line-height: 1.6; background-color: var(--color-washi); padding: 0.85rem 1rem; border-left: 4px solid var(--color-ink); border-radius: 4px;">
                        <?= nl2br(e($product['condition_notes'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 2rem;">
                <?php if ($isAvailable): ?>
                    <button type="button" 
                            class="btn-sketch btn-sketch-primary js-add-to-cart" 
                            style="width: 100%; font-size: 1.35rem;"
                            data-id="<?= (int)$product['id'] ?>"
                            data-name="<?= e($product['name']) ?>"
                            data-size="<?= e($product['size']) ?>"
                            data-price="<?= $effectivePrice ?>"
                            data-image="<?= $primaryImage ?>"
                            data-slug="<?= e($product['slug']) ?>">
                        + Masukkan ke Tas Belanja
                    </button>
                    
                    <a href="<?= url('checkout?direct=' . (int)$product['id']) ?>" 
                       class="btn-sketch btn-sketch-sage" 
                       style="width: 100%; font-size: 1.25rem;">
                        Beli Sekarang via WhatsApp
                    </a>
                <?php elseif ($isBooked): ?>
                    <div class="alert-sketch alert-sketch-error" style="margin-bottom: 0;">
                        <span>Barang ini sedang di-hold oleh pembeli lain selama 60 menit. Jika pembayaran tidak diverifikasi, status barang akan otomatis kembali tersedia.</span>
                    </div>
                <?php else: ?>
                    <div class="alert-sketch" style="background-color: #E2DFD8; border-color: var(--color-ink); margin-bottom: 0;">
                        <span>Barang ini telah laku terjual. Telusuri katalog untuk pilihan pakaian menarik lainnya!</span>
                    </div>
                <?php endif; ?>

                <a href="<?= url('') ?>" style="text-align: center; text-decoration: underline; font-size: 0.95rem; margin-top: 0.5rem; color: var(--color-ink-muted);">
                    &larr; Kembali ke Katalog Pakaian
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
