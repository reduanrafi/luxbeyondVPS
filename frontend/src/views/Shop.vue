<template>
    <div class="min-h-screen bg-background">
        <div class="flex">
            <!-- Sidebar -->
            <aside
                class="hidden lg:block w-80 shrink-0 bg-surface border-r border-white/10 sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto">
                <!-- Sidebar Events Carousel -->
                <div v-if="sidebarEvents.length > 0" class="p-1 border-b border-white/10">
                    <div class="relative">
                        <!-- Carousel Container -->
                        <div class="overflow-hidden rounded-lg">
                            <div class="flex transition-transform duration-500 ease-in-out"
                                :style="{ transform: `translateY(-${currentSidebarSlide * 100}%)` }">
                                <div v-for="event in sidebarEvents" :key="event.id" class="w-full shrink-0">
                                    <router-link :to="event.url || `/shop?events=${event.slug}`"
                                        class="block rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300"
                                        :style="event.image_url
                                            ? {}
    : { backgroundColor: event.bg_color || '#7c3aed' }">
                                        <img v-if="event.image_url || event.banner_image_url"
                                            :src="event.image_url || event.banner_image_url" :alt="event.name"
                                            class="w-full h-20 object-cover" />
                                        <div v-else class="w-full h-32 flex items-center justify-center p-4"
                                            :style="{ backgroundColor: event.bg_color || '#7c3aed' }">
                                            <div class="text-center text-white">
                                                <h3 class="text-lg font-bold mb-1">{{ event.name }}</h3>
                                                <p v-if="event.short_description"
                                                    class="text-xs opacity-90 line-clamp-2">{{ event.short_description
                                                    }}</p>
                                            </div>
                                        </div>
                                        <!-- Only show button if no image and show_button is enabled -->
                                        <div v-if="!event.image_url && event.show_button && event.bg_color"
                                            class="p-3 text-center"
                                            :style="{ backgroundColor: event.button_color || '#7c3aed' }">
                                            <span class="text-white font-semibold text-sm">
                                                {{ event.button_text || 'Shop Now' }}
                                            </span>
                                        </div>
                                    </router-link>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div v-if="sidebarEvents.length > 1" class="flex items-center justify-center gap-2 mt-3">
                            <button @click="prevSidebarSlide"
                                class="p-1 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 transition-colors">
                                <ChevronUp class="w-4 h-4" />
                            </button>
                            <div class="flex gap-1">
                                <button v-for="(event, index) in sidebarEvents" :key="event.id"
                                    @click="currentSidebarSlide = index" class="w-2 h-2 rounded-full transition-all"
                                    :class="currentSidebarSlide === index ? 'bg-primary w-4' : 'bg-white/20'"></button>
                            </div>
                            <button @click="nextSidebarSlide"
                                class="p-1 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 transition-colors">
                                <ChevronDown class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
                <h2 class="p-4 border-b border-white/10 text-lg font-bold text-white mb-2" v-else>
                    Shop By Category
                </h2>
                <ShopSidebar />
            </aside>

            <!-- Main content -->
            <main class="flex-1 w-full min-h-[calc(100vh-5rem)] pb-10 mx-auto">
                <!-- Hero Events Carousel -->
                <div v-if="heroEvents.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                    <div class="relative">
                        <!-- Carousel Container -->
                        <div class="overflow-hidden rounded-2xl mt-5">
                            <div class="flex transition-transform duration-500 ease-in-out h-[400px]"
                                :style="{ transform: `translateX(-${currentHeroSlide * 100}%)` }">
                                <div v-for="event in heroEvents" :key="event.id" class="w-full shrink-0">
                                    <router-link :to="event.url || `/shop?events=${event.slug}`"
                                        class="block overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300"
                                        :style="event.banner_image_url || event.image_url
                                            ? {}
                                            : { backgroundColor: event.bg_color || '#7c3aed' }">
                                        <img v-if="event.banner_image_url || event.image_url"
                                            :src="event.banner_image_url || event.image_url" :alt="event.name"
                                            class="w-full h-full object-cover object-center" />
                                        <!-- Only show text/button if no banner/image -->
                                        <div v-else class="w-full h-64 flex items-center justify-center p-8"
                                            :style="{ backgroundColor: event.bg_color || '#7c3aed' }">
                                            <div class="text-center text-white">
                                                <h2 class="text-3xl font-bold mb-2">{{ event.name }}</h2>
                                                <p v-if="event.short_description" class="text-lg opacity-90">{{
                                                    event.short_description }}</p>
                                                <button v-if="event.show_button"
                                                    class="mt-4 px-6 py-3 rounded-lg font-semibold text-white transition-all hover:scale-105"
                                                    :style="{ backgroundColor: event.button_color || '#ffffff', color: getContrastColor(event.button_color || '#ffffff') }">
                                                    {{ event.button_text || 'Shop Now' }}
                                                </button>
                                            </div>
                                        </div>
                                    </router-link>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <button v-if="heroEvents.length > 1" @click="prevHeroSlide"
                            class="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-surface/90 hover:bg-surface shadow-lg transition-all z-10">
                            <ChevronLeft class="w-6 h-6 text-slate-300" />
                        </button>
                        <button v-if="heroEvents.length > 1" @click="nextHeroSlide"
                            class="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-surface/90 hover:bg-surface shadow-lg transition-all z-10">
                            <ChevronRight class="w-6 h-6 text-slate-300" />
                        </button>

                        <!-- Indicators -->
                        <div v-if="heroEvents.length > 1"
                            class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                            <button v-for="(event, index) in heroEvents" :key="event.id"
                                @click="currentHeroSlide = index" class="w-2 h-2 rounded-full transition-all"
                                :class="currentHeroSlide === index ? 'bg-surface w-6' : 'bg-white/50'"></button>
                        </div>
                    </div>
                </div>

                <!-- Hero Section -->
                <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 hidden lg:block">
                    <HeroSection />
                </div> -->

                <!-- Products Section -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 px-4">

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <!-- Mobile Filter Button -->
                        <button @click="showMobileFilters = true"
                            class="lg:hidden w-full md:w-auto px-4 py-2 bg-surface border border-white/10 text-slate-300 font-medium rounded-none hover:border-primary hover:text-primary transition-colors flex items-center justify-center gap-2 uppercase tracking-wider text-sm">
                            <Filter class="w-4 h-4" />
                            Filters
                        </button>

                        <div v-if="filters.category"
                            class="p-2 border border-gray-2 text-xs rounded-full flex items-center gap-2 cursor-pointer"
                            @click="clearFilters">
                            {{ filters.category }}
                            <X class="w-4 h-4" />
                        </div>

                    </div>

                    <!-- Toolbar: View Mode & Sort -->
                    <div class="flex items-center justify-between mb-6">
                        <!-- Sort Dropdown -->
                        <select v-model="sortBy" @change="applyFilters"
                            class="py-2 bg-surface border border-white/10 rounded-none text-sm text-slate-300 focus:outline-none focus:border-primary transition-colors cursor-pointer uppercase tracking-wider">
                            <option value="latest">Latest</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="name_desc">Name: Z to A</option>
                        </select>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1 bg-surface border border-white/10 p-1">
                                <button @click="viewMode = 'grid'" :class="[
                                    'p-2 transition-colors rounded-sm',
                                    viewMode === 'grid' ? 'bg-primary text-slate-900' : 'text-slate-500 hover:text-primary'
                                ]">
                                    <Grid class="w-4 h-4" />
                                </button>
                                <button @click="viewMode = 'list'" :class="[
                                    'p-2 transition-colors rounded-sm',
                                    viewMode === 'list' ? 'bg-primary text-slate-900' : 'text-slate-500 hover:text-primary'
                                ]">
                                    <List class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <!-- <span class="text-sm text-gray-600">
                                Showing {{ products.length }} of {{ totalProducts }} products
                            </span> -->
                    </div>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Loading State -->
                    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div v-for="n in 8" :key="n" class="bg-surface rounded-xl border border-white/10 animate-pulse">
                            <div class="aspect-square bg-white/5"></div>
                            <div class="p-4 space-y-2">
                                <div class="h-4 bg-white/10 rounded w-3/4"></div>
                                <div class="h-4 bg-white/10 rounded w-1/2"></div>
                                <div class="h-6 bg-white/10 rounded w-1/3"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid/List -->
                    <div v-else-if="products.length > 0" :class="[
                        'gap-6',
                        viewMode === 'grid'
                            ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4'
                            : 'flex flex-col space-y-1'
                    ]">
                        <ProductCard v-for="product in products" :key="product.id" :product="product"
                            :view-mode="viewMode" />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16">
                        <Package class="w-16 h-16 text-slate-600 mx-auto mb-4" />
                        <h3 class="text-xl font-bold text-white mb-2">No products found</h3>
                        <p class="text-slate-400 mb-6">Try adjusting your filters or search query</p>
                        <button @click="clearFilters"
                            class="px-6 py-3 bg-primary text-slate-900 font-semibold rounded-full hover:bg-primary-hover hover:rounded-full transition-all">
                            Clear Filters
                        </button>

                        or
                        <router-link to="/request-product"
                            class="px-6 py-3 bg-primary text-slate-900 font-semibold rounded-full hover:bg-primary-hover hover:rounded-full transition-all">
                            Request a Product
                        </router-link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="mt-12 flex items-center justify-center gap-2">
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                            class="p-2 border border-white/10 text-slate-500 hover:border-primary hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-none">
                            <ChevronLeft class="w-5 h-5" />
                        </button>

                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="[
                            'px-4 py-2 border transition-all text-sm font-medium rounded-none',
                            page === currentPage
                                ? 'bg-primary border-primary text-slate-900 font-bold'
                                : 'border-white/10 text-slate-500 hover:border-primary hover:text-primary'
                        ]">
                            {{ page }}
                        </button>

                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                            class="p-2 border border-white/10 text-slate-500 hover:border-primary hover:text-primary disabled:opacity-30 disabled:cursor-not-allowed transition-all rounded-none">
                            <ChevronRight class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- Mobile Filters Drawer -->
        <div v-if="showMobileFilters" class="fixed inset-0 z-50 lg:hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
                @click="showMobileFilters = false"></div>

            <!-- Drawer Panel -->
            <div
                class="absolute inset-y-0 left-0 w-80 max-w-[85vw] bg-surface border-r border-white/10 shadow-2xl transform transition-transform duration-300 flex flex-col">
                <div class="p-4 border-b border-white/10 flex items-center justify-between bg-surface">
                    <h2 class="text-lg font-bold text-white uppercase tracking-wider">Filters</h2>
                    <button @click="showMobileFilters = false"
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-full transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <ShopSidebar />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Search, Filter, Grid, List, Package, ChevronLeft, ChevronRight, Layers, Tag, Sparkles, X, ChevronUp, ChevronDown } from 'lucide-vue-next';
import axios from '../axios';
import ProductCard from '../components/ProductCard.vue';
import ShopSidebar from '@/components/shop/ShopSidebar.vue';

