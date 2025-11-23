<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirm & Pay</h2>
            <p class="mb-4">Total Amount: <strong>BDT {{ amount }}</strong></p>
            <p class="mb-4 text-sm text-gray-600">Please pay 60% + 1.5% charge to confirm your order.</p>
            
            <form @submit.prevent="submitPayment">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Payment Method</label>
                    <select v-model="paymentMethod" class="w-full p-2 border rounded">
                        <option value="bkash">bKash</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Transaction ID</label>
                    <input v-model="transactionId" type="text" class="w-full p-2 border rounded" required placeholder="e.g. 8J2K9L1M">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Upload Proof (Mock)</label>
                    <input type="file" class="w-full p-2 border rounded">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="$emit('close')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    amount: Number,
    requestId: Number
});

const emit = defineEmits(['close', 'payment-submitted']);

const paymentMethod = ref('bkash');
const transactionId = ref('');

const submitPayment = () => {
    // Mock payment submission
    emit('payment-submitted', {
        requestId: props.requestId,
        method: paymentMethod.value,
        transactionId: transactionId.value
    });
};
</script>
