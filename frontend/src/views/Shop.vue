<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Hero Section with Search -->
        <div class="bg-gradient-to-br from-primary via-primary/90 to-primary/80 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Discover Amazing Products</h1>
                    <p class="text-lg text-white/90 max-w-2xl mx-auto">
                        Shop from thousands of premium products with fast delivery and secure payment
                    </p>
                </div>

                <!-- Search Bar -->
                <div class="max-w-3xl mx-auto">
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search products, brands, categories..."
                            class="w-full pl-12 pr-4 py-4 rounded-xl border-0 focus:ring-2 focus:ring-white/50 text-gray-900 text-lg shadow-lg"
                        />
                        <button
                            @click="applyFilters"
                            class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2 bg-primary-dark hover:bg-primary-darker text-white font-semibold rounded-lg transition-all"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Categories Section -->
            <div v-if="categories.length > 0" class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Shop by Category</h2>
                    <router-link to="/shop" class="text-primary hover:text-primary-dark font-medium text-sm">
                        View All
                    </router-link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <router-link
                        v-for="category in categories.slice(0, 6)"
                        :key="category.id"
                        :to="`/shop?category=${category.name}`"
                        class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-lg hover:border-primary transition-all text-center"
                    >
                        <div class="w-16 h-16 bg-gradient-to-br from-primary/10 to-primary/5 rounded-xl mx-auto mb-3 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <Layers class="w-8 h-8 text-primary" />
                        </div>
                        <h3 class="font-semibold text-gray-900 text-sm group-hover:text-primary transition-colors">
                            {{ category.name }}
                        </h3>
                    </router-link>
                </div>
            </div>

            <!-- Brands Section -->
            <div v-if="brands.length > 0" class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Popular Brands</h2>
                    <button @click="showAllBrands = !showAllBrands" class="text-primary hover:text-primary-dark font-medium text-sm">
                        {{ showAllBrands ? 'Show Less' : 'View All' }}
                    </button>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <button
                        v-for="brand in (showAllBrands ? brands : brands.slice(0, 6))"
                        :key="brand.id"
                        @click="filterByBrand(brand.name)"
                        class="group bg-white rounded-xl shadow-sm border-2 p-4 hover:shadow-lg transition-all text-center"
                        :class="filters.brands.includes(brand.name) ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-primary'"
                    >
                        <div class="w-16 h-16 bg-gray-100 rounded-xl mx-auto mb-3 flex items-center justify-center overflow-hidden">
                            <img
                                v-if="brand.image_url"
                                :src="brand.image_url"
                                :alt="brand.name"
                                class="w-full h-full object-cover"
                            />
                            <Tag v-else class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="font-semibold text-gray-900 text-sm group-hover:text-primary transition-colors">
                            {{ brand.name }}
                        </h3>
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters -->
                <aside class="lg:w-80 flex-shrink-0">
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 sticky top-4">
                        <!-- Filter Header -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                    <Filter class="w-5 h-5" />
                                    Filters
                                </h2>
                                <button
                                    @click="clearFilters"
                                    class="text-sm text-primary hover:text-primary-dark font-medium"
                                >
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <div class="p-6 space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                            <!-- Categories -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Categories</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                        <input
                                            type="radio"
                                            v-model="filters.category"
                                            value=""
                                            @change="applyFilters"
                                            class="w-4 h-4 text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm text-gray-700">All Categories</span>
                                    </label>
                                    <label
                                        v-for="category in categories"
                                        :key="category.id"
                                        class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                                    >
                                        <input
                                            type="radio"
                                            v-model="filters.category"
                                            :value="category.name"
                                            @change="applyFilters"
                                            class="w-4 h-4 text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm text-gray-700">{{ category.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Price Range (৳)</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <input
                                            v-model.number="filters.minPrice"
                                            type="number"
                                            placeholder="Min"
                                            @change="applyFilters"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                        <span class="text-gray-500">-</span>
                                        <input
                                            v-model.number="filters.maxPrice"
                                            type="number"
                                            placeholder="Max"
                                            @change="applyFilters"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Brands -->
                            <div v-if="brands.length > 0">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Brands</h3>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <label
                                        v-for="brand in brands"
                                        :key="brand.id"
                                        class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="filters.brands"
                                            :value="brand.name"
                                            @change="applyFilters"
                                            class="w-4 h-4 text-primary focus:ring-primary rounded"
                                        />
                                        <span class="text-sm text-gray-700">{{ brand.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Stock Status -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Stock Status</h3>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                        <input
                                            type="checkbox"
                                            v-model="filters.inStock"
                                            @change="applyFilters"
                                            class="w-4 h-4 text-primary focus:ring-primary rounded"
                                        />
                                        <span class="text-sm text-gray-700">In Stock</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                        <input
                                            type="checkbox"
                                            v-model="filters.featured"
                                            @change="applyFilters"
                                            class="w-4 h-4 text-primary focus:ring-primary rounded"
                                        />
                                        <span class="text-sm text-gray-700">Featured Only</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1">
                    <!-- Toolbar -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-4 mb-6">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <!-- Results Count -->
                            <div class="text-sm text-gray-600">
                                Showing <span class="font-semibold text-gray-900">{{ products.length }}</span> of
                                <span class="font-semibold text-gray-900">{{ totalProducts }}</span> products
                            </div>

                            <!-- View Options & Sort -->
                            <div class="flex items-center gap-4">
                                <!-- View Toggle -->
                                <div class="flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                                    <button
                                        @click="viewMode = 'grid'"
                                        :class="viewMode === 'grid' ? 'bg-white text-primary shadow-sm' : 'text-gray-600'"
                                        class="p-2 rounded-md transition-all"
                                    >
                                        <Grid class="w-5 h-5" />
                                    </button>
                                    <button
                                        @click="viewMode = 'list'"
                                        :class="viewMode === 'list' ? 'bg-white text-primary shadow-sm' : 'text-gray-600'"
                                        class="p-2 rounded-md transition-all"
                                    >
                                        <List class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Sort -->
                                <select
                                    v-model="sortBy"
                                    @change="applyFilters"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-sm"
                                >
                                    <option value="latest">Latest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="name_asc">Name: A to Z</option>
                                    <option value="name_desc">Name: Z to A</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="n in 6" :key="n" class="animate-pulse">
                            <div class="bg-gray-200 rounded-2xl h-80 mb-4"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="products.length === 0" class="bg-white rounded-2xl shadow-md border border-gray-200 p-12 text-center">
                        <Package class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-600 mb-6">Try adjusting your filters or search terms</p>
                        <button
                            @click="clearFilters"
                            class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-all"
                        >
                            Clear Filters
                        </button>
                    </div>

                    <!-- Products Grid -->
                    <div
                        v-else
                        :class="[
                            'grid gap-6',
                            viewMode === 'grid'
                                ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'
                                : 'grid-cols-1'
                        ]"
                    >
                        <ProductCard
                            v-for="product in products"
                            :key="product.id"
                            :product="product"
                            :view-mode="viewMode"
                        />
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="mt-8 flex justify-center">
                        <div class="flex items-center gap-2">
                            <button
                                @click="goToPage(currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            >
                                <ChevronLeft class="w-5 h-5" />
                            </button>
                            <button
                                v-for="page in visiblePages"
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-all',
                                    page === currentPage
                                        ? 'bg-primary text-white font-semibold'
                                        : 'border border-gray-300 hover:bg-gray-50'
                                ]"
                            >
                                {{ page }}
                            </button>
                            <button
                                @click="goToPage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            >
                                <ChevronRight class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Search, Filter, Grid, List, Package, ChevronLeft, ChevronRight, Layers, Tag } from 'lucide-vue-next';
import axios from '../axios';
import ProductCard from '../components/ProductCard.vue';

const route = useRoute();
const products = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const viewMode = ref('grid');
const sortBy = ref('latest');
const totalProducts = ref(0);
const currentPage = ref(1);
const perPage = ref(12);
const showAllBrands = ref(false);

const filters = ref({
    category: '',
    minPrice: null,
    maxPrice: null,
    brands: [],
    inStock: false,
    featured: false,
});

const totalPages = computed(() => {
    return Math.ceil(totalProducts.value / perPage.value);
});

const visiblePages = computed(() => {
    const pages = [];
    const maxVisible = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages.value, start + maxVisible - 1);
    
    if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1);
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const fetchProducts = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
        };

        if (searchQuery.value) {
            params.search = searchQuery.value;
        }

        if (filters.value.category) {
            params.category = filters.value.category;
        }

        if (filters.value.minPrice !== null) {
            params.min_price = filters.value.minPrice;
        }

        if (filters.value.maxPrice !== null) {
            params.max_price = filters.value.maxPrice;
        }

        if (filters.value.brands.length > 0) {
            params.brands = filters.value.brands.join(',');
        }

        if (filters.value.inStock) {
            params.status = 'in_stock';
        }

        if (filters.value.featured) {
            params.featured = true;
        }

        // Sort
        switch (sortBy.value) {
            case 'price_low':
                params.sort = 'price';
                params.order = 'asc';
                break;
            case 'price_high':
                params.sort = 'price';
                params.order = 'desc';
                break;
            case 'name_asc':
                params.sort = 'name';
                params.order = 'asc';
                break;
            case 'name_desc':
                params.sort = 'name';
                params.order = 'desc';
                break;
            default:
                params.sort = 'created_at';
                params.order = 'desc';
        }

        const response = await axios.get('/products', { params });

        // Handle paginated response
        if (response.data.data) {
            products.value = Array.isArray(response.data.data)
                ? response.data.data
                : [];
            totalProducts.value = response.data.total || response.data.data.length;
        } else if (Array.isArray(response.data)) {
            products.value = response.data;
            totalProducts.value = response.data.length;
        } else {
            products.value = [];
            totalProducts.value = 0;
        }
    } catch (error) {
        console.error('Error fetching products:', error);
        products.value = [];
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get('/categories');
        categories.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const fetchBrands = async () => {
    try {
        const response = await axios.get('/brands', { params: { all: true } });
        brands.value = response.data || [];
    } catch (error) {
        console.error('Error fetching brands:', error);
    }
};

const handleSearch = () => {
    // Debounce search
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        currentPage.value = 1;
        applyFilters();
    }, 500);
};

const searchTimeout = ref(null);

const filterByBrand = (brandName) => {
    const index = filters.value.brands.indexOf(brandName);
    if (index > -1) {
        filters.value.brands.splice(index, 1);
    } else {
        filters.value.brands.push(brandName);
    }
    applyFilters();
};

const applyFilters = () => {
    currentPage.value = 1;
    fetchProducts();
};

const clearFilters = () => {
    filters.value = {
        category: '',
        minPrice: null,
        maxPrice: null,
        brands: [],
        inStock: false,
        featured: false,
    };
    searchQuery.value = '';
    sortBy.value = 'latest';
    applyFilters();
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchProducts();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

// Check URL params on mount
onMounted(() => {
    if (route.query.category) {
        filters.value.category = route.query.category;
    }
    fetchProducts();
    fetchCategories();
    fetchBrands();
});

watch([currentPage], () => {
    fetchProducts();
});
</script>