const route = useRoute();
const router = useRouter();

// Mobile Filters State
const showMobileFilters = ref(false);

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
const heroEvents = ref([]);
const sidebarEvents = ref([]);
const currentHeroSlide = ref(0);
const currentSidebarSlide = ref(0);

// Auto-play carousels - use refs to maintain state
const heroCarouselInterval = ref(null);
const sidebarCarouselInterval = ref(null);

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

        // Event filter from URL query
        if (route.query.events) {
            params.events = route.query.events;
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

const popularSearches = ['Electronics', 'Fashion', 'Accessories', 'Beauty', 'Home & Garden'];

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
    // Remove category and events from URL
    const query = { ...route.query };
    delete query.category;
    delete query.events;
    router.replace({ query });
    applyFilters();
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchProducts();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

// Fetch events for display
const fetchEvents = async () => {
    try {
        // Fetch hero events
        const heroResponse = await axios.get('/events', {
            params: { position: 'hero', status: 'live' }
        });
        heroEvents.value = heroResponse.data || [];
        currentHeroSlide.value = 0; // Reset to first slide

        // Fetch sidebar events
        const sidebarResponse = await axios.get('/events', {
            params: { position: 'sidebar', status: 'live' }
        });
        sidebarEvents.value = sidebarResponse.data || [];
        currentSidebarSlide.value = 0; // Reset to first slide

        // Start carousels after fetching - use nextTick to ensure DOM is ready
        await nextTick();
        setTimeout(() => {
            startCarousels();
        }, 100);
    } catch (error) {
        console.error('Error fetching events:', error);
    }
};

// Get contrast color for button text
const getContrastColor = (hexColor) => {
    if (!hexColor) return '#000000';
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 128 ? '#000000' : '#ffffff';
};

// Hero carousel navigation
const nextHeroSlide = () => {
    if (currentHeroSlide.value < heroEvents.value.length - 1) {
        currentHeroSlide.value++;
    } else {
        currentHeroSlide.value = 0; // Loop back to start
    }
};

const prevHeroSlide = () => {
    if (currentHeroSlide.value > 0) {
        currentHeroSlide.value--;
    } else {
        currentHeroSlide.value = heroEvents.value.length - 1; // Loop to end
    }
};

// Sidebar carousel navigation
const nextSidebarSlide = () => {
    if (currentSidebarSlide.value < sidebarEvents.value.length - 1) {
        currentSidebarSlide.value++;
    } else {
        currentSidebarSlide.value = 0; // Loop back to start
    }
};

const prevSidebarSlide = () => {
    if (currentSidebarSlide.value > 0) {
        currentSidebarSlide.value--;
    } else {
        currentSidebarSlide.value = sidebarEvents.value.length - 1; // Loop to end
    }
};

// Auto-play carousels
const startCarousels = () => {
    // Stop any existing intervals first
    stopCarousels();

    // Auto-play hero carousel (every 5 seconds)
    if (heroEvents.value && heroEvents.value.length > 1) {
        heroCarouselInterval.value = setInterval(() => {
            if (heroEvents.value.length > 0) {
                nextHeroSlide();
            }
        }, 5000);
    }

    // Auto-play sidebar carousel (every 6 seconds)
    if (sidebarEvents.value && sidebarEvents.value.length > 1) {
        sidebarCarouselInterval.value = setInterval(() => {
            if (sidebarEvents.value.length > 0) {
                nextSidebarSlide();
            }
        }, 6000);
    }
};

const stopCarousels = () => {
    if (heroCarouselInterval.value) {
        clearInterval(heroCarouselInterval.value);
        heroCarouselInterval.value = null;
    }
    if (sidebarCarouselInterval.value) {
        clearInterval(sidebarCarouselInterval.value);
        sidebarCarouselInterval.value = null;
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
    fetchEvents();
});

// Watch for route query changes (category filter)
watch(() => route.query.category, (newCategory) => {
    filters.value.category = newCategory || '';
    currentPage.value = 1;
    fetchProducts();
}, { immediate: false });

// Watch for event query parameter changes
watch(() => route.query.events, (newEvent) => {
    currentPage.value = 1;
    fetchProducts();
}, { immediate: false });

// Watch for filter changes and update URL (but only when category is set)
watch(() => filters.value.category, (newCategory) => {
    if (newCategory) {
        router.replace({ query: { ...route.query, category: newCategory } });
    } else {
        // Remove category from URL if cleared
        const query = { ...route.query };
        delete query.category;
        router.replace({ query });
    }
});

watch([currentPage, sortBy], () => {
    fetchProducts();
});
</script>
