<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-slate-500">Loading order details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-surface rounded-2xl shadow-lg border border-red-100 p-8 text-center">
            <p class="text-red-600 font-semibold">{{ error }}</p>
            <button @click="$router.push('/dashboard/orders')" class="mt-4 px-4 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary-hover transition-colors">
                Back to Orders
            </button>
        </div>

        <!-- Order Details -->
        <div v-else-if="order" class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Order Details</h2>
                    <p class="text-sm text-slate-500 mt-1">Order #{{ order.order_number || order.id }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="printInvoice" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        📄 Print Invoice
                    </button>
                    <button @click="$router.push('/dashboard/orders')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        ← Back to Orders
                    </button>
                </div>
            </div>

            <!-- Order Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Order Status</p>
                    <span :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }" 
                        class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                        {{ order.status?.label || order.status || 'Pending' }}
                    </span>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Order Date</p>
                    <p class="text-sm font-semibold text-slate-900">{{ formatDate(order.created_at) }}</p>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Payment Status</p>
                    <p class="text-sm font-semibold text-slate-900">{{ order.payment_status || 'Pending' }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-gray-50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-white/10">
                            <tr>
                                <th class="p-3 text-left text-xs font-semibold text-slate-700 uppercase">Product</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-700 uppercase">Quantity</th>
                                <th class="p-3 text-left text-xs font-semibold text-slate-700 uppercase">Price</th>
                                <th class="p-3 text-right text-xs font-semibold text-slate-700 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in order.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <img v-if="item.image" :src="item.image" :alt="item.product_name" class="w-12 h-12 rounded-lg object-cover">
                                        <div>
                                            <p class="text-xs font-semibold text-slate-900">{{ item.product_name }}</p>
                                            <p v-if="item.product_sku" class="text-xs text-slate-500">SKU: {{ item.product_sku }}</p>
                                            <p v-if="item.variant_data" class="text-xs text-slate-500">{{ formatVariantData(item.variant_data) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-xs text-slate-600">{{ item.quantity }}</td>
                                <td class="p-3 text-xs text-slate-600">{{ formatPrice(item.price) }}</td>
                                <td class="p-3 text-right text-xs font-semibold text-slate-900">{{ formatPrice(item.subtotal || (item.price * item.quantity)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shipping Information -->
                <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">Shipping Information</h3>
                    <div class="space-y-2 text-xs">
                        <p><span class="font-semibold text-slate-700">Name:</span> <span class="text-slate-600">{{ order.shipping_name || 'N/A' }}</span></p>
                        <p><span class="font-semibold text-slate-700">Email:</span> <span class="text-slate-600">{{ order.shipping_email || 'N/A' }}</span></p>
                        <p><span class="font-semibold text-slate-700">Phone:</span> <span class="text-slate-600">{{ order.shipping_phone || 'N/A' }}</span></p>
                        <p><span class="font-semibold text-slate-700">Address:</span> <span class="text-slate-600">{{ order.shipping_address || 'N/A' }}</span></p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-4">Order Summary</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal:</span>
                            <span class="font-semibold text-slate-900">{{ formatPrice(order.subtotal || 0) }}</span>
                        </div>
                        <div v-if="order.tax > 0" class="flex justify-between">
                            <span class="text-slate-600">Tax:</span>
                            <span class="font-semibold text-slate-900">{{ formatPrice(order.tax) }}</span>
                        </div>
                        <div v-if="order.shipping > 0" class="flex justify-between">
                            <span class="text-slate-600">Shipping:</span>
                            <span class="font-semibold text-slate-900">{{ formatPrice(order.shipping) }}</span>
                        </div>
                        <div v-if="order.discount > 0" class="flex justify-between">
                            <span class="text-slate-600">Discount:</span>
                            <span class="font-semibold text-green-600">-{{ formatPrice(order.discount) }}</span>
                        </div>
                        <div class="border-t border-white/10 pt-3 flex justify-between">
                            <span class="text-sm font-bold text-slate-900">Total:</span>
                            <span class="text-sm font-bold text-primary">{{ formatPrice(order.total || order.total_amount || 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="order.notes" class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-2">Order Notes</h3>
                <p class="text-xs text-slate-600">{{ order.notes }}</p>
            </div>

            <!-- Status History -->
            <div v-if="order.status_histories && order.status_histories.length > 0" class="bg-surface rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-gray-50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Status History</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="history in order.status_histories" :key="history.id" class="flex items-start gap-4 pb-4 border-b border-gray-100 last:border-0">
                            <div class="flex-shrink-0">
                                <div :style="{ backgroundColor: history.status?.color + '20', color: history.status?.color }" 
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold">
                                    {{ history.status?.label?.charAt(0) || 'P' }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-slate-900">{{ history.status?.label || 'Status Changed' }}</p>
                                <p v-if="history.note" class="text-xs text-slate-500 mt-1">{{ history.note }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ formatDate(history.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice (Hidden, for printing) -->
            <div id="invoice-content" class="hidden">
                <div class="p-8 bg-surface">
                    <div class="mb-8 text-center">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">INVOICE</h1>
                        <p class="text-sm text-slate-600">Order #{{ order.order_number || order.id }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Bill To:</h3>
                            <p class="text-sm text-slate-700">{{ order.shipping_name || 'N/A' }}</p>
                            <p class="text-sm text-slate-700">{{ order.shipping_email || 'N/A' }}</p>
                            <p v-if="order.shipping_address" class="text-sm text-slate-700 mt-2">{{ order.shipping_address }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-600 mb-1"><strong>Date:</strong> {{ formatDate(order.created_at) }}</p>
                            <p class="text-sm text-slate-600 mb-1"><strong>Status:</strong> {{ order.status?.label || order.status }}</p>
                            <p class="text-sm text-slate-600"><strong>Payment:</strong> {{ order.payment_status }}</p>
                        </div>
                    </div>
                    <table class="w-full mb-8 border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2 text-left">Product</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Price</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Qty</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="border border-gray-300 px-4 py-2">{{ item.product_name }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ item.quantity }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatPrice(item.subtotal || (item.price * item.quantity)) }}</td>
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

