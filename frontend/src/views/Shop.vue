<template>
    <div class="bg-gray-50 min-h-screen pt-20 pb-12">
        <!-- Modern Header Section -->
        <div class="bg-white border-b border-gray-100 mb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-extrabold text-primary tracking-tight mb-2">Shop Collection</h1>
                        <p class="text-slate-500 text-lg">Discover premium products curated just for you.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                            <input v-model="searchQuery" type="text" placeholder="Search products..."
                                class="w-full md:w-80 pl-12 pr-4 py-3 bg-gray-50 border-transparent focus:bg-white border focus:border-primary rounded-xl outline-none transition-all shadow-sm group-hover:shadow-md" />
                            <Search
                                class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 group-hover:text-primary transition-colors" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Mobile Filter Toggle & Sort -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 lg:hidden">
                <button @click="isFilterOpen = true"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-lg font-medium text-slate-700 hover:border-primary hover:text-primary transition-colors shadow-sm">
                    <Filter class="w-4 h-4" />
                    Filters
                </button>
                <select v-model="sortBy"
                    class="w-full sm:w-auto px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-slate-700 focus:ring-2 focus:ring-primary outline-none shadow-sm">
                    <option value="featured">Sort by: Featured</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="newest">Newest Arrivals</option>
                </select>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Desktop Sidebar Filters -->
                <aside class="hidden lg:block w-64 flex-shrink-0 space-y-8">
                    <!-- Sort (Desktop) -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-primary mb-4">Sort By</h3>
                        <select v-model="sortBy"
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-slate-700 focus:ring-2 focus:ring-primary outline-none">
                            <option value="featured">Featured</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="newest">Newest Arrivals</option>
                        </select>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-primary mb-4 flex items-center justify-between">
                            Categories
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ categories.length
                            }}</span>
                        </h3>
                        <div class="space-y-3">
                            <label v-for="category in categories" :key="category"
                                class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" :value="category" v-model="selectedCategories"
                                        class="peer h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary transition-all" />
                                </div>
                                <span class="text-slate-600 group-hover:text-primary transition-colors">{{ category
                                }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Brands -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-primary mb-4">Brands</h3>
                        <div class="space-y-3">
                            <label v-for="brand in brands" :key="brand"
                                class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" :value="brand" v-model="selectedBrands"
                                    class="h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary transition-all" />
                                <span class="text-slate-600 group-hover:text-primary transition-colors">{{ brand
                                }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-primary mb-4">Price Range</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Min</label>
                                    <input v-model.number="minPrice" type="number" placeholder="0"
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-primary focus:border-primary outline-none" />
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Max</label>
                                    <input v-model.number="maxPrice" type="number" placeholder="Max"
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-primary focus:border-primary outline-none" />
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Mobile Filter Drawer -->
                <Teleport to="body">
                    <div v-if="isFilterOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
                        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                            @click="isFilterOpen = false"></div>
                        <div
                            class="fixed inset-y-0 right-0 z-50 w-full max-w-xs bg-white shadow-xl transform transition-transform overflow-y-auto">
                            <div class="flex items-center justify-between p-4 border-b border-gray-100">
                                <h2 class="text-lg font-bold text-primary">Filters</h2>
                                <button @click="isFilterOpen = false" class="p-2 text-gray-400 hover:text-gray-500">
                                    <X class="w-6 h-6" />
                                </button>
                            </div>
                            <div class="p-4 space-y-6">
                                <!-- Mobile Filter Content (Duplicate of Desktop for simplicity) -->
                                <div>
                                    <h3 class="font-bold text-primary mb-3">Categories</h3>
                                    <div class="space-y-3">
                                        <label v-for="category in categories" :key="category"
                                            class="flex items-center gap-3">
                                            <input type="checkbox" :value="category" v-model="selectedCategories"
                                                class="h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary" />
                                            <span class="text-slate-600">{{ category }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-primary mb-3">Brands</h3>
                                    <div class="space-y-3">
                                        <label v-for="brand in brands" :key="brand" class="flex items-center gap-3">
                                            <input type="checkbox" :value="brand" v-model="selectedBrands"
                                                class="h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary" />
                                            <span class="text-slate-600">{{ brand }}</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-primary mb-3">Price Range</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <input v-model.number="minPrice" type="number" placeholder="Min"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg" />
                                        <input v-model.number="maxPrice" type="number" placeholder="Max"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg" />
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 border-t border-gray-100 bg-gray-50">
                                <button @click="isFilterOpen = false"
                                    class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg">
                                    Show Results
                                </button>
                            </div>
                        </div>
                    </div>
                </Teleport>

                <!-- Product Grid Area -->
                <div class="flex-1">
                    <!-- Active Filters -->
                    <div v-if="hasActiveFilters" class="mb-6 flex flex-wrap gap-2 items-center">
                        <span class="text-sm text-slate-500 mr-2">Active filters:</span>
                        <button v-for="cat in selectedCategories" :key="cat" @click="removeCategory(cat)"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded-full hover:bg-primary/20 transition-colors">
                            {{ cat }}
                            <X class="w-3 h-3" />
                        </button>
                        <button v-for="brand in selectedBrands" :key="brand" @click="removeBrand(brand)"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 text-sm font-medium rounded-full hover:bg-purple-200 transition-colors">
                            {{ brand }}
                            <X class="w-3 h-3" />
                        </button>
                        <button v-if="minPrice || maxPrice" @click="resetPrice"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full hover:bg-green-200 transition-colors">
                            Price Range
                            <X class="w-3 h-3" />
                        </button>
                        <button @click="resetFilters"
                            class="text-sm text-red-500 hover:text-red-600 font-medium ml-auto">
                            Clear all
                        </button>
                    </div>

                    <div v-if="filteredProducts.length > 0">
                        <TransitionGroup name="list" tag="div"
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Card v-for="product in filteredProducts" :key="product.id" bodyClass="p-0"
                                class="group h-full flex flex-col border-transparent hover:border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300">
                                <template #header>
                                    <div
                                        class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-100 relative rounded-t-2xl">
                                        <img :src="product.image" :alt="product.name"
                                            class="w-full h-64 object-cover object-center group-hover:scale-110 transition-transform duration-700">

                                        <!-- Badges -->
                                        <div class="absolute top-3 left-3 flex flex-col gap-2">
                                            <span v-if="product.discount"
                                                class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm backdrop-blur-md">
                                                -{{ product.discount }}%
                                            </span>
                                            <span v-if="product.isNew"
                                                class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm backdrop-blur-md">
                                                New
                                            </span>
                                        </div>

                                        <!-- Quick Actions -->
                                        <div
                                            class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                                            <button @click.prevent="wishlistStore.toggleItem(product)"
                                                class="p-2.5 bg-white text-slate-400 hover:text-red-500 rounded-full shadow-lg hover:shadow-xl transition-all duration-200"
                                                :class="{ '!text-red-500': wishlistStore.isInWishlist(product.id) }">
                                                <Heart class="w-5 h-5"
                                                    :fill="wishlistStore.isInWishlist(product.id) ? 'currentColor' : 'none'" />
                                            </button>
                                        </div>

                                        <!-- Overlay on Hover -->
                                        <div
                                            class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors duration-300">
                                        </div>
                                    </div>
                                </template>
                                <div class="p-5 flex-1 flex flex-col">
                                    <div class="mb-2">
                                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">{{
                                            product.category }}</span>
                                    </div>
                                    <router-link :to="`/product/${product.id}`">
                                        <h3
                                            class="text-lg font-bold text-slate-900 mb-1 line-clamp-2 hover:text-primary transition-colors leading-snug">
                                            {{ product.name }}
                                        </h3>
                                    </router-link>
                                    <div class="flex items-baseline gap-2 mt-2 mb-4">
                                        <span class="text-xl font-bold text-primary">৳{{ product.price }}</span>
                                        <span v-if="product.oldPrice"
                                            class="text-sm text-slate-400 line-through decoration-slate-300">৳{{
                                                product.oldPrice }}</span>
                                    </div>
                                    <div class="mt-auto pt-4 border-t border-gray-50">
                                        <Button variant="primary" block @click.prevent="cartStore.addItem(product)"
                                            class="flex items-center justify-center gap-2 py-2.5 rounded-xl font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
                                            <ShoppingCart class="w-4 h-4" />
                                            Add to Cart
                                        </Button>
                                    </div>
                                </div>
                            </Card>
                        </TransitionGroup>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-24 bg-white rounded-3xl border border-dashed border-gray-200">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-6 animate-pulse">
                            <Search class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">No products found</h3>
                        <p class="text-slate-500 mb-6 max-w-md mx-auto">We couldn't find any products matching your
                            criteria. Try adjusting
                            your filters or search query.</p>
                        <button @click="resetFilters"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 text-slate-700 font-bold rounded-xl hover:border-primary hover:text-primary transition-colors shadow-sm">
                            <RefreshCw class="w-4 h-4" />
                            Clear all filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Card from '../components/ui/Card.vue';
import Button from '../components/ui/Button.vue';
import { ShoppingCart, Heart, Search, Filter, X, RefreshCw } from 'lucide-vue-next';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';

const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

// Filter States
const searchQuery = ref('');
const sortBy = ref('featured');
const selectedCategories = ref([]);
const selectedBrands = ref([]);
const minPrice = ref(null);
const maxPrice = ref(null);
const isFilterOpen = ref(false);

// Mock Data
const products = ref([
    { id: 1, name: 'Wireless Noise Cancelling Headphones', price: '12,500', oldPrice: '15,000', discount: 15, category: 'Electronics', brand: 'Sony', isNew: true, image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 2, name: 'Smart Fitness Watch Series 5', price: '4,200', oldPrice: '5,500', discount: 20, category: 'Wearables', brand: 'Apple', isNew: false, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 3, name: 'Premium Leather Backpack', price: '8,500', category: 'Accessories', brand: 'Samsonite', isNew: false, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 4, name: 'Ergonomic Office Chair', price: '18,000', category: 'Furniture', brand: 'Herman Miller', isNew: true, image: 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 5, name: '4K Action Camera', price: '22,000', category: 'Electronics', brand: 'GoPro', isNew: false, image: 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 6, name: 'Mechanical Keyboard', price: '6,500', category: 'Electronics', brand: 'Logitech', isNew: false, image: 'https://images.unsplash.com/photo-1587829741301-dc798b91add1?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
]);

// Derived Lists
const categories = computed(() => [...new Set(products.value.map(p => p.category))]);
const brands = computed(() => [...new Set(products.value.map(p => p.brand))]);

// Active Filter Helpers
const hasActiveFilters = computed(() => {
    return selectedCategories.value.length > 0 ||
        selectedBrands.value.length > 0 ||
        minPrice.value ||
        maxPrice.value;
});

const removeCategory = (cat) => {
    selectedCategories.value = selectedCategories.value.filter(c => c !== cat);
};

const removeBrand = (brand) => {
    selectedBrands.value = selectedBrands.value.filter(b => b !== brand);
};

const resetPrice = () => {
    minPrice.value = null;
    maxPrice.value = null;
};

// Filter Logic
const filteredProducts = computed(() => {
    return products.value.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategories.value.length === 0 || selectedCategories.value.includes(product.category);
        const matchesBrand = selectedBrands.value.length === 0 || selectedBrands.value.includes(product.brand);
        const price = parseFloat(product.price.replace(/,/g, ''));
        const matchesMinPrice = !minPrice.value || price >= minPrice.value;
        const matchesMaxPrice = !maxPrice.value || price <= maxPrice.value;

        return matchesSearch && matchesCategory && matchesBrand && matchesMinPrice && matchesMaxPrice;
    }).sort((a, b) => {
        const priceA = parseFloat(a.price.replace(/,/g, ''));
        const priceB = parseFloat(b.price.replace(/,/g, ''));

        if (sortBy.value === 'price-low') return priceA - priceB;
        if (sortBy.value === 'price-high') return priceB - priceA;
        if (sortBy.value === 'newest') return b.isNew ? 1 : -1;
        return 0;
    });
});

function resetFilters() {
    searchQuery.value = '';
    selectedCategories.value = [];
    selectedBrands.value = [];
    minPrice.value = null;
    maxPrice.value = null;
    sortBy.value = 'featured';
}
</script>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

.list-leave-active {
    position: absolute;
}
</style>
