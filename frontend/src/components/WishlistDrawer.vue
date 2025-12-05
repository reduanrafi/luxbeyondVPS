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
                            <h2 class="text-lg font-bold text-primary" id="slide-over-title">My Wishlist</h2>
                            <div class="ml-3 h-7 flex items-center">
                                <button @click="$emit('close')"
                                    class="-m-2 p-2 text-gray-400 hover:text-primary transition-colors">
                                    <span class="sr-only">Close panel</span>
                                    <X class="h-6 w-6" />
                                </button>
                            </div>
                        </div>

                        <!-- Add All to Cart Button -->
                        <div v-if="wishlistStore.items.length > 0" class="mt-6 mb-4">
                            <button
                                @click="addAllToCart"
                                class="w-full px-4 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg"
                            >
                                <ShoppingCart class="w-5 h-5" />
                                Add All to Cart ({{ wishlistStore.items.length }})
                            </button>
                        </div>

                        <div class="mt-8">
                            <div class="flow-root">
                                <ul role="list" class="-my-6 divide-y divide-gray-100">
                                    <li v-if="wishlistStore.items.length === 0" class="py-6 text-center text-gray-500">
                                        Your wishlist is empty.
                                    </li>
                                    <li v-for="item in wishlistStore.items" :key="item.id" class="py-6 flex">
                                        <div
                                            class="flex-shrink-0 w-24 h-24 border border-gray-200 rounded-lg overflow-hidden">
                                            <img :src="item.image_url" :alt="item.name"
                                                class="w-full h-full object-center object-contain">
                                        </div>
                                        <div class="ml-4 flex-1 flex flex-col">
                                            <div>
                                                <div class="flex justify-between text-base font-medium text-slate-900">
                                                    <h3><router-link :to="`/products/${item.slug || item.id}`">{{ item.name
                                                    }}</router-link></h3>
                                                    <p class="ml-4 text-primary font-bold">
                                                        <template v-if="typeof item.price === 'string' && (item.price.includes('৳') || item.price.includes('$'))">
                                                            {{ item.price }}
                                                        </template>
                                                        <template v-else>
                                                            ৳{{ typeof item.price === 'number' ? item.price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : item.price }}
                                                        </template>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex-1 flex items-end justify-between text-sm">
                                                <button type="button" @click="moveToCart(item)"
                                                    class="font-medium text-primary hover:text-primary-hover transition-colors">Add
                                                    to Cart</button>
                                                <button type="button" @click="wishlistStore.removeItem(item.id)"
                                                    class="font-medium text-secondary hover:text-red-600 transition-colors">Remove</button>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<script setup>
import { X, ShoppingCart } from 'lucide-vue-next';
import { watch } from 'vue';
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useModalStore } from '../stores/modal';

const props = defineProps({
    isOpen: Boolean
});
const emit = defineEmits(['close']);

const wishlistStore = useWishlistStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const modalStore = useModalStore();

// Watch for drawer opening - if user is not authenticated, close drawer and show login modal
watch(() => props.isOpen, (newValue) => {
    if (newValue && !authStore.isAuthenticated) {
        emit('close');
        modalStore.openModal('login');
    }
});

function moveToCart(item) {
    // Ensure slug is included
    const productWithSlug = {
        ...item,
        slug: item.slug || item.id || String(item.id)
    };
    cartStore.addItem(productWithSlug);
    wishlistStore.removeItem(item.id);
}

function addAllToCart() {
    if (wishlistStore.items.length === 0) return;
    
    // Create a copy of items array to avoid mutation during iteration
    const itemsToAdd = [...wishlistStore.items];
    
    // Add all items to cart with ensured slugs
    itemsToAdd.forEach(item => {
        const productWithSlug = {
            ...item,
            slug: item.slug || item.id || String(item.id)
        };
        cartStore.addItem(productWithSlug);
    });
    
    // Clear wishlist after adding all to cart
    itemsToAdd.forEach(item => {
        wishlistStore.removeItem(item.id);
    });
    
    // Open cart drawer to show added items
    cartStore.shouldOpenDrawer = true;
}
</script>
