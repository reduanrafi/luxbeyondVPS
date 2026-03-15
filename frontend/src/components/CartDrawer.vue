<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[60] flex justify-end pointer-events-none">
            <!-- Backdrop -->
            <Transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isOpen" class="absolute inset-0 bg-black/80 backdrop-blur-sm pointer-events-auto"
                    @click="$emit('close')">
                </div>
            </Transition>

            <!-- Panel -->
            <Transition enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                enter-from-class="translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                <div v-if="isOpen"
                    class="w-screen max-w-md bg-surface shadow-2xl flex flex-col pointer-events-auto h-full relative z-10 border-l border-white/5">
                    <div class="flex-1 py-6 overflow-y-auto px-4 sm:px-6">
                        <div class="flex items-start justify-between border-b border-white/5 pb-4">
                            <h2 class="text-xl font-serif text-white tracking-wide" id="slide-over-title">Shopping Cart
                            </h2>

                            <div class="ml-3 h-7 flex items-center">
                                <button @click="$emit('close')"
                                    class="-m-2 p-2 text-slate-400 hover:text-white transition-colors">
                                    <span class="sr-only">Close panel</span>
                                    <X class="h-6 w-6" />
                                </button>
                            </div>
                        </div>

                        <!-- Stock Issue Warning -->
                        <div v-if="hasStockIssues"
                            class="mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-sm flex items-start gap-3">
                            <AlertCircle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                            <div>
                                <h3 class="text-xs font-bold text-red-500">Stock Issues Detected</h3>
                                <p class="text-[10px] text-red-400 mt-1">Some items in your cart are no longer
                                    available.
                                    Please remove them to proceed.</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="flow-root">
                                <ul role="list" class="-my-6 divide-y divide-white/5">
                                    <li v-if="cartStore.items.length === 0" class="py-12 text-center">
                                        <div
                                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 mb-4">
                                            <ShoppingCart class="w-8 h-8 text-[#00ffff]" />
                                        </div>
                                        <p class="text-slate-400 text-lg font-light">Your cart is empty.</p>
                                        <button @click="$emit('close')"
                                            class="mt-4 text-primary hover:text-primary-hover text-sm font-medium uppercase tracking-wider">Start
                                            Shopping</button>
                                    </li>
                                    <li v-for="item in cartStore.items" :key="item.id" class="py-4 flex">
                                        <div class="shrink-0 w-20 h-24 border bg-white/5 rounded-sm overflow-hidden relative"
                                            :class="isOutOfStock(item) ? 'border-red-500/50' : 'border-white/10'">
                                            <img :src="getItemImage(item)" :alt="item.name"
                                                class="w-full h-full object-center object-contain opacity-90 group-hover:opacity-100 transition-opacity"
                                                :class="{ 'opacity-50 grayscale': isOutOfStock(item) }">

                                            <!-- Out of Stock Overlay -->
                                            <div v-if="isOutOfStock(item)"
                                                class="absolute inset-0 flex items-center justify-center bg-black/40">
                                                <span
                                                    class="text-[10px] font-bold text-white bg-red-500 px-2 py-1 uppercase tracking-wider">Out
                                                    of Stock</span>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1 flex flex-col">

                                            <div>
                                                <div class="flex justify-between text-base font-medium">
                                                    <h3>
                                                        <router-link :to="`/shop/${item.slug || item.id}`"
                                                            class="font-serif text-sm tracking-wide transition-colors"
                                                            :class="isOutOfStock(item) ? 'text-red-400' : 'text-white hover:text-primary'">
                                                            {{ item.name }}
                                                        </router-link>
                                                    </h3>
                                                    <div class="ml-4 text-right">
                                                        <p class="font-bold text-sm"
                                                            :class="isOutOfStock(item) ? 'text-[#00ffff]' : 'text-primary'">
                                                            {{ item.price }}</p>
                                                        <!-- <p v-if="item.original_price"
                                                            class="text-xs text-slate-600 line-through">
                                                            {{ item.original_price }}
                                                        </p> -->
                                                    </div>
                                                </div>
                                                <!-- Variant Information -->
                                                <p v-if="item.variant && item.variant.attributes"
                                                    class="mt-1 text-xs text-[#00ffff] uppercase tracking-wider">
                                                    {{ formatVariantAttributes(item.variant.attributes) }}
                                                </p>
                                                <!-- <p v-else-if="item.color"
                                                    class="mt-1 text-xs text-[#00ffff] uppercase tracking-wider">{{
                                                        item.color }}</p> -->

                                                <!-- Stock Error Message -->
                                                <p v-if="isOutOfStock(item)"
                                                    class="mt-2 text-xs text-red-500 font-medium flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    Item unavailable
                                                </p>
                                            </div>
                                            <div class="flex-1 flex items-end justify-between text-sm mt-2">
                                                <div class="flex items-center border border-white/10 rounded-sm bg-background"
                                                    :class="{ 'opacity-50 pointer-events-none': isOutOfStock(item) }">
                                                    <button
                                                        @click="cartStore.updateQuantity(item.id, item.quantity - 1, item.variant)"
                                                        class="px-2 py-1 text-slate-400 hover:text-white hover:bg-white/5 transition-colors disabled:opacity-50">-</button>
                                                    <span
                                                        class="px-2 py-1 text-white font-mono min-w-[2ch] text-center">{{
                                                            item.quantity }}</span>
                                                    <button
                                                        @click="cartStore.updateQuantity(item.id, Number(item.quantity) + 1, item.variant)"
                                                        class="px-2 py-1 text-slate-400 hover:text-white hover:bg-white/5 transition-colors">+</button>
                                                </div>
                                                <div class="flex">
                                                    <button type="button"
                                                        @click="cartStore.removeItem(item.id, item.variant)"
                                                        class="font-medium transition-colors uppercase text-xs tracking-wider text-[#00ffff]"
                                                        :class="isOutOfStock(item) ? 'text-red-500 hover:text-red-400' : 'text-[#00ffff] hover:text-red-500'">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-white/10 py-6 px-4 sm:px-6 bg-background">
                        <div class="flex justify-between text-base font-medium">
                            <p class="text-white font-serif">Subtotal</p>
                            <p class="text-[#00ffff] font-serif text-xl">{{ cartStore.formattedSubtotal }}</p>
                        </div>
                        <p class="mt-1 text-xs text-[#00ffff] font-light">Shipping and taxes calculated at checkout.</p>
                        <div class="mt-6">
                            <button @click="handleCheckout"
                                class="w-full flex justify-center items-center px-6 py-4 border border-transparent rounded-none shadow-sm text-sm font-bold text-white transition-all duration-200 uppercase tracking-widest"
                                :class="hasStockIssues || cartStore.items.length === 0 ? 'bg-slate-700 text-[#00ffff] cursor-not-allowed' : 'bg-primary hover:bg-primary-hover'"
                                :disabled="hasStockIssues || cartStore.items.length === 0">
                                <span v-if="hasStockIssues">Remove Unavailable Items</span>
                                <span v-else>Checkout</span>
                            </button>
                        </div>
                        <div class="mt-6 flex justify-center text-sm text-center text-[#00ffff]">
                            <p>
                                or <button type="button"
                                    class="text-primary font-bold hover:text-primary-hover transition-colors uppercase tracking-wider text-xs ml-1"
                                    @click="$emit('close')">Continue Shopping<span aria-hidden="true">
                                        &rarr;</span></button>
                            </p>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<script setup>
