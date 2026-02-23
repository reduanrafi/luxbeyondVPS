<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-800">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-pink-600 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z">
                        </path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white">bKash Payment</h2>
            </div>

            <!-- Payment Summary -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6 border border-slate-700">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm text-slate-400">Total Amount</span>
                    <span class="text-2xl font-bold text-primary">৳{{ formatPrice(amount) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-400">Minimum Payment (60%)</span>
                    <span class="text-lg font-semibold text-green-400">৳{{ formatPrice(amount * 0.6) }}</span>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="mb-6">
                <p class="text-sm font-semibold text-slate-300 mb-3">Select Payment Amount</p>
                <div class="space-y-3">
                    <!-- Partial Payment Option -->
                    <label
                        class="flex items-center gap-4 p-4 bg-slate-800 rounded-xl border-2 cursor-pointer transition-all"
                        :class="selectedAmount === 'partial' ? 'border-primary bg-primary/5' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" v-model="selectedAmount" value="partial"
                            class="w-5 h-5 text-primary focus:ring-primary focus:ring-2">
                        <div class="flex-1">
                            <p class="text-white font-semibold">Partial Payment (60%)</p>
                            <p class="text-sm text-slate-400">Pay ৳{{ formatPrice(amount * 0.6) }} now</p>
                        </div>
                    </label>

                    <!-- Full Payment Option -->
                    <label
                        class="flex items-center gap-4 p-4 bg-slate-800 rounded-xl border-2 cursor-pointer transition-all"
                        :class="selectedAmount === 'full' ? 'border-primary bg-primary/5' : 'border-slate-700 hover:border-slate-600'">
                        <input type="radio" v-model="selectedAmount" value="full"
                            class="w-5 h-5 text-primary focus:ring-primary focus:ring-2">
                        <div class="flex-1">
                            <p class="text-white font-semibold">Full Payment</p>
                            <p class="text-sm text-slate-400">Pay ৳{{ formatPrice(amount) }} now</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Important Note -->
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-400 mb-1">Payment Information</p>
                        <p class="text-xs text-blue-300">You will be redirected to bKash to complete your payment
                            securely.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="$emit('close')"
                    class="px-6 py-3 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors border border-slate-700">
                    Cancel
                </button>
                <button type="button" @click="proceedToBkash" :disabled="processing"
                    class="px-6 py-3 bg-gradient-to-r from-pink-600 to-pink-700 text-white font-semibold rounded-lg hover:from-pink-700 hover:to-pink-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg">
                    <span v-if="processing">Processing...</span>
                    <span v-else>Proceed to bKash</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    amount: Number,
    requestId: Number,
});

const emit = defineEmits(['close', 'proceed-to-bkash']);

const selectedAmount = ref('partial'); // Default to partial payment
const processing = ref(false);

const formatPrice = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toFixed(2);
};

const proceedToBkash = () => {
    processing.value = true;
    const paymentAmount = selectedAmount.value === 'full'
        ? props.amount
        : props.amount * 0.6;

    emit('proceed-to-bkash', {
        requestId: props.requestId,
        amount: paymentAmount,
        paymentType: selectedAmount.value
    });
};
</script>
