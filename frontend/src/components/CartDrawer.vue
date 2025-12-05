<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[60] flex justify-end pointer-events-none">
            <!-- Backdrop -->
            <Transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isOpen" class="absolute inset-0 bg-gray-500/45 pointer-events-auto"
                    @click="$emit('close')">
                </div>
            </Transition>

            <!-- Panel -->
            <Transition enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                enter-from-class="translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                <div v-if="isOpen"
                    class="w-screen max-w-md bg-white shadow-2xl flex flex-col pointer-events-auto h-full relative z-10">
                    <div class="flex-1 py-6 overflow-y-auto px-4 sm:px-6">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-bold text-primary" id="slide-over-title">Shopping Cart</h2>
                            <div class="ml-3 h-7 flex items-center">
                                <button @click="$emit('close')"
                                    class="-m-2 p-2 text-gray-400 hover:text-primary transition-colors">
                                    <span class="sr-only">Close panel</span>
                                    <X class="h-6 w-6" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="flow-root">
                                <ul role="list" class="-my-6 divide-y divide-gray-100">
                                    <li v-if="cartStore.items.length === 0" class="py-6 text-center text-gray-500">
                                        Your cart is empty.
                                    </li>
                                    <li v-for="item in cartStore.items" :key="item.id" class="py-6 flex">
                                        <div
                                            class="shrink-0 w-24 h-24 border border-gray-200 rounded-lg overflow-hidden">
                                            <img :src="getItemImage(item)" :alt="item.name"
                                                class="w-full h-full object-center object-cover">
                                        </div>
                                        <div class="ml-4 flex-1 flex flex-col">

                                            <div>
                                                <div class="flex justify-between text-base font-medium text-slate-900">
                                                    <h3><router-link :to="`/products/${item.slug || item.id}`">{{ item.name
                                                    }}</router-link></h3>
                                                    <div class="ml-4 text-right">
                                                        <p class="text-primary font-bold">{{ item.price }}</p>
                                                        <p v-if="item.original_price" 
                                                           class="text-xs text-gray-400 line-through">
                                                            {{ item.original_price }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <!-- Variant Information -->
                                                <p v-if="item.variant && item.variant.attributes" class="mt-1 text-sm text-slate-500">
                                                    {{ formatVariantAttributes(item.variant.attributes) }}
                                                </p>
                                                <p v-else-if="item.color" class="mt-1 text-sm text-slate-500">{{ item.color }}</p>
                                            </div>
                                            <div class="flex-1 flex items-end justify-between text-sm">
                                                <div class="flex items-center border border-gray-300 rounded">
                                                    <button
                                                        @click="cartStore.updateQuantity(item.id, item.quantity - 1, item.variant)"
                                                        class="px-2 py-1 text-gray-600 hover:bg-gray-100">-</button>
                                                    <span class="px-2 py-1 text-gray-900">{{ item.quantity }}</span>
                                                    <button
                                                        @click="cartStore.updateQuantity(item.id, item.quantity + 1, item.variant)"
                                                        class="px-2 py-1 text-gray-600 hover:bg-gray-100">+</button>
                                                </div>
                                                <div class="flex">
                                                    <button type="button" @click="cartStore.removeItem(item.id, item.variant)"
                                                        class="font-medium text-secondary hover:text-red-600 transition-colors">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 py-6 px-4 sm:px-6 bg-gray-50">
                        <div class="flex justify-between text-base font-medium text-slate-900">
                            <p>Subtotal</p>
                            <p class="text-primary font-bold">{{ cartStore.formattedSubtotal }}</p>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-500">Shipping and taxes calculated at checkout.</p>
                        <div class="mt-6">
                            <router-link
                                to="/checkout"
                                @click="$emit('close')"
                                class="flex justify-center items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-bold text-white bg-primary hover:bg-primary-hover transition-all duration-200"
                                :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': cartStore.items.length === 0 }"
                            >
                                Checkout
                            </router-link>
                        </div>
                        <div class="mt-6 flex justify-center text-sm text-center text-slate-500">
                            <p>
                                or <button type="button"
                                    class="text-primary font-medium hover:text-primary-hover transition-colors"
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
import { X } from 'lucide-vue-next';
import { useCartStore } from '../stores/cart';

defineProps({
    isOpen: Boolean
});
defineEmits(['close']);

const cartStore = useCartStore();

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
    return '/assets/placeholder.png';
};

// Format variant attributes for display
const formatVariantAttributes = (attributes) => {
    if (!attributes || typeof attributes !== 'object') return '';
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
