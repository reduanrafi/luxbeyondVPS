<template>
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky w-full z-50 top-0 left-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="shrink-0">
                    <router-link to="/" class="flex items-center">
                        <img src="/assets/logo.png" alt="Luxbeyond Logo" class="h-12 w-auto">
                    </router-link>
                </div>

                <!-- Search Bar (Desktop) -->
                <div class="hidden lg:flex flex-1 max-w-2xl mx-8 relative">
                    <div class="relative w-full">
                        <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                        <input type="text" v-model="searchQuery" @input="handleSearch"
                            @focus="showSearchDropdown = true" @blur="handleSearchBlur"
                            placeholder="Search for products, brands, categories..."
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-900 placeholder-gray-400" />
                        <button v-if="searchQuery" @click="clearSearch"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Search Dropdown -->
                    <div v-if="showSearchDropdown && (searchQuery || searchResults.length > 0)"
                        class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                        <!-- Loading State -->
                        <div v-if="isSearching" class="p-6 text-center">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                            <p class="mt-2 text-sm text-gray-500">Searching...</p>
                        </div>

                        <!-- Search Results -->
                        <div v-else-if="searchResults.length > 0" class="py-2">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Products ({{ searchResults.length }})
                            </div>
                            <router-link v-for="product in searchResults" :key="product.id"
                                :to="`/products/${product.slug || product.id}`" @click="closeSearchDropdown"
                                class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 transition-colors group">
                                <img :src="product.image_url ? product.image_url : '/assets/placeholder.png'"
                                    :alt="product.name"
                                    class="w-16 h-16 object-cover rounded-lg border border-gray-200" />
                                <div class="flex-1 min-w-0">
                                    <h4
                                        class="text-sm font-medium text-gray-900 group-hover:text-primary transition-colors truncate">
                                        {{ product.name }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ product.category }}</p>
                                    <p class="text-sm font-semibold text-primary mt-1">
                                        ৳{{ formatPrice(product.sellable_price || product.price) }}
                                    </p>
                                </div>
                            </router-link>
                        </div>

                        <!-- No Results -->
                        <div v-else-if="searchQuery && !isSearching" class="p-6 text-center">
                            <Search class="h-12 w-12 text-gray-300 mx-auto mb-3" />
                            <p class="text-sm font-medium text-gray-900">No products found</p>
                            <p class="text-xs text-gray-500 mt-1">
                                We are working on it.
                            </p>
                        </div>

                        <!-- Recent Searches / Suggestions (when no query) -->
                        <div v-else-if="!searchQuery" class="p-4">
                            <div class="px-2 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Popular Searches
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button v-for="suggestion in popularSearches" :key="suggestion"
                                    @click="searchQuery = suggestion; handleSearch()"
                                    class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition-colors text-gray-700">
                                    {{ suggestion }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <router-link to="/"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors">
                        Home
                    </router-link>
                    <router-link to="/shop"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors">
                        Shop
                    </router-link>
                    <router-link to="/travellers"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors">
                        Travellers
                    </router-link>
                    <router-link to="/request-product"
                        class="ml-2 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm">
                        Request Order
                    </router-link>
                </div>

                <!-- Action Icons (Desktop) -->
                <div class="hidden lg:flex items-center space-x-3 ml-6 pl-6 border-l border-gray-200">
                    <button @click="handleWishlistClick"
                        class="relative p-2 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors"
                        title="Wishlist">
                        <Heart class="h-6 w-6" />
                        <span v-if="wishlistStore.totalItems > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ wishlistStore.totalItems }}
                        </span>
                    </button>
                    <button @click="handleCartClick"
                        class="relative p-2 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors"
                        title="Cart">
                        <ShoppingCart class="h-6 w-6" />
                        <span v-if="cartStore.totalItems > 0"
                            class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ cartStore.totalItems }}
                        </span>
                    </button>
                    <button v-if="authStore.isAuthenticated" @click="isNotificationOpen = true"
                        class="relative p-2 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors"
                        title="Notifications">
                        <Bell class="h-6 w-6" />
                        <span v-if="unreadCount > 0"
                            class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ unreadCount }}
                        </span>
                    </button>
                    <button v-if="!authStore.isAuthenticated" @click="modalStore.openModal('login')"
                        class="flex items-center gap-3 px-4 py-2.5  rounded-xl hover:bg-primary/10 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center transition-colors bg-primary/10">
                            <User class="w-5 h-5 text-primary-600 " />
                        </div>
                        <div class="text-left">
                            <div class="text-sm font-semibold text-gray-800">Hello, User</div>
                            <div class="text-xs text-gray-600">Account & Orders</div>
                        </div>
                    </button>
                    <router-link v-if="authStore.isAuthenticated" to="/dashboard"
                        class="flex items-center gap-3 px-4 py-2.5  rounded-xl hover:bg-primary/10 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center transition-colors bg-primary/10">
                            <User class="w-5 h-5 text-primary-600 " />
                        </div>
                        <div class="text-left">
                            <div class="text-sm font-semibold text-gray-800">
                                Hello, {{ authStore.user && authStore.user.name ? authStore.user.name : 'User' }}
                            </div>
                            <div class="text-xs text-gray-600">Account & Orders</div>
                        </div>
                    </router-link>
                </div>

                <!-- Mobile Actions -->
                <div class="lg:hidden flex items-center space-x-2">
                    <!-- Mobile Search Button -->
                    <button @click="isMobileSearchOpen = !isMobileSearchOpen"
                        class="p-2 text-gray-700 hover:text-primary rounded-lg transition-colors">
                        <Search class="h-6 w-6" />
                    </button>

                    <!-- Mobile Icons -->
                    <button @click="handleWishlistClick"
                        class="relative p-2 text-gray-700 hover:text-primary transition-colors">
                        <Heart class="h-6 w-6" />
                        <span v-if="wishlistStore.totalItems > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ wishlistStore.totalItems }}
                        </span>
                    </button>
                    <button @click="handleCartClick"
                        class="relative p-2 text-gray-700 hover:text-primary transition-colors">
                        <ShoppingCart class="h-6 w-6" />
                        <span v-if="cartStore.totalItems > 0"
                            class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ cartStore.totalItems }}
                        </span>
                    </button>
                    <button v-if="authStore.isAuthenticated" @click="isNotificationOpen = true"
                        class="relative p-2 text-gray-700 hover:text-primary transition-colors">
                        <Bell class="h-6 w-6" />
                        <span v-if="unreadCount > 0"
                            class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ unreadCount }}
                        </span>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button @click="isOpen = !isOpen"
                        class="p-2 text-gray-700 hover:text-primary rounded-lg transition-colors">
                        <Menu v-if="!isOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div v-if="isMobileSearchOpen" class="lg:hidden pb-4">
                <div class="relative">
                    <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input type="text" v-model="searchQuery" @input="handleSearch" @focus="showSearchDropdown = true"
                        @blur="handleSearchBlur" placeholder="Search for products..."
                        class="w-full pl-12 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-gray-900" />
                    <button v-if="searchQuery" @click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Mobile Search Dropdown -->
                <div v-if="showSearchDropdown && (searchQuery || searchResults.length > 0)"
                    class="absolute left-0 right-0 mt-2 mx-4 bg-white border border-gray-200 rounded-lg shadow-xl max-h-96 overflow-y-auto z-50">
                    <div v-if="isSearching" class="p-6 text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                        <p class="mt-2 text-sm text-gray-500">Searching...</p>
                    </div>
                    <div v-else-if="searchResults.length > 0" class="py-2">
                        <router-link v-for="product in searchResults" :key="product.id"
                            :to="`/products/${product.slug || product.id}`" @click="closeSearchDropdown"
                            class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 transition-colors">
                            <img :src="product.image_url" :alt="product.name"
                                class="w-16 h-16 object-cover rounded-lg border border-gray-200" />
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ product.category }}</p>
                                <p class="text-sm font-semibold text-primary mt-1">
                                    ৳{{ formatPrice(product.sellable_price || product.price) }}
                                </p>
                            </div>
                        </router-link>
                    </div>
                    <div v-else-if="searchQuery && !isSearching" class="p-6 text-center">
                        <p class="text-sm text-gray-500">No products found</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="isOpen" class="lg:hidden bg-white border-t border-gray-200 shadow-lg">
            <div class="px-4 py-3 space-y-1">
                <router-link to="/" @click="isOpen = false"
                    class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 transition-colors">
                    Home
                </router-link>
                <router-link to="/shop" @click="isOpen = false"
                    class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 transition-colors">
                    Shop
                </router-link>
                <router-link to="/travellers" @click="isOpen = false"
                    class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 transition-colors">
                    Travellers
                </router-link>
                <router-link to="/request-product" @click="isOpen = false"
                    class="block px-3 py-2 rounded-lg text-base font-medium text-primary hover:bg-gray-50 transition-colors">
                    Request Order
                </router-link>
                <div class="border-t border-gray-200 my-2"></div>
                <button v-if="!authStore.isAuthenticated" @click="modalStore.openModal('login'); isOpen = false"
                    class="flex items-center gap-3 w-full px-4 py-3 bg-pink-50 rounded-xl hover:bg-purple-100 transition-colors group">
                    <div
                        class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center group-hover:bg-teal-200 transition-colors shrink-0">
                        <User class="w-5 h-5 text-teal-600" />
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-semibold text-gray-800">Hello, User</div>
                        <div class="text-xs text-gray-600">Account & Orders</div>
                    </div>
                </button>
                <button v-if="!authStore.isAuthenticated" @click="modalStore.openModal('register'); isOpen = false"
                    class="block w-full text-left px-3 py-2 rounded-lg text-base font-medium text-primary hover:bg-gray-50 transition-colors mt-2">
                    Register
                </button>
                <router-link v-if="authStore.isAuthenticated" to="/dashboard" @click="isOpen = false"
                    class="block px-3 py-2 rounded-lg text-base font-medium text-primary hover:bg-gray-50 transition-colors">
                    Dashboard
                </router-link>
            </div>
        </div>

        <!-- Drawers -->
        <CartDrawer :isOpen="isCartOpen" @close="handleCartClose" />
        <WishlistDrawer :isOpen="isWishlistOpen" @close="isWishlistOpen = false" />
        <NotificationDrawer :isOpen="isNotificationOpen" @close="isNotificationOpen = false" />
    </nav>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import CartDrawer from './CartDrawer.vue';
