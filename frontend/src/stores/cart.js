import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import axios from '../axios';
import { useAuthStore } from './auth';
import { useModalStore } from './modal';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const shouldOpenDrawer = ref(false);

    const authStore = useAuthStore();
    const modalStore = useModalStore();

    async function loadFromServer() {
        if (!authStore.isAuthenticated) {
            items.value = [];
            return;
        }

        try {
            const response = await axios.get('/cart');
            const serverItems = Array.isArray(response.data) ? response.data : [];

            items.value = serverItems.map((item) => {
                const rawPrice = item.price ?? item.sellable_price ?? 0;
                const numericPrice = typeof rawPrice === 'string'
                    ? parseFloat(rawPrice.replace(/[^\d.]/g, ''))
                    : rawPrice;

                // Format original price if available
                let formattedOriginalPrice = null;
                if (item.original_price) {
                    const rawOriginalPrice = typeof item.original_price === 'string'
                        ? parseFloat(item.original_price.replace(/[^\d.]/g, ''))
                        : item.original_price;
                    formattedOriginalPrice = `৳${rawOriginalPrice.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    })}`;
                }

                return {
                    ...item,
                    slug: item.slug || item.id || String(item.id),
                    price: `৳${numericPrice.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    })}`,
                    original_price: formattedOriginalPrice || item.original_price,
                    quantity: item.quantity ?? 1,
                };
            });
        } catch (error) {
            console.error('Failed to load cart from server:', error);
        }
    }

    // Initial load if authenticated
    if (authStore.isAuthenticated) {
        loadFromServer();
    }

    // React to login/logout
    watch(
        () => authStore.isAuthenticated,
        (isAuth) => {
            if (isAuth) {
                loadFromServer();
            } else {
                items.value = [];
            }
        }
    );

    const totalItems = computed(() => items.value.reduce((total, item) => total + item.quantity, 0));

    const subtotal = computed(() => {
        return items.value.reduce((total, item) => {
            // Remove currency symbol and commas to parse price
            const price = parseFloat(item.price.replace(/[^\d.]/g, ''));
            return total + (price * item.quantity);
        }, 0);
    });

    const formattedSubtotal = computed(() => {
        return '৳' + subtotal.value.toLocaleString();
    });

    /**
     * Generate a unique cart key for an item
     * For products with variants, use product_id + variant_id or attributes hash
     * For products without variants, use just product_id
     */
    function getCartItemKey(product, variant = null) {
        if (variant && variant.id) {
            // Use variant ID if available
            return `${product.id}_variant_${variant.id}`;
        } else if (variant && variant.attributes) {
            // Use attributes hash if no variant ID
            const attrsString = JSON.stringify(variant.attributes);
            return `${product.id}_attrs_${btoa(attrsString).replace(/[^a-zA-Z0-9]/g, '')}`;
        }
        // No variant, just use product ID
        return `${product.id}`;
    }

    async function addItem(product, variant = null, quantity = 1) {
        if (!authStore.isAuthenticated) {
            modalStore.openModal('login');
            return;
        }

        // Ensure slug is always present before processing
        const productWithSlug = {
            ...product,
            slug: product.slug || product.id || String(product.id) // Ensure slug is always set
        };
        
        try {
            await axios.post('/cart', {
                product_id: productWithSlug.id,
                product_variant_id: variant?.id || null,
                quantity,
            });
            await loadFromServer();
        } catch (error) {
            console.error('Failed to add item to cart:', error);
        }

        // Trigger drawer to open
        shouldOpenDrawer.value = true;
    }

    function closeDrawer() {
        shouldOpenDrawer.value = false;
    }

    async function removeItem(productId, variant = null) {
        const itemKey = variant ? getCartItemKey({ id: productId }, variant) : String(productId);
        const item = items.value.find((i) => {
            const existingKey = getCartItemKey(i, i.variant || null);
            return existingKey === itemKey;
        });

        if (!item || !item.cart_item_id) {
            return;
        }

        try {
            await axios.delete(`/cart/${item.cart_item_id}`);
            await loadFromServer();
        } catch (error) {
            console.error('Failed to remove item from cart:', error);
        }
    }

    async function updateQuantity(productId, quantity, variant = null) {
        const itemKey = variant ? getCartItemKey({ id: productId }, variant) : String(productId);
        const item = items.value.find(item => {
            const existingKey = getCartItemKey(item, item.variant || null);
            return existingKey === itemKey;
        });
        if (!item || !item.cart_item_id) {
            return;
        }

        if (quantity <= 0) {
            await removeItem(productId, variant);
            return;
        }

        try {
            await axios.patch(`/cart/${item.cart_item_id}`, { quantity });
            await loadFromServer();
        } catch (error) {
            console.error('Failed to update cart quantity:', error);
        }
    }

    async function clearCart() {
        try {
            await axios.post('/cart/clear');
            items.value = [];
        } catch (error) {
            console.error('Failed to clear cart:', error);
        }
    }

    return {
        items,
        totalItems,
        subtotal,
        formattedSubtotal,
        shouldOpenDrawer,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        closeDrawer
    };
});
