<template>
    <section id="products" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary/10 text-primary rounded-full text-sm font-semibold mb-4">
                    <Star class="w-4 h-4 fill-current" />
                    Featured Collection
                </div>
                <h2 class="text-4xl font-bold text-gray-900 sm:text-5xl mb-4">
                    Handpicked for You
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Discover our curated selection of premium products, ready for immediate delivery
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="n in 4" :key="n" class="animate-pulse">
                    <div class="bg-gray-200 rounded-2xl h-80 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Products Grid -->
            <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col"
                >
                    <!-- Product Image -->
                    <div class="relative overflow-hidden bg-gray-100 aspect-square">
                        <router-link :to="`/products/${product.id}`" class="block h-full">
                            <img
                                :src="getProductImage(product)"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                @error="handleImageError"
                            />
                        </router-link>

                        <!-- Discount Badge -->
                        <div
                            v-if="product.sellable_price && product.price > product.sellable_price"
                            class="absolute top-4 left-4 bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg"
                        >
                            -{{ calculateDiscount(product.price, product.sellable_price) }}%
                        </div>

                        <!-- Featured Badge -->
                        <div
                            v-if="product.is_featured"
                            class="absolute top-4 right-4 bg-primary text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1"
                        >
                            <Star class="w-3 h-3 fill-current" />
                            Featured
                        </div>

                        <!-- Wishlist Button -->
                        <button
                            @click.prevent="toggleWishlist(product)"
                            class="absolute bottom-4 right-4 p-2.5 bg-white/95 backdrop-blur-sm rounded-full hover:bg-white transition-all duration-200 shadow-lg hover:scale-110 opacity-0 group-hover:opacity-100"
                            :class="wishlistStore.isInWishlist(product.id) ? 'opacity-100 text-red-500' : 'text-gray-600'"
                        >
                            <Heart
                                class="w-5 h-5"
                                :fill="wishlistStore.isInWishlist(product.id) ? 'currentColor' : 'none'"
                            />
                        </button>

                        <!-- Quick View Overlay -->
                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center"
                        >
                            <router-link
                                :to="`/products/${product.id}`"
                                class="px-6 py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-primary hover:text-white transform translate-y-4 group-hover:translate-y-0 transition-all duration-300"
                            >
                                Quick View
                            </router-link>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Category -->
                        <div class="mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                {{ product.category || 'Uncategorized' }}
                            </span>
                        </div>

                        <!-- Product Name -->
                        <router-link :to="`/products/${product.id}`" class="mb-3 group/link">
                            <h3 class="text-lg font-bold text-gray-900 line-clamp-2 group-hover/link:text-primary transition-colors min-h-14">
                                {{ product.name }}
                            </h3>
                        </router-link>

                        <!-- Brand -->
                        <p v-if="product.brand" class="text-sm text-gray-500 mb-3">
                            {{ product.brand }}
                        </p>

                        <!-- Price -->
                        <div class="flex items-baseline gap-2 mb-4">
                            <span class="text-2xl font-bold text-primary">
                                ${{ formatPrice(product.sellable_price || product.price) }}
                            </span>
                            <span
                                v-if="product.sellable_price && product.price > product.sellable_price"
                                class="text-sm text-gray-400 line-through"
                            >
                                ${{ formatPrice(product.price) }}
                            </span>
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-4">
                            <span
                                v-if="product.in_stock"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-green-600 bg-green-50 px-2.5 py-1 rounded-full"
                            >
                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                In Stock
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-full"
                            >
                                <div class="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                                Out of Stock
                            </span>
                        </div>

                        <!-- Add to Cart Button -->
                        <button
                            @click.prevent="addToCart(product)"
                            :disabled="!product.in_stock"
                            class="w-full px-4 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2 shadow-sm hover:shadow-md"
                        >
                            <ShoppingCart class="w-5 h-5" />
                            <span>{{ product.in_stock ? 'Add to Cart' : 'Out of Stock' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <Package class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Featured Products</h3>
                <p class="text-gray-500 mb-6">Check back soon for our featured collection</p>
                <router-link to="/shop">
                    <Button variant="primary" size="lg">Browse All Products</Button>
                </router-link>
            </div>

            <!-- View All Button -->
            <div v-if="products.length > 0" class="text-center mt-12">
                <router-link to="/shop">
                    <Button variant="outline" size="lg" class="px-8">
                        View All Products
                        <ArrowRight class="w-5 h-5 ml-2 inline" />
                    </Button>
                </router-link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Button from './ui/Button.vue';
import { ShoppingCart, Heart, Star, Package, ArrowRight } from 'lucide-vue-next';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import axios from '../axios';

const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const products = ref([]);
const loading = ref(true);

const fetchFeaturedProducts = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/products', {
            params: {
                featured: true,
                per_page: 8
            }
        });

        // Handle paginated response
        if (response.data.data) {
            products.value = Array.isArray(response.data.data)
                ? response.data.data
                : response.data.data.data || [];
        } else {
            products.value = Array.isArray(response.data) ? response.data : [];
        }
    } catch (error) {
        console.error('Error fetching featured products:', error);
        products.value = [];
    } finally {
        loading.value = false;
    }
};

const getProductImage = (product) => {
    if (product.image_url) {
        return product.image_url;
    }
    if (product.image) {
        return product.image.startsWith('http') ? product.image : `/storage/${product.image}`;
    }
    return '/assets/placeholder.png';
};

const handleImageError = (event) => {
    event.target.src = '/assets/placeholder.png';
};

const formatPrice = (price) => {
    if (!price) return '0.00';
    return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const calculateDiscount = (originalPrice, salePrice) => {
    if (!originalPrice || !salePrice) return 0;
    return Math.round(((originalPrice - salePrice) / originalPrice) * 100);
};

const addToCart = (product) => {
    if (!product.in_stock) return;
    
    // Format product for cart
    const cartProduct = {
        ...product,
        price: `$${formatPrice(product.sellable_price || product.price)}`
    };
    
    cartStore.addItem(cartProduct);
};

const toggleWishlist = (product) => {
    wishlistStore.toggleItem(product);
};

onMounted(() => {
    fetchFeaturedProducts();
});
</script>
