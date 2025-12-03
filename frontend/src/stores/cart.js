import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const shouldOpenDrawer = ref(false);

    // Load from local storage on init
    if (localStorage.getItem('cart')) {
        items.value = JSON.parse(localStorage.getItem('cart'));
    }

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

    function addItem(product) {
        const existingItem = items.value.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            items.value.push({ ...product, quantity: 1 });
        }
        saveToLocalStorage();
        // Trigger drawer to open
        shouldOpenDrawer.value = true;
    }

    function closeDrawer() {
        shouldOpenDrawer.value = false;
    }

    function removeItem(productId) {
        items.value = items.value.filter(item => item.id !== productId);
        saveToLocalStorage();
    }

    function updateQuantity(productId, quantity) {
        const item = items.value.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            if (item.quantity <= 0) {
                removeItem(productId);
            }
        }
        saveToLocalStorage();
    }

    function clearCart() {
        items.value = [];
        saveToLocalStorage();
    }

    function saveToLocalStorage() {
        localStorage.setItem('cart', JSON.stringify(items.value));
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
