<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-slate-500">Loading order details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-white rounded-2xl shadow-lg border border-red-100 p-8 text-center">
            <p class="text-red-600 font-semibold">{{ error }}</p>
            <button @click="$router.push('/admin/orders')" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors">
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
                    <button @click="$router.push('/admin/orders')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        ← Back to Orders
                    </button>
                </div>
            </div>

            <!-- Order Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
                    <p class="text-xs text-slate-500 mb-1">Order Status</p>
                    <span :style="{ backgroundColor: order.status?.color + '20', color: order.status?.color }" 
                        class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                        {{ order.status?.label || order.status || 'Pending' }}
                    </span>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
                    <p class="text-xs text-slate-500 mb-1">Order Date</p>
                    <p class="text-sm font-semibold text-slate-900">{{ formatDate(order.created_at) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
                    <p class="text-xs text-slate-500 mb-1">Payment Status</p>
                    <p class="text-sm font-semibold text-slate-900">{{ order.payment_status || 'Pending' }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
                    <p class="text-xs text-slate-500 mb-1">Event</p>
                    <div v-if="order.event" class="flex items-center gap-2">
                        <span :style="{ backgroundColor: order.event.bg_color + '20', color: order.event.bg_color }" 
                            class="px-3 py-1 rounded-full text-xs font-semibold">
                            {{ order.event.name }}
                        </span>
                    </div>
                    <p v-else class="text-sm text-slate-400 italic">No event</p>
                </div>
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
                    <p class="text-xs text-slate-500 mb-1">Total Amount</p>
                    <p class="text-sm font-semibold text-slate-900">৳{{ formatPrice(order.total || order.total_amount) }}</p>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Customer Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Name</p>
                        <p class="text-sm font-semibold text-slate-900">{{ order.user?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Email</p>
                        <p class="text-sm font-semibold text-slate-900">{{ order.user?.email || 'N/A' }}</p>
                    </div>
                    <div v-if="order.shipping_name">
                        <p class="text-xs text-slate-500 mb-1">Shipping Name</p>
                        <p class="text-sm font-semibold text-slate-900">{{ order.shipping_name }}</p>
                    </div>
                    <div v-if="order.shipping_phone">
                        <p class="text-xs text-slate-500 mb-1">Shipping Phone</p>
                        <p class="text-sm font-semibold text-slate-900">{{ order.shipping_phone }}</p>
                    </div>
                    <div v-if="order.shipping_address" class="md:col-span-2">
                        <p class="text-xs text-slate-500 mb-1">Shipping Address</p>
                        <p class="text-sm font-semibold text-slate-900">{{ order.shipping_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in order.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product.name"
                                            class="w-12 h-12 object-cover rounded-lg">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ item.product?.name || 'Product' }}</p>
                                            <p v-if="item.variant_attributes" class="text-xs text-slate-500">{{ formatVariantAttributes(item.variant_attributes) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900">৳{{ formatPrice(item.price) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900">{{ item.quantity }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">৳{{ formatPrice(item.price * item.quantity) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Event Assignment -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Event Assignment</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <select v-model="selectedEventId" @change="updateEvent"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">No Event</option>
                            <option v-for="event in events" :key="event.id" :value="event.id">
                                {{ event.name }}
                            </option>
                        </select>
                        <span v-if="updatingEvent" class="text-sm text-slate-500">Saving...</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Assign this order to an event for verification and tracking purposes</p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Order Summary</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Subtotal</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(order.subtotal || (order.total - (order.tax || 0) - (order.shipping || 0) + (order.discount || 0))) }}</span>
                    </div>
                    <div v-if="order.discount" class="flex justify-between text-sm">
                        <span class="text-slate-600">Discount</span>
                        <span class="font-semibold text-green-600">-৳{{ formatPrice(order.discount) }}</span>
                    </div>
                    <div v-if="order.tax" class="flex justify-between text-sm">
                        <span class="text-slate-600">Tax</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(order.tax) }}</span>
                    </div>
                    <div v-if="order.shipping" class="flex justify-between text-sm">
                        <span class="text-slate-600">Shipping</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(order.shipping) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200">
                        <span class="text-slate-900">Total</span>
                        <span class="text-primary">৳{{ formatPrice(order.total || order.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div v-if="order.status_histories && order.status_histories.length > 0" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Status History</h3>
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
                                <p class="text-sm font-semibold text-slate-900">{{ history.status?.label || 'Status Changed' }}</p>
                                <p v-if="history.note" class="text-xs text-slate-500 mt-1">{{ history.note }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ formatDate(history.created_at) }}</p>
                                <p v-if="history.changed_by" class="text-xs text-slate-400">Changed by: {{ history.changed_by?.name || 'System' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice (Hidden, for printing) -->
            <div id="invoice-content" class="hidden">
                <div class="p-8 bg-white">
                    <div class="mb-8 text-center">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">INVOICE</h1>
                        <p class="text-sm text-slate-600">Order #{{ order.order_number || order.id }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Bill To:</h3>
                            <p class="text-sm text-slate-700">{{ order.user?.name }}</p>
                            <p class="text-sm text-slate-700">{{ order.user?.email }}</p>
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
                                <td class="border border-gray-300 px-4 py-2">{{ item.product?.name }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">৳{{ formatPrice(item.price) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ item.quantity }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">৳{{ formatPrice(item.price * item.quantity) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-right">
                        <div class="inline-block text-right space-y-2">
                            <div class="flex justify-between gap-8 text-sm">
                                <span>Subtotal:</span>
                                <span>৳{{ formatPrice(order.subtotal || (order.total - (order.tax || 0) - (order.shipping || 0) + (order.discount || 0))) }}</span>
                            </div>
                            <div v-if="order.discount" class="flex justify-between gap-8 text-sm">
                                <span>Discount:</span>
                                <span>-৳{{ formatPrice(order.discount) }}</span>
                            </div>
                            <div v-if="order.tax" class="flex justify-between gap-8 text-sm">
                                <span>Tax:</span>
                                <span>৳{{ formatPrice(order.tax) }}</span>
                            </div>
                            <div v-if="order.shipping" class="flex justify-between gap-8 text-sm">
                                <span>Shipping:</span>
                                <span>৳{{ formatPrice(order.shipping) }}</span>
                            </div>
                            <div class="flex justify-between gap-8 text-lg font-bold pt-2 border-t-2">
                                <span>Total:</span>
                                <span>৳{{ formatPrice(order.total || order.total_amount) }}</span>
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
const orderId = route.params.id;

const loading = ref(true);
const error = ref(null);
const order = ref(null);
const events = ref([]);
const selectedEventId = ref(null);
const updatingEvent = ref(false);

const fetchOrder = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/admin/orders/${orderId}`);
        order.value = response.data;
        selectedEventId.value = order.value.event_id || null;
    } catch (err) {
        console.error('Error fetching order:', err);
        error.value = err.response?.data?.message || 'Failed to load order details';
    } finally {
        loading.value = false;
    }
};

const fetchEvents = async () => {
    try {
        const response = await axios.get('/admin/events');
        events.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Error fetching events:', err);
    }
};

const updateEvent = async () => {
    updatingEvent.value = true;
    try {
        await axios.put(`/admin/orders/${orderId}`, {
            event_id: selectedEventId.value || null
        });
        await fetchOrder();
        alert('Event updated successfully');
    } catch (err) {
        console.error('Error updating event:', err);
        alert(err.response?.data?.message || 'Failed to update event');
        // Reset to original value
        selectedEventId.value = order.value?.event_id || null;
    } finally {
        updatingEvent.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPrice = (price) => {
    if (!price) return '0.00';
    return parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatVariantAttributes = (attributes) => {
    if (!attributes || typeof attributes !== 'object') return '';
    return Object.entries(attributes).map(([key, value]) => `${key}: ${value}`).join(', ');
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
    fetchEvents();
    fetchOrder();
});
</script>


