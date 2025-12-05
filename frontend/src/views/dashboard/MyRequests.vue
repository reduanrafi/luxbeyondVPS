<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">My Requests</h2>
                <p class="text-xs text-slate-500 mt-1">Track and manage your product requests</p>
            </div>
            <router-link to="/request-product"
                class="bg-primary text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-primary-hover transition-colors">
                + New Request
            </router-link>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Request ID</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Product URL</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Date</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Quantity</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Total</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Status</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="7" class="p-6 text-center text-slate-500">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                <p class="mt-2 text-xs">Loading requests...</p>
                            </td>
                        </tr>
                        <tr v-else-if="requests.length === 0">
                            <td colspan="7" class="p-6 text-center text-slate-500">
                                <p class="text-sm font-semibold mb-1">No requests found</p>
                                <p class="text-xs">You haven't made any product requests yet.</p>
                            </td>
                        </tr>
                        <tr v-for="request in requests" :key="request.id" class="hover:bg-blue-50/50 transition-all group">
                            <td class="p-3 font-semibold text-primary group-hover:text-primary-hover transition-colors text-xs">#{{ request.id }}</td>
                            <td class="p-3 text-slate-600 text-xs max-w-xs truncate">
                                <a :href="request.url" target="_blank" class="hover:underline" :title="request.url">{{ request.url }}</a>
                            </td>
                            <td class="p-3 text-slate-600 text-xs">{{ formatDate(request.created_at) }}</td>
                            <td class="p-3 text-slate-600 text-xs">{{ request.quantity }}</td>
                            <td class="p-3 font-semibold text-slate-900 text-sm">৳{{ parseFloat(request.total_amount_bdt || 0).toLocaleString() }}</td>
                            <td class="p-3">
                                <span :class="getStatusClass(request.status)" class="px-2 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide shadow-sm">
                                    {{ request.status }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/dashboard/requests/${request.id}`" 
                                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline transition-all">
                                        View →
                                    </router-link>
                                    <button v-if="request.status === 'approved'" @click="openPayment(request)" class="text-xs font-semibold text-green-600 hover:text-green-700 hover:underline transition-all">
                                        Pay Now
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <PaymentModal :isOpen="showPayment" :amount="selectedRequest?.total_amount_bdt" :requestId="selectedRequest?.id"
            @close="showPayment = false" @payment-submitted="handlePayment" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../axios';
import PaymentModal from '../../components/PaymentModal.vue';

const requests = ref([]);
const loading = ref(false);
const showPayment = ref(false);
const selectedRequest = ref(null);

const fetchRequests = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/requests');
        const requestsData = response.data.data || response.data;
        requests.value = Array.isArray(requestsData) ? requestsData : (requestsData.data || []);
    } catch (error) {
        console.error('Error fetching requests:', error);
        requests.value = [];
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300';
        case 'approved':
            return 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300';
        case 'paid':
            return 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300';
        case 'rejected':
            return 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300';
        default:
            return 'bg-gray-100 text-gray-700';
    }
};

const openPayment = (req) => {
    selectedRequest.value = req;
    showPayment.value = true;
};

const handlePayment = async (paymentData) => {
    try {
        await axios.post(`/requests/${paymentData.requestId}/confirm`, paymentData);
        await fetchRequests();
        showPayment.value = false;
        alert('Payment submitted successfully!');
    } catch (error) {
        alert('Payment failed: ' + (error.response?.data?.message || 'Unknown error'));
    }
};

onMounted(() => {
    fetchRequests();
});
</script>
