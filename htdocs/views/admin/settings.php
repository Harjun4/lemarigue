<?php
/**
 * Admin Store Settings View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Pengaturan Toko Preloved';
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="max-width: 760px; margin: 0 auto; padding-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 2.2rem; margin-bottom: 0.15rem;">Pengaturan Toko</h1>
            <p style="color: var(--color-ink-muted); margin: 0; font-size: 0.95rem;">
                Sesuaikan informasi profil toko, nomor WhatsApp, dan rekening pembayaran.
            </p>
        </div>
        <a href="<?= url('admin') ?>" class="btn-sketch btn-sketch-sm">&larr; Kembali ke Dasbor</a>
    </div>

    <div class="scrapbook-card washi-tape washi-tape-angle-left" style="background-color: var(--color-paper-light); padding: 2rem;">
        <form method="POST" action="<?= url('admin/settings') ?>">
            <!-- Store Name -->
            <div class="form-group">
                <label for="settingStoreName" class="form-label">Nama Toko Preloved:</label>
                <input type="text" id="settingStoreName" name="store_name" required value="<?= e($settings['store_name'] ?? 'Isi Lemarigue Preloved') ?>" class="input-sketch">
            </div>

            <!-- Tagline -->
            <div class="form-group">
                <label for="settingTagline" class="form-label">Slogan / Tagline Toko:</label>
                <input type="text" id="settingTagline" name="store_tagline" value="<?= e($settings['store_tagline'] ?? 'Koleksi pakaian preloved pilihan dengan cerita personal.') ?>" class="input-sketch">
            </div>

            <!-- WhatsApp Number -->
            <div class="form-group">
                <label for="settingWhatsApp" class="form-label">Nomor WhatsApp Penerima Order:*</label>
                <input type="text" id="settingWhatsApp" name="whatsapp_number" required value="<?= e($settings['whatsapp_number'] ?? '6281234567890') ?>" placeholder="6281234567890" class="input-sketch">
                <small style="color: var(--color-ink-muted); font-size: 0.8rem;">Gunakan format angka lengkap tanpa spasi atau tanda hubung (misal: 6281234567890).</small>
            </div>

            <!-- Hold Booking Duration -->
            <div class="form-group">
                <label for="settingHoldMinutes" class="form-label">Durasi Kunci Stok / Booking (Menit):</label>
                <input type="number" id="settingHoldMinutes" name="booking_hold_duration_minutes" required min="5" max="1440" value="<?= (int)($settings['booking_hold_duration_minutes'] ?? 60) ?>" class="input-sketch">
                <small style="color: var(--color-ink-muted); font-size: 0.8rem;">Waktu tenggat transfer sebelum stok pakaian otomatis dikembalikan ke katalog.</small>
            </div>

            <!-- Bank / QRIS Accounts -->
            <div class="form-group">
                <label for="settingBankDetails" class="form-label">Rincian Rekening Bank & QRIS:</label>
                <textarea id="settingBankDetails" name="bank_payment_details" rows="4" class="input-sketch"><?= e($settings['bank_payment_details'] ?? '') ?></textarea>
                <small style="color: var(--color-ink-muted); font-size: 0.8rem;">Informasi ini akan tertera pada nota receipt sukses pemesanan pembeli.</small>
            </div>

            <!-- Shipping Note -->
            <div class="form-group">
                <label for="settingShippingNote" class="form-label">Catatan Kebijakan Pengiriman:</label>
                <textarea id="settingShippingNote" name="shipping_note" rows="3" class="input-sketch"><?= e($settings['shipping_note'] ?? '') ?></textarea>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="<?= url('admin') ?>" class="btn-sketch">Batal</a>
                <button type="submit" class="btn-sketch btn-sketch-primary" style="font-size: 1.25rem; padding: 0.75rem 2rem;">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
