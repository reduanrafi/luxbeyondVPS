<template>
  <div :class="[
    'group bg-surface rounded-none border border-white/[0.05] hover:border-primary/50 transition-all duration-300 relative p-6 flex flex-col',
    viewMode === 'grid' ? '' : 'sm:flex-row sm:items-center sm:gap-6'
  ]">
    <!-- Product Image Area -->
    <div :class="[
      'relative overflow-hidden mb-6 flex items-center justify-center bg-white/[0.02]',
      viewMode === 'grid' ? 'aspect-square w-full' : 'w-48 h-48 shrink-0'
    ]">
      <router-link :to="`/shop/${product.slug || product.id}`" class="block h-full w-full p-4">
        <img :src="getProductImage(product)" :alt="product.name"
          class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 opacity-90 group-hover:opacity-100"
          @error="handleImageError" />
      </router-link>

      <!-- Discount Badge (Minimalist) -->
      <div
        v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
        class="absolute top-0 right-0 bg-primary text-slate-900 text-[10px] font-bold px-2 py-1 uppercase tracking-wider">
        {{ calculateDiscount(
          product.event_price ? product.original_price : product.price,
          product.event_price || product.sellable_price || product.price
        ) }}% OFF
      </div>

      <!-- Wishlist Button (Minimalist) -->
      <button v-if="showWishlist" @click.stop.prevent="toggleWishlist(product)"
        class="absolute bottom-2 right-2 text-primary shadow-md hover:text-primary transition-colors duration-200"
        :class="{ 'text-primary': isInWishlist }">
        <Heart class="w-6 h-6" :class="{ 'fill-primary': isInWishlist }" />
      </button>
    </div>

    <!-- Product Info -->
    <div class="flex-1 flex flex-col text-left">
      <!-- Name -->
      <router-link :to="`/shop/${product.slug || product.id}`" class="group/link">
        <h3
          class="font-serif text-xl text-white uppercase tracking-widest mb-2 group-hover/link:text-primary transition-colors">
          {{ product.name }}
        </h3>
      </router-link>

      <!-- Subtitle/Description -->
      <p class="text-xs text-slate-500 uppercase tracking-wide mb-4 line-clamp-1">
        {{ product.short_description || 'Precision Engineered' }}
      </p>

      <!-- Price -->
      <div class="mb-4">
        <span
          v-if="(product.event_price && product.original_price) || (product.sellable_price && parseFloat(product.price) > parseFloat(product.sellable_price))"
          class="text-sm text-slate-600 line-through mr-2">
          ৳{{ formatPrice(product.event_price ? product.original_price : product.price) }}
        </span>
        <span class="text-2xl font-serif text-primary font-bold">
          ৳{{ formatPrice(product.event_price || product.sellable_price || product.price) }}
        </span>
      </div>

      <!-- Rating -->
      <div class="flex items-center gap-1 mb-6">
        <Star v-for="i in 5" :key="i" class="w-3 h-3 text-primary fill-primary" />
      </div>

      <!-- Add to Cart / Out of Stock -->
      <button v-if="(product.total_stock || 0) > 0" @click="addToCart(product)"
        class="mt-auto flex items-center gap-2 text-sm text-white font-bold uppercase tracking-widest hover:underline hover:text-primary transition-colors group/btn">
        Add to Cart
        <ArrowRight class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform duration-300" />
      </button>
      <div v-else class="mt-auto text-sm text-red-500 font-bold uppercase tracking-widest cursor-not-allowed">
        Out of Stock
      </div>
    </div>
  </div>
</template>


<script setup>

import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { Star, Heart, ShoppingCart, Eye, EyeIcon, Zap, Rocket, ArrowRight } from 'lucide-vue-next';
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { useModalStore } from '../stores/modal';
import { trackAddToCart as trackAddToCartGA4 } from '../utils/analytics';

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
  event.target.src = '/assets/placeholder.webp';
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

const router = useRouter();

const addToCart = (product) => {
  // If product has variants, redirect to product details
  if (product.has_variants) {
    router.push(`/shop/${product.slug || product.id}`);
    return;
  }

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
  trackAddToCartGA4(cartProduct, 1);
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
