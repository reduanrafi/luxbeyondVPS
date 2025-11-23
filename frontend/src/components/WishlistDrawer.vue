<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-[60] overflow-hidden" aria-labelledby="slide-over-title" role="dialog"
            aria-modal="true">
            <div class="absolute inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity" @click="$emit('close')">
            </div>
            <div class="fixed inset-y-0 right-0 max-w-full flex pointer-events-none">
                <div
                    class="w-screen max-w-md transform transition ease-in-out duration-500 sm:duration-700 bg-white shadow-2xl flex flex-col pointer-events-auto">
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

                        <div class="mt-8">
                            <div class="flow-root">
                                <ul role="list" class="-my-6 divide-y divide-gray-100">
                                    <li v-if="wishlistStore.items.length === 0" class="py-6 text-center text-gray-500">
                                        Your wishlist is empty.
                                    </li>
                                    <li v-for="item in wishlistStore.items" :key="item.id" class="py-6 flex">
                                        <div
                                            class="flex-shrink-0 w-24 h-24 border border-gray-200 rounded-lg overflow-hidden">
                                            <img :src="item.image" :alt="item.name"
                                                class="w-full h-full object-center object-cover">
                                        </div>
                                        <div class="ml-4 flex-1 flex flex-col">
                                            <div>
                                                <div class="flex justify-between text-base font-medium text-slate-900">
                                                    <h3><router-link :to="`/product/${item.id}`">{{ item.name
                                                            }}</router-link></h3>
                                                    <p class="ml-4 text-primary font-bold">৳{{ item.price }}</p>
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
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { X } from 'lucide-vue-next';
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';

defineProps({
    isOpen: Boolean
});
defineEmits(['close']);

const wishlistStore = useWishlistStore();
const cartStore = useCartStore();

function moveToCart(item) {
    cartStore.addItem(item);
    wishlistStore.removeItem(item.id);
}
</script>
