<template>
    <div>
        <div class="mb-8">
            <h2 class="text-2xl font-serif font-bold text-white uppercase tracking-widest">My Coupons</h2>
            <p class="text-xs text-slate-400 mt-2 font-light tracking-wide">Exclusive offers tailored just for you</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-surface border border-white/5 p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-4 text-xs text-slate-400 uppercase tracking-widest">Loading offers...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="coupons.length === 0" class="bg-surface border border-white/5 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 text-slate-600 mb-4">
                <Ticket class="w-8 h-8" />
            </div>
            <p class="text-sm font-serif text-white uppercase tracking-widest mb-2">No Active Coupons</p>
            <p class="text-xs text-slate-400">You don't have any exclusive offers at the moment.</p>
        </div>

        <!-- Coupons Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="coupon in coupons" :key="coupon.id || coupon.code"
                class="bg-surface border border-white/5 relative group hover:border-primary/30 transition-all duration-500 overflow-hidden">
                <!-- Decorative Circle -->
                <div class="absolute -right-12 -top-12 w-32 h-32 rounded-full border border-white/5 opacity-20 transition-transform duration-700 group-hover:scale-150"
                    :class="isActive(coupon) ? 'bg-primary/20' : 'bg-slate-500/10'"></div>

                <div class="p-6 flex flex-col h-full relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest mb-2 block"
                                :class="isActive(coupon) ? 'text-primary' : 'text-slate-500'">
                                {{ coupon.type || 'Special Offer' }}
                            </span>
                            <h3 class="text-3xl font-serif text-white">{{ formatDiscount(coupon) }}</h3>
                        </div>
                        <div class="p-3 bg-white/5">
                            <Ticket class="w-5 h-5 text-primary" />
                        </div>
                    </div>

                    <p class="text-slate-400 text-xs mb-6 flex-1 leading-relaxed">{{ coupon.description || `Exclusive
                        discount for our premium members.` }}</p>

                    <div class="pt-4 border-t border-dashed border-white/10">
                        <div class="flex justify-between items-center mb-3">
                            <div
                                class="font-mono bg-white/5 px-3 py-1.5 text-xs text-primary tracking-wider select-all border border-white/5">
                                {{ coupon.code }}
                            </div>
                            <button v-if="isActive(coupon)" @click="copyCode(coupon.code)"
                                class="text-[10px] font-bold text-white uppercase tracking-widest hover:text-primary transition-colors">
                                Copy Code
                            </button>
                            <span v-else
                                class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Expired</span>
                        </div>
                        <div class="flex items-center gap-4 text-[10px] text-slate-500 uppercase tracking-wider">
                            <span v-if="coupon.expires_at">Valid until: {{ formatDate(coupon.expires_at) }}</span>
                            <span v-if="coupon.min_spend" class="pl-4 border-l border-white/10">Min. spend: ৳{{
                                parseFloat(coupon.min_spend).toLocaleString() }}</span>
                        </div>
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
import { useToast } from "vue-toastification";

const toast = useToast();
const coupons = ref([]);
const loading = ref(false);

const fetchCoupons = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/coupons/available');
        // Handle new structured response: { featured, private, other, all }
        const data = response.data;
        const couponsData = data.all || data.data || data;
        coupons.value = Array.isArray(couponsData) ? couponsData : [];
    } catch (error) {
        console.error('Error fetching coupons:', error);
        // Fallback to empty
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
        toast.success(`Coupon code "${code}" copied to clipboard!`);
    }).catch(() => {
        toast.error('Failed to copy code');
    });
};

onMounted(() => {
    fetchCoupons();
});
</script>
