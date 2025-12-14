<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">My Requests</h2>
                <p class="text-xs text-slate-500 mt-1">Track and manage your product requests</p>
            </div>
            <router-link to="/request-product"
                class="bg-primary text-slate-900 px-4 py-2 rounded-lg text-xs font-semibold hover:bg-primary-hover transition-colors">
                + New Request
            </router-link>
        </div>

        <!-- Requests Table -->
        <div class="bg-surface border border-white/5 overflow-hidden hover:border-primary/30 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-white/5">
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Request ID</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Product URL</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Date</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Quantity</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Total</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Status</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="loading">
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                <p class="mt-2 text-xs uppercase tracking-wider">Loading requests...</p>
                            </td>
                        </tr>
                        <tr v-else-if="requests.length === 0">
                            <td colspan="7" class="p-8 text-center text-slate-500">
                                <p class="text-sm font-semibold mb-1">No requests found</p>
                                <p class="text-xs">You haven't made any product requests yet.</p>
                            </td>
                        </tr>
                        <tr v-for="request in requests" :key="request.id" class="hover:bg-white/5 transition-colors group">
                            <td class="p-4 font-semibold text-primary group-hover:text-white transition-colors text-xs tracking-wide">{{ request.request_number || '#' + request.id }}</td>
                            <td class="p-4 text-slate-400 text-xs max-w-xs truncate">
                                <a :href="request.url" target="_blank" class="hover:text-primary hover:underline transition-colors" :title="request.url">{{ request.url }}</a>
                            </td>
                            <td class="p-4 text-slate-400 text-xs">{{ formatDate(request.created_at) }}</td>
                            <td class="p-4 text-slate-400 text-xs">{{ request.quantity }}</td>
                            <td class="p-4 font-serif text-white text-sm tracking-wide">৳{{ parseFloat(request.total_amount_bdt || 0).toLocaleString() }}</td>
                            <td class="p-4">
                                <span :class="getStatusClass(request.status)" class="px-2 py-1 border text-[10px] font-bold uppercase tracking-widest">
                                    {{ request.status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/dashboard/requests/${request.id}`" 
                                        class="text-xs font-bold uppercase tracking-wider text-primary hover:text-white hover:underline transition-all">
                                        View
                                    </router-link>
                                    <button v-if="request.status === 'approved'" @click="openPayment(request)" class="text-xs font-bold uppercase tracking-wider text-green-500 hover:text-green-400 hover:underline transition-all">
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
        const response = await axios.get('/product-requests');
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
            return 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
        case 'approved':
            return 'bg-green-500/10 text-green-500 border-green-500/20';
        case 'paid':
            return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
        case 'rejected':
            return 'bg-red-500/10 text-red-500 border-red-500/20';
        default:
            return 'bg-slate-500/10 text-slate-400 border-white/10';
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
