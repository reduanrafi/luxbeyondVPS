<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-slate-500">Loading request details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-surface rounded-2xl shadow-lg border border-red-100 p-8 text-center">
            <p class="text-red-600 font-semibold">{{ error }}</p>
            <button @click="$router.push('/dashboard/requests')" class="mt-4 px-4 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary-hover transition-colors">
                Back to Requests
            </button>
        </div>

        <!-- Request Details -->
        <div v-else-if="request" class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Product Request Details</h2>
                    <p class="text-sm text-slate-500 mt-1">Request #{{ request.id }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="printInvoice" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        📄 Print Invoice
                    </button>
                    <button @click="$router.push('/dashboard/requests')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        ← Back to Requests
                    </button>
                </div>
            </div>

            <!-- Request Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Request Status</p>
                    <span :style="{ backgroundColor: request.order_status?.color + '20', color: request.order_status?.color }" 
                        class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                        {{ request.order_status?.label || request.status || 'Pending' }}
                    </span>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Request Date</p>
                    <p class="text-sm font-semibold text-slate-900">{{ formatDate(request.created_at) }}</p>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Currency</p>
                    <p class="text-sm font-semibold text-slate-900">{{ request.currency || 'N/A' }}</p>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-500 mb-1">Total Amount (BDT)</p>
                    <p class="text-sm font-semibold text-slate-900">৳{{ formatPrice(request.total_amount_bdt) }}</p>
                </div>
            </div>

            <!-- Product Information -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Product Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Product URL</p>
                        <a :href="request.url" target="_blank" class="text-sm font-semibold text-primary hover:underline">
                            {{ request.url }}
                        </a>
                    </div>
                    <div v-if="request.admin_image_url" class="mt-4">
                        <p class="text-xs text-slate-500 mb-2">Product Image</p>
                        <img :src="request.admin_image_url" alt="Product Image" class="w-32 h-32 object-cover rounded-lg border border-white/10">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Price</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.currency }} {{ formatPrice(request.price) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Quantity</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.quantity }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Subtotal</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.currency }} {{ formatPrice(request.price * request.quantity) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Charges -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Shipping & Charges</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Location</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.is_inside_city ? 'Inside City' : 'Outside City' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Weight (kg)</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.weight || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Payment Method</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.payment_method || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Declared Shipping Cost</p>
                            <p class="text-sm font-semibold text-slate-900">{{ request.currency }} {{ formatPrice(request.declared_shipping_cost) }}</p>
                        </div>
                    </div>
                    <div class="border-t border-white/10 pt-4 space-y-2">
                        <div v-if="request.tax" class="flex justify-between text-sm">
                            <span class="text-slate-600">Tax:</span>
                            <span class="font-semibold text-slate-900">৳{{ formatPrice(request.tax) }}</span>
                        </div>
                        <div v-if="request.additional_charges" class="flex justify-between text-sm">
                            <span class="text-slate-600">Additional Charges:</span>
                            <span class="font-semibold text-slate-900">৳{{ formatPrice(request.additional_charges) }}</span>
                        </div>
                        <div v-if="request.delivery_charge" class="flex justify-between text-sm">
                            <span class="text-slate-600">Delivery Charge:</span>
                            <span class="font-semibold text-slate-900">৳{{ formatPrice(request.delivery_charge) }}</span>
                        </div>
                        <div v-if="request.payment_processing_fee" class="flex justify-between text-sm">
                            <span class="text-slate-600">Payment Processing Fee:</span>
                            <span class="font-semibold text-slate-900">৳{{ formatPrice(request.payment_processing_fee) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Summary -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Request Summary</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Product Subtotal</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(calculateProductTotal()) }}</span>
                    </div>
                    <div v-if="request.declared_shipping_cost" class="flex justify-between text-sm">
                        <span class="text-slate-600">Declared Shipping</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(request.declared_shipping_cost) }}</span>
                    </div>
                    <div v-if="request.tax" class="flex justify-between text-sm">
                        <span class="text-slate-600">Tax</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(request.tax) }}</span>
                    </div>
                    <div v-if="request.additional_charges" class="flex justify-between text-sm">
                        <span class="text-slate-600">Additional Charges</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(request.additional_charges) }}</span>
                    </div>
                    <div v-if="request.delivery_charge" class="flex justify-between text-sm">
                        <span class="text-slate-600">Delivery Charge</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(request.delivery_charge) }}</span>
                    </div>
                    <div v-if="request.payment_processing_fee" class="flex justify-between text-sm">
                        <span class="text-slate-600">Payment Processing Fee</span>
                        <span class="font-semibold text-slate-900">৳{{ formatPrice(request.payment_processing_fee) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-white/10">
                        <span class="text-slate-900">Total Amount (BDT)</span>
                        <span class="text-primary">৳{{ formatPrice(request.total_amount_bdt) }}</span>
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            <div v-if="request.admin_note" class="bg-surface rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-gray-50">
                    <h3 class="text-lg font-bold text-slate-900">Admin Notes</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ request.admin_note }}</p>
                </div>
            </div>

            <!-- Invoice (Hidden, for printing) -->
            <div id="invoice-content" class="hidden">
                <div class="p-8 bg-surface">
                    <div class="mb-8 text-center">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">PRODUCT REQUEST INVOICE</h1>
                        <p class="text-sm text-slate-600">Request #{{ request.id }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">Customer:</h3>
                            <p class="text-sm text-slate-700">{{ request.user?.name }}</p>
                            <p class="text-sm text-slate-700">{{ request.user?.email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-600 mb-1"><strong>Date:</strong> {{ formatDate(request.created_at) }}</p>
                            <p class="text-sm text-slate-600 mb-1"><strong>Status:</strong> {{ request.order_status?.label || request.status }}</p>
                        </div>
                    </div>
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Product Details</h3>
                        <p class="text-sm text-slate-700 mb-2"><strong>URL:</strong> {{ request.url }}</p>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-slate-500">Price</p>
                                <p class="text-sm font-semibold">{{ request.currency }} {{ formatPrice(request.price) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Quantity</p>
                                <p class="text-sm font-semibold">{{ request.quantity }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Subtotal</p>
                                <p class="text-sm font-semibold">{{ request.currency }} {{ formatPrice(request.price * request.quantity) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="inline-block text-right space-y-2">
                            <div class="flex justify-between gap-8 text-sm">
                                <span>Product Subtotal:</span>
                                <span>৳{{ formatPrice(calculateProductTotal()) }}</span>
                            </div>
                            <div v-if="request.declared_shipping_cost" class="flex justify-between gap-8 text-sm">
                                <span>Declared Shipping:</span>
                                <span>৳{{ formatPrice(request.declared_shipping_cost) }}</span>
                            </div>
                            <div v-if="request.tax" class="flex justify-between gap-8 text-sm">
                                <span>Tax:</span>
                                <span>৳{{ formatPrice(request.tax) }}</span>
                            </div>
                            <div v-if="request.additional_charges" class="flex justify-between gap-8 text-sm">
                                <span>Additional Charges:</span>
                                <span>৳{{ formatPrice(request.additional_charges) }}</span>
                            </div>
                            <div v-if="request.delivery_charge" class="flex justify-between gap-8 text-sm">
                                <span>Delivery Charge:</span>
                                <span>৳{{ formatPrice(request.delivery_charge) }}</span>
                            </div>
                            <div v-if="request.payment_processing_fee" class="flex justify-between gap-8 text-sm">
                                <span>Payment Processing Fee:</span>
                                <span>৳{{ formatPrice(request.payment_processing_fee) }}</span>
                            </div>
                            <div class="flex justify-between gap-8 text-lg font-bold pt-2 border-t-2">
                                <span>Total Amount (BDT):</span>
                                <span>৳{{ formatPrice(request.total_amount_bdt) }}</span>
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
const requestId = route.params.id;

const loading = ref(true);
const error = ref(null);
const request = ref(null);
const currencies = ref([]);

const fetchRequest = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/requests/${requestId}`);
        request.value = response.data;
    } catch (err) {
        console.error('Error fetching request:', err);
        error.value = err.response?.data?.message || 'Failed to load request details';
    } finally {
        loading.value = false;
    }
};

const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/currencies');
        currencies.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Error fetching currencies:', err);
    }
};

const calculateProductTotal = () => {
    if (!request.value || !currencies.value.length) return 0;
    const currency = currencies.value.find(c => c.code === request.value.currency);
    let productTotal = request.value.price * request.value.quantity;
    if (currency && !currency.is_base) {
        productTotal = productTotal * currency.rate_to_base;
    }
    return productTotal;
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

const printInvoice = () => {
    const invoiceContent = document.getElementById('invoice-content');
    if (!invoiceContent) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Invoice - Request #${request.value.id}</title>
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
    fetchCurrencies();
    fetchRequest();
});
</script>


