<?php
/**
 * Admin Dashboard View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Dasbor Pengelola';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Admin Navigation Subbar -->
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: var(--border-ink);">
    <div>
        <h1 style="font-size: 2.4rem; margin-bottom: 0.15rem;">Dasbor Pengelola Toko</h1>
        <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
            Halo, <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>! Kelola katalog dan pesanan masuk dari sini.
        </p>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="<?= url('admin/products/create') ?>" class="btn-sketch btn-sketch-sm btn-sketch-primary">+ Tambah Barang</a>
        <a href="<?= url('admin/products') ?>" class="btn-sketch btn-sketch-sm">Katalog Produk</a>
        <a href="<?= url('admin/orders') ?>" class="btn-sketch btn-sketch-sm">Semua Pesanan</a>
        <a href="<?= url('admin/settings') ?>" class="btn-sketch btn-sketch-sm">Pengaturan Toko</a>
        <a href="<?= url('admin/logout') ?>" class="btn-sketch btn-sketch-sm btn-sketch-terracotta" style="margin-left: 0.5rem;">Keluar</a>
    </div>
</div>

<!-- Metric Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
    <!-- Total Revenue -->
    <div class="scrapbook-card" style="margin: 0; background-color: #FFFFFF; border-left: 6px solid var(--color-sage);">
        <p style="font-size: 0.85rem; font-weight: 600; color: var(--color-ink-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Pendapatan Terverifikasi</p>
        <h2 style="font-size: 2rem; color: var(--color-sage); margin: 0;"><?= format_rupiah($orderStats['total_revenue']) ?></h2>
        <span style="font-size: 0.8rem; color: var(--color-ink-muted);"><?= (int)$orderStats['verified_orders'] ?> pesanan terbayar</span>
    </div>

    <!-- Waiting Payment -->
    <div class="scrapbook-card" style="margin: 0; background-color: #FFFFFF; border-left: 6px solid #9A6B0A;">
        <p style="font-size: 0.85rem; font-weight: 600; color: var(--color-ink-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Menunggu Pembayaran</p>
        <h2 style="font-size: 2rem; color: #9A6B0A; margin: 0;"><?= (int)$orderStats['waiting_orders'] ?> Order</h2>
        <a href="<?= url('admin/orders?status=waiting_payment') ?>" style="font-size: 0.8rem; text-decoration: underline; color: #9A6B0A;">Lihat yang perlu dicek &rarr;</a>
    </div>

    <!-- Total Orders -->
    <div class="scrapbook-card" style="margin: 0; background-color: #FFFFFF; border-left: 6px solid var(--color-highlighter);">
        <p style="font-size: 0.85rem; font-weight: 600; color: var(--color-ink-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Total Seluruh Pesanan</p>
        <h2 style="font-size: 2rem; margin: 0;"><?= (int)$orderStats['total_orders'] ?> Order</h2>
        <span style="font-size: 0.8rem; color: var(--color-ink-muted);">Sepanjang waktu</span>
    </div>

    <!-- Inventory Stats -->
    <div class="scrapbook-card" style="margin: 0; background-color: #FFFFFF; border-left: 6px solid var(--color-ink);">
        <p style="font-size: 0.85rem; font-weight: 600; color: var(--color-ink-muted); text-transform: uppercase; margin-bottom: 0.35rem;">Status Stok Inventaris</p>
        <div style="display: flex; gap: 0.75rem; align-items: baseline; margin-top: 0.25rem;">
            <span style="font-size: 1.25rem; font-weight: 700; color: var(--color-sage);"><?= (int)$inventoryStats['available'] ?> Tersedia</span>
            <span style="font-size: 0.95rem; font-weight: 600; color: #9A6B0A;"><?= (int)$inventoryStats['booked'] ?> Hold</span>
            <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-terracotta);"><?= (int)$inventoryStats['sold'] ?> Sold</span>
        </div>
        <span style="font-size: 0.8rem; color: var(--color-ink-muted);"><?= (int)$inventoryStats['total'] ?> total pakaian tercatat</span>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="scrapbook-card" style="background-color: var(--color-paper-light);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h3 style="font-size: 1.8rem; margin: 0;">Pesanan Masuk Terbaru</h3>
            <p style="color: var(--color-ink-muted); font-size: 0.9rem; margin: 0;">Daftar 5 pesanan terakhir yang masuk ke sistem.</p>
        </div>
        <a href="<?= url('admin/orders') ?>" class="btn-sketch btn-sketch-sm">Lihat Semua Pesanan &rarr;</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <p style="text-align: center; color: var(--color-ink-muted); padding: 2rem 0;">Belum ada pesanan yang masuk ke sistem.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kode Order</th>
                        <th>Pembeli</th>
                        <th>No. WhatsApp</th>
                        <th>Ekspedisi</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $ro): ?>
                        <?php
                            $stClass = match ($ro['payment_status']) {
                                'verified' => 'stamp-badge-available',
                                'cancelled' => 'stamp-badge-sold',
                                default => 'stamp-badge-booked',
                            };
                            $stLabel = match ($ro['payment_status']) {
                                'verified' => 'Terverifikasi (Lunas)',
                                'cancelled' => 'Dibatalkan',
                                default => 'Menunggu Pembayaran',
                            };
                        ?>
                        <tr>
                            <td>
                                <strong style="font-family: var(--font-hand); font-size: 1.25rem;"><?= e($ro['order_code']) ?></strong>
                            </td>
                            <td><?= e($ro['customer_name']) ?></td>
                            <td><?= e($ro['customer_phone']) ?></td>
                            <td><?= e($ro['courier']) ?></td>
                            <td><strong><?= format_rupiah($ro['total_amount']) ?></strong></td>
                            <td>
                                <span class="stamp-badge <?= $stClass ?>" style="font-size: 0.8rem; padding: 2px 6px;">
                                    <?= $stLabel ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('admin/orders/detail/' . $ro['id']) ?>" class="btn-sketch btn-sketch-sm" style="font-size: 0.9rem; padding: 0.2rem 0.6rem;">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
