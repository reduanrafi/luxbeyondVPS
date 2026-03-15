<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { CheckCircle, XCircle } from 'lucide-vue-next';
import { useCartStore } from '@/stores/cart'; // Ensure this path is correct for your project

const route = useRoute();
const cartStore = useCartStore();

// 1. Identify the status from the URL
const type = computed(() => route.query.type || 'order');
const id = computed(() => route.query.id);
const paymentStatus = computed(() => route.query.payment);

// 2. Logic Helpers
const isOrder = computed(() => type.value === 'order');
// We assume success ONLY if 'success' is explicitly passed OR if no payment param exists (for manual orders)
const isSuccess = computed(() => paymentStatus.value === 'success' || !paymentStatus.value);
const isFailure = computed(() => paymentStatus.value === 'failed' || paymentStatus.value === 'error');

onMounted(() => {
  /** * THE CRITICAL PART:
   * Only clear the cart if it's a success. 
   * If isFailure is true, this code block is skipped, preserving the cart.
   */
  if (isSuccess.value) {
    cartStore.clearCart();

    // Optional: Trigger a purchase tracking event only on success
    if (typeof trackPurchase === 'function') {
      trackPurchase({ id: id.value, type: type.value });
    }
  }
});
</script>

<template>
  <div class="min-h-screen bg-background pt-0 flex items-center justify-center px-4">
    <div class="max-w-2xl w-full text-center space-y-8 animate-fadeIn">

      <div class="flex justify-center">
        <div class="relative">
          <div :class="[isSuccess ? 'bg-primary/20' : 'bg-red-500/20']"
            class="absolute inset-0 blur-3xl rounded-full scale-150"></div>
          <div :class="[isSuccess ? 'border-primary/30' : 'border-red-500/30']"
            class="relative w-24 h-24 bg-surface border rounded-full flex items-center justify-center">
            <CheckCircle v-if="isSuccess" class="w-12 h-12 text-primary" />
            <XCircle v-else class="w-12 h-12 text-red-500" />
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <h1 class="text-4xl md:text-5xl font-serif text-white tracking-widest uppercase">
          {{ isSuccess ? (isOrder ? 'Order Placed' : 'Request Received') : 'Payment Failed' }}
        </h1>
        <p class="text-slate-400 font-light tracking-wide max-w-md mx-auto">
          <span v-if="isSuccess">
            {{ isOrder ? 'Thank you. Your order is being processed.' : 'Your request has been submitted successfully.'
            }}
          </span>
          <span v-else class="text-red-400">
            We couldn't process your payment. Don't worry, your items are still in your cart.
          </span>
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">

        <template v-if="isFailure">
          <router-link to="/checkout"
            class="px-8 py-3 bg-red-600 text-white font-bold uppercase tracking-widest text-sm rounded-none hover:bg-red-700 transition-all shadow-[0_0_20px_rgba(239,68,68,0.2)]">
            Try Payment Again
          </router-link>

          <router-link to="/shop"
            class="px-8 py-3 bg-transparent border border-white/20 text-white font-bold uppercase tracking-widest text-sm rounded-none hover:bg-white/5 transition-all">
            Back to Shop
          </router-link>
        </template>

        <template v-else>
          <router-link :to="isOrder ? `/dashboard/orders/${id}` : `/dashboard/requests/${id}`"
            class="px-8 py-3 bg-primary text-white font-bold uppercase tracking-widest text-sm rounded-none hover:bg-primary-hover transition-all shadow-[0_0_20px_rgba(200,174,125,0.2)]">
            View Details
          </router-link>

          <router-link v-if="isOrder" to="/shop"
            class="px-8 py-3 bg-transparent border border-white/20 text-white font-bold uppercase tracking-widest text-sm rounded-none hover:bg-white/5 transition-all">
            Continue Shopping
          </router-link>

          <router-link v-else to="/request-product"
            class="px-8 py-3 bg-transparent border border-white/20 text-white font-bold uppercase tracking-widest text-sm rounded-none hover:bg-white/5 transition-all">
            Another Request
          </router-link>
        </template>
      </div>

      <p class="text-xs text-slate-600 pt-8">
        Need help? <a href="#" class="text-primary hover:underline">Contact our support team</a>
      </p>

    </div>
  </div>
</template>