<?php
/**
 * Storefront Index & Product Catalog View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Katalog Pakaian Preloved';
require_once __DIR__ . '/../layouts/header.php';

$selectedCategory = $_GET['category'] ?? '';
$selectedGrade = $_GET['grade'] ?? '';
$selectedStatus = $_GET['status'] ?? '';
$searchKeyword = $_GET['q'] ?? '';

$categories = [
    '' => 'Semua Rak',
    'tops' => 'Atasan',
    'bottoms' => 'Bawahan',
    'outerwear' => 'Outerwear',
    'accessories' => 'Aksesori',
    'others' => 'Lainnya',
];
?>

<!-- Hero Banner (Scrapbook Diary Cover) -->
<section style="margin-bottom: 2.5rem; text-align: center; position: relative;">
    <div class="scrapbook-card washi-tape washi-tape-angle-left" style="max-width: 800px; margin: 0 auto; padding: 2.5rem 1.5rem 2rem 1.5rem; background-color: var(--color-paper-light);">
        <p style="font-family: var(--font-hand); font-size: 1.4rem; color: var(--color-ink-muted); margin-bottom: 0.25rem;">
            Buku Catatan Koleksi Preloved
        </p>
        <h1 style="margin-bottom: 0.75rem; letter-spacing: -0.5px;">
            Pakaian Pilihan dengan Cerita Baru
        </h1>
        <p style="max-width: 600px; margin: 0 auto 1.5rem auto; color: var(--color-ink-muted); font-size: 1rem;">
            Setiap potong pakaian di sini hanya ada 1 potong. Kami mencatat setiap ukuran riil dan kondisi pemakaian secara jujur agar pas untuk lemari pakaian Anda.
        </p>

        <!-- Feature Badges -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.75rem;">
            <span class="stamp-badge stamp-badge-available" style="transform: rotate(-1deg); font-size: 0.95rem;">
                Stok Satuan (1 of 1)
            </span>
            <span class="stamp-badge stamp-badge-good" style="transform: rotate(1deg); font-size: 0.95rem;">
                Kondisi Bersih Siap Pakai
            </span>
            <span class="stamp-badge stamp-badge-booked" style="transform: rotate(-2deg); font-size: 0.95rem;">
                Konfirmasi Cepat via WhatsApp
            </span>
        </div>
    </div>
</section>

<!-- Filter & Search Toolbar -->
<section style="margin-bottom: 2rem;">
    <form method="GET" action="<?= url('') ?>" class="filter-bar">
        <!-- Category Filter -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center;">
            <span style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 0.35rem;">Kategori:</span>
            <?php foreach ($categories as $catKey => $catLabel): ?>
                <?php 
                    $params = $_GET;
                    if ($catKey === '') {
                        unset($params['category']);
                    } else {
                        $params['category'] = $catKey;
                    }
                    $filterUrl = url('?' . http_build_query($params));
                    $isActive = ($selectedCategory === $catKey);
                ?>
                <a href="<?= $filterUrl ?>" class="filter-pill <?= $isActive ? 'active' : '' ?>">
                    <?= e($catLabel) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Grade Filter -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; align-items: center; margin-left: auto;">
            <span style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 0.35rem;">Grade:</span>
            <?php
            $grades = ['' => 'Semua', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair'];
            foreach ($grades as $gKey => $gLabel):
                $params = $_GET;
                if ($gKey === '') {
                    unset($params['grade']);
                } else {
                    $params['grade'] = $gKey;
                }
                $filterUrl = url('?' . http_build_query($params));
                $isActive = ($selectedGrade === $gKey);
            ?>
                <a href="<?= $filterUrl ?>" class="filter-pill <?= $isActive ? 'active' : '' ?>">
                    <?= e($gLabel) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Bar -->
        <div style="width: 100%; display: flex; gap: 0.5rem; margin-top: 0.5rem;">
            <input type="text" name="q" value="<?= e($searchKeyword) ?>" placeholder="Cari nama pakaian, brand, atau jenis kain..." class="input-sketch" style="flex-grow: 1;">
            <?php if (!empty($selectedCategory)): ?>
                <input type="hidden" name="category" value="<?= e($selectedCategory) ?>">
            <?php endif; ?>
            <?php if (!empty($selectedGrade)): ?>
                <input type="hidden" name="grade" value="<?= e($selectedGrade) ?>">
            <?php endif; ?>
            <button type="submit" class="btn-sketch btn-sketch-sm btn-sketch-primary">Cari</button>
            <?php if (!empty($searchKeyword) || !empty($selectedCategory) || !empty($selectedGrade)): ?>
                <a href="<?= url('') ?>" class="btn-sketch btn-sketch-sm" title="Hapus Filter">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<!-- Product Catalog Grid -->
<section aria-label="Daftar Produk Preloved">
    <?php if (empty($products)): ?>
        <!-- Empty State -->
        <div class="scrapbook-card" style="text-align: center; padding: 4rem 1.5rem; max-width: 550px; margin: 3rem auto;">
            <div style="width: 60px; height: 60px; margin: 0 auto 1rem auto; border: 2px dashed var(--color-ink); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-hand); font-size: 2rem;">
                ?
            </div>
            <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Belum Menemukan Pakaian yang Cocok</h3>
            <p style="color: var(--color-ink-muted); margin-bottom: 1.5rem;">
                Tidak ada pakaian yang sesuai dengan kata kunci atau filter yang Anda pilih saat ini.
            </p>
            <a href="<?= url('') ?>" class="btn-sketch btn-sketch-primary">
                Tampilkan Seluruh Koleksi
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 md:grid-cols-4 gap-4 polaroid-grid" style="align-items: stretch;">
            <?php foreach ($products as $p): ?>
                <?php
                    $isAvailable = ($p['status'] === 'available');
                    $isBooked = ($p['status'] === 'booked');
                    $isSold = ($p['status'] === 'sold');
                    
                    $price = (float)$p['price'];
                    $discountPrice = !empty($p['discount_price']) ? (float)$p['discount_price'] : null;
                    $effectivePrice = $discountPrice ?? $price;

                    $imageSrc = !empty($p['primary_image']) ? asset($p['primary_image']) : asset('assets/images/placeholder.svg');
                    $detailUrl = url('product/' . urlencode($p['slug']));

                    $gradeLabel = match ($p['condition_grade']) {
                        'like_new' => 'Like New',
                        'fair' => 'Fair',
                        default => 'Good',
                    };
                    $gradeBadgeClass = match ($p['condition_grade']) {
                        'like_new' => 'stamp-badge-likenew',
                        'fair' => 'stamp-badge-fair',
                        default => 'stamp-badge-good',
                    };
                ?>
                <article class="polaroid-card washi-tape">
                    <!-- Photo Box -->
                    <a href="<?= $detailUrl ?>" class="polaroid-photo-box" tabindex="-1">
                        <img src="<?= $imageSrc ?>" alt="<?= e($p['name']) ?>" class="polaroid-photo" loading="lazy">
                        
                        <!-- Status Stamp Overlay -->
                        <?php if ($isSold): ?>
                            <span class="stamp-badge stamp-badge-sold photo-stamp">TERJUAL</span>
                        <?php elseif ($isBooked): ?>
                            <span class="stamp-badge stamp-badge-booked photo-stamp">DIBOOKING</span>
                        <?php else: ?>
                            <span class="stamp-badge <?= $gradeBadgeClass ?> photo-stamp"><?= $gradeLabel ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Information -->
                    <div class="polaroid-info">
                        <div class="polaroid-meta">
                            <span>Ukuran: <strong><?= e($p['size']) ?></strong></span>
                            <span style="text-transform: capitalize;"><?= e($p['category']) ?></span>
                        </div>

                        <h2 class="polaroid-title">
                            <a href="<?= $detailUrl ?>"><?= e($p['name']) ?></a>
                        </h2>

                        <div class="polaroid-price-wrap">
                            <span class="polaroid-price"><?= format_rupiah($effectivePrice) ?></span>
                            <?php if ($discountPrice && $discountPrice < $price): ?>
                                <span class="polaroid-price-strike"><?= format_rupiah($price) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div style="margin-top: 0.75rem; display: flex; gap: 0.35rem;">
                            <?php if ($isAvailable): ?>
                                <button type="button" 
                                        class="btn-sketch btn-sketch-sm btn-sketch-primary js-add-to-cart" 
                                        style="width: 100%;"
                                        data-id="<?= (int)$p['id'] ?>"
                                        data-name="<?= e($p['name']) ?>"
                                        data-size="<?= e($p['size']) ?>"
                                        data-price="<?= $effectivePrice ?>"
                                        data-image="<?= $imageSrc ?>"
                                        data-slug="<?= e($p['slug']) ?>">
                                    + Ke Tas
                                </button>
                            <?php elseif ($isBooked): ?>
                                <button type="button" class="btn-sketch btn-sketch-sm" style="width: 100%;" disabled>
                                    Sedang Ditahan
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-sketch btn-sketch-sm" style="width: 100%;" disabled>
                                    Terjual
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
