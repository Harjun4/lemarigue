<?php
/**
 * Global Footer Layout
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */
require_once __DIR__ . '/../../models/Setting.php';
$settingModel = new Setting();
$storeName = $settingModel->get('store_name', 'Isi Lemarigue Preloved');
$storeTagline = $settingModel->get('store_tagline', 'Koleksi pakaian preloved pilihan dengan cerita personal.');
$whatsappNumber = $settingModel->get('whatsapp_number', '6281234567890');
$shippingNote = $settingModel->get('shipping_note', 'Pengiriman dari Tangerang.');
?>
    </div><!-- /.container -->
</main><!-- /#mainContent -->

<!-- Scrapbook Hand-Drawn Footer -->
<footer style="background-color: var(--color-paper-light); border-top: var(--border-ink); padding: 3rem 0 2rem 0; margin-top: auto; position: relative;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem; margin-bottom: 2.5rem;">
            <!-- Brand Column -->
            <div>
                <h3 style="font-size: 1.8rem; margin-bottom: 0.5rem;"><?= e($storeName) ?></h3>
                <p style="color: var(--color-ink-muted); margin-bottom: 1rem;"><?= e($storeTagline) ?></p>
                <div style="display: inline-block; padding: 0.35rem 0.75rem; background-color: var(--color-washi); border: 1.5px dashed var(--color-ink); border-radius: 4px; font-family: var(--font-hand); font-size: 1.15rem; font-weight: bold;">
                    Stok Satuan (One-of-a-kind)
                </div>
            </div>

            <!-- Notes & Shipping Column -->
            <div>
                <h4 style="font-family: var(--font-hand); font-size: 1.4rem; margin-bottom: 0.5rem;">Informasi Pengiriman</h4>
                <p style="color: var(--color-ink-muted); font-size: 0.925rem; margin-bottom: 0.5rem;"><?= e($shippingNote) ?></p>
                <p style="color: var(--color-ink-muted); font-size: 0.925rem;">Setiap pakaian telah dicuci bersih dan siap langsung digunakan.</p>
            </div>

            <!-- Contact & Admin Column -->
            <div>
                <h4 style="font-family: var(--font-hand); font-size: 1.4rem; margin-bottom: 0.5rem;">Bantuan & Kontak</h4>
                <p style="margin-bottom: 0.5rem;">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsappNumber) ?>" target="_blank" rel="noopener" style="text-decoration: underline; font-weight: 600;">
                        WhatsApp: +<?= e($whatsappNumber) ?>
                    </a>
                </p>
                <p style="font-size: 0.85rem; margin-top: 1rem;">
                    <?php if (empty($_SESSION['admin_logged_in'])): ?>
                        <a href="<?= url('admin/login') ?>" style="color: var(--color-ink-light); text-decoration: underline;">Masuk Sebagai Pengelola Toko</a>
                    <?php else: ?>
                        <a href="<?= url('admin') ?>" style="font-weight: 600; text-decoration: underline;">Buka Ruang Pengelola</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div style="border-top: 1.5px dashed var(--color-ink); padding-top: 1.5rem; text-align: center; font-size: 0.85rem; color: var(--color-ink-muted);">
            <p>&copy; <?= date('Y') ?> <?= e($storeName) ?>. Dibuat dengan cinta untuk pecinta thrifting.</p>
        </div>
    </div>
</footer>

<!-- Floating Cart Button for Quick Access -->
<button type="button" class="floating-cart-btn js-open-cart" id="floatingCartBtn" style="display: none;" aria-label="Buka Keranjang Belanja">
    <span>Tas Belanja</span>
    <span class="cart-count-badge">0</span>
</button>

<!-- Slide-out Cart Drawer Modal -->
<div class="cart-drawer-overlay" id="cartDrawerOverlay" aria-hidden="true"></div>
<aside class="cart-drawer" id="cartDrawer" role="dialog" aria-modal="true" aria-label="Tas Belanja Anda">
    <div class="cart-drawer-header">
        <h3 id="cartDrawerTitle">Tas Belanja</h3>
        <button type="button" class="cart-drawer-close js-close-cart" aria-label="Tutup Tas Belanja">&times;</button>
    </div>

    <div class="cart-drawer-body" id="cartDrawerItems">
        <!-- Rendered dynamically by cart.js -->
    </div>

    <div class="cart-drawer-footer">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1rem;">
            <span style="font-weight: 600;">Subtotal Barang:</span>
            <span id="cartDrawerSubtotal" style="font-family: var(--font-hand); font-size: 1.7rem; font-weight: 700; color: var(--color-ink);">Rp 0</span>
        </div>
        <a href="<?= url('checkout') ?>" class="btn-sketch btn-sketch-primary" id="cartDrawerCheckoutBtn" style="width: 100%; display: none;">
            Lanjut ke Checkout
        </a>
    </div>
</aside>

<!-- Core Scripts -->
<script src="<?= asset('assets/js/cart.js') ?>"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>

</body>
</html>
