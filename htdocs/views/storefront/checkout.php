<?php
/**
 * Receipt-Style Checkout View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Nota Pemesanan & Checkout';
require_once __DIR__ . '/../layouts/header.php';

$directId = isset($_GET['direct']) ? (int)$_GET['direct'] : 0;
$directProduct = null;
if ($directId > 0) {
    $productModel = new Product();
    $directProduct = $productModel->getById($directId);
}
?>

<div style="max-width: 650px; margin: 0 auto; padding-bottom: 2rem;">
    <!-- Receipt Envelope Box -->
    <div class="receipt-box washi-tape washi-tape-angle-left">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div style="margin-bottom: 0.5rem;">
                <img src="<?= asset('assets/images/logo.jpg') ?>" alt="Logo Toko" style="width: 64px; height: 64px; object-fit: contain; border-radius: 10px; border: 2px solid var(--color-ink); background: #ffffff; padding: 3px; display: inline-block;">
            </div>
            <span class="stamp-badge stamp-badge-good" style="margin-bottom: 0.5rem; display: inline-block;">NOTA PRA-PEMESANAN</span>
            <h2>Lembar Pesanan Preloved</h2>
            <p style="font-size: 0.875rem; color: var(--color-ink-muted); margin: 0;">
                Stok Satuan &bull; Konfirmasi Pembayaran Langsung via WhatsApp
            </p>
        </div>

        <!-- Cart Items List in Receipt -->
        <div id="checkoutItemsContainer">
            <p style="text-align: center; color: var(--color-ink-muted); font-size: 0.95rem;">
                Memuat rincian pakaian di tas belanja Anda...
            </p>
        </div>

        <hr class="receipt-divider">

        <!-- Checkout Form -->
        <form method="POST" action="<?= url('order/process') ?>" id="checkoutForm" style="display: none;">
            <input type="hidden" name="item_ids" id="formItemIds" value="">

            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Data Pengiriman Paket</h3>

            <div class="form-group">
                <label for="customerName" class="form-label">Nama Lengkap Penerima:</label>
                <input type="text" id="customerName" name="customer_name" required placeholder="Contoh: Budi Santoso" class="input-sketch">
            </div>

            <div class="form-group">
                <label for="customerPhone" class="form-label">Nomor WhatsApp Aktif:</label>
                <input type="tel" id="customerPhone" name="customer_phone" required placeholder="Contoh: 081234567890" class="input-sketch">
                <small style="color: var(--color-ink-muted); font-size: 0.8rem;">Draf konfirmasi order akan dikirimkan melalui nomor ini.</small>
            </div>

            <div class="form-group">
                <label for="shippingAddress" class="form-label">Alamat Lengkap Pengiriman:</label>
                <textarea id="shippingAddress" name="shipping_address" required placeholder="Jl. Anggrek No. 12, RT 02/RW 04, Kel. Kebayoran Lama, Kec. Kebayoran Baru, Jakarta Selatan 12240" class="input-sketch"></textarea>
            </div>

            <div class="form-group">
                <label for="courierSelect" class="form-label">Pilihan Jasa Ekspedisi:</label>
                <select id="courierSelect" name="courier" required class="input-sketch" style="cursor: pointer;">
                    <option value="" disabled selected>Pilih Kurir Pengiriman...</option>
                    <?php foreach ($couriers as $cr): ?>
                        <option value="<?= e($cr) ?>"><?= e($cr) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--color-ink-muted); font-size: 0.8rem;">Total ongkir riil akan dihitungkan penjual saat pesan WhatsApp diterima.</small>
            </div>

            <div class="form-group">
                <label for="orderNotes" class="form-label">Catatan Tambahan (Opsional):</label>
                <input type="text" id="orderNotes" name="notes" placeholder="Contoh: Titipkan di pos satpam atau info patokan rumah" class="input-sketch">
            </div>

            <!-- Anti Double-Booking Hold Notice -->
            <div style="background-color: var(--color-washi); border: 1.5px dashed var(--color-ink); border-radius: 6px; padding: 0.85rem 1rem; margin: 1.5rem 0; font-size: 0.875rem;">
                <p style="font-weight: 700; margin-bottom: 0.25rem;">[Pemberitahuan Sistem Anti-Rebutan Stok]</p>
                <p style="margin: 0; color: var(--color-ink);">
                    Saat Anda menekan tombol di bawah, status barang akan otomatis dikunci (Hold) selama <strong><?= (int)$holdDuration ?> menit</strong> khusus untuk Anda, agar tidak bisa dipesan pembeli lain sembari Anda menyelesaikan transfer.
                </p>
            </div>

            <button type="submit" class="btn-sketch btn-sketch-primary" style="width: 100%; font-size: 1.35rem; padding: 0.85rem;">
                Kunci Pesanan & Buka WhatsApp
            </button>
        </form>

        <!-- Empty Cart Notice -->
        <div id="checkoutEmptyNotice" style="display: none; text-align: center; padding: 2rem 1rem;">
            <p style="font-family: var(--font-hand); font-size: 1.6rem; margin-bottom: 0.5rem;">Tidak Ada Barang untuk Dicheckout</p>
            <p style="color: var(--color-ink-muted); margin-bottom: 1.5rem;">Keranjang Anda kosong atau barang yang dipilih sudah tidak tersedia.</p>
            <a href="<?= url('') ?>" class="btn-sketch btn-sketch-primary">Kembali ke Katalog</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const directProduct = <?= $directProduct ? json_encode([
        'id' => (int)$directProduct['id'],
        'name' => $directProduct['name'],
        'size' => $directProduct['size'],
        'price' => (float)($directProduct['discount_price'] ?? $directProduct['price']),
        'image' => !empty($directProduct['images'][0]['image_path']) ? asset($directProduct['images'][0]['image_path']) : asset('assets/images/placeholder.svg'),
        'slug' => $directProduct['slug'],
        'status' => $directProduct['status']
    ]) : 'null' ?>;

    if (directProduct && directProduct.status === 'available') {
        Cart.addItem(directProduct);
    }

    window.refreshCheckoutView = async function() {
        const items = Cart.getItems();
        const container = document.getElementById('checkoutItemsContainer');
        const form = document.getElementById('checkoutForm');
        const emptyNotice = document.getElementById('checkoutEmptyNotice');
        const itemIdsInput = document.getElementById('formItemIds');

        if (!items || items.length === 0) {
            container.innerHTML = '';
            form.style.display = 'none';
            emptyNotice.style.display = 'block';
            return;
        }

        // Validate items with server
        const validation = await Cart.validateWithServer();
        if (validation && validation.items) {
            let hasUnavailable = false;
            let subtotal = 0;
            let html = '<div style="margin-bottom: 1rem;">';

            const activeIds = [];

            validation.items.forEach(it => {
                if (!it.is_available) {
                    hasUnavailable = true;
                    html += `
                        <div class="receipt-item-row" style="opacity: 0.6; text-decoration: line-through;">
                            <span>${it.name} (${it.size})</span>
                            <span style="color: var(--color-terracotta);">[Sudah Terjual/Hold]</span>
                        </div>
                    `;
                } else {
                    activeIds.push(it.id);
                    subtotal += it.effective_price;
                    html += `
                        <div class="receipt-item-row">
                            <div>
                                <strong>${it.name}</strong>
                                <span style="font-size: 0.8rem; color: var(--color-ink-muted); display: block;">Ukuran: ${it.size}</span>
                            </div>
                            <span style="font-weight: 700;">${it.price_formatted}</span>
                        </div>
                    `;
                }
            });

            html += `</div>`;
            html += `
                <div class="receipt-total-row">
                    <span>Total Belanja:</span>
                    <span style="font-family: var(--font-hand); font-size: 1.8rem; color: var(--color-ink);">${validation.subtotal_formatted}</span>
                </div>
            `;

            if (hasUnavailable) {
                html += `
                    <div class="alert-sketch alert-sketch-error" style="margin-top: 1rem; font-size: 0.85rem;">
                        Perhatian: Salah satu barang di tas Anda telah dipesan orang lain dan tidak diikutsertakan.
                    </div>
                `;
            }

            container.innerHTML = html;

            if (activeIds.length > 0) {
                form.style.display = 'block';
                emptyNotice.style.display = 'none';
                itemIdsInput.value = JSON.stringify(activeIds);
            } else {
                form.style.display = 'none';
                emptyNotice.style.display = 'block';
            }
        }
    };

    await window.refreshCheckoutView();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
