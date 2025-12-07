import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import axios from '../axios';
import { useAuthStore } from './auth';
import { useModalStore } from './modal';

export const useWishlistStore = defineStore('wishlist', () => {
    const items = ref([]);
    const authStore = useAuthStore();
    const modalStore = useModalStore();

    async function loadFromServer() {
        if (!authStore.isAuthenticated) {
            items.value = [];
            return;
        }

        try {
            const response = await axios.get('/wishlist');
            const serverItems = Array.isArray(response.data) ? response.data : [];

            items.value = serverItems.map((item) => {
                const rawPrice = item.price ?? item.sellable_price ?? 0;
                const numericPrice = typeof rawPrice === 'string'
                    ? parseFloat(rawPrice.replace(/[^\d.]/g, ''))
                    : rawPrice;

                return {
                    ...item,
                    slug: item.slug || item.id || String(item.id),
                    price: numericPrice,
                    total_stock: item.total_stock ?? item.stock ?? 0,
                };
            });
        } catch (error) {
            console.error('Failed to load wishlist from server:', error);
        }
    }

    if (authStore.isAuthenticated) {
        loadFromServer();
    }

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

    const totalItems = computed(() => items.value.length);

    async function addItem(product, variant = null) {
        if (!authStore.isAuthenticated) {
            modalStore.openModal('login');
            return;
        }

        try {
            await axios.post('/wishlist', {
                product_id: product.id,
                product_variant_id: variant?.id || null,
            });
            await loadFromServer();
        } catch (error) {
            console.error('Failed to add to wishlist:', error);
        }
    }

    async function removeItem(productId, variant = null) {
        const target = items.value.find((item) => {
            if (item.id !== productId) return false;
            if (variant && item.variant) {
                return item.variant.id === variant.id;
            }
            return !variant;
        });

        if (!target || !target.wishlist_item_id) {
            return;
        }

        try {
            await axios.delete(`/wishlist/${target.wishlist_item_id}`);
            await loadFromServer();
        } catch (error) {
            console.error('Failed to remove from wishlist:', error);
        }
    }

    function isInWishlist(productId) {
        return items.value.some(item => item.id === productId);
    }

    async function toggleItem(product, variant = null) {
        if (!authStore.isAuthenticated) {
            modalStore.openModal('login');
            return;
        }

        try {
            await axios.post('/wishlist/toggle', {
                product_id: product.id,
                product_variant_id: variant?.id || null,
            });
            await loadFromServer();
        } catch (error) {
            console.error('Failed to toggle wishlist:', error);
        }
    }

    return {
        items,
        totalItems,
        addItem,
        removeItem,
        isInWishlist,
        toggleItem,
    };
});
