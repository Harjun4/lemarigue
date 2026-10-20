<?php
/**
 * Admin Orders List View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Daftar Pesanan Masuk';
require_once __DIR__ . '/../../layouts/header.php';

$filterStatus = $_GET['status'] ?? '';
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: var(--border-ink);">
    <div>
        <h1 style="font-size: 2.2rem; margin-bottom: 0.15rem;">Daftar Pesanan Masuk</h1>
        <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
            Pantau dan verifikasi pesanan yang dikonfirmasi pembeli melalui WhatsApp.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="<?= url('admin') ?>" class="btn-sketch btn-sketch-sm">&larr; Kembali ke Dasbor</a>
    </div>
</div>

<!-- Filter Bar -->
<form method="GET" action="<?= url('admin/orders') ?>" class="filter-bar" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; width: 100%; align-items: center;">
        <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Cari kode order, nama pembeli, atau nomor WhatsApp..." class="input-sketch" style="flex: 1 1 250px;">
        
        <select name="status" class="input-sketch" style="flex: 0 0 180px; cursor: pointer;">
            <option value="">Semua Status</option>
            <option value="waiting_payment" <?= ($filterStatus === 'waiting_payment') ? 'selected' : '' ?>>Menunggu Pembayaran</option>
            <option value="verified" <?= ($filterStatus === 'verified') ? 'selected' : '' ?>>Terverifikasi (Lunas)</option>
            <option value="cancelled" <?= ($filterStatus === 'cancelled') ? 'selected' : '' ?>>Dibatalkan</option>
        </select>

        <button type="submit" class="btn-sketch btn-sketch-sm btn-sketch-primary">Filter</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['status'])): ?>
            <a href="<?= url('admin/orders') ?>" class="btn-sketch btn-sketch-sm">Reset</a>
        <?php endif; ?>
    </div>
</form>

<!-- Orders Table -->
<div class="scrapbook-card" style="padding: 1rem; background-color: var(--color-paper-light);">
    <?php if (empty($orders)): ?>
        <p style="text-align: center; color: var(--color-ink-muted); padding: 3rem 0;">Belum ada pesanan yang sesuai dengan filter.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kode Order</th>
                        <th>Waktu Order</th>
                        <th>Pembeli</th>
                        <th>WhatsApp</th>
                        <th>Kurir</th>
                        <th>Total Belanja</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <?php
                            $stClass = match ($ord['payment_status']) {
                                'verified' => 'stamp-badge-available',
                                'cancelled' => 'stamp-badge-sold',
                                default => 'stamp-badge-booked',
                            };
                            $stLabel = match ($ord['payment_status']) {
                                'verified' => 'Terverifikasi (Lunas)',
                                'cancelled' => 'Dibatalkan',
                                default => 'Menunggu Pembayaran',
                            };
                        ?>
                        <tr>
                            <td>
                                <strong style="font-family: var(--font-hand); font-size: 1.25rem;"><?= e($ord['order_code']) ?></strong>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--color-ink-muted);">
                                <?= date('d M Y, H:i', strtotime($ord['created_at'])) ?>
                            </td>
                            <td><strong><?= e($ord['customer_name']) ?></strong></td>
                            <td>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ord['customer_phone']) ?>" target="_blank" rel="noopener" style="text-decoration: underline;">
                                    <?= e($ord['customer_phone']) ?>
                                </a>
                            </td>
                            <td><?= e($ord['courier']) ?></td>
                            <td><strong><?= format_rupiah($ord['total_amount']) ?></strong></td>
                            <td>
                                <span class="stamp-badge <?= $stClass ?>" style="font-size: 0.75rem; padding: 2px 6px;">
                                    <?= $stLabel ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= url('admin/orders/detail/' . $ord['id']) ?>" class="btn-sketch btn-sketch-sm" style="font-size: 0.85rem; padding: 0.25rem 0.65rem;">
                                    Rincian & Aksi
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
