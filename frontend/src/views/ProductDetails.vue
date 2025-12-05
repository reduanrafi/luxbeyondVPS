<template>
    <div class="bg-gray-50 min-h-screen pt-20 pb-10">
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="animate-pulse">
                <div class="h-6 bg-gray-200 rounded w-1/4 mb-6"></div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="h-96 bg-gray-200 rounded-2xl"></div>
                    <div class="space-y-4">
                        <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-12 bg-gray-200 rounded w-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-600">
                    <li><router-link to="/" class="hover:text-primary transition-colors">Home</router-link></li>
                    <li>/</li>
                    <li><router-link to="/shop" class="hover:text-primary transition-colors">Shop</router-link></li>
                    <li v-if="product.category">/</li>
                    <li v-if="product.category">
                        <router-link :to="`/shop?category=${product.category}`" class="hover:text-primary transition-colors">
                            {{ product.category }}
                        </router-link>
                    </li>
                    <li>/</li>
                    <li class="text-gray-900 font-medium">{{ product.name }}</li>
                </ol>
            </nav>

            <!-- Product Main Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6">
                <!-- Product Images -->
                <div class="space-y-4">
                    <div class="rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg h-80 lg:h-[420px] relative group">
                        <img
                            :src="selectedImage"
                            :alt="product.name"
                            class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                        />
                        <!-- Zoom on hover -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>
                    </div>
                    <div v-if="productImages.length > 1" class="grid grid-cols-4 gap-3">
                        <button
                            v-for="(img, index) in productImages"
                            :key="index"
                            @click="selectedImage = img"
                            class="rounded-lg overflow-hidden border-2 transition-all hover:scale-105"
                            :class="selectedImage === img ? 'border-primary shadow-md ring-2 ring-primary/20' : 'border-gray-200 hover:border-gray-300'"
                        >
                            <img :src="img" :alt="`${product.name} ${index + 1}`" class="w-full h-20 object-contain">
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                    <!-- Category & Brand -->
                    <div class="flex items-center gap-3 mb-4">
                        <span v-if="product.category" class="px-3 py-1 bg-primary/10 text-primary text-xs font-semibold rounded-full">
                            {{ product.category }}
                        </span>
                        <span v-if="product.brand" class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                            {{ product.brand }}
                        </span>
                        <span v-if="product.is_featured" class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full flex items-center gap-1">
                            <Star class="w-3 h-3 fill-current" />
                            Featured
                        </span>
                    </div>

                    <h1 class="font-bold text-gray-900">{{ product.name }}</h1>

                    <!-- SKU -->
                    <p v-if="product.sku" class="text-sm text-gray-500 mb-4">SKU: {{ product.sku }}</p>

                    <!-- Price -->
                    <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-200">
                        <span class="text-xl font-bold text-primary">
                            ৳{{ formatPrice(product.event_price || product.sellable_price || product.price) }}
                        </span>
                        <span
                            v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
                            class="text-sm text-gray-400 line-through"
                        >
                            ৳{{ formatPrice(product.event_price ? product.original_price : product.price) }}
                        </span>
                        <span
                            v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
                            class="px-3 py-1 bg-red-100 text-red-700 text-sm font-bold rounded-full"
                        >
                            -{{ calculateDiscount(
                                product.event_price ? product.original_price : product.price,
                                product.event_price || product.sellable_price || product.price
                            ) }}%
                        </span>
                    </div>

                    <!-- Short Description -->
                    <p v-if="product.short_description" class="text-gray-600 mb-4">
                        {{ product.short_description }}
                    </p>

                    <!-- Stock Status -->
                    <div class="mb-6 p-4 rounded-lg"
                        :class="(product.total_stock || 0) > 10 ? 'bg-green-50 border border-green-200' : (product.total_stock || 0) > 0 ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200'">
                        <div class="flex items-center gap-2 mb-2">
                            <div
                                class="w-3 h-3 rounded-full"
                                :class="(product.total_stock || 0) > 10 ? 'bg-green-500' : (product.total_stock || 0) > 0 ? 'bg-yellow-500' : 'bg-red-500'"
                            ></div>
                            <span
                                class="text-sm font-semibold"
                                :class="(product.total_stock || 0) > 10 ? 'text-green-700' : (product.total_stock || 0) > 0 ? 'text-yellow-700' : 'text-red-700'"
                            >
                                {{
                                    (product.total_stock || 0) > 10
                                        ? 'In Stock'
                                        : (product.total_stock || 0) > 0
                                        ? 'Low Stock'
                                        : 'Out of Stock'
                                }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">
                            {{ (product.total_stock || 0) > 0 ? `${product.total_stock} units available` : 'Currently unavailable' }}
                        </p>
                    </div>

                    <!-- Variants (if available) -->
                    <div v-if="product.has_variants && product.variants && product.variants.length > 0" class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Select Variant</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="variant in product.variants"
                                :key="variant.id"
                                @click="selectedVariant = variant"
                                class="p-3 border-2 rounded-lg text-left transition-all hover:border-primary flex justify-between"
                                :class="selectedVariant?.id === variant.id ? 'border-primary bg-primary/5' : 'border-gray-200'"
                            >
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ formatVariantAttributes(variant.attributes) }}
                                </div>
                                <div class="text-sm text-primary font-semibold mt-1">
                                    ৳{{ formatPrice(variant.price) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Stock: {{ variant.stock }}
                                </div>
                            </div>
                            <img :src="variant.image_url" :alt="variant.name" class="w-16 h-16 object-contain rounded" v-if="variant.image_url">
                            </button>
                        </div>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                            <div class="flex items-center gap-3">
                                <button
                                    @click="decrementQty"
                                    :disabled="quantity <= 1"
                                    class="w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition-all"
                                >
                                    <Minus class="w-4 h-4" />
                                </button>
                                <input
                                    type="number"
                                    v-model.number="quantity"
                                    min="1"
                                    :max="maxQuantity"
                                    class="w-20 text-center border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                />
                                <button
                                    @click="incrementQty"
                                    :disabled="quantity >= maxQuantity"
                                    class="w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition-all"
                                >
                                    <Plus class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="addToCart"
                                :disabled="(product.total_stock || 0) === 0"
                                class="flex-1 bg-primary text-white font-semibold py-4 rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                            >
                                <ShoppingCart class="w-5 h-5" />
                                Add to Cart
                            </button>
                            <button
                                @click="toggleWishlist"
                                class="w-14 h-14 rounded-lg border-2 transition-all flex items-center justify-center"
                                :class="isInWishlist ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-red-500'"
                            >
                                <Heart class="w-6 h-6" :class="isInWishlist ? 'text-red-500 fill-red-500' : 'text-gray-400'" />
                            </button>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3">Key Features</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start gap-2 text-sm text-gray-600">
                                <CheckCircle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                                Fast & Secure Delivery
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600">
                                <CheckCircle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                                7-Day Return Policy
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600">
                                <CheckCircle class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
                                Genuine Product Guarantee
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-10">
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex gap-8 overflow-x-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab"
                            @click="activeTab = tab"
                            class="pb-4 text-sm font-semibold transition-all relative whitespace-nowrap"
                            :class="activeTab === tab ? 'text-primary' : 'text-gray-600 hover:text-gray-900'"
                        >
                            {{ tab }}
                            <div v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div v-if="activeTab === 'Description'" class="prose max-w-none">
                    <div v-html="product.description || product.short_description || 'No description available.'" class="text-gray-600 leading-relaxed"></div>
                </div>

                <div v-if="activeTab === 'Specifications'">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="product.sku" class="flex py-3 border-b border-gray-100">
                            <dt class="font-semibold text-gray-700 w-1/2">SKU</dt>
                            <dd class="text-gray-600 w-1/2">{{ product.sku }}</dd>
                        </div>
                        <div v-if="product.category" class="flex py-3 border-b border-gray-100">
                            <dt class="font-semibold text-gray-700 w-1/2">Category</dt>
                            <dd class="text-gray-600 w-1/2">{{ product.category }}</dd>
                        </div>
                        <div v-if="product.brand" class="flex py-3 border-b border-gray-100">
                            <dt class="font-semibold text-gray-700 w-1/2">Brand</dt>
                            <dd class="text-gray-600 w-1/2">{{ product.brand }}</dd>
                        </div>
                        <div class="flex py-3 border-b border-gray-100">
                            <dt class="font-semibold text-gray-700 w-1/2">Stock</dt>
                            <dd class="text-gray-600 w-1/2">{{ product.total_stock || 0 }} units</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="activeTab === 'Reviews'">
                    <div class="text-center py-12">
                        <p class="text-gray-600">No reviews yet. Be the first to review this product!</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">You May Also Like</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <ProductCard
                        v-for="item in relatedProducts"
                        :key="item.id"
                        :product="item"
                        view-mode="grid"
                    />
                </div>
            </div>
        </div>

        <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <Package class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-xl font-bold text-gray-900 mb-2">Product Not Found</h3>
            <p class="text-gray-600 mb-6">The product you're looking for doesn't exist or has been removed.</p>
            <router-link
                to="/shop"
                class="inline-block px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-all"
            >
                Back to Shop
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import { Star, ShoppingCart, Heart, CheckCircle, Minus, Plus, Package } from 'lucide-vue-next';
import axios from '../axios';
import ProductCard from '../components/ProductCard.vue';

const route = useRoute();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const product = ref(null);
const relatedProducts = ref([]);
const loading = ref(true);
const quantity = ref(1);
const activeTab = ref('Description');
const tabs = ['Description', 'Specifications', 'Reviews'];
const selectedVariant = ref(null);

const productImages = computed(() => {
    if (!product.value) return [];
    const images = [];
    
    // Add main product image
    if (product.value.image_url) {
        images.push(product.value.image_url);
    } else if (product.value.image) {
        images.push(product.value.image.startsWith('http') ? product.value.image : `/storage/${product.value.image}`);
    }
    
    // Add gallery images
    if (product.value.images && product.value.images.length > 0) {
        product.value.images.forEach(img => {
            if (img.url) {
                images.push(img.url);
            } else if (img.image_url) {
                images.push(img.image_url);
            } else if (img.path) {
                images.push(img.path.startsWith('http') ? img.path : `/storage/${img.path}`);
            }
        });
    }
    
    return images.length > 0 ? images : ['/assets/placeholder.png'];
});

const selectedImage = ref('');

const isInWishlist = computed(() => {
    return product.value ? wishlistStore.isInWishlist(product.value.id) : false;
});

const maxQuantity = computed(() => {
    if (selectedVariant.value) {
        return selectedVariant.value.stock || 0;
    }
    return product.value?.total_stock || 0;
});

const fetchProduct = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/products/${route.params.slug}`);
        product.value = response.data;
        selectedImage.value = productImages.value[0];
        
        // Check if product has variants
        if (product.value.has_variants && product.value.variants && product.value.variants.length > 0) {
            selectedVariant.value = product.value.variants[0];
        }

        // Fetch related products
        fetchRelatedProducts();
    } catch (error) {
        console.error('Error fetching product:', error);
        product.value = null;
    } finally {
        loading.value = false;
    }
};

const fetchRelatedProducts = async () => {
    try {
        const params = {
            per_page: 4,
            category: product.value?.category,
        };
        const response = await axios.get('/products', { params });
        const allProducts = response.data.data || response.data || [];
        relatedProducts.value = allProducts
            .filter(p => p.id !== product.value.id)
            .slice(0, 4);
    } catch (error) {
        console.error('Error fetching related products:', error);
    }
};

const formatPrice = (price) => {
    if (!price) return '0.00';
    return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const calculateDiscount = (originalPrice, salePrice) => {
    if (!originalPrice || !salePrice) return 0;
    const original = parseFloat(originalPrice);
    const sale = parseFloat(salePrice);
    return Math.round(((original - sale) / original) * 100);
};

const formatVariantAttributes = (attributes) => {
    if (!attributes) return 'Default';
    if (typeof attributes === 'string') {
        try {
            attributes = JSON.parse(attributes);
        } catch (e) {
            return attributes;
        }
    }
    return Object.entries(attributes)
        .map(([key, value]) => `${key}: ${value}`)
        .join(', ');
};

const incrementQty = () => {
    if (quantity.value < maxQuantity.value) quantity.value++;
};

const decrementQty = () => {
    if (quantity.value > 1) quantity.value--;
};

const addToCart = () => {
    if ((product.value.total_stock || 0) === 0) return;
    
    // Check if variant is required but not selected
    if (product.value.has_variants && !selectedVariant.value) {
        alert('Please select a variant');
        return;
    }

    // Ensure slug is explicitly set
    const cartProduct = {
        id: product.value.id,
        name: product.value.name,
        slug: product.value.slug || product.value.id || String(product.value.id),
        price: selectedVariant.value 
            ? (selectedVariant.value.price || product.value.event_price || product.value.sellable_price || product.value.price)
            : (product.value.event_price || product.value.sellable_price || product.value.price),
        image: product.value.image_url || product.value.image,
        category: product.value.category,
        brand: product.value.brand
    };

    // Pass variant and quantity to cart store
    const variant = selectedVariant.value ? {
        id: selectedVariant.value.id,
        attributes: selectedVariant.value.attributes,
        price: selectedVariant.value.price,
        stock: selectedVariant.value.stock,
        sku: selectedVariant.value.sku,
        image: selectedVariant.value.image || selectedVariant.value.image_url || null,
        image_url: selectedVariant.value.image_url || (selectedVariant.value.image ? (selectedVariant.value.image.startsWith('http') ? selectedVariant.value.image : `/storage/${selectedVariant.value.image}`) : null)
    } : null;

    cartStore.addItem(cartProduct, variant, quantity.value);
};

const toggleWishlist = () => {
    if (product.value) {
        wishlistStore.toggleItem(product.value);
    }
};

onMounted(() => {
    fetchProduct();
});
</script>
