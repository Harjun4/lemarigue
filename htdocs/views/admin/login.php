<?php
/**
 * Admin Login View
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

$pageTitle = 'Masuk Pengelola Toko';
require_once __DIR__ . '/../layouts/header.php';
?>

<div style="max-width: 440px; margin: 3rem auto; padding-bottom: 2rem;">
    <div class="scrapbook-card washi-tape washi-tape-angle-left" style="background-color: var(--color-paper-light); padding: 2.25rem 2rem;">
        <div style="text-align: center; margin-bottom: 1.75rem;">
            <span class="stamp-badge stamp-badge-good" style="margin-bottom: 0.5rem;">PORTAL PENGELOLA</span>
            <h1 style="font-size: 2rem; margin-bottom: 0.25rem;">Masuk Admin</h1>
            <p style="font-size: 0.9rem; color: var(--color-ink-muted); margin: 0;">
                Kelola katalog preloved dan verifikasi pesanan masuk.
            </p>
        </div>

        <form method="POST" action="<?= url('admin/login') ?>">
            <div class="form-group">
                <label for="adminUsername" class="form-label">Nama Pengguna (Username):</label>
                <input type="text" id="adminUsername" name="username" required autocomplete="username" class="input-sketch" placeholder="admin">
            </div>

            <div class="form-group">
                <label for="adminPassword" class="form-label">Kata Sandi (Password):</label>
                <input type="password" id="adminPassword" name="password" required autocomplete="current-password" class="input-sketch" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>

            <button type="submit" class="btn-sketch btn-sketch-primary" style="width: 100%; font-size: 1.25rem; margin-top: 1rem; padding: 0.75rem;">
                Masuk ke Panel Dasbor
            </button>
        </form>

        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1.5px dashed var(--color-ink); text-align: center; font-size: 0.85rem; color: var(--color-ink-muted);">
            <p style="margin-bottom: 0.25rem;">Akun bawaan sistem:</p>
            <p style="font-family: monospace; font-weight: bold; color: var(--color-ink); margin: 0;">admin / admin123</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
