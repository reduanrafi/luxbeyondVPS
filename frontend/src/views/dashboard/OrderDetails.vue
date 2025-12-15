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
                    <button @click="printInvoice"
                        class="flex gap-2 px-6 py-2 text-sm font-bold uppercase tracking-widest text-slate-400 hover:text-primary border border-white/10 rounded-none hover:border-primary/30 transition-all">
                        <Printer class="w-5 h-5" />
                        Print Invoice
                    </button>
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
                    <p class="text-sm font-semibold text-white">{{ order.payment_status || 'Pending' }}</p>
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
                                            <img v-if="item.image || (item.product && item.product.image)"
                                                :src="item.image || (item.product ? item.product.image_url : null)"
                                                :alt="item.product_name"
                                                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                                            <div v-else
                                                class="w-full h-full flex items-center justify-center text-slate-500 text-xs">
                                                No Image
                                            </div>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                {{ item.product_name }}</p>
                                            <p v-if="item.product_sku" class="text-xs text-slate-400 mt-1">SKU: {{
                                                item.product_sku }}</p>
                                            <p v-if="item.variant_data" class="text-xs text-slate-500 mt-1">{{
                                                formatVariantData(item.variant_data) }}</p>
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
                            order.shipping_address || 'N/A' }}</span></p>
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

            <!-- Payment History -->
            <div v-if="order.payments && order.payments.length > 0"
                class="bg-surface border border-white/5 overflow-hidden">
                <div class="p-4 border-b border-white/5 bg-background/50">
                    <h3 class="text-sm font-serif text-white uppercase tracking-widest">Payment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-white/5 border-b border-white/5">
                            <tr>
                                <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">Method</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">Amount</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">TrxID/Ref</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-400 uppercase">Date</th>
                                <th class="p-3 text-right text-xs font-semibold text-slate-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="payment in order.payments" :key="payment.id"
                                class="hover:bg-white/5 transition-colors">
                                <td class="p-3 text-xs text-white capitalize">{{
                                    formatPaymentMethod(payment.payment_method) }}</td>
                                <td class="p-3 text-xs font-semibold text-white">{{ formatPrice(payment.amount) }}</td>
                                <td class="p-3 text-xs text-slate-400 font-mono">
                                    {{ payment.payment_reference || '-' }}
                                    <a v-if="payment.payment_slip" :href="payment.payment_slip_url || '#'"
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
                            <div class="absolute -left-[21px] bg-background border-2 border-primary rounded-full w-4 h-4"></div>
                            <div class="mb-1">
                                <span class="font-bold text-white">{{ history.status?.label || 'Status Changed' }}</span>
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

            <!-- Invoice (Hidden, for printing) -->
            <div id="invoice-content" class="hidden">
                <div class="p-8 bg-surface">
                    <div class="mb-8 text-center">
                        <h1 class="text-3xl font-bold text-white mb-2">INVOICE</h1>
                        <p class="text-sm text-slate-400">Order #{{ order.order_number || order.id }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">Bill To:</h3>
                            <p class="text-sm text-slate-300">{{ order.shipping_name || 'N/A' }}</p>
                            <p class="text-sm text-slate-300">{{ order.shipping_email || 'N/A' }}</p>
                            <p v-if="order.shipping_address" class="text-sm text-slate-300 mt-2">{{
                                order.shipping_address }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-400 mb-1"><strong>Date:</strong> {{
                                formatDate(order.created_at) }}</p>
                            <p class="text-sm text-slate-400 mb-1"><strong>Status:</strong> {{ order.status?.label ||
                                order.status }}</p>
                            <p class="text-sm text-slate-400"><strong>Payment:</strong> {{ order.payment_status }}</p>
                        </div>
                    </div>
                    <table class="w-full mb-8 border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-white/10 px-4 py-2 text-left">Product</th>
                                <th class="border border-white/10 px-4 py-2 text-right">Price</th>
                                <th class="border border-white/10 px-4 py-2 text-right">Qty</th>
                                <th class="border border-white/10 px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="border border-white/10 px-4 py-2">{{ item.product_name }}</td>
                                <td class="border border-white/10 px-4 py-2 text-right">{{ formatPrice(item.price) }}
                                </td>
                                <td class="border border-white/10 px-4 py-2 text-right">{{ item.quantity }}</td>
                                <td class="border border-white/10 px-4 py-2 text-right">{{ formatPrice(item.subtotal ||
                                    (item.price * item.quantity)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-right">
                        <div class="inline-block text-right space-y-2">
                            <div class="flex justify-between gap-8 text-sm">
                                <span>Subtotal:</span>
                                <span>{{ formatPrice(order.subtotal || 0) }}</span>
                            </div>
                            <div v-if="order.discount > 0" class="flex justify-between gap-8 text-sm">
                                <span>Discount:</span>
                                <span>-{{ formatPrice(order.discount) }}</span>
                            </div>
                            <div v-if="order.tax > 0" class="flex justify-between gap-8 text-sm">
                                <span>Tax:</span>
                                <span>{{ formatPrice(order.tax) }}</span>
                            </div>
                            <div v-if="order.shipping > 0" class="flex justify-between gap-8 text-sm">
                                <span>Shipping:</span>
                                <span>{{ formatPrice(order.shipping) }}</span>
                            </div>
                            <div class="flex justify-between gap-8 text-lg font-bold pt-2 border-t-2">
                                <span>Total:</span>
                                <span>{{ formatPrice(order.total || order.total_amount || 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../../axios';
import { Printer } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const order = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchOrder = async () => {
    loading.value = true;
    error.value = null;
    try {
        // Use order_number from route params
        const orderNumber = route.params.orderNumber;
        const response = await axios.get(`/orders/${orderNumber}`);
        order.value = response.data;
    } catch (err) {
        console.error('Error fetching order:', err);
        error.value = err.response?.data?.message || 'Order not found';
    } finally {
        loading.value = false;
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

const formatVariantData = (variantData) => {
    if (!variantData) return '';
    if (typeof variantData === 'string') {
        try {
            variantData = JSON.parse(variantData);
        } catch {
            return variantData;
        }
    }
    if (typeof variantData === 'object') {
        return Object.entries(variantData).map(([key, value]) => `${key}: ${value}`).join(', ');
    }
    return '';
};

const formatPaymentMethod = (method) => {
    if (!method) return 'N/A';
    return method.replace(/_/g, ' ');
};

const printInvoice = () => {
    const invoiceContent = document.getElementById('invoice-content');
    if (!invoiceContent) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Invoice - Order #${order.value.order_number || order.value.id}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                ${invoiceContent.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

onMounted(() => {
    fetchOrder();
});
</script>

