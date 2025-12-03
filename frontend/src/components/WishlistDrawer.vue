<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[60] flex justify-end pointer-events-none">
            <!-- Backdrop -->
            <Transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isOpen" class="absolute inset-0 bg-gray-500/45 backdrop-blur-sm pointer-events-auto"
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
            </Transition>
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
