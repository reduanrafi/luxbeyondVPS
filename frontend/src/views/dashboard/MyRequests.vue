<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">My Requests</h2>
                <p class="text-xs text-slate-500 mt-1">Track and manage your product requests</p>
            </div>
            <div class="flex gap-3">
                <button v-if="selectedRequests.length > 0" @click="openConfirmationModal"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-green-700 transition-colors flex items-center gap-2">
                    <span>Create Order ({{ selectedRequests.length }})</span>
                </button>
                <router-link to="/request-product"
                    class="bg-primary text-slate-900 px-4 py-2 rounded-lg text-xs font-semibold hover:bg-primary-hover transition-colors">
                    + New Request
                </router-link>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="bg-surface border border-white/5 overflow-hidden hover:border-primary/30 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-white/5">
                            <th class="p-4 w-10">
                                <!-- Select All Checkbox could go here if needed -->
                            </th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Request ID
                            </th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Product URL
                            </th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Date</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Quantity</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Total</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Status</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="loading">
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary">
                                </div>
                                <p class="mt-2 text-xs uppercase tracking-wider">Loading requests...</p>
                            </td>
                        </tr>
                        <tr v-else-if="requests.length === 0">
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                <p class="text-sm font-semibold mb-1">No requests found</p>
                                <p class="text-xs">You haven't made any product requests yet.</p>
                            </td>
                        </tr>
                        <tr v-for="request in requests" :key="request.id"
                            class="hover:bg-white/5 transition-colors group">
                            <td class="p-4">
                                <input v-if="canSelect(request)" type="checkbox" v-model="selectedMap[request.id]"
                                    class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary bg-white/5 cursor-pointer">
                            </td>
                            <td
                                class="p-4 font-semibold text-primary group-hover:text-white transition-colors text-xs tracking-wide">
                                {{ request.request_number || '#' + request.id }}</td>
                            <td class="p-4 text-slate-400 text-xs max-w-xs truncate">
                                <a :href="request.url" target="_blank"
                                    class="hover:text-primary hover:underline transition-colors" :title="request.url">{{
                                        request.url }}</a>
                            </td>
                            <td class="p-4 text-slate-400 text-xs">{{ formatDate(request.created_at) }}</td>
                            <td class="p-4 text-slate-400 text-xs">{{ request.quantity }}</td>
                            <td class="p-4 font-serif text-white text-sm tracking-wide">৳{{
                                parseFloat(request.total_amount_bdt || 0).toLocaleString() }}</td>
                            <td class="p-4">
                                <span :class="getStatusClass(request.status)"
                                    class="px-2 py-1 border text-[10px] font-bold uppercase tracking-widest">
                                    {{ request.status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/dashboard/requests/${request.request_number}`"
                                        class="text-xs font-bold uppercase tracking-wider text-primary hover:text-white hover:underline transition-all">
                                        View
                                    </router-link>
                                    <button
                                        v-if="['request_accepted', 'accepted'].includes(request.status) && !request.shipping_address"
                                        @click="selectAndPay(request)"
                                        class="text-xs font-bold uppercase tracking-wider text-green-500 hover:text-green-400 hover:underline transition-all">
                                        Select
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

        <AddressConfirmationModal :isOpen="showAddressModal" :selectedRequests="selectedRequests"
            :loading="creatingOrder" :initialAddress="userAddress" @close="showAddressModal = false"
            @confirm="createOrder" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router'; // Add useRouter
import axios from '../../axios';
import PaymentModal from '../../components/PaymentModal.vue';
import AddressConfirmationModal from '../../components/AddressConfirmationModal.vue';
import { trackPurchase } from '../../utils/analytics';
import { useToast } from "vue-toastification";

const toast = useToast();
const router = useRouter(); // Initialize router
const requests = ref([]);
const loading = ref(false);
const showPayment = ref(false);
const selectedRequest = ref(null);
// Selection logic
const selectedMap = ref({});
const showAddressModal = ref(false);
const creatingOrder = ref(false);
const userAddress = ref({});

const selectedRequests = computed(() => {
    return requests.value.filter(r => selectedMap.value[r.id]);
});

const canSelect = (request) => {
    // Determine if request can be selected for consolidation
    // Logic: Status is 'request_accepted' OR 'accepted' AND no shipping address set (not confirmed yet)
    return ['request_accepted', 'accepted'].includes(request.status) && !request.shipping_address;
};

const fetchRequests = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/product-requests');
        const requestsData = response.data.data || response.data;
        requests.value = Array.isArray(requestsData) ? requestsData : (requestsData.data || []);

        // Reset selection on fetch
        selectedMap.value = {};

        // Try to pre-fill user address from user profile if available in one of the requests
        if (requests.value.length > 0 && requests.value[0].user && requests.value[0].user.shipping_address) {
            userAddress.value = requests.value[0].user.shipping_address;
        }

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
        case 'request_accepted':
        case 'accepted':
        case 'approved':
            return 'bg-green-500/10 text-green-500 border-green-500/20';
        case 'paid':
        case 'completed':
            return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
        case 'rejected':
        case 'cancelled':
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
        toast.success('Payment submitted successfully!');
    } catch (error) {
        toast.error('Payment failed: ' + (error.response?.data?.message || 'Unknown error'));
    }
};

const selectAndPay = (req) => {
    // Convenience function to select a single item and open modal immediately
    selectedMap.value = { [req.id]: true };
    openConfirmationModal();
};

const openConfirmationModal = () => {
    showAddressModal.value = true;
};

const createOrder = async (orderPayload) => {
    creatingOrder.value = true;
    try {
        let payload = orderPayload;
        let config = {};

        // If there's a payment slip, we must send FormData
        if (orderPayload.payment_slip) {
            payload = new FormData();

            payload.append('payment_method', orderPayload.payment_method);
            if (orderPayload.payment_type) {
                payload.append('payment_type', orderPayload.payment_type);
            }

            Object.keys(orderPayload.shipping_address).forEach(key => {
                payload.append(`shipping_address[${key}]`, orderPayload.shipping_address[key]);
            });

            orderPayload.request_items.forEach((item, index) => {
                payload.append(`request_items[${index}][id]`, item.id);
                payload.append(`request_items[${index}][quantity]`, item.quantity);
            });

            payload.append('payment_slip', orderPayload.payment_slip);

            config = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            };
        }

        const response = await axios.post('/product-requests/create-order', payload, config);

        showAddressModal.value = false;

        // Handle bKash redirection
        if (response.data.initiate_bkash && response.data.order_id) {
            // Initiate bKash payment for the order
            try {
                const bkashResponse = await axios.post('/payments/bkash/initiate', {
                    order_id: response.data.order_id,
                    amount: response.data.pay_amount,
                    payment_type: response.data.payment_type || 'partial'
                });

                if (bkashResponse.data.bkashURL) {
                    window.location.href = bkashResponse.data.bkashURL;
                    return;
                }
            } catch (bkashErr) {
                console.error('bKash Initiation Error:', bkashErr);
                toast.error('Order created, but bKash initiation failed. Please pay from Order Details.');
            }
        } else {
            // Track purchase for manual payment
            trackPurchase(response.data.order || {
                order_number: response.data.order_number,
                total: orderPayload.request_items.reduce((sum, ri) => {
                    const req = requests.value.find(r => r.id === ri.id);
                    return sum + (parseFloat(req?.total_amount_bdt || 0));
                }, 0),
                currency: 'BDT',
                items: orderPayload.request_items.map(ri => {
                    const req = requests.value.find(r => r.id === ri.id);
                    return {
                        product_id: ri.id,
                        product_name: req?.product_name || ('Request #' + ri.id),
                        price: parseFloat(req?.total_amount_bdt || 0) / ri.quantity,
                        quantity: ri.quantity
                    };
                })
            });
            toast.success('Request confirmed successfully!. It is now an order. Order id is ' + response.data.order_number);
        }

        // Redirect to the new order
        if (response.data.order_number) {
            router.push(`/dashboard/orders/${response.data.order_number}`);
        } else {
            await fetchRequests();
        }

    } catch (err) {
        console.error('Create Order Error:', err);
        toast.error(err.response?.data?.message || 'Failed to create order');
    } finally {
        creatingOrder.value = false;
    }
};

onMounted(() => {
    fetchRequests();
});
</script>
