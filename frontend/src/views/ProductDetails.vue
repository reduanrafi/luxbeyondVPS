<template>
    <div class="bg-background min-h-screen pt-20 pb-10">
        <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="animate-pulse">
                <div class="h-6 bg-white/5 rounded w-1/4 mb-6"></div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="h-[500px] bg-white/5 rounded-sm"></div>
                    <div class="space-y-6">
                        <div class="h-10 bg-white/5 rounded w-3/4"></div>
                        <div class="h-6 bg-white/5 rounded w-1/2"></div>
                        <div class="h-16 bg-white/5 rounded w-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <ol class="flex items-center space-x-2 text-xs font-medium uppercase tracking-wider text-slate-500">
                    <li><router-link to="/" class="hover:text-primary transition-colors">Home</router-link></li>
                    <li>/</li>
                    <li><router-link to="/shop" class="hover:text-primary transition-colors">Shop</router-link></li>
                    <li v-if="product.category">/</li>
                    <li v-if="product.category">
                        <router-link :to="`/shop?category=${product.category}`"
                            class="hover:text-primary transition-colors">
                            {{ product.category }}
                        </router-link>
                    </li>
                    <li>/</li>
                    <li class="text-white">{{ product.name }}</li>
                </ol>
            </nav>

            <!-- Product Main Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
                <!-- Product Images -->
                <div class="space-y-4">
                    <div
                        class="overflow-hidden bg-surface border border-white/5 h-[500px] relative group flex items-center justify-center p-8">
                        <img :src="selectedImage" :alt="product.name"
                            class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-110" />
                    </div>
                    <div v-if="productImages.length > 1" class="grid grid-cols-5 gap-4">
                        <button v-for="(img, index) in productImages" :key="index" @click="selectedImage = img"
                            class="aspect-square flex items-center justify-center bg-surface border transition-all hover:border-primary p-2"
                            :class="selectedImage === img ? 'border-primary' : 'border-white/5'">
                            <img :src="img" :alt="`${product.name} ${index + 1}`" class="w-full h-full object-contain">
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col h-full">
                    <!-- Heading -->
                    <div class="mb-6 border-b border-white/5 pb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <span v-if="product.brand" class="text-primary text-sm font-bold uppercase tracking-widest">
                                {{ product.brand }}
                            </span>
                            <span v-if="product.is_featured"
                                class="px-2 py-0.5 border border-primary/30 text-primary text-[10px] uppercase tracking-wide">
                                Featured
                            </span>
                        </div>

                        <h1 class="text-4xl font-serif text-white mb-4 leading-tight">{{ product.name }}</h1>

                        <!-- Price -->
                        <div class="flex items-baseline gap-4">
                            <span class="text-3xl font-serif text-primary">
                                ৳{{ formatPrice(product.event_price || product.sellable_price || product.price) }}
                            </span>
                            <span
                                v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
                                class="text-lg text-slate-500 line-through font-serif">
                                ৳{{ formatPrice(product.event_price ? product.original_price : product.price) }}
                            </span>
                        </div>
                    </div>

                    <!-- Short Description -->
                    <p v-if="product.short_description" class="text-slate-400 mb-8 leading-relaxed font-light">
                        {{ product.short_description }}
                    </p>

                    <!-- Variants (if available) -->
                    <div v-if="product.has_variants && product.variants && product.variants.length > 0" class="mb-8">
                        <label class="block text-xs font-bold text-white uppercase tracking-widest mb-3">Select
                            Variant</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button v-for="variant in product.variants" :key="variant.id"
                                @click="selectedVariant = variant"
                                class="p-4 border bg-surface/50 text-left transition-all hover:border-primary group"
                                :class="selectedVariant?.id === variant.id ? 'border-primary bg-primary/5' : 'border-white/10'">
                                <div class="flex items-center justify-between mb-1">
                                    <span
                                        class="text-sm font-medium text-white group-hover:text-primary transition-colors">{{
                                            formatVariantAttributes(variant.attributes) }}</span>
                                    <span class="text-xs text-primary font-bold">৳{{ formatPrice(variant.price)
                                        }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 uppercase tracking-wide">
                                    {{ variant.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="space-y-6 mt-auto">
                        <div class="flex items-end gap-6">
                            <div class="w-32">
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Quantity</label>
                                <div class="flex items-center border border-white/10 bg-surface">
                                    <button @click="decrementQty" :disabled="quantity <= 1"
                                        class="w-10 h-12 flex items-center justify-center text-slate-400 hover:text-white transition-colors disabled:opacity-30">
                                        <Minus class="w-4 h-4" />
                                    </button>
                                    <input type="number" v-model.number="quantity" min="1" :max="maxQuantity"
                                        class="flex-1 w-full bg-transparent text-center text-white font-medium focus:outline-none" />
                                    <button @click="incrementQty" :disabled="quantity >= maxQuantity"
                                        class="w-10 h-12 flex items-center justify-center text-slate-400 hover:text-white transition-colors disabled:opacity-30">
                                        <Plus class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Stock Status -->
                            <div class="pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full"
                                        :class="(product.total_stock || 0) > 0 ? 'bg-primary' : 'bg-red-500'"></div>
                                    <span class="text-xs font-medium uppercase tracking-wider"
                                        :class="(product.total_stock || 0) > 0 ? 'text-primary' : 'text-red-500'">
                                        {{ (product.total_stock || 0) > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button @click="addToCart" :disabled="(product.total_stock || 0) === 0"
                                class="flex-1 bg-primary text-slate-900 h-14 uppercase tracking-[0.15em] font-bold text-sm hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-[0_0_20px_rgba(200,174,125,0.2)] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)]">
                                {{ (product.total_stock || 0) > 0 ? 'Add to Cart' : 'Sold Out' }}
                            </button>
                            <button @click="toggleWishlist"
                                class="w-14 h-14 border border-white/10 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-all"
                                :class="isInWishlist ? 'text-red-500 border-red-500/50' : ''">
                                <Heart class="w-6 h-6" :class="isInWishlist ? 'fill-current' : ''" />
                            </button>
                        </div>

                        <!-- Trust Badges -->
                        <div class="grid grid-cols-2 gap-4 py-6 border-t border-white/5">
                            <div class="flex items-center gap-3">
                                <Package class="w-5 h-5 text-primary" />
                                <span class="text-xs text-slate-400 uppercase tracking-wide">Premium Packaging</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <CheckCircle class="w-5 h-5 text-primary" />
                                <span class="text-xs text-slate-400 uppercase tracking-wide">Authenticity
                                    Guaranteed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="mb-20">
                <div class="border-b border-white/10 mb-8">
                    <nav class="flex gap-12 overflow-x-auto">
                        <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                            class="pb-4 text-sm font-bold uppercase tracking-widest transition-all relative whitespace-nowrap"
                            :class="activeTab === tab ? 'text-primary' : 'text-slate-500 hover:text-white'">
                            {{ tab }}
                            <div v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary">
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div v-if="activeTab === 'Description'" class="prose prose-invert max-w-none">
                    <div v-html="product.description || product.short_description || 'No description available.'"
                        class="text-slate-400 leading-relaxed font-light"></div>
                </div>

                <div v-if="activeTab === 'Specifications'">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                        <div v-if="product.sku" class="flex py-3 border-b border-white/5">
                            <dt class="font-medium text-slate-400 w-1/3 text-sm">SKU</dt>
                            <dd class="text-white w-2/3 text-sm">{{ product.sku }}</dd>
                        </div>
                        <div v-if="product.category" class="flex py-3 border-b border-white/5">
                            <dt class="font-medium text-slate-400 w-1/3 text-sm">Category</dt>
                            <dd class="text-white w-2/3 text-sm">{{ product.category }}</dd>
                        </div>
                        <div v-if="product.brand" class="flex py-3 border-b border-white/5">
                            <dt class="font-medium text-slate-400 w-1/3 text-sm">Brand</dt>
                            <dd class="text-white w-2/3 text-sm">{{ product.brand }}</dd>
                        </div>
                        <div class="flex py-3 border-b border-white/5">
                            <dt class="font-medium text-slate-400 w-1/3 text-sm">Stock</dt>
                            <dd class="text-white w-2/3 text-sm">{{ product.total_stock || 0 }} units</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="activeTab === 'Reviews'">
                    <div class="text-center py-12 border border-white/5 bg-surface/30">
                        <p class="text-slate-500 italic">No reviews yet. Be the first to review this product!</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0">
                <h2 class="text-2xl font-bold text-white mb-6">You May Also Like</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <ProductCard v-for="item in relatedProducts" :key="item.id" :product="item" view-mode="grid" />
                </div>
            </div>
        </div>

        <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <Package class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-xl font-bold text-white mb-2">Product Not Found</h3>
            <p class="text-gray-600 mb-6">The product you're looking for doesn't exist or has been removed.</p>
            <router-link to="/shop"
                class="inline-block px-6 py-3 bg-primary text-slate-900 font-semibold rounded-lg hover:bg-primary-dark transition-all">
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
        wishlistStore.toggleItem(product.value, selectedVariant.value);
    }
};

onMounted(() => {
    fetchProduct();
});
</script>
