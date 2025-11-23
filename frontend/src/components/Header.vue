<template>
    <nav class="bg-white/80 backdrop-blur-md shadow-sm fixed w-full z-50 top-0 left-0 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <router-link to="/" class="text-2xl font-bold text-primary">LuxBusiness</router-link>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6 items-center">
                    <router-link to="/" class="text-gray-700 hover:text-primary transition">Home</router-link>
                    <router-link to="/shop" class="text-gray-700 hover:text-primary transition">Shop</router-link>
                    <!-- <router-link to="/blogs" class="text-gray-700 hover:text-primary transition">Blogs</router-link> -->
                    <router-link to="/travellers"
                        class="text-gray-700 hover:text-primary transition">Travellers</router-link>

                    <!-- Request Order CTA -->
                    <router-link to="/request-product"
                        class="bg-accent text-white px-4 py-2 rounded-full hover:bg-yellow-500 transition font-semibold text-sm">
                        Request Order
                    </router-link>

                    <!-- Icons (Desktop) -->
                    <div class="flex items-center space-x-4 border-l pl-4 border-gray-200">
                        <button @click="isWishlistOpen = true" class="text-gray-600 hover:text-primary relative">
                            <Heart class="h-6 w-6" />
                            <span v-if="wishlistStore.totalItems > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{
                                    wishlistStore.totalItems }}</span>
                        </button>
                        <button @click="isCartOpen = true" class="text-gray-600 hover:text-primary relative">
                            <ShoppingCart class="h-6 w-6" />
                            <span v-if="cartStore.totalItems > 0"
                                class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{
                                    cartStore.totalItems }}</span>
                        </button>
                        <button v-if="authStore.isAuthenticated" @click="isNotificationOpen = true"
                            class="text-gray-600 hover:text-primary relative">
                            <Bell class="h-6 w-6" />
                            <span v-if="unreadCount > 0"
                                class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{
                                    unreadCount }}</span>
                        </button>
                    </div>

                    <router-link v-if="!authStore.isAuthenticated" to="/login"
                        class="text-gray-700 hover:text-primary transition">Login</router-link>
                    <router-link v-if="authStore.isAuthenticated" to="/dashboard"
                        class="text-gray-700 hover:text-primary transition font-medium">Dashboard</router-link>
                </div>

                <!-- Mobile Actions (Icons + Menu) -->
                <div class="md:hidden flex items-center space-x-4">
                    <!-- Icons (Mobile) -->
                    <div class="flex items-center space-x-3 mr-2">
                        <button @click="isWishlistOpen = true" class="text-gray-600 hover:text-primary relative">
                            <Heart class="h-6 w-6" />
                            <span v-if="wishlistStore.totalItems > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{
                                    wishlistStore.totalItems }}</span>
                        </button>
                        <button @click="isCartOpen = true" class="text-gray-600 hover:text-primary relative">
                            <ShoppingCart class="h-6 w-6" />
                            <span v-if="cartStore.totalItems > 0"
                                class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{
                                    cartStore.totalItems }}</span>
                        </button>
                        <button v-if="authStore.isAuthenticated" @click="isNotificationOpen = true"
                            class="text-gray-600 hover:text-primary relative">
                            <Bell class="h-6 w-6" />
                            <span v-if="unreadCount > 0"
                                class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">{{
                                    unreadCount }}</span>
                        </button>
                    </div>

                    <!-- Mobile menu button -->
                    <button @click="isOpen = !isOpen" class="text-gray-700 hover:text-primary focus:outline-none">
                        <Menu v-if="!isOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div v-if="isOpen"
            class="md:hidden bg-white shadow-lg absolute w-full max-h-[calc(100vh-4rem)] overflow-y-auto">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <router-link to="/"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Home</router-link>
                <router-link to="/shop"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Shop</router-link>
                <router-link to="/blogs"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Blogs</router-link>
                <router-link to="/travellers"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Travellers</router-link>
                <router-link to="/request-product"
                    class="block px-3 py-2 rounded-md text-base font-medium text-accent hover:bg-gray-50">Request
                    Order</router-link>
                <router-link v-if="!authStore.isAuthenticated" to="/login"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Login</router-link>
                <router-link v-if="!authStore.isAuthenticated" to="/register"
                    class="block px-3 py-2 rounded-md text-base font-medium text-primary hover:bg-gray-50">Register</router-link>
                <router-link v-if="authStore.isAuthenticated" to="/dashboard"
                    class="block px-3 py-2 rounded-md text-base font-medium text-primary hover:bg-gray-50">Dashboard</router-link>
            </div>
        </div>

        <!-- Drawers -->
        <CartDrawer :isOpen="isCartOpen" @close="isCartOpen = false" />
        <WishlistDrawer :isOpen="isWishlistOpen" @close="isWishlistOpen = false" />
        <NotificationDrawer :isOpen="isNotificationOpen" @close="isNotificationOpen = false" />
    </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import CartDrawer from './CartDrawer.vue';
import WishlistDrawer from './WishlistDrawer.vue';
import NotificationDrawer from './NotificationDrawer.vue';
import { ShoppingCart, Heart, Menu, X, Bell } from 'lucide-vue-next';

const isOpen = ref(false);
const isCartOpen = ref(false);
const isWishlistOpen = ref(false);
const isNotificationOpen = ref(false);
const authStore = useAuthStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

// Mock unread notifications count (should come from a notifications store)
const unreadCount = computed(() => authStore.isAuthenticated ? 3 : 0);
</script>
