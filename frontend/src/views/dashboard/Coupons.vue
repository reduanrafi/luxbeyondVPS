<template>
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900">My Coupons</h2>
            <p class="text-xs text-slate-500 mt-1">Available coupons and discount codes</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <p class="mt-2 text-xs text-slate-500">Loading coupons...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="coupons.length === 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            <p class="text-sm font-semibold mb-1">No coupons available</p>
            <p class="text-xs text-slate-500">You don't have any active coupons at the moment.</p>
        </div>

        <!-- Coupons Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="coupon in coupons" :key="coupon.id || coupon.code"
                class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden relative group hover:shadow-lg transition-all">
                <!-- Decorative Circle -->
                <div class="absolute -right-6 -top-6 w-20 h-20 rounded-full opacity-10 transition-transform group-hover:scale-150"
                    :class="isActive(coupon) ? 'bg-primary' : 'bg-gray-400'"></div>

                <div class="p-4 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider mb-1 block"
                                :class="isActive(coupon) ? 'text-primary' : 'text-gray-400'">
                                {{ coupon.type || 'Discount' }}
                            </span>
                            <h3 class="text-2xl font-extrabold text-slate-900">{{ formatDiscount(coupon) }}</h3>
                        </div>
                        <div class="p-2 rounded-lg bg-gray-50">
                            <Ticket class="w-5 h-5 text-slate-400" />
                        </div>
                    </div>

                    <p class="text-slate-600 text-xs mb-4 flex-1 line-clamp-2">{{ coupon.description || 'No description' }}</p>

                    <div class="pt-3 border-t border-dashed border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <div
                                class="font-mono bg-gray-100 px-2 py-1 rounded text-xs font-bold text-slate-700 tracking-wide select-all">
                                {{ coupon.code }}
                            </div>
                            <button v-if="isActive(coupon)" @click="copyCode(coupon.code)"
                                class="text-xs font-semibold text-primary hover:text-primary-hover">
                                Copy
                            </button>
                            <span v-else class="text-[10px] font-bold text-red-500 uppercase">Expired</span>
                        </div>
                        <p v-if="coupon.expires_at" class="text-[10px] text-slate-400">Expires: {{ formatDate(coupon.expires_at) }}</p>
                        <p v-if="coupon.min_spend" class="text-[10px] text-slate-500 mt-1">Min. spend: ৳{{ parseFloat(coupon.min_spend).toLocaleString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Ticket } from 'lucide-vue-next';
import axios from '../../axios';

const coupons = ref([]);
const loading = ref(false);

const fetchCoupons = async () => {
    loading.value = true;
    try {
        // Note: Since there's no customer-facing coupon endpoint,
        // we'll show an empty state or you can create a backend endpoint
        // For now, we'll set empty array and show message
        coupons.value = [];
        
        // TODO: Create a customer-facing endpoint like /api/coupons/available
        // that returns coupons available to the logged-in user
    } catch (error) {
        console.error('Error fetching coupons:', error);
        coupons.value = [];
    } finally {
        loading.value = false;
    }
};

const formatDiscount = (coupon) => {
    if (coupon.discount_type === 'percentage') {
        return `${coupon.discount_value}% OFF`;
    } else if (coupon.discount_type === 'fixed') {
        return `৳${parseFloat(coupon.discount_value || 0).toLocaleString()} OFF`;
    }
    return coupon.discount || 'Discount';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const isActive = (coupon) => {
    if (!coupon.is_active) return false;
    const now = new Date();
    const expiresAt = coupon.expires_at ? new Date(coupon.expires_at) : null;
    if (expiresAt && now > expiresAt) return false;
    return true;
};

const copyCode = (code) => {
    navigator.clipboard.writeText(code).then(() => {
        alert(`Coupon code "${code}" copied to clipboard!`);
    }).catch(() => {
        alert('Failed to copy code');
    });
};

onMounted(() => {
    fetchCoupons();
});
</script>
