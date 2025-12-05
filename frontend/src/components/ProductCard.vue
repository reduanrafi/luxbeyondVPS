<template>
  <div :class="[
    'group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 relative',
    viewMode === 'grid' ? 'flex flex-col' : 'flex flex-row'
  ]">
    <!-- Product Image -->
    <div :class="[
      'relative overflow-hidden bg-gray-50 flex items-center justify-center',
      viewMode === 'grid' ? 'aspect-square' : 'w-32 h-32 shrink-0'
    ]">
      <router-link :to="`/products/${product.slug || product.id}`" class="block h-full w-full p-2">
        <img :src="getProductImage(product)" :alt="product.name"
          class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
          @error="handleImageError" />
      </router-link>

      <!-- Discount Badge - Top Right -->
      <div v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
        class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-md z-10 flex items-center gap-1">
        <Zap class="w-3 h-3" />
        {{ calculateDiscount(
          product.event_price ? product.original_price : product.price,
          product.event_price || product.sellable_price || product.price
        ) }}% OFF
      </div>

      <!-- Wishlist Button - Show on Hover -->
      <button
        v-if="showWishlist"
        @click.stop.prevent="toggleWishlist(product)"
        class="absolute bottom-3 right-3 p-2 bg-white/90 backdrop-blur-sm rounded-full hover:bg-white shadow-md hover:scale-110 transition-all duration-200 z-10"
        :class="isInWishlist ? 'text-red-500 opacity-100' : 'text-gray-400 opacity-0 group-hover:opacity-100'"
      >
        <Heart class="w-5 h-5" :fill="isInWishlist ? 'currentColor' : 'none'" />
      </button>
    </div>

    <!-- Product Info -->
    <div :class="['flex-1 flex flex-col', viewMode === 'grid' ? 'p-4' : 'p-3']">
      <!-- Product Name -->
      <router-link :to="`/products/${product.slug || product.id}`" class="mb-1 group/link">
        <h3 :class="[
          'font-bold text-gray-900 line-clamp-1 group-hover/link:text-primary transition-colors',
          viewMode === 'grid' ? 'text-base' : 'text-sm'
        ]">
          {{ product.name }}
        </h3>
      </router-link>

      <p class="text-xs text-gray-500 mb-1.5 pr-4" v-if="viewMode === 'list'">
        {{ product.short_description }}
      </p>

      <!-- Rating -->
      <div class="flex items-center gap-1.5 mb-1.5">
        <div class="flex items-center gap-0.5">
          <Star v-for="i in 5" :key="i" :class="['text-yellow-400 fill-yellow-400', viewMode === 'grid' ? 'w-4 h-4' : 'w-3 h-3']" />
        </div>
        <span class="text-xs text-gray-500">({{ product.reviews_count || 0 }})</span>
      </div>

      <!-- Add Button - Bottom Right -->
      <div class="mt-auto flex justify-between items-center">
        <!-- Price -->
        <div>
          <div class=" flex-col items-baseline">
            <!-- Show original price if event price is applied or if there's a discount -->
            <span v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
              class="text-sm text-gray-400 line-through">
              ৳{{ formatPrice(product.event_price ? product.original_price : product.price) }}
            </span>
            <span class="font-bold text-gray-900 text-[15px]">
              ৳{{ formatPrice(product.event_price || product.sellable_price || product.price) }}
            </span>
          </div>
        </div>
        <button @click="addToCart(product)" :disabled="(product.total_stock || 0) === 0"
          :class="[
            'bg-purple-50 border border-purple-200 text-gray-900 font-semibold rounded-lg hover:bg-purple-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all',
            viewMode === 'grid' ? 'px-6 py-2.5 text-sm' : 'px-4 py-1.5 text-xs'
          ]">
          ADD
        </button>
      </div>
    </div>
  </div>
</template>


<script setup>

import { computed } from 'vue';
import { Star, Heart, ShoppingCart, Eye, EyeIcon, Zap, Rocket } from 'lucide-vue-next';
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useModalStore } from '../stores/modal';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  viewMode: {
    type: String,
    default: 'grid',
    validator: (value) => ['grid', 'list'].includes(value),
  },
  showStock: {
    type: Boolean,
    default: true,
  },
  showCategory: {
    type: Boolean,
    default: true,
  },
  showBrand: {
    type: Boolean,
    default: true,
  },
  showQuickView: {
    type: Boolean,
    default: true,
  },
  showWishlist: {
    type: Boolean,
    default: true,
  },
});

const wishlistStore = useWishlistStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const modalStore = useModalStore();

const isInWishlist = computed(() => {
  return wishlistStore.isInWishlist(props.product.id);
});

const isInStock = computed(() => {
  // Support both total_stock and in_stock properties
  if (props.product.total_stock !== undefined) {
    return (props.product.total_stock || 0) > 0;
  }
  return props.product.in_stock !== false;
});

const stockStatus = computed(() => {
  const stock = props.product.total_stock || 0;
  if (stock > 10) return { text: 'In Stock', class: 'bg-green-100 text-green-700' };
  if (stock > 0) return { text: 'Low Stock', class: 'bg-yellow-100 text-yellow-700' };
  return { text: 'Out of Stock', class: 'bg-red-100 text-red-700' };
});

const getProductImage = (product) => {
  if (product.image_url) {
    return product.image_url;
  }
  
  return '/assets/product-default.png';
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
  const original = parseFloat(originalPrice);
  const sale = parseFloat(salePrice);
  return Math.round(((original - sale) / original) * 100);
};

const addToCart = (product) => {
  // Check authentication first
  if (!authStore.isAuthenticated) {
    modalStore.openModal('login');
    return;
  }

  // Check stock
  if (!isInStock.value) return;

  // Ensure slug is explicitly set before creating cart product
  const cartProduct = {
    ...product,
    slug: product.slug || product.id || String(product.id),
    price: `৳${formatPrice(product.sellable_price || product.price)}`,
  };

  cartStore.addItem(cartProduct);
};

const toggleWishlist = (product) => {
  // Check authentication first
  if (!authStore.isAuthenticated) {
    modalStore.openModal('login');
    return;
  }
  wishlistStore.toggleItem(product);
};
</script>
