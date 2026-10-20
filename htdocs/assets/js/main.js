/**
 * Main UI Interactivity & Accessibility Controls
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Drawer elements
    const cartDrawer = document.getElementById('cartDrawer');
    const cartOverlay = document.getElementById('cartDrawerOverlay');
    const openCartButtons = document.querySelectorAll('.js-open-cart');
    const closeCartButtons = document.querySelectorAll('.js-close-cart');

    function openCart() {
        if (!cartDrawer || !cartOverlay) return;
        Cart.renderDrawer();
        cartDrawer.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus first actionable element inside drawer for keyboard accessibility
        const closeBtn = cartDrawer.querySelector('.cart-drawer-close');
        if (closeBtn) closeBtn.focus();
    }

    function closeCart() {
        if (!cartDrawer || !cartOverlay) return;
        cartDrawer.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    openCartButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openCart();
        });
    });

    closeCartButtons.forEach(btn => {
        btn.addEventListener('click', closeCart);
    });

    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCart);
    }

    // Escape key closes modals and drawers
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCart();
        }
    });

    // Handle "Tambah ke Tas" buttons
    const addToCartButtons = document.querySelectorAll('.js-add-to-cart');
    addToCartButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const product = {
                id: this.dataset.id,
                name: this.dataset.name,
                size: this.dataset.size,
                price: this.dataset.price,
                image: this.dataset.image,
                slug: this.dataset.slug,
            };

            const result = Cart.addItem(product);
            showToast(result.message, result.added ? 'success' : 'info');
            if (result.added) {
                // Animate floating cart button
                const floatBtn = document.getElementById('floatingCartBtn');
                if (floatBtn) {
                    floatBtn.style.transform = 'scale(1.15) rotate(-3deg)';
                    setTimeout(() => {
                        floatBtn.style.transform = '';
                    }, 250);
                }
            }
        });
    });

    // Gallery Thumbnail Switcher on Product Detail Page
    const mainGalleryImage = document.getElementById('mainGalleryImage');
    const galleryThumbs = document.querySelectorAll('.js-gallery-thumb');
    galleryThumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const fullSrc = this.dataset.full;
            if (mainGalleryImage && fullSrc) {
                mainGalleryImage.src = fullSrc;
                galleryThumbs.forEach(t => t.style.borderColor = 'var(--color-ink)');
                this.style.borderColor = 'var(--color-highlighter)';
            }
        });
    });

    // Toast Notification Maker
    function showToast(message, type = 'info') {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.cssText = 'position: fixed; bottom: 84px; left: 50%; transform: translateX(-50%); z-index: 1000; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; width: 90%; max-width: 380px;';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'alert-sketch alert-sketch-' + (type === 'success' ? 'success' : 'info');
        toast.style.cssText = 'pointer-events: auto; background-color: #FFFFFF; border: var(--border-ink); border-radius: 8px; box-shadow: var(--shadow-sketch); padding: 0.75rem 1.25rem; font-family: var(--font-sans); font-size: 0.95rem; font-weight: 600; text-align: center;';
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2800);
    }

    window.showToast = showToast;
});
