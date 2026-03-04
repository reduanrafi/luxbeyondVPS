<template>
  <div class="min-h-screen bg-background pt-0 flex items-center justify-center px-4">
    <div class="max-w-2xl w-full text-center space-y-8 animate-fadeIn">
      <!-- Success Icon -->
      <div class="flex justify-center">
        <div class="relative">
          <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full scale-150"></div>
          <div
            class="relative w-24 h-24 bg-surface border border-primary/30 rounded-full flex items-center justify-center shadow-[0_0_40px_rgba(200,174,125,0.2)]">
            <CheckCircle class="w-12 h-12 text-primary" />
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="space-y-4">
        <h1 class="text-4xl md:text-5xl font-serif text-white tracking-widest uppercase">
          {{ isOrder ? 'Order Placed' : 'Request Received' }}
        </h1>
        <p class="text-slate-400 font-light tracking-wide max-w-md mx-auto">
          {{ isOrder
            ? 'Thank you for your purchase. Your order has been placed successfully and is being processed.'
            : 'Your product request has been submitted. Our team will review it and get back to you shortly.'
          }}
        </p>
      </div>

      <!-- ID Display -->
      <div v-if="id" class="inline-block px-6 py-2 bg-white/5 border border-white/10 rounded-none">
        <span class="text-xs text-slate-500 uppercase tracking-widest mr-2">
          {{ isOrder ? 'Order ID' : 'Request ID' }}
        </span>
        <span class="text-primary font-bold tracking-wider">{{ id }}</span>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
        <router-link :to="isOrder ? `/dashboard/orders/${id}` : `/dashboard/requests/${id}`"
          class="px-8 py-3 bg-primary text-slate-900 font-bold uppercase tracking-widest text-sm rounded-none hover:bg-primary-hover transition-all shadow-[0_0_20px_rgba(200,174,125,0.2)]">
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
      </div>

      <!-- Support Info -->
      <p class="text-xs text-slate-600 pt-8">
        Need help? <a href="#" class="text-primary hover:underline">Contact our support team</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { CheckCircle } from 'lucide-vue-next';

const route = useRoute();
const type = computed(() => route.query.type || 'order');
const id = computed(() => route.query.id);
const isOrder = computed(() => type.value === 'order');
</script>

<style scoped>
.animate-fadeIn {
  animation: fadeIn 1s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
