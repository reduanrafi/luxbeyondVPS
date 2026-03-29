<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

                <!-- Modal -->
                <div class="relative bg-surface border border-white/10 w-full max-w-lg max-h-[80vh] flex flex-col shadow-2xl shadow-primary/10 overflow-hidden"
                    @click.stop>
                    <!-- Header -->
                    <div class="flex items-center justify-between p-6 border-b border-white/10 bg-background/50 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary/10 border border-primary/20">
                                <Ticket class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h2 class="text-lg font-serif text-white tracking-wide">Available Coupons</h2>
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-0.5">
                                    {{ allCoupons.length }} coupon{{ allCoupons.length !== 1 ? 's' : '' }} available
                                </p>
                            </div>
                        </div>
                        <button @click="$emit('close')"
                            class="p-2 text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Coupon Code Input -->
                    <div class="px-6 pt-5 pb-3 border-b border-white/5 flex-shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                            Have a private code?
                        </label>
                        <div class="flex gap-2">
                            <input v-model="privateCode" type="text" placeholder="Enter coupon code"
                                class="flex-1 px-4 py-2.5 bg-background border border-white/10 text-white text-sm placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none font-mono tracking-wider"
                                @keyup.enter="applyPrivateCode" />
                            <button @click="applyPrivateCode" :disabled="!privateCode.trim() || applying"
                                class="px-5 py-2.5 bg-primary text-white text-xs font-bold uppercase tracking-widest rounded-none hover:bg-primary-hover disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                                {{ applying ? 'Wait...' : 'Apply' }}
                            </button>
                        </div>
                        <p v-if="codeError" class="text-xs text-red-400 mt-2">{{ codeError }}</p>
                    </div>

                    <!-- Coupons List -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-3" v-if="!loading">
                        <!-- Featured Section -->
                        <div v-if="featured.length > 0">
                            <p class="text-[10px] font-bold text-primary uppercase tracking-widest mb-3">
                                ★ Featured Offers
                            </p>
                            <div class="space-y-3">
                                <div v-for="coupon in featured" :key="coupon.id"
                                    class="group relative border rounded-none transition-all duration-300 overflow-hidden"
                                    :class="isCouponApplied(coupon.id)
                                        ? 'border-green-500/30 bg-green-500/5'
                                        : 'border-white/10 hover:border-primary/30 bg-background/30 hover:bg-primary/5'">
                                    <div class="absolute top-0 left-0 w-1 h-full transition-all duration-300"
                                        :class="isCouponApplied(coupon.id) ? 'bg-green-500' : 'bg-primary/40 group-hover:bg-primary'"></div>
                                    <div class="p-4 pl-5 flex items-center gap-4">
                                        <div class="flex-shrink-0 w-16 h-16 flex flex-col items-center justify-center border border-dashed rounded-none"
                                            :class="isCouponApplied(coupon.id) ? 'border-green-500/30 bg-green-500/10' : 'border-primary/30 bg-primary/5'">
                                            <span class="text-lg font-bold leading-none"
                                                :class="isCouponApplied(coupon.id) ? 'text-green-400' : 'text-primary'">
                                                {{ formatDiscount(coupon) }}
                                            </span>
                                            <span class="text-[8px] uppercase tracking-wider mt-0.5"
                                                :class="isCouponApplied(coupon.id) ? 'text-green-500/60' : 'text-primary/60'">OFF</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-mono text-xs px-2 py-0.5 bg-white/5 border border-white/10 tracking-wider"
                                                    :class="isCouponApplied(coupon.id) ? 'text-green-400' : 'text-white'">
                                                    {{ coupon.code }}
                                                </span>
                                                <span class="text-[8px] font-bold text-primary uppercase tracking-widest">★ Featured</span>
                                            </div>
                                            <p v-if="coupon.description" class="text-[11px] text-slate-400 leading-relaxed truncate">
                                                {{ coupon.description }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-1.5 text-[9px] text-slate-500 uppercase tracking-wider">
                                                <span v-if="coupon.min_spend > 0">Min ৳{{ parseFloat(coupon.min_spend).toLocaleString() }}</span>
                                                <span v-if="coupon.max_discount > 0">Max ৳{{ parseFloat(coupon.max_discount).toLocaleString() }}</span>
                                                <span v-if="coupon.expires_at">Exp {{ formatDate(coupon.expires_at) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <button v-if="isCouponApplied(coupon.id)" @click="$emit('remove', coupon)"
                                                class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-red-400 border border-red-500/30 hover:bg-red-500/10 transition-all">
                                                Remove
                                            </button>
                                            <button v-else @click="handleApply(coupon)"
                                                class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-primary border border-primary/30 hover:bg-primary/10 hover:border-primary/50 transition-all">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div v-if="featured.length > 0 && otherCoupons.length > 0"
                            class="border-t border-dashed border-white/10 my-4"></div>

                        <!-- Other Coupons -->
                        <div v-if="otherCoupons.length > 0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                                More Coupons
                            </p>
                            <div class="space-y-3">
                                <div v-for="coupon in otherCoupons" :key="coupon.id"
                                    class="group relative border rounded-none transition-all duration-300 overflow-hidden"
                                    :class="isCouponApplied(coupon.id)
                                        ? 'border-green-500/30 bg-green-500/5'
                                        : 'border-white/10 hover:border-primary/30 bg-background/30 hover:bg-primary/5'">
                                    <div class="absolute top-0 left-0 w-1 h-full transition-all duration-300"
                                        :class="isCouponApplied(coupon.id) ? 'bg-green-500' : 'bg-primary/40 group-hover:bg-primary'"></div>
                                    <div class="p-4 pl-5 flex items-center gap-4">
                                        <div class="flex-shrink-0 w-16 h-16 flex flex-col items-center justify-center border border-dashed rounded-none"
                                            :class="isCouponApplied(coupon.id) ? 'border-green-500/30 bg-green-500/10' : 'border-primary/30 bg-primary/5'">
                                            <span class="text-lg font-bold leading-none"
                                                :class="isCouponApplied(coupon.id) ? 'text-green-400' : 'text-primary'">
                                                {{ formatDiscount(coupon) }}
                                            </span>
                                            <span class="text-[8px] uppercase tracking-wider mt-0.5"
                                                :class="isCouponApplied(coupon.id) ? 'text-green-500/60' : 'text-primary/60'">OFF</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-mono text-xs px-2 py-0.5 bg-white/5 border border-white/10 tracking-wider"
                                                    :class="isCouponApplied(coupon.id) ? 'text-green-400' : 'text-white'">
                                                    {{ coupon.code }}
                                                </span>
                                            </div>
                                            <p v-if="coupon.description" class="text-[11px] text-slate-400 leading-relaxed truncate">
                                                {{ coupon.description }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-1.5 text-[9px] text-slate-500 uppercase tracking-wider">
                                                <span v-if="coupon.min_spend > 0">Min ৳{{ parseFloat(coupon.min_spend).toLocaleString() }}</span>
                                                <span v-if="coupon.max_discount > 0">Max ৳{{ parseFloat(coupon.max_discount).toLocaleString() }}</span>
                                                <span v-if="coupon.expires_at">Exp {{ formatDate(coupon.expires_at) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <button v-if="isCouponApplied(coupon.id)" @click="$emit('remove', coupon)"
                                                class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-red-400 border border-red-500/30 hover:bg-red-500/10 transition-all">
                                                Remove
                                            </button>
                                            <button v-else @click="handleApply(coupon)"
                                                class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-primary border border-primary/30 hover:bg-primary/10 hover:border-primary/50 transition-all">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="allCoupons.length === 0" class="text-center py-12">
                            <Ticket class="w-12 h-12 text-slate-700 mx-auto mb-3" />
                            <p class="text-sm text-slate-400 font-serif">No coupons available</p>
                            <p class="text-xs text-slate-600 mt-1">Try entering a private code above</p>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-else class="flex-1 flex items-center justify-center p-12">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            <p class="text-xs text-slate-400 mt-3 uppercase tracking-widest">Loading coupons...</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Ticket, X } from 'lucide-vue-next';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    coupons: { type: Object, default: () => ({ featured: [], private: [], other: [], all: [] }) },
    appliedCouponIds: { type: Array, default: () => [] },
    subtotal: { type: Number, default: 0 },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'apply', 'apply-code', 'remove']);

const privateCode = ref('');
const codeError = ref('');
const applying = ref(false);

const featured = computed(() => props.coupons.featured || []);
const otherCoupons = computed(() => {
    const priv = props.coupons.private || [];
    const other = props.coupons.other || [];
    return [...priv, ...other];
});
const allCoupons = computed(() => props.coupons.all || []);

const isCouponApplied = (id) => props.appliedCouponIds.includes(id);

const handleApply = (coupon) => {
    if (isCouponApplied(coupon.id)) return;
    emit('apply', coupon);
};

const applyPrivateCode = () => {
    if (!privateCode.value.trim()) return;
    codeError.value = '';
    applying.value = true;
    emit('apply-code', {
        code: privateCode.value.trim(),
        onSuccess: () => {
            privateCode.value = '';
            applying.value = false;
        },
        onError: (msg) => {
            codeError.value = msg;
            applying.value = false;
        },
    });
};

const formatDiscount = (coupon) => {
    if (coupon.type === 'percent' || coupon.discount_type === 'percentage') {
        return `${coupon.value || coupon.discount_value}%`;
    }
    return `৳${parseFloat(coupon.value || coupon.discount_value || 0).toLocaleString()}`;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    });
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-active > div:last-child,
.modal-leave-active > div:last-child {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div:last-child {
    transform: translateY(20px) scale(0.97);
    opacity: 0;
}

.modal-leave-to > div:last-child {
    transform: translateY(10px) scale(0.98);
    opacity: 0;
}
</style>
