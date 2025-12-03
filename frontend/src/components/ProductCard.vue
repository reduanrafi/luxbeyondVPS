<template>
    <div
        :class="[
            'group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300',
            viewMode === 'grid' ? 'flex flex-col' : 'flex flex-row'
        ]"
    >
        <!-- Product Image -->
        <div
            :class="[
                'relative overflow-hidden bg-gray-100',
                viewMode === 'grid' ? 'aspect-square' : 'w-64 flex-shrink-0'
            ]"
        >
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
                class="absolute top-4 left-4 bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg z-10"
            >
                -{{ calculateDiscount(product.price, product.sellable_price) }}%
            </div>

            <!-- Featured Badge -->
            <div
                v-if="product.is_featured"
                class="absolute top-4 right-4 bg-primary text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1 z-10"
            >
                <Star class="w-3 h-3 fill-current" />
                Featured
            </div>

            <!-- Wishlist Button -->
            <button
                @click.prevent="toggleWishlist(product)"
                class="absolute bottom-4 right-4 p-2.5 bg-white/95 backdrop-blur-sm rounded-full hover:bg-white transition-all duration-200 shadow-lg hover:scale-110 z-10"
                :class="isInWishlist ? 'opacity-100 text-red-500' : 'text-gray-600 opacity-0 group-hover:opacity-100'"
            >
                <Heart class="w-5 h-5" :fill="isInWishlist ? 'currentColor' : 'none'" />
            </button>

            <!-- Quick View Overlay -->
            <div
                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center z-10"
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
        <div :class="['flex-1 flex flex-col', viewMode === 'grid' ? 'p-5' : 'p-6']">
            <!-- Category -->
            <div class="mb-2">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    {{ product.category || 'Uncategorized' }}
                </span>
            </div>

            <!-- Product Name -->
            <router-link :to="`/products/${product.id}`" class="mb-3 group/link">
                <h3
                    :class="[
                        'font-bold text-gray-900 line-clamp-2 group-hover/link:text-primary transition-colors',
                        viewMode === 'grid' ? 'text-lg min-h-14' : 'text-xl'
                    ]"
                >
                    {{ product.name }}
                </h3>
            </router-link>

            <!-- Brand -->
            <p v-if="product.brand" class="text-sm text-gray-500 mb-3">
                {{ product.brand }}
            </p>

            <!-- Description (List View Only) -->
            <p v-if="viewMode === 'list' && product.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                {{ product.description }}
            </p>

            <!-- Price -->
            <div class="flex items-baseline gap-2 mb-4">
                <span :class="['font-bold text-primary', viewMode === 'grid' ? 'text-2xl' : 'text-3xl']">
                    ৳{{ formatPrice(product.sellable_price || product.price) }}
                </span>
                <span
                    v-if="product.sellable_price && product.price > product.sellable_price"
                    class="text-sm text-gray-400 line-through"
                >
                    ৳{{ formatPrice(product.price) }}
                </span>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                <span
                    :class="[
                        'px-3 py-1 rounded-full text-xs font-semibold',
                        (product.total_stock || 0) > 10
                            ? 'bg-green-100 text-green-700'
                            : (product.total_stock || 0) > 0
                            ? 'bg-yellow-100 text-yellow-700'
                            : 'bg-red-100 text-red-700'
                    ]"
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

            <!-- Actions -->
            <div class="mt-auto flex items-center gap-2">
                <button
                    @click="addToCart(product)"
                    :disabled="(product.total_stock || 0) === 0"
                    class="flex-1 px-4 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2"
                >
                    <ShoppingCart class="w-5 h-5" />
                    Add to Cart
                </button>
                <router-link
                    :to="`/products/${product.id}`"
                    class="px-4 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:border-primary hover:text-primary transition-all"
                >
                    <Eye class="w-5 h-5" />
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Star, Heart, ShoppingCart, Eye } from 'lucide-vue-next';
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    viewMode: {
        type: String,
        default: 'grid',
    },
});

const wishlistStore = useWishlistStore();
const cartStore = useCartStore();

const isInWishlist = computed(() => {
    return wishlistStore.isInWishlist(props.product.id);
});

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
        maximumFractionDigits: 2,
    });
};

const calculateDiscount = (originalPrice, salePrice) => {
    if (!originalPrice || !salePrice) return 0;
    return Math.round(((originalPrice - salePrice) / originalPrice) * 100);
};

const addToCart = (product) => {
    if ((product.total_stock || 0) === 0) return;

    const cartProduct = {
        ...product,
        price: `৳${formatPrice(product.sellable_price || product.price)}`,
    };

    cartStore.addItem(cartProduct);
};

const toggleWishlist = (product) => {
    wishlistStore.toggleItem(product);
};
</script>

