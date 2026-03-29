<template>
    <div class="min-h-[700px] pt-32 pb-20 px-4 md:px-8 relative my-auto">
        <div
                class="absolute inset-0 bg-[url('/assets/track.png')] bg-cover bg-center opacity-20">
            </div>
        <div class="max-w-4xl m-auto space-y-12 mt-10">
            <!-- Header -->
            <div class="text-center space-y-4">
                <h1 class="text-4xl md:text-5xl font-bold font-serif text-primary">Track Your Order</h1>
                <p class="text-[#00ffff] max-w-lg mx-auto">
                    Enter your Order Number (e.g., ORD-1234) or Request Number (e.g., REQ-5678) to see the current
                    status.
                </p>
            </div>

            <!-- Search Form -->
            <div class="max-w-md mx-auto relative group">
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-primary/50 to-primary/20 rounded-xl blur opacity-25 group-hover:opacity-50 transition duration-1000">
                </div>
                <div class="relative flex gap-2">
                    <input v-model="trackingNumber" @keyup.enter="trackOrder" type="text"
                        placeholder="Enter Tracking Number..."
                        class="w-full bg-[#111111] border border-white/10 rounded-xl px-6 py-4 text-white placeholder:text-[#00ffff]/70 focus:outline-none focus:border-primary/50 transition-colors">
                    <button @click="trackOrder" :disabled="loading"
                        class="bg-primary text-white font-bold px-8 rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-50 flex items-center gap-2">
                        <Search v-if="!loading" class="w-5 h-5" />
                        <Loader2 v-else class="w-5 h-5 animate-spin" />
                        <span class="hidden md:inline">Track</span>
                    </button>
                </div>
                <p v-if="error" class="text-red-500 text-sm mt-2 text-center">{{ error }}</p>
            </div>

            <!-- Result -->
            <div v-if="result"
                class="bg-[#111111] border border-white/10 rounded-2xl p-6 md:p-10 space-y-8 animate-fade-in-up">
                <!-- Status Header -->
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-white/10 pb-6">
                    <div>
                        <p class="text-[#00ffff] text-sm mb-1">{{ result.type === 'order' ? 'Shop Order' : `Product
                            Request` }}</p>
                        <h2 class="text-2xl font-bold text-white">{{ result.order_number || result.request_number }}
                        </h2>
                        <p class="text-zinc-500 text-sm mt-1">Placed on {{ result.date }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="px-3 py-1 bg-primary/10 text-primary rounded-full text-sm font-medium border border-primary/20">
                            {{ result.status }}
                        </span>
                        <span class="text-xl font-bold text-white">
                            {{ formatPrice(result.total_amount) }}
                        </span>
                    </div>
                </div>

                <!-- Timeline Steps -->
                <div class="py-4">
                    <div class="relative">
                        <!-- Progress Line -->
                        <div class="absolute top-1/2 left-0 w-full h-1 bg-white/5 -translate-y-1/2 hidden md:block">
                        </div>
                        <div class="absolute top-1/2 left-0 h-1 bg-gradient-to-r from-primary to-primary/50 -translate-y-1/2 hidden md:block transition-all duration-1000"
                            :style="{ width: getProgressWidth(result.current_status_step) }"></div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-4 relative z-10">
                            <!-- Step 1: Pending -->
                            <div class="flex flex-row md:flex-col items-center gap-4 md:text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors duration-300"
                                    :class="getStepClass(1, result.current_status_step)">
                                    <ClipboardList class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Order Placed</h3>
                                    <p class="text-xs text-zinc-500 mt-1">We have received your order</p>
                                </div>
                            </div>

                            <!-- Step 2: Processing -->
                            <div class="flex flex-row md:flex-col items-center gap-4 md:text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors duration-300"
                                    :class="getStepClass(2, result.current_status_step)">
                                    <Package class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Processing</h3>
                                    <p class="text-xs text-zinc-500 mt-1">We are preparing your items</p>
                                </div>
                            </div>

                            <!-- Step 3: Shipped -->
                            <div class="flex flex-row md:flex-col items-center gap-4 md:text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors duration-300"
                                    :class="getStepClass(3, result.current_status_step)">
                                    <Truck class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm">On The Way</h3>
                                    <p class="text-xs text-zinc-500 mt-1">Your order is being delivered</p>
                                </div>
                            </div>

                            <!-- Step 4: Delivered -->
                            <div class="flex flex-row md:flex-col items-center gap-4 md:text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors duration-300"
                                    :class="getStepClass(4, result.current_status_step)">
                                    <CheckCircle2 class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Delivered</h3>
                                    <p class="text-xs text-zinc-500 mt-1">Enjoy your items!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white/5 rounded-xl p-6">
                    <h3 class="font-bold text-white mb-4">Items in Order</h3>
                    <div class="space-y-4">
                        <div v-for="(item, index) in result.items" :key="index" class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 bg-[#111111] rounded-lg border border-white/10 overflow-hidden flex-shrink-0">
                                <img v-if="item.image" :src="item.image" :alt="item.name"
                                    class="w-full h-full object-contain">
                                <div v-else class="w-full h-full flex items-center justify-center text-zinc-600">
                                    <Package class="w-6 h-6" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-white font-medium line-clamp-1">{{ item.name }}</h4>
                                <p class="text-zinc-500 text-sm">Qty: {{ item.quantity }}</p>
                            </div>
                            <div class="text-white font-medium">
                                {{ formatPrice(item.price) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Details -->
                <div v-if="result.timeline && result.timeline.length > 0" class="border-t border-white/10 pt-6">
                    <h3 class="font-bold text-white mb-4">Tracking History</h3>
                    <div class="space-y-4 pl-4 border-l border-white/10 ml-2">
                        <div v-for="(log, idx) in result.timeline" :key="idx" class="relative pl-6 pb-2">
                            <div
                                class="absolute -left-[5px] top-2 w-2.5 h-2.5 rounded-full bg-primary ring-4 ring-[#111111]">
                            </div>
                            <p class="text-white font-medium">{{ log.status }}</p>
                            <p v-if="log.note" class="text-[#00ffff] text-sm italic">"{{ log.note }}"</p>
                            <p class="text-zinc-500 text-xs mt-1">{{ log.date }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from '../axios';
import { Search, Loader2, ClipboardList, Package, Truck, CheckCircle2 } from 'lucide-vue-next';

const trackingNumber = ref('');
const loading = ref(false);
const result = ref(null);
const error = ref('');

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0
    }).format(price).replace('BDT', '৳');
};

const trackOrder = async () => {
    if (!trackingNumber.value) {
        error.value = 'Please enter a tracking number';
        return;
    }

    loading.value = true;
    error.value = '';
    result.value = null;

    try {
        const response = await axios.post('/track-order', {
            tracking_number: trackingNumber.value
        });
        result.value = response.data.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Order not found. Please check your number.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const getProgressWidth = (step) => {
    if (step === 0) return '0%'; // Cancelled
    const percentage = ((step - 1) / 3) * 100;
    return `${percentage}%`;
};

const getStepClass = (stepNumber, currentStep) => {
    // 0 is cancelled/failed
    if (currentStep === 0) return 'border-red-500/50 bg-red-500/10 text-red-500';

    if (stepNumber <= currentStep) {
        return 'border-primary bg-primary text-black shadow-[0_0_15px_rgba(212,175,55,0.4)]';
    }
    return 'border-white/10 bg-white/5 text-zinc-500';
};
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
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
