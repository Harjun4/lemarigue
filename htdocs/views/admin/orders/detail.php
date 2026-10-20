<?php
/**
 * Admin Order Detail View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Rincian Pesanan ' . $order['order_code'];
require_once __DIR__ . '/../../layouts/header.php';

$stClass = match ($order['payment_status']) {
    'verified' => 'stamp-badge-available',
    'cancelled' => 'stamp-badge-sold',
    default => 'stamp-badge-booked',
};

$stLabel = match ($order['payment_status']) {
    'verified' => 'Terverifikasi (Lunas)',
    'cancelled' => 'Dibatalkan',
    default => 'Menunggu Pembayaran',
};

$phoneClean = preg_replace('/[^0-9]/', '', $order['customer_phone']);
if (str_starts_with($phoneClean, '0')) {
    $phoneClean = '62' . substr($phoneClean, 1);
}
?>

<div style="max-width: 800px; margin: 0 auto; padding-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h1 style="font-size: 2.2rem; margin-bottom: 0.15rem;">Rincian Pesanan</h1>
            <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
                Kode: <strong><?= e($order['order_code']) ?></strong> &bull; Dibuat pada <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
            </p>
        </div>
        <a href="<?= url('admin/orders') ?>" class="btn-sketch btn-sketch-sm">&larr; Kembali ke Daftar Pesanan</a>
    </div>

    <!-- Order Status & Quick Actions Card -->
    <div class="scrapbook-card washi-tape washi-tape-angle-left" style="background-color: var(--color-paper-light); margin-bottom: 1.5rem; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="font-size: 0.85rem; color: var(--color-ink-muted); display: block; margin-bottom: 0.25rem;">Status Transaksi Saat Ini:</span>
                <span class="stamp-badge <?= $stClass ?>" style="font-size: 1.15rem;">
                    <?= $stLabel ?>
                </span>
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <?php if ($order['payment_status'] !== 'verified'): ?>
                    <form method="POST" action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" onsubmit="return confirm('Verifikasi pembayaran? Seluruh barang dalam pesanan ini akan otomatis ditandai Sold Out.');">
                        <input type="hidden" name="payment_status" value="verified">
                        <button type="submit" class="btn-sketch btn-sketch-sage">
                            Verifikasi Pembayaran (Sold Out)
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($order['payment_status'] !== 'cancelled'): ?>
                    <form method="POST" action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" onsubmit="return confirm('Batalkan pesanan ini? Stok barang akan otomatis dikembalikan ke katalog Available.');">
                        <input type="hidden" name="payment_status" value="cancelled">
                        <button type="submit" class="btn-sketch btn-sketch-terracotta">
                            Batalkan Pesanan (Kembalikan Stok)
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($order['payment_status'] !== 'waiting_payment'): ?>
                    <form method="POST" action="<?= url('admin/orders/' . $order['id'] . '/status') ?>">
                        <input type="hidden" name="payment_status" value="waiting_payment">
                        <button type="submit" class="btn-sketch">
                            Ubah ke Menunggu Pembayaran
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Customer & Shipping Information -->
    <div class="scrapbook-card" style="background-color: #FFFFFF; margin-bottom: 1.5rem; padding: 1.5rem;">
        <h3 style="font-size: 1.4rem; margin-bottom: 0.75rem; border-bottom: 1.5px dashed var(--color-ink); padding-bottom: 0.5rem;">
            Data Penerima & Pengiriman
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <span style="font-size: 0.85rem; color: var(--color-ink-muted);">Nama Pembeli:</span>
                <p style="font-weight: 700; margin: 0; font-size: 1.05rem;"><?= e($order['customer_name']) ?></p>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: var(--color-ink-muted);">WhatsApp Pembeli:</span>
                <p style="margin: 0; font-weight: 700;">
                    <a href="https://wa.me/<?= $phoneClean ?>" target="_blank" rel="noopener" style="color: var(--color-sage); text-decoration: underline;">
                        <?= e($order['customer_phone']) ?> (Buka Chat)
                    </a>
                </p>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: var(--color-ink-muted);">Pilihan Kurir:</span>
                <p style="font-weight: 700; margin: 0;"><?= e($order['courier']) ?></p>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: var(--color-ink-muted);">Total Belanja:</span>
                <p style="font-weight: 700; margin: 0; font-size: 1.25rem;"><?= format_rupiah($order['total_amount']) ?></p>
            </div>
        </div>

        <div>
            <span style="font-size: 0.85rem; color: var(--color-ink-muted);">Alamat Lengkap Pengiriman:</span>
            <p style="background-color: var(--color-paper); padding: 0.75rem 1rem; border-radius: 6px; border: 1px solid #DDD8CE; margin-top: 0.25rem; white-space: pre-line;">
                <?= e($order['shipping_address']) ?>
            </p>
        </div>
    </div>

    <!-- Items in Order -->
    <div class="scrapbook-card" style="background-color: #FFFFFF; margin-bottom: 1.5rem; padding: 1.5rem;">
        <h3 style="font-size: 1.4rem; margin-bottom: 0.75rem; border-bottom: 1.5px dashed var(--color-ink); padding-bottom: 0.5rem;">
            Barang yang Dipesan
        </h3>
        <table class="admin-table" style="margin-top: 0;">
            <thead>
                <tr>
                    <th style="width: 60px;">Foto</th>
                    <th>Nama Pakaian</th>
                    <th>Ukuran</th>
                    <th>Kondisi</th>
                    <th style="text-align: right;">Harga Beli</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $it): ?>
                    <?php $itemImg = !empty($it['product_image']) ? asset($it['product_image']) : asset('assets/images/placeholder.svg'); ?>
                    <tr>
                        <td>
                            <img src="<?= $itemImg ?>" alt="" style="width: 48px; height: 48px; object-fit: cover; border: 1px solid var(--color-ink); border-radius: 4px;">
                        </td>
                        <td>
                            <a href="<?= url('product/' . urlencode($it['product_slug'])) ?>" target="_blank" style="text-decoration: underline; font-weight: 600;">
                                <?= e($it['product_name']) ?>
                            </a>
                        </td>
                        <td><?= e($it['size']) ?></td>
                        <td><span style="text-transform: capitalize;"><?= e(str_replace('_', ' ', $it['condition_grade'])) ?></span></td>
                        <td style="text-align: right; font-weight: 700;"><?= format_rupiah($it['price_at_purchase']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: 700; font-size: 1.1rem; padding-top: 1rem;">Total Tagihan:</td>
                    <td style="text-align: right; font-weight: 700; font-size: 1.25rem; color: var(--color-ink); padding-top: 1rem;"><?= format_rupiah($order['total_amount']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- WhatsApp Generated Message Log -->
    <?php if (!empty($order['whatsapp_message'])): ?>
        <div class="scrapbook-card" style="background-color: var(--color-paper-light); padding: 1.5rem;">
            <h4 style="font-family: var(--font-hand); font-size: 1.4rem; margin-bottom: 0.35rem;">
                Salinan Pesan WhatsApp Otomatis:
            </h4>
            <pre style="background-color: #FFFFFF; border: 1.5px dashed var(--color-ink); border-radius: 6px; padding: 1rem; font-family: monospace; font-size: 0.85rem; white-space: pre-wrap; color: var(--color-ink);"><?= e($order['whatsapp_message']) ?></pre>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
