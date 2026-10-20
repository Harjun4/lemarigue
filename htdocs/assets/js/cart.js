/**
 * Cart Manager (LocalStorage based)
 * Preloved Hand-Drawn Storefront (isi-lemarigue)
 * Rules: Stock = 1 per item (unique preloved piece)
 */

const Cart = {
    STORAGE_KEY: 'isi_lemarigue_cart_v1',

    getItems() {
        try {
            const raw = localStorage.getItem(this.STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    },

    saveItems(items) {
        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(items));
            this.updateBadge();
        } catch (e) {
            console.error('Failed to save cart to localStorage', e);
        }
    },

    addItem(product) {
        const items = this.getItems();
        const exists = items.some(item => String(item.id) === String(product.id));

        if (exists) {
            return { added: false, message: 'Barang ini sudah ada di tas belanja Anda.' };
        }

        items.push({
            id: Number(product.id),
            name: product.name,
            size: product.size,
            price: Number(product.price),
            image: product.image,
            slug: product.slug,
        });

        this.saveItems(items);
        this.renderDrawer();
        return { added: true, message: 'Barang berhasil dimasukkan ke tas belanja!' };
    },

    removeItem(productId) {
        const items = this.getItems().filter(item => String(item.id) !== String(productId));
        this.saveItems(items);
        this.renderDrawer();
        
        // If on checkout page, refresh checkout table
        if (typeof window.refreshCheckoutView === 'function') {
            window.refreshCheckoutView();
        }
    },

    clear() {
        localStorage.removeItem(this.STORAGE_KEY);
        this.updateBadge();
        this.renderDrawer();
    },

    getCount() {
        return this.getItems().length;
    },

    updateBadge() {
        const count = this.getCount();
        const badges = document.querySelectorAll('.cart-count-badge');
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        });

        const floatBtn = document.getElementById('floatingCartBtn');
        if (floatBtn) {
            floatBtn.style.display = count > 0 ? 'flex' : 'none';
        }
    },

    async validateWithServer() {
        const items = this.getItems();
        if (items.length === 0) {
            return { success: true, items: [], unavailable_count: 0, subtotal: 0 };
        }

        const ids = items.map(i => i.id);
        const baseUrl = window.APP_BASE_URL || '';

        try {
            const response = await fetch(`${baseUrl}/api/cart/validate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            });

            if (!response.ok) {
                throw new Error('Server error');
            }

            return await response.json();
        } catch (err) {
            console.warn('Could not validate cart with server:', err);
            return null;
        }
    },

    renderDrawer() {
        const container = document.getElementById('cartDrawerItems');
        const subtotalEl = document.getElementById('cartDrawerSubtotal');
        const checkoutBtn = document.getElementById('cartDrawerCheckoutBtn');
        if (!container) return;

        const items = this.getItems();

        if (items.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2.5rem 1rem;">
                    <p style="font-family: var(--font-hand); font-size: 1.6rem; margin-bottom: 0.5rem;">Tas Belanja Masih Kosong</p>
                    <p style="color: var(--color-ink-muted); font-size: 0.9rem;">Yuk telusuri rak pakaian vintage dan temukan item unik impianmu!</p>
                </div>
            `;
            if (subtotalEl) subtotalEl.textContent = 'Rp 0';
            if (checkoutBtn) checkoutBtn.style.display = 'none';
            return;
        }

        if (checkoutBtn) checkoutBtn.style.display = 'inline-flex';

        let subtotal = 0;
        let html = '';

        items.forEach(item => {
            subtotal += Number(item.price || 0);
            html += `
                <div class="cart-item-card" style="display: flex; gap: 0.85rem; padding: 0.75rem; border: var(--border-ink); border-radius: 6px; background-color: #FFFFFF; box-shadow: 2px 2px 0 var(--color-ink); align-items: center;">
                    <img src="${item.image}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid var(--color-ink); border-radius: 4px; flex-shrink: 0;">
                    <div style="flex-grow: 1; min-width: 0;">
                        <h4 style="font-size: 1.15rem; font-family: var(--font-hand); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.1rem;">${item.name}</h4>
                        <p style="font-size: 0.85rem; color: var(--color-ink-muted); margin-bottom: 0.2rem;">Ukuran: ${item.size}</p>
                        <p style="font-weight: 700; font-size: 0.95rem; color: var(--color-ink); margin: 0;">Rp ${Number(item.price).toLocaleString('id-ID')}</p>
                    </div>
                    <button type="button" onclick="Cart.removeItem(${item.id})" aria-label="Hapus ${item.name} dari tas" style="background: none; border: none; color: var(--color-terracotta); font-weight: bold; font-size: 1.25rem; cursor: pointer; padding: 0.25rem 0.5rem;">&times;</button>
                </div>
            `;
        });

        container.innerHTML = html;
        if (subtotalEl) {
            subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        }
    }
};

window.Cart = Cart;

document.addEventListener('DOMContentLoaded', () => {
    Cart.updateBadge();
    Cart.renderDrawer();
});
