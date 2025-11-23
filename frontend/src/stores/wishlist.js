import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useWishlistStore = defineStore('wishlist', () => {
    const items = ref([]);

    // Load from local storage on init
    if (localStorage.getItem('wishlist')) {
        items.value = JSON.parse(localStorage.getItem('wishlist'));
    }

    const totalItems = computed(() => items.value.length);

    function addItem(product) {
        if (!isInWishlist(product.id)) {
            items.value.push(product);
            saveToLocalStorage();
        }
    }

    function removeItem(productId) {
        items.value = items.value.filter(item => item.id !== productId);
        saveToLocalStorage();
    }

    function isInWishlist(productId) {
        return items.value.some(item => item.id === productId);
    }

    function toggleItem(product) {
        if (isInWishlist(product.id)) {
            removeItem(product.id);
        } else {
            addItem(product);
        }
    }

    function saveToLocalStorage() {
        localStorage.setItem('wishlist', JSON.stringify(items.value));
    }

    return {
        items,
        totalItems,
        addItem,
        removeItem,
        isInWishlist,
        toggleItem
    };
});
