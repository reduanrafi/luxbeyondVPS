<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-serif font-bold text-white tracking-wide">Order Details</h2>
                <div v-if="order" class="flex items-center gap-2 mt-2">
                    <p class="text-zinc-400 text-sm">Order <span class="font-mono text-white">#{{ order.order_number ||
                        order.id }}</span></p>
                    <span class="text-zinc-600">•</span>
                    <p class="text-zinc-400 text-sm">{{ formatDate(order.created_at) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="$router.push(`/admin/orders/${orderId}/edit`)"
                    class="flex items-center gap-2 px-4 py-2.5 bg-white/5 text-zinc-300 font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-white/10 hover:text-white transition-colors border border-white/10">
                    <Edit2 class="w-4 h-4" />
                    Edit Order
                </button>
                <button @click="printInvoice"
                    class="flex items-center gap-2 px-4 py-2.5 bg-white/5 text-zinc-300 font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-white/10 hover:text-white transition-colors border border-white/10">
                    <Printer class="w-4 h-4" />
                    Invoice
                </button>
                <button @click="$router.push('/admin/orders')"
                    class="flex items-center gap-2 px-4 py-2.5 bg-primary text-black font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-primary-hover transition-colors">
                    <ArrowLeft class="w-4 h-4" />
                    Back to Orders
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="min-h-[400px] flex flex-col items-center justify-center">
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-white/10 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-primary border-t-transparent rounded-full animate-spin">
                </div>
            </div>
            <p class="text-zinc-500 mt-4 font-medium animate-pulse">Loading order details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-8 text-center">
            <p class="text-red-400 font-semibold">{{ error }}</p>
            <button @click="$router.push('/admin/orders')"
                class="mt-4 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                Back to Orders
            </button>
        </div>

        <template v-else-if="order">
            <!-- Order Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status -->
                <div class="bg-[#111111] p-5 rounded-xl border border-white/5 relative overflow-hidden">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Order Status</p>
                            <div class="mt-2">
                                <span :class="getStatusClass(order.status)"
                                    class="px-3 py-1 rounded text-xs font-bold uppercase tracking-wide border transition-colors inline-block">
                                    {{ order.status?.label || order.status || 'Pending' }}
                                </span>
                            </div>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-zinc-400">
                            <Activity class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Payment Status</p>
                            <p class="text-lg font-bold text-white mt-2">{{ order.payment_status || 'Pending' }}</p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-zinc-400">
                            <CreditCard class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <!-- Event -->
                <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div class="w-full">
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Event</p>
                            <div class="mt-2 flex items-center gap-2">
                                <select v-model="selectedEventId" @change="updateEvent"
                                    class="w-full bg-black/20 border border-white/10 text-white text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-primary/50 transition-colors cursor-pointer">
                                    <option value="">No Event</option>
                                    <option v-for="event in events" :key="event.id" :value="event.id"
                                        class="bg-[#111111]">
                                        {{ event.name }}
                                    </option>
                                </select>
                                <div v-if="updatingEvent"
                                    class="animate-spin w-4 h-4 border-2 border-primary border-t-transparent rounded-full">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Total Amount</p>
                            <p class="text-2xl font-bold text-primary mt-1">৳{{ formatPrice(order.total ||
                                order.total_amount) }}</p>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-primary">
                            <DollarSign class="w-5 h-5" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content (Items & Summary) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Items -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-5 border-b border-white/5">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <Package class="w-5 h-5 text-primary" />
                                Order Items
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead
                                    class="bg-black/20 text-xs font-bold text-zinc-400 uppercase tracking-wider border-b border-white/5">
                                    <tr>
                                        <th class="px-6 py-4 text-left">Product</th>
                                        <th class="px-6 py-4 text-left">Price</th>
                                        <th class="px-6 py-4 text-center">Quantity</th>
                                        <th class="px-6 py-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="item in order.items" :key="item.id"
                                        class="group hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 overflow-hidden flex-shrink-0">
                                                    <img v-if="item.product?.image_url" :src="item.product.image_url"
                                                        :alt="item.product.name" class="w-full h-full object-contain">
                                                    <div v-else
                                                        class="w-full h-full flex items-center justify-center text-zinc-600">
                                                        <ImageIcon class="w-5 h-5" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                        {{ item.product?.name || 'Product' }}</p>
                                                    <p v-if="item.variant_attributes"
                                                        class="text-xs text-zinc-500 mt-0.5">{{
                                                            formatVariantAttributes(item.variant_attributes) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-zinc-300">৳{{ formatPrice(item.price) }}</td>
                                        <td class="px-6 py-4 text-sm text-zinc-300 text-center">{{ item.quantity }}</td>
                                        <td class="px-6 py-4 text-sm font-bold text-white text-right">৳{{
                                            formatPrice(item.price * item.quantity) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-5 border-b border-white/5">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <FileText class="w-5 h-5 text-zinc-500" />
                                Order Summary
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-400">Subtotal</span>
                                <span class="font-medium text-white">৳{{ formatPrice(order.subtotal || (order.total -
                                    (order.tax || 0) - (order.shipping || 0) + (order.discount || 0))) }}</span>
                            </div>
                            <div v-if="order.discount" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Discount</span>
                                <span class="font-medium text-green-400">-৳{{ formatPrice(order.discount) }}</span>
                            </div>
                            <div v-if="order.tax" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Tax</span>
                                <span class="font-medium text-white">৳{{ formatPrice(order.tax) }}</span>
                            </div>
                            <div v-if="order.shipping" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Shipping</span>
                                <span class="font-medium text-white">৳{{ formatPrice(order.shipping) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-4 mt-2 border-t border-white/10">
                                <span class="text-base font-bold text-white">Total</span>
                                <span class="text-2xl font-serif font-bold text-primary">৳{{ formatPrice(order.total ||
                                    order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Customer & History) -->
                <div class="space-y-6">
                    <!-- Customer Information -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-5 border-b border-white/5">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <User class="w-5 h-5 text-zinc-500" />
                                Customer Details
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center text-lg font-bold text-primary border border-white/10">
                                    {{ order.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white">{{ order.user?.name || 'Guest User' }}</p>
                                    <p class="text-xs text-zinc-500">{{ order.user?.email || 'No email' }}</p>
                                </div>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-white/5">
                                <div v-if="order.shipping_name">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mb-1">
                                        Shipping Name</p>
                                    <p class="text-sm text-zinc-300">{{ order.shipping_name }}</p>
                                </div>
                                <div v-if="order.shipping_phone">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mb-1">Phone
                                    </p>
                                    <p class="text-sm text-zinc-300 font-mono">{{ order.shipping_phone }}</p>
                                </div>
                                <div v-if="order.shipping_address">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mb-1">Address
                                    </p>
                                    <p class="text-sm text-zinc-300 leading-relaxed">{{ order.shipping_address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="order.payments">
                        {{ order.payments }}
                    </div>

                    <!-- Status History -->
                    <div v-if="order.status_histories && order.status_histories.length > 0"
                        class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-5 border-b border-white/5">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <Clock class="w-5 h-5 text-zinc-500" />
                                Timeline
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                class="space-y-6 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-white/5">
                                <div v-for="history in order.status_histories" :key="history.id" class="relative pl-8">
                                    <div class="absolute left-0 top-1.5 w-4 h-4 rounded-full border-2 border-[#111111]"
                                        :style="{ backgroundColor: history.status?.color || '#3f3f46' }"></div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-white">{{ history.status?.label || `Status
                                            Changed` }}</span>
                                        <span class="text-xs text-zinc-500 mt-0.5">{{ formatDate(history.created_at)
                                            }}</span>
                                        <p v-if="history.note"
                                            class="text-xs text-zinc-400 mt-2 bg-white/5 p-2 rounded border border-white/5">
                                            {{ history.note }}
                                        </p>
                                        <span v-if="history.changed_by" class="text-[10px] text-zinc-600 mt-1">
                                            by {{ history.changed_by?.name || 'System' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Hidden Invoice for Printing -->
    <div id="invoice-content" class="hidden">
        <!-- Keep existing invoice structure but perhaps simplify styling for print compatibility -->
        <div class="p-8 bg-white text-black">
            <div class="mb-8 text-center border-b pb-4">
                <h1 class="text-3xl font-bold mb-2">INVOICE</h1>
                <p class="text-sm text-gray-600">Order #{{ order?.order_number || order?.id }}</p>
            </div>
            <!-- ... rest of invoice ... -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-bold mb-2">Bill To:</h3>
                    <p class="text-sm">{{ order?.user?.name }}</p>
                    <p class="text-sm">{{ order?.user?.email }}</p>
                    <p v-if="order?.shipping_address" class="text-sm mt-2">{{ order?.shipping_address }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm mb-1"><strong>Date:</strong> {{ formatDate(order?.created_at) }}</p>
                    <p class="text-sm mb-1"><strong>Status:</strong> {{ order?.status?.label || order?.status }}</p>
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
                    <tr v-for="item in order?.items" :key="item.id">
                        <td class="border border-gray-300 px-4 py-2">{{ item.product?.name }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">৳{{ formatPrice(item.price) }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">{{ item.quantity }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">৳{{ formatPrice(item.price *
                            item.quantity) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-right">
                <div class="inline-block text-right space-y-2">
                    <div class="flex justify-between gap-8 text-sm">
                        <span>Subtotal:</span>
                        <span>৳{{ formatPrice(order?.subtotal || (order?.total - (order?.tax || 0) - (order?.shipping ||
                            0) + (order?.discount || 0))) }}</span>
                    </div>
                    <div class="flex justify-between gap-8 text-lg font-bold pt-2 border-t-2">
                        <span>Total:</span>
                        <span>৳{{ formatPrice(order?.total || order?.total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    Printer,
    ArrowLeft,
    User,
    Package,
    Calendar,
    CreditCard,
    Activity,
    DollarSign,
    MapPin,
    Phone,
    Mail,
    Clock,
    FileText,
    Image as ImageIcon,
    Edit2
} from 'lucide-vue-next';
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
        // Maybe add toast notification here
    } catch (err) {
        console.error('Error updating event:', err);
        alert(err.response?.data?.message || 'Failed to update event');
        selectedEventId.value = order.value?.event_id || null;
    } finally {
        updatingEvent.value = false;
    }
};

const getStatusClass = (status) => {
    if (!status) return 'bg-white/5 text-zinc-400 border-white/10';

    // Check if status is object or string
    const label = (typeof status === 'object' ? status.label : status) || '';

    return {
        'Completed': 'bg-green-500/10 text-green-400 border-green-500/20',
        'Processing': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'Shipped': 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'Pending': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        'Cancelled': 'bg-red-500/10 text-red-400 border-red-500/20'
    }[label] || 'bg-white/5 text-zinc-400 border-white/10';
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
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
                    .text-right { text-align: right; }
                    .mb-8 { margin-bottom: 2rem; }
                    .font-bold { font-weight: bold; }
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