import WishlistDrawer from './WishlistDrawer.vue';
import NotificationDrawer from './NotificationDrawer.vue';
import { ShoppingCart, Heart, Menu, X, Bell, Search, User } from 'lucide-vue-next';
import { useModalStore } from '../stores/modal';
import axios from '../axios';

const router = useRouter();
const isOpen = ref(false);
const isCartOpen = ref(false);
const isWishlistOpen = ref(false);
const isNotificationOpen = ref(false);
const isMobileSearchOpen = ref(false);
const authStore = useAuthStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const modalStore = useModalStore();

// Watch for cart drawer trigger from cart store
watch(() => cartStore.shouldOpenDrawer, (shouldOpen) => {
    if (shouldOpen) {
        // Check authentication before opening drawer
        if (!authStore.isAuthenticated) {
            modalStore.openModal('login');
            cartStore.closeDrawer();
            return;
        }
        isCartOpen.value = true;
    }
});

// Handle cart drawer close
const handleCartClose = () => {
    isCartOpen.value = false;
    cartStore.closeDrawer();
};

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showSearchDropdown = ref(false);
const searchTimeout = ref(null);

const popularSearches = ['Electronics', 'Fashion', 'Home & Garden', 'Accessories', 'Beauty'];

// Mock unread notifications count
const unreadCount = computed(() => authStore.isAuthenticated ? 3 : 0);

const handleSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    showSearchDropdown.value = true;

    // Debounce search
    searchTimeout.value = setTimeout(async () => {
        try {
            const response = await axios.get('/products', {
                params: {
                    search: searchQuery.value,
                    per_page: 8
                }
            });

            // Handle both paginated and non-paginated responses
            if (response.data.data) {
                searchResults.value = Array.isArray(response.data.data)
                    ? response.data.data
                    : response.data.data.data || [];
            } else {
                searchResults.value = Array.isArray(response.data) ? response.data : [];
            }
        } catch (error) {
            console.error('Search error:', error);
            searchResults.value = [];
        } finally {
            isSearching.value = false;
        }
    }, 300);
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
    showSearchDropdown.value = false;
};

const closeSearchDropdown = () => {
    showSearchDropdown.value = false;
    isMobileSearchOpen.value = false;
};

const handleSearchBlur = () => {
    // Delay closing to allow click events on results
    setTimeout(() => {
        showSearchDropdown.value = false;
    }, 200);
};

const formatPrice = (price) => {
    if (!price) return '0.00';
    return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

// Close mobile search when route changes
watch(() => router.currentRoute.value.path, () => {
    isMobileSearchOpen.value = false;
    showSearchDropdown.value = false;
});

// Handle cart click - check authentication
const handleCartClick = () => {
    if (!authStore.isAuthenticated) {
        modalStore.openModal('login');
        return;
    }
    isCartOpen.value = true;
};

// Handle wishlist click - check authentication
const handleWishlistClick = () => {
    if (!authStore.isAuthenticated) {
        modalStore.openModal('login');
        return;
    }
    isWishlistOpen.value = true;
};
</script>
