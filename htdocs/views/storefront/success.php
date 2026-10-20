<?php
/**
 * Order Success Receipt View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Pesanan Terbuat ' . $order['order_code'];
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="max-width: 650px; margin: 0 auto; padding-bottom: 2.5rem;">
    <div class="receipt-box washi-tape washi-tape-angle-right">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <span class="stamp-badge stamp-badge-booked" style="margin-bottom: 0.5rem;">STATUS: MENUNGGU PEMBAYARAN</span>
            <h1 style="font-size: 2.2rem; margin-bottom: 0.25rem;">Pesanan Berhasil Dicatat!</h1>
            <p style="font-size: 1.15rem; font-weight: 700; color: var(--color-ink);">
                Kode Order: <span style="font-family: var(--font-hand); font-size: 1.6rem; color: var(--color-terracotta);"><?= e($order['order_code']) ?></span>
            </p>
        </div>

        <!-- Hold Alert -->
        <div style="background-color: var(--color-washi); border: 2px dashed var(--color-ink); border-radius: 6px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; text-align: center;">
            <p style="font-family: var(--font-hand); font-size: 1.4rem; font-weight: bold; margin-bottom: 0.25rem;">
                Barang Anda Sedang Dikunci (Hold <?= (int)$holdDuration ?> Menit)
            </p>
            <p style="font-size: 0.9rem; color: var(--color-ink); margin: 0;">
                Silakan lanjutkan membuka WhatsApp untuk mengirim konfirmasi dan mendapatkan total ongkir resmi serta nomor rekening.
            </p>
        </div>

        <!-- WhatsApp Big CTA -->
        <div style="margin-bottom: 2rem; text-align: center;">
            <a href="<?= e($waUrl) ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="btn-sketch btn-sketch-sage" 
               style="width: 100%; font-size: 1.45rem; padding: 0.95rem 1.5rem; text-decoration: none;">
                Buka WhatsApp & Kirim Pesanan Sekarang
            </a>
            <small style="display: block; margin-top: 0.5rem; color: var(--color-ink-muted);">
                Pesan telah terformat otomatis, tinggal tekan tombol kirim di aplikasi WhatsApp.
            </small>
        </div>

        <!-- Itemized Order Summary -->
        <h3 style="font-size: 1.4rem; margin-bottom: 0.75rem;">Rincian Barang:</h3>
        <div style="margin-bottom: 1.25rem;">
            <?php foreach ($order['items'] as $it): ?>
                <div class="receipt-item-row">
                    <div>
                        <strong><?= e($it['product_name']) ?></strong>
                        <span style="font-size: 0.8rem; color: var(--color-ink-muted); display: block;">Ukuran: <?= e($it['size']) ?></span>
                    </div>
                    <span style="font-weight: 700;"><?= format_rupiah($it['price_at_purchase']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="receipt-total-row" style="margin-bottom: 1.5rem;">
            <span>Subtotal Barang:</span>
            <span style="font-family: var(--font-hand); font-size: 1.8rem;"><?= format_rupiah($order['total_amount']) ?></span>
        </div>

        <!-- Buyer Information -->
        <div style="background-color: var(--color-paper); border: 1.5px solid var(--color-ink); border-radius: 6px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
            <p style="margin-bottom: 0.25rem;"><strong>Nama Penerima:</strong> <?= e($order['customer_name']) ?></p>
            <p style="margin-bottom: 0.25rem;"><strong>No. WhatsApp:</strong> <?= e($order['customer_phone']) ?></p>
            <p style="margin-bottom: 0.25rem;"><strong>Ekspedisi:</strong> <?= e($order['courier']) ?></p>
            <p style="margin: 0;"><strong>Alamat Pengiriman:</strong><br><?= nl2br(e($order['shipping_address'])) ?></p>
        </div>

        <!-- Bank Payment Accounts -->
        <?php if (!empty($bankDetails)): ?>
            <div style="background-color: #FFFFFF; border: 2px solid var(--color-ink); border-radius: 6px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; box-shadow: 2px 2px 0 var(--color-ink);">
                <h4 style="font-family: var(--font-hand); font-size: 1.35rem; margin-bottom: 0.35rem;">Rekening Pembayaran Resmi:</h4>
                <div style="font-family: monospace; font-size: 0.95rem; white-space: pre-line; background-color: var(--color-paper); padding: 0.75rem; border-radius: 4px; border: 1px dashed var(--color-ink); color: var(--color-ink);">
                    <?= e($bankDetails) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Backup Copy WhatsApp Draft -->
        <details style="margin-bottom: 1.5rem; cursor: pointer;">
            <summary style="font-weight: 600; font-size: 0.9rem; color: var(--color-ink);">
                Lihat salinan draf teks pesan WhatsApp
            </summary>
            <div style="margin-top: 0.75rem; position: relative;">
                <textarea id="waRawDraft" readonly style="width: 100%; min-height: 160px; font-family: monospace; font-size: 0.85rem; padding: 0.75rem; border: var(--border-ink); border-radius: 4px; background-color: var(--color-paper);"><?= e($waMessage) ?></textarea>
                <button type="button" id="copyWaBtn" class="btn-sketch btn-sketch-sm" style="position: absolute; top: 8px; right: 8px;">
                    Salin Teks
                </button>
            </div>
        </details>

        <!-- Home Button -->
        <div style="text-align: center;">
            <a href="<?= url('') ?>" class="btn-sketch btn-sketch-primary">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Clear cart once order is confirmed
    if (window.Cart) {
        Cart.clear();
    }

    const copyBtn = document.getElementById('copyWaBtn');
    const waText = document.getElementById('waRawDraft');
    if (copyBtn && waText) {
        copyBtn.addEventListener('click', () => {
            waText.select();
            navigator.clipboard.writeText(waText.value).then(() => {
                copyBtn.textContent = 'Tersalin!';
                setTimeout(() => { copyBtn.textContent = 'Salin Teks'; }, 2000);
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
