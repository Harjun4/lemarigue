<?php
/**
 * Admin Products Management Index View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Manajemen Produk Preloved';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: var(--border-ink);">
    <div>
        <h1 style="font-size: 2.2rem; margin-bottom: 0.15rem;">Katalog Produk Preloved</h1>
        <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
            Daftar seluruh pakaian satuan (one-of-a-kind) dalam sistem.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?= url('admin') ?>" class="btn-sketch btn-sketch-sm">&larr; Kembali ke Dasbor</a>
        <a href="<?= url('admin/products/create') ?>" class="btn-sketch btn-sketch-sm btn-sketch-primary">+ Tambah Barang Baru</a>
    </div>
</div>

<!-- Product Filter & Search Bar -->
<form method="GET" action="<?= url('admin/products') ?>" class="filter-bar" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; width: 100%; align-items: center;">
        <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Cari nama pakaian atau catatan..." class="input-sketch" style="flex: 1 1 200px;">
        
        <select name="category" class="input-sketch" style="flex: 0 0 150px; cursor: pointer;">
            <option value="">Semua Kategori</option>
            <option value="tops" <?= (($_GET['category'] ?? '') === 'tops') ? 'selected' : '' ?>>Atasan</option>
            <option value="bottoms" <?= (($_GET['category'] ?? '') === 'bottoms') ? 'selected' : '' ?>>Bawahan</option>
            <option value="outerwear" <?= (($_GET['category'] ?? '') === 'outerwear') ? 'selected' : '' ?>>Outerwear</option>
            <option value="accessories" <?= (($_GET['category'] ?? '') === 'accessories') ? 'selected' : '' ?>>Aksesori</option>
            <option value="others" <?= (($_GET['category'] ?? '') === 'others') ? 'selected' : '' ?>>Lainnya</option>
        </select>

        <select name="status" class="input-sketch" style="flex: 0 0 150px; cursor: pointer;">
            <option value="">Semua Status</option>
            <option value="available" <?= (($_GET['status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
            <option value="booked" <?= (($_GET['status'] ?? '') === 'booked') ? 'selected' : '' ?>>Booked</option>
            <option value="sold" <?= (($_GET['status'] ?? '') === 'sold') ? 'selected' : '' ?>>Sold Out</option>
        </select>

        <button type="submit" class="btn-sketch btn-sketch-sm btn-sketch-primary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['category']) || !empty($_GET['status'])): ?>
            <a href="<?= url('admin/products') ?>" class="btn-sketch btn-sketch-sm">Reset</a>
        <?php endif; ?>
    </div>
</form>

<!-- Products Table -->
<div class="scrapbook-card" style="padding: 1rem; background-color: var(--color-paper-light);">
    <?php if (empty($products)): ?>
        <p style="text-align: center; color: var(--color-ink-muted); padding: 3rem 0;">Belum ada produk yang cocok dengan pencarian.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Foto</th>
                        <th>Nama Barang & Rincian</th>
                        <th>Kategori</th>
                        <th>Ukuran</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <?php
                            $imgSrc = !empty($p['primary_image']) ? asset($p['primary_image']) : asset('assets/images/placeholder.svg');
                            $statusBadge = match ($p['status']) {
                                'available' => 'stamp-badge-available',
                                'booked' => 'stamp-badge-booked',
                                'sold' => 'stamp-badge-sold',
                            };
                            $statusLabel = match ($p['status']) {
                                'available' => 'Available',
                                'booked' => 'Hold Booking',
                                'sold' => 'Sold Out',
                            };
                        ?>
                        <tr>
                            <td>
                                <img src="<?= $imgSrc ?>" alt="" style="width: 54px; height: 54px; object-fit: cover; border: 1.5px solid var(--color-ink); border-radius: 4px;">
                            </td>
                            <td>
                                <a href="<?= url('product/' . urlencode($p['slug'])) ?>" target="_blank" style="font-weight: 700; text-decoration: underline;">
                                    <?= e($p['name']) ?>
                                </a>
                                <span style="display: block; font-size: 0.8rem; color: var(--color-ink-muted);">
                                    Grade: <?= e(ucwords(str_replace('_', ' ', $p['condition_grade']))) ?>
                                </span>
                            </td>
                            <td style="text-transform: capitalize;"><?= e($p['category']) ?></td>
                            <td><strong><?= e($p['size']) ?></strong></td>
                            <td>
                                <strong><?= format_rupiah($p['discount_price'] ?? $p['price']) ?></strong>
                                <?php if (!empty($p['discount_price'])): ?>
                                    <span style="display: block; font-size: 0.8rem; text-decoration: line-through; color: var(--color-terracotta);">
                                        <?= format_rupiah($p['price']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="stamp-badge <?= $statusBadge ?>" style="font-size: 0.75rem; padding: 2px 6px;">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.35rem; justify-content: flex-end;">
                                    <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="btn-sketch btn-sketch-sm" style="font-size: 0.85rem; padding: 0.2rem 0.5rem;" title="Ubah Rincian">
                                        Edit
                                    </a>
                                    <form method="POST" action="<?= url('admin/products/delete/' . $p['id']) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display: inline;">
                                        <button type="submit" class="btn-sketch btn-sketch-sm btn-sketch-terracotta" style="font-size: 0.85rem; padding: 0.2rem 0.5rem;" title="Hapus Produk">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
