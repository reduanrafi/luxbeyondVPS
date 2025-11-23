<template>
    <div class="mt-8">
        <div v-if="requestStore.loading" class="text-gray-500">Loading...</div>
        <div v-else-if="requestStore.requests.length === 0" class="text-gray-500">No requests found.</div>
        <div v-else class="grid gap-4">
            <div v-for="req in requestStore.requests" :key="req.id" class="bg-white p-4 rounded shadow border">
                <div class="flex justify-between items-start">
                    <div>
                        <a :href="req.url" target="_blank"
                            class="text-blue-600 font-semibold hover:underline truncate block max-w-md">{{ req.url
                            }}</a>
                        <p class="text-sm text-gray-600">
                            {{ req.quantity }} x {{ req.currency }} {{ req.price }}
                            <span v-if="req.total_amount_bdt"> | Total: BDT {{ req.total_amount_bdt }}</span>
                        </p>
                        <p class="text-xs text-gray-500">Date: {{ new Date(req.created_at).toLocaleDateString() }}</p>
                    </div>
                    <div class="text-right">
                        <span :class="{
                            'bg-yellow-100 text-yellow-800': req.status === 'pending',
                            'bg-green-100 text-green-800': req.status === 'approved',
                            'bg-blue-100 text-blue-800': req.status === 'paid',
                            'bg-red-100 text-red-800': req.status === 'rejected'
                        }" class="px-2 py-1 rounded text-xs font-bold uppercase">
                            {{ req.status }}
                        </span>
                        <button v-if="req.status === 'approved'" @click="openPayment(req)"
                            class="ml-2 text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">Pay
                            Now</button>
                    </div>
                </div>
            </div>
        </div>
        <PaymentModal :isOpen="showPayment" :amount="selectedRequest?.total_amount_bdt" :requestId="selectedRequest?.id"
            @close="showPayment = false" @payment-submitted="handlePayment" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRequestStore } from '../stores/request';
import PaymentModal from './PaymentModal.vue';
import axios from '../axios';

const requestStore = useRequestStore();
const showPayment = ref(false);
const selectedRequest = ref(null);

onMounted(() => {
    requestStore.fetchRequests();
});

const openPayment = (req) => {
    selectedRequest.value = req;
    showPayment.value = true;
};

const handlePayment = async (paymentData) => {
    try {
        // In a real app, this would hit a payment endpoint.
        // For now, we'll just update the status to 'paid' via the update endpoint (simulating admin/system action)
        // OR create a specific 'confirm' endpoint. Let's use a confirm endpoint.
        await axios.post(`/requests/${paymentData.requestId}/confirm`, paymentData);

        // Update local state
        const index = requestStore.requests.findIndex(r => r.id === paymentData.requestId);
        if (index !== -1) {
            requestStore.requests[index].status = 'paid';
        }
        showPayment.value = false;
        alert('Payment submitted successfully!');
    } catch (error) {
        alert('Payment failed');
    }
};
</script>