import { X, ShoppingCart, AlertCircle } from 'lucide-vue-next';
import { useCartStore } from '../stores/cart';
import { useRouter } from 'vue-router';
import { computed } from 'vue';

const props = defineProps({
    isOpen: Boolean
});
const emit = defineEmits(['close']);

const cartStore = useCartStore();
const router = useRouter();

// Get item image - prefer variant image, fallback to product image
const getItemImage = (item) => {
    // Check if variant has image
    if (item.variant && (item.variant.image || item.variant.image_url)) {
        const variantImage = item.variant.image_url || item.variant.image;
        // If it's already a full URL, return as is
        if (variantImage.startsWith('http')) {
            return variantImage;
        }
        // Otherwise, assume it's a storage path
        return variantImage.startsWith('/storage/') ? variantImage : `/storage/${variantImage}`;
    }
    // Fallback to product image
    if (item.image_url) {
        return item.image_url;
    }
    if (item.image) {
        return item.image.startsWith('http') || item.image.startsWith('/')
            ? item.image
            : `/storage/${item.image}`;
    }
    // Default placeholder
    return '/assets/placeholder.webp';
};

// Stock Validation Logic
const isOutOfStock = (item) => {
    return (item.total_stock != undefined && item.total_stock <= 0);
};

const hasStockIssues = computed(() => {
    return cartStore.items.some(item => isOutOfStock(item));
});

const handleCheckout = () => {
    if (hasStockIssues.value || cartStore.items.length === 0) return;

    emit('close');
    router.push('/checkout');
};

// Format variant attributes for display
const formatVariantAttributes = (attributes) => {
    if (!attributes || typeof attributes != 'object') return '';
    return Object.entries(attributes)
        .map(([key, value]) => `${key}: ${value}`)
        .join(', ');
};

// Format price to ensure BDT symbol (৳) instead of $
const formatPrice = (price) => {
    if (!price) return '৳0.00';
    // Convert string to string, replacing $ with ৳
    if (typeof price === 'string') {
        // If it already has ৳, return as is
        if (price.includes('৳')) {
            return price;
        }
        // If it has $, replace with ৳
        if (price.includes('$')) {
            return price.replace(/\$/g, '৳');
        }
        // If it's just a number, add ৳
        const numPrice = parseFloat(price.replace(/[^\d.]/g, ''));
        if (!isNaN(numPrice)) {
            return '৳' + numPrice.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }
    // If it's a number, format it
    if (typeof price === 'number') {
        return '৳' + price.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    return price;
};
</script>
