<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-slate-900 p-8 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-800">
            <h2 class="text-2xl font-bold text-white mb-6">Bank Transfer Payment</h2>

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

            <!-- Important Note -->
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-400 mb-1">Important</p>
                        <p class="text-xs text-yellow-300">To confirm your order, you must pay at least 60% of the total
                            amount. Upload your payment slip after making the transfer.</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitPayment">
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Transaction ID / Reference Number
                        *</label>
                    <input v-model="transactionId" type="text"
                        class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        required placeholder="e.g. TXN123456789">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Upload Payment Slip / Screenshot
                        *</label>
                    <div class="relative">
                        <input type="file" @change="handleFileChange" accept="image/*" required
                            class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p v-if="fileName" class="mt-2 text-xs text-green-400">✓ {{ fileName }}</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="$emit('close')"
                        class="px-6 py-3 bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors border border-slate-700">
                        Cancel
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span v-if="submitting">Submitting...</span>
                        <span v-else>Submit Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    amount: Number,
    requestId: Number,
    orderId: Number,
    isOrder: Boolean,
    paymentMethod: String
});

const emit = defineEmits(['close', 'payment-submitted']);

const transactionId = ref('');
const file = ref(null);
const fileName = ref('');
const submitting = ref(false);

const handleFileChange = (event) => {
    file.value = event.target.files[0];
    fileName.value = event.target.files[0]?.name || '';
};

const formatPrice = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toFixed(2);
};

const submitPayment = () => {
    submitting.value = true;
    emit('payment-submitted', {
        requestId: props.requestId,
        orderId: props.orderId,
        method: 'bank',
        transactionId: transactionId.value,
        proof: file.value
    });
    // Reset submitting after a delay (parent should close modal)
    setTimeout(() => {
        submitting.value = false;
    }, 2000);
};
</script>
