<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="bg-surface border border-white/5 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-slate-400 uppercase tracking-widest">Loading order details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-surface border border-red-500/30 p-8 text-center">
            <p class="text-red-400 font-semibold">{{ error }}</p>
            <button @click="$router.push('/dashboard/orders')"
                class="mt-4 px-6 py-2 bg-primary text-slate-900 font-bold uppercase tracking-widest rounded-none hover:bg-primary-hover transition-colors">
                Back to Orders
            </button>
        </div>

        <!-- Order Details -->
        <div v-else-if="order" class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-white/5 pb-6">
                <div>
                    <h2 class="text-3xl font-serif text-white tracking-wide">Order Details</h2>
                    <p class="text-sm text-slate-400 mt-2 uppercase tracking-widest">Order #{{ order.order_number ||
                        order.id }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="$router.push('/dashboard/orders')"
                        class="px-6 py-2 text-sm font-bold uppercase tracking-widest text-slate-400 hover:text-primary border border-white/10 rounded-none hover:border-primary/30 transition-all">
                        ← Back
                    </button>
                </div>
            </div>

            <!-- Order Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-surface rounded-none border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Order Status</p>
                    <span :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }"
                        class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                        {{ order.status?.label || order.status || 'Pending' }}
                    </span>
                </div>
                <div class="bg-surface rounded-none border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Order Date</p>
                    <p class="text-sm font-semibold text-white">{{ formatDate(order.created_at) }}</p>
                </div>
                <div class="bg-surface rounded-none border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Payment Status</p>
                    <p class="text-sm font-semibold text-white mb-2">{{ order.payment_status || 'Pending' }}</p>
                    <div v-if="order.payment_method" class="space-y-1">
                        <p class="text-xs text-slate-400 capitalize">Method: <span class="text-white">{{
                            order.payment_method.replace('_', ' ') }}</span></p>
                        <p v-if="order.payment_reference" class="text-xs text-slate-400">TrxID: <span
                                class="text-white font-mono">{{ order.payment_reference }}</span></p>
                        <p v-if="order.bkash_trx_id && !order.payment_reference" class="text-xs text-slate-400">TrxID:
                            <span class="text-white font-mono">{{ order.bkash_trx_id }}</span>
                        </p>
                        <a v-if="order.payment_slip_url" :href="order.payment_slip_url" target="_blank"
                            class="text-xs text-primary hover:underline block mt-1">View Payment Slip</a>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-surface border border-white/5 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-background/50">
                    <h3 class="text-sm font-serif text-white uppercase tracking-widest">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-background/50 border-b border-white/10">
                            <tr>
                                <th class="p-3 text-left text-xs font-semibold text-slate-300 uppercase">Product</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-300 uppercase">Quantity</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-300 uppercase">Price</th>
                                <th class="p-3 text-right text-xs font-semibold text-slate-300 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="item in order.items" :key="item.id" class="hover:bg-white/5 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-4 group cursor-pointer"
                                        @click="item.product && router.push(`/shop/${item.product.slug}`)">
                                        <div
                                            class="w-16 h-16 bg-white/5 rounded-none overflow-hidden border border-white/10 group-hover:border-primary/50 transition-colors">
                                            <div v-if="item.request">
                                                <img :src="item.request.admin_image_url"
                                                    :alt="item.request.product_name"
                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                                            </div>
                                            <div v-else-if="item.product">
                                                <img v-if="item.image || (item.product && item.product.image)"
                                                    :src="item.image || (item.product ? item.product.image_url : null)"
                                                    :alt="item.product_name"
                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                                            </div>
                                            <div v-else
                                                class="w-full h-full flex items-center justify-center text-slate-500 text-xs">
                                                No Image
                                            </div>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                {{ item.request?.product_name || item.product?.name }}</p>
                                            <p v-if="item.product_sku" class="text-xs text-slate-400 mt-1">SKU: {{
                                                item.product_sku }}</p>
                                            <div v-if="item.variant_data"
                                                class="text-xs text-slate-500 mt-1 space-y-0.5">
                                                <div v-for="(val, key) in getParsedVariantData(item.variant_data)"
                                                    :key="key">
                                                    <span v-if="key === 'request_url'">
                                                        URL: <a :href="val" target="_blank"
                                                            class="text-primary hover:underline break-all"
                                                            @click.stop>{{ val.substring(0, 50) + '...' }}</a>
                                                    </span>
                                                    <span v-else>{{ key }}: {{ val }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-xs text-slate-400">{{ item.quantity }}</td>
                                <td class="p-3 text-xs text-slate-400">{{ formatPrice(item.price) }}</td>
                                <td class="p-3 text-right text-xs font-semibold text-white">{{ formatPrice(item.subtotal
                                    || (item.price * item.quantity)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charges Summary -->
            <div v-if="hasAppliedCharges" class="bg-surface border border-white/5 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-background/50">
                    <h3 class="text-sm font-serif text-white uppercase tracking-widest">Applied Charges Summary</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div v-for="item in order.items" :key="`charges-${item.id}`">
                        <ul v-if="item.request && item.request.charges_breakdown">
                            <li v-for="charge in item.request.charges_breakdown" :key="charge.charge">
                                <div class="flex justify-between items-center text-sm">
                                    <div class="space-y-1">
                                        <p class="text-white font-medium">{{ charge.charge }}</p>
                                        <p class="text-[10px] text-slate-500 uppercase tracking-wider">
                                            {{ formatPrice((charge.amount_in_bdt ?? charge.amount ?? 0) /
                                                (item.request.quantity || 1)) }} x {{ item.quantity }}
                                        </p>
                                    </div>
                                    <span class="font-bold text-white">{{
                                        formatPrice(((charge.amount_in_bdt ?? charge.amount ?? 0) /
                                            (item.request.quantity || 1)) * item.quantity) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="px-6 py-4 bg-white/5 border-t border-white/10 flex justify-between items-center">
                    <span class="text-xs font-serif text-slate-400 uppercase tracking-widest">Total Applied Charges</span>
                    <span class="text-lg font-bold text-primary">{{ formatPrice(getTotalCharges) }}</span>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shipping Information -->
                <div class="bg-surface border border-white/5 p-6">
                    <h3
                        class="text-sm font-serif text-white uppercase tracking-widest mb-4 border-b border-white/5 pb-3">
                        Shipping Information</h3>
                    <div class="space-y-2 text-xs">
                        <p><span class="font-semibold text-slate-300">Name:</span> <span class="text-slate-400">{{
                            order.shipping_name || 'N/A' }}</span></p>
                        <p><span class="font-semibold text-slate-300">Email:</span> <span class="text-slate-400">{{
                            order.shipping_email || 'N/A' }}</span></p>
                        <p><span class="font-semibold text-slate-300">Phone:</span> <span class="text-slate-400">{{
                            order.shipping_phone || 'N/A' }}</span></p>
                        <p><span class="font-semibold text-slate-300">Address:</span> <span class="text-slate-400">{{
                            formatAddress(order.shipping_address) }}</span>
                        </p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-surface border border-white/5 p-6">
                    <h3
                        class="text-sm font-serif text-white uppercase tracking-widest mb-4 border-b border-white/5 pb-3">
                        Order Summary</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subtotal:</span>
                            <span class="font-semibold text-white">{{ formatPrice(order.subtotal || 0) }}</span>
                        </div>
                        <div v-if="order.tax > 0" class="flex justify-between">
                            <span class="text-slate-400">Tax:</span>
                            <span class="font-semibold text-white">{{ formatPrice(order.tax) }}</span>
                        </div>
                        <div v-if="order.shipping > 0" class="flex justify-between">
                            <span class="text-slate-400">Shipping:</span>
                            <span class="font-semibold text-white">{{ formatPrice(order.shipping) }}</span>
                        </div>
                        <div v-if="hasAppliedCharges" class="flex justify-between">
                            <span class="text-slate-400">Charges:</span>
                            <span class="font-semibold text-white">{{ formatPrice(getTotalCharges) }}</span>
                        </div>
                        <div v-if="order.discount > 0" class="flex justify-between">
                            <span class="text-slate-400">Discount:</span>
                            <span class="font-semibold text-green-600">-{{ formatPrice(order.discount) }}</span>
                        </div>
                        <div class="border-t border-white/10 pt-3 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm font-bold text-white">Total:</span>
                                <span class="text-sm font-bold text-primary">{{ formatPrice(order.total ||
                                    order.total_amount || 0) }}</span>
                            </div>
                            <!-- Paid Amount -->
                            <div class="flex justify-between">
                                <span class="text-xs text-slate-400">Paid:</span>
                                <span class="text-xs font-bold text-green-500">{{ formatPrice(order.paid_amount || 0)
                                    }}</span>
                            </div>
                            <!-- Due Amount -->
                            <div class="flex justify-between">
                                <span class="text-xs text-slate-400">Due:</span>
                                <span class="text-xs font-bold text-red-500">{{ formatPrice(order.due_amount || 0)
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Options -->
            <div v-if="order.payment_status === 'unpaid'" class="bg-surface border border-white/5 p-6">
                <h3 class="text-sm font-serif text-white uppercase tracking-widest mb-4 border-b border-white/5 pb-3">
                    Payment Options</h3>

                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Bkash -->
                    <button @click="initiateBkash" :disabled="processingBkash"
                        class="px-6 py-3 bg-[#E2136E] text-white font-bold rounded-lg hover:bg-[#C2105E] transition-colors flex items-center justify-center gap-2">
                        <span v-if="processingBkash"
                            class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        {{ processingBkash ? 'Processing...' : 'Pay with bKash' }}
                    </button>

                    <!-- Bank Transfer -->
                    <button @click="showPayment = true"
                        class="px-6 py-3 bg-white text-slate-900 border border-slate-300 font-bold rounded-lg hover:bg-slate-50 transition-colors">
                        Bank Transfer / Manual
                    </button>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-surface border border-white/5 overflow-hidden">
                <div class="p-4 border-b border-white/5 bg-background/50">
                    <h3 class="text-sm font-serif text-white uppercase tracking-widest">Payment Information</h3>
                </div>
                <div class="p-4">
                    <!-- From payments relation -->
                    <div v-if="order.payments && order.payments.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-white/5 border-b border-white/5">
                                <tr>
                                    <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">Method</th>
                                    <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">Amount</th>
                                    <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">TrxID/Ref
                                    </th>
                                    <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">Date</th>
                                    <th class="p-3 text-right text-xs font-semibold text-slate-400 uppercase">Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="payment in order.payments" :key="payment.id"
                                    class="hover:bg-white/5 transition-colors">
                                    <td class="p-3 text-xs text-white capitalize">{{
                                        formatPaymentMethod(payment.payment_method) }}</td>
                                    <td class="p-3 text-xs font-semibold text-white">{{ formatPrice(payment.amount) }}
                                    </td>
                                    <td class="p-3 text-xs text-slate-400 font-mono">
                                        {{ payment.payment_reference || '-' }}
                                        <a v-if="payment.payment_slip_url" :href="payment.payment_slip_url"
                                            target="_blank" class="text-primary hover:underline ml-2" @click.stop>
                                            [View Slip]
                                        </a>
                                    </td>
                                    <td class="p-3 text-xs text-slate-400">{{ formatDate(payment.paid_at ||
                                        payment.created_at) }}</td>
                                    <td class="p-3 text-right">
                                        <span :class="{
                                            'bg-green-500/20 text-green-500': payment.status === 'completed',
                                            'bg-yellow-500/20 text-yellow-500': payment.status === 'pending',
                                            'bg-red-500/20 text-red-500': payment.status === 'failed' || payment.status === 'refunded',
                                        }" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                            {{ payment.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Fallback: show from order fields if no payment records -->
                    <div v-else-if="order.payment_method" class="flex items-start gap-4">
                        <div class="p-3 bg-white/5 rounded border border-white/10 flex-shrink-0">
                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="flex-1 space-y-1">
                            <p class="text-sm text-slate-300">
                                <span class="font-semibold">Method:</span>
                                <span class="ml-2 capitalize">{{ order.payment_method.replace('_', ' ') }}</span>
                            </p>
                            <p v-if="order.bkash_trx_id" class="text-xs text-slate-400">TrxID: <span
                                    class="font-mono text-white">{{ order.bkash_trx_id }}</span></p>
                            <p v-else-if="order.payment_reference" class="text-xs text-slate-400">Reference: <span
                                    class="font-mono text-white">{{ order.payment_reference }}</span></p>
                            <a v-if="order.payment_slip_url" :href="order.payment_slip_url" target="_blank"
                                class="text-xs font-bold text-primary hover:underline uppercase tracking-wide inline-block mt-1">
                                View Slip →
                            </a>
                            <p v-if="!order.bkash_trx_id && !order.payment_reference && !order.payment_slip_url"
                                class="text-xs text-slate-500 italic mt-1">Awaiting payment details.</p>
                        </div>
                        <div v-if="order.payment_status === 'pending'" class="ml-auto flex-shrink-0">
                            <span
                                class="px-3 py-1 bg-yellow-500/20 text-yellow-500 rounded-full text-xs font-bold uppercase tracking-wider">
                                Pending Review
                            </span>
                        </div>
                    </div>
                    <p v-else class="text-xs text-slate-500 italic">No payment information available.</p>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="order.notes" class="bg-surface border border-white/5 p-6">
                <h3 class="text-sm font-serif text-white uppercase tracking-widest mb-3 border-b border-white/5 pb-3">
                    Order Notes</h3>
                <p class="text-xs text-slate-400 leading-relaxed">{{ order.notes }}</p>
            </div>

            <!-- Timeline -->
            <div v-if="order.status_histories && order.status_histories.length > 0"
                class="bg-surface border border-white/5 overflow-hidden">
                <div class="p-4 border-b border-white/5 bg-background/50">
                    <h3 class="text-sm font-serif text-white uppercase tracking-widest">Order Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="relative pl-4 border-l-2 border-white/10 space-y-8">
                        <div v-for="(history, index) in order.status_histories" :key="history.id" class="relative">
                            <div
                                class="absolute -left-[21px] bg-background border-2 border-primary rounded-full w-4 h-4">
                            </div>
                            <div class="mb-1">
                                <span class="font-bold text-white">{{ history.status?.label || 'Status Changed'
                                }}</span>
                                <span class="text-xs text-slate-400 ml-2">{{ formatDate(history.created_at) }}</span>
                            </div>
                            <p v-if="history.note" class="text-xs text-slate-400 opacity-80 mb-1">{{ history.note }}</p>
                            <p v-if="history.changed_by" class="text-[10px] text-slate-500 uppercase tracking-wider">
                                UT: {{ history.changed_by.name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <PaymentModal :isOpen="showPayment" :amount="order?.due_amount || order?.total" :orderId="order?.id"
            :isOrder="true" @close="showPayment = false" @payment-submitted="handleBankTransfer" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../../axios';
import PaymentModal from '../../components/PaymentModal.vue';
import { useToast } from "vue-toastification";

const toast = useToast();
const route = useRoute();
const router = useRouter();
const order = ref(null);
const loading = ref(true);
const error = ref(null);

// Payment States
const showPayment = ref(false);
const processingBkash = ref(false);

const hasAppliedCharges = computed(() => {
    if (!order.value?.items) return false;
    return order.value.items.some(item =>
        item.request &&
        item.request.charges_breakdown &&
        item.request.charges_breakdown.length > 0
    );
});

const getTotalCharges = computed(() => {
    let total = 0;
    if (!order.value?.items) return 0;
    order.value.items.forEach(item => {
        if (item.request?.charges_breakdown) {
            item.request.charges_breakdown.forEach(charge => {
                const baseAmount = charge.amount_in_bdt ?? charge.amount ?? 0;
                const calculatedCharge = (baseAmount / (item.request.quantity || 1)) * (item.quantity || 1);
                total += calculatedCharge;
            });
        }
    });
    return total;
});

const fetchOrder = async () => {
    loading.value = true;
    error.value = null;
    try {
        const orderId = route.params.orderNumber; // Use orderNumber from route
        const response = await axios.get(`/orders/${orderId}`);
        order.value = response.data.data || response.data;
    } catch (err) {
        console.error('Error fetching order:', err);
        error.value = err.response?.data?.message || 'Order not found';
    } finally {
        loading.value = false;
    }
};

const initiateBkash = async () => {
    if (processingBkash.value) return;
    processingBkash.value = true;
    try {
        const response = await axios.post('/payments/bkash/initiate', {
            order_id: order.value.id,
            amount: order.value.due_amount || order.value.total
        });

        if (response.data.bkashURL) {
            window.location.href = response.data.bkashURL;
        } else {
            toast.error('Failed to get payment URL');
        }
    } catch (err) {
        console.error('Bkash Error:', err);
        toast.error(err.response?.data?.message || 'Payment initiation failed');
    } finally {
        processingBkash.value = false;
    }
};
const formatAddress = (address) => {
    if (!address) return 'N/A';

    // Convert JSON string to object if needed
    if (typeof address === 'string' && address.startsWith('{')) {
        try {
            address = JSON.parse(address);
        } catch (e) {
            return address;
        }
    }

    return [address.street, address.thana, address.division]
        .filter(Boolean)
        .join(', ');
};

const handleBankTransfer = async (data) => {
    // PaymentModal emits 'payment-submitted' with { transactionId, proof: File, ... }
    // But for Order, we usually hit /orders/{id}/upload-payment-slip
    // Let's assume PaymentModal handles the data formatting

    // We need to construct FormData
    const formData = new FormData();
    formData.append('order_id', order.value.id);
    formData.append('transaction_id', data.transactionId);
    if (data.proof) {
        formData.append('payment_slip', data.proof);
    }

    try {
        await axios.post(`/orders/${order.value.id}/upload-payment-slip`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        showPayment.value = false;
        toast.success('Payment slip uploaded successfully! Waiting for admin approval.');
        fetchOrder();
    } catch (err) {
        console.error('Bank Transfer Error:', err);
        toast.error(err.response?.data?.message || 'Failed to upload payment slip');
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPrice = (price) => {
    if (!price) return '৳0.00';
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    return `৳${numPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const getParsedVariantData = (variantData) => {
    if (!variantData) return {};
    let data = variantData;
    if (typeof variantData === 'string') {
        try {
            data = JSON.parse(variantData);
        } catch {
            return { raw: variantData };
        }
    }
    if (typeof data === 'object') {
        return data.attributes || data;
    }
    return {};
};

const formatPaymentMethod = (method) => {
    if (!method) return 'N/A';
    return method.replace(/_/g, ' ');
};



onMounted(() => {
    fetchOrder();
});
</script>
