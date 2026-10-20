<?php
/**
 * Global Header Layout
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Setting.php';

$settingModel = new Setting();
$storeName = $settingModel->get('store_name', 'Isi Lemarigue Preloved');
$storeTagline = $settingModel->get('store_tagline', 'Koleksi pakaian preloved pilihan dengan cerita personal.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= e($storeName) ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? e($pageDescription) : e($storeTagline) ?>">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="<?= asset('assets/css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/hand-drawn.css') ?>">
    
    <script>
        window.APP_BASE_URL = '<?= rtrim(BASE_URL, '/') ?>';
    </script>
</head>
<body>

<!-- Scrapbook Notebook Header -->
<header class="notebook-header" role="banner">
    <div class="container">
        <nav class="notebook-nav" aria-label="Navigasi Utama">
            <a href="<?= url('') ?>" class="logo-brand">
                <span class="logo-pin" aria-hidden="true"></span>
                <span><?= e($storeName) ?></span>
            </a>

            <div class="flex items-center gap-3">
                <a href="<?= url('') ?>" class="btn-sketch btn-sketch-sm" style="display: none;" id="navHomeLink">Katalog</a>
                
                <!-- Cart Button with Counter Badge -->
                <button type="button" class="btn-sketch btn-sketch-sm js-open-cart" id="headerCartBtn" aria-label="Buka Tas Belanja">
                    <span>Tas Belanja</span>
                    <span class="cart-count-badge" style="display: none;">0</span>
                </button>

                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                    <a href="<?= url('admin') ?>" class="btn-sketch btn-sketch-sm btn-sketch-sage">Panel Admin</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main class="main-content" id="mainContent">
    <div class="container">
        <!-- Flash Message Alerts -->
        <?php if ($flashSuccess = get_flash('success')): ?>
            <div class="alert-sketch alert-sketch-success" role="alert">
                <span>[OK]</span>
                <span><?= e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashError = get_flash('error')): ?>
            <div class="alert-sketch alert-sketch-error" role="alert">
                <span>[Perhatian]</span>
                <span><?= e($flashError) ?></span>
            </div>
        <?php endif; ?>
