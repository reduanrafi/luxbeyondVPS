<template>
    <div class="space-y-8">
        <!-- Loading State -->
        <div v-if="loading" class="bg-[#111111] rounded-2xl border border-white/5 p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-4 text-sm text-zinc-400">Loading request details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-[#111111] rounded-2xl border border-red-500/20 p-12 text-center">
            <p class="text-red-400 font-semibold mb-4">{{ error }}</p>
            <button @click="$router.push('/admin/request-dashboard')"
                class="px-6 py-2 bg-primary text-black font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-primary-hover transition-colors">
                Back to Requests
            </button>
        </div>

        <!-- Request Details -->
        <div v-else-if="request" class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-serif font-bold text-white tracking-wide">Request Details</h2>
                    <p class="text-sm text-zinc-400 mt-1 font-mono">{{ request.request_number || 'ID: #' + request.id }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="printInvoice"
                        class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-zinc-400 hover:text-white border border-white/10 rounded-lg hover:bg-white/5 transition-colors">
                        <Printer class="w-4 h-4" />
                        Invoice
                    </button>
                    <router-link :to="`/admin/requests/${request.id}/edit`" 
                        class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider text-black bg-primary rounded-lg hover:bg-primary-hover transition-colors">
                        <Edit class="w-4 h-4" />
                        Edit
                    </router-link>
                    <button @click="$router.push('/admin/request-dashboard')"
                        class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-zinc-400 hover:text-white border border-white/10 rounded-lg hover:bg-white/5 transition-colors">
                        Back
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-[#111111] rounded-xl border border-white/5 p-5">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Status</p>
                    <span :style="getStatusStyle(request.order_status)"
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide inline-block">
                        {{ request.order_status?.label || request.status || 'Pending' }}
                    </span>
                </div>
                <div class="bg-[#111111] rounded-xl border border-white/5 p-5">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Date</p>
                    <p class="text-sm font-medium text-white">{{ formatDate(request.created_at) }}</p>
                </div>
                <div class="bg-[#111111] rounded-xl border border-white/5 p-5">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Currency</p>
                    <p class="text-sm font-medium text-white">{{ request.currency || 'N/A' }}</p>
                </div>
                <div class="bg-[#111111] rounded-xl border border-white/5 p-5">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Total Amount</p>
                    <p class="text-lg font-serif font-bold text-primary">৳{{ formatPrice(request.total_amount_bdt) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info Column -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Product Information -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-4 border-b border-white/5 bg-white/[0.02] flex items-center gap-2">
                            <Package class="w-4 h-4 text-primary" />
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Product Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="mb-6">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Product URL</p>
                                <a :href="request.url" target="_blank"
                                    class="text-sm text-blue-400 hover:text-blue-300 hover:underline break-all flex items-center gap-2">
                                    <Link2 class="w-3 h-3 flex-shrink-0" />
                                    {{ request.url }}
                                </a>
                            </div>

                            <div v-if="request.admin_image_url" class="mb-6">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-3">Product Image
                                </p>
                                <img :src="request.admin_image_url" 
                                     alt="Product Image"
                                     referrerpolicy="no-referrer"
                                     class="w-full max-w-sm rounded-lg border border-white/10 shadow-lg"
                                     @error="(e) => e.target.src = 'https://placehold.co/600x400/1e1e1e/FFF?text=Image+Blocked+by+Source'">
                                <p v-if="request.admin_image_url" class="text-xs text-zinc-500 mt-2">
                                    Note: If image says "Blocked", the source website prevents external linking. Please re-host the image.
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-4 bg-white/[0.02] rounded-lg p-4 border border-white/5">
                                <div>
                                    <p class="text-xs text-zinc-500 mb-1">Unit Price</p>
                                    <p class="text-sm font-medium text-white">{{ request.currency }} {{
                                        formatPrice(request.price) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500 mb-1">Quantity</p>
                                    <p class="text-sm font-medium text-white">x {{ request.quantity }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500 mb-1">Subtotal</p>
                                    <p class="text-sm font-bold text-white">{{ request.currency }} {{
                                        formatPrice(request.price * request.quantity) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-4 border-b border-white/5 bg-white/[0.02] flex items-center gap-2">
                            <User class="w-4 h-4 text-primary" />
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Customer Details</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Name</p>
                                <p class="text-sm font-medium text-white">{{ request.user?.name || 'Guest' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Email</p>
                                <div class="flex items-center gap-2">
                                    <Mail class="w-3 h-3 text-zinc-500" />
                                    <p class="text-sm font-medium text-white">{{ request.user?.email || 'N/A' }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Location</p>
                                <div class="flex items-center gap-2">
                                    <MapPin class="w-3 h-3 text-zinc-500" />
                                    <p class="text-sm font-medium text-white">
                                        {{ request.user?.city || 'N/A' }} 
                                        <span v-if="request.user?.country" class="text-zinc-500">({{ request.user.country }})</span>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Shipping Address -->
                            <div class="md:col-span-2 pt-4 border-t border-white/5" v-if="request.shipping_address">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Shipping Address</p>
                                <div class="bg-white/[0.02] rounded-lg p-3 border border-white/5">
                                    <p class="text-sm text-white font-medium">{{ request.shipping_address.street }}</p>
                                    <p class="text-sm text-zinc-400">
                                        {{ request.shipping_address.city }}
                                        <span v-if="request.shipping_address.postal_code">- {{ request.shipping_address.postal_code }}</span>
                                    </p>
                                    <p class="text-sm text-zinc-400">{{ request.shipping_address.state }}</p>
                                    <p class="text-sm text-zinc-400 mt-2 flex items-center gap-2">
                                        <span class="text-zinc-500 text-xs uppercase">Phone:</span>
                                        {{ request.shipping_address.phone }}
                                    </p>
                                </div>
                            </div>
                            <!-- Fallback if no specific shipping address but user has one -->
                             <div class="md:col-span-2 pt-4 border-t border-white/5" v-else-if="!request.shipping_address && request.user?.shipping_address">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">User Default Address</p>
                                <div class="bg-white/[0.02] rounded-lg p-3 border border-white/5 opacity-75">
                                    <p class="text-sm text-white font-medium">{{ request.user.shipping_address.street }}</p>
                                    <p class="text-sm text-zinc-400">
                                        {{ request.user.shipping_address.city }}
                                        <span v-if="request.user.shipping_address.postal_code">- {{ request.user.shipping_address.postal_code }}</span>
                                    </p>
                                    <p class="text-sm text-zinc-400">{{ request.user.shipping_address.state }}</p>
                                     <p class="text-sm text-zinc-400 mt-2 flex items-center gap-2">
                                        <span class="text-zinc-500 text-xs uppercase">Phone:</span>
                                        {{ request.user.shipping_address.phone }}
                                    </p>
                                    <p class="text-xs text-amber-500 mt-2">* Not confirmed for this specific request yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div v-if="request.admin_note"
                        class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-4 border-b border-white/5 bg-white/[0.02] flex items-center gap-2">
                            <FileText class="w-4 h-4 text-primary" />
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Internal Notes</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-zinc-300 leading-relaxed whitespace-pre-wrap">{{ request.admin_note
                                }}</p>
                        </div>
                    </div>
                </div>

                <!-- Side Column -->
                <div class="space-y-6">
                    <!-- Cost Breakdown -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-4 border-b border-white/5 bg-white/[0.02]">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Cost Breakdown</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-400">Product Subtotal</span>
                                <span class="text-white font-medium">৳{{ formatPrice(calculateProductTotal()) }}</span>
                            </div>
                            <div v-if="request.declared_shipping_cost > 0" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Declared Shipping</span>
                                <span class="text-white font-medium">৳{{ formatPrice(request.declared_shipping_cost)
                                    }}</span>
                            </div>

                            <!-- Detailed Breakdown -->
                            <div v-if="request.charges_breakdown && request.charges_breakdown.length > 0" class="border-t border-white/10 pt-3 mt-3">
                                 <p class="text-xs font-bold text-zinc-500 uppercase mb-2">Detailed Charges</p>
                                 <div class="space-y-2">
                                    <div v-for="(charge, index) in request.charges_breakdown" :key="index" class="flex justify-between text-xs">
                                        <span class="text-zinc-400">
                                            {{ charge.charge }} 
                                            <span v-if="charge.currency !== 'BDT'" class="text-[10px] text-zinc-600">
                                                ({{ charge.currency }} {{ formatPrice(charge.amount_in_currency) }})
                                            </span>
                                        </span>
                                        <span class="text-zinc-300">৳{{ formatPrice(charge.amount_in_bdt) }}</span>
                                    </div>
                                 </div>
                            </div>

                            <div v-if="request.tax > 0" class="flex justify-between text-sm pt-2 border-t border-white/10">
                                <span class="text-zinc-400">Tax</span>
                                <span class="text-white font-medium">৳{{ formatPrice(request.tax) }}</span>
                            </div>
                            <div v-if="request.additional_charges > 0" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Additional Charges</span>
                                <span class="text-white font-medium">৳{{ formatPrice(request.additional_charges)
                                    }}</span>
                            </div>
                            <div v-if="request.delivery_charge > 0" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Delivery Charge</span>
                                <span class="text-white font-medium">৳{{ formatPrice(request.delivery_charge) }}</span>
                            </div>
                            <div v-if="request.payment_processing_fee > 0" class="flex justify-between text-sm">
                                <span class="text-zinc-400">Processing Fee</span>
                                <span class="text-white font-medium">৳{{ formatPrice(request.payment_processing_fee)
                                    }}</span>
                            </div>

                            <div class="pt-4 mt-2 border-t border-white/10 flex justify-between items-center">
                                <span class="text-sm font-bold text-white uppercase">Total</span>
                                <span class="text-xl font-serif font-bold text-primary">৳{{
                                    formatPrice(request.total_amount_bdt) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
                        <div class="p-4 border-b border-white/5 bg-white/[0.02]">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Payment Details</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <p class="text-xs text-zinc-500 mb-1">Payment Status</p>
                                <span :class="{
                                    'text-green-400 bg-green-400/10 border-green-400/20': request.payment_status === 'paid',
                                    'text-yellow-400 bg-yellow-400/10 border-yellow-400/20': request.payment_status === 'processing',
                                    'text-red-400 bg-red-400/10 border-red-400/20': request.payment_status === 'failed' || request.payment_status === 'unpaid',
                                    'text-blue-400 bg-blue-400/10 border-blue-400/20': request.payment_status === 'partial'
                                }" class="px-2 py-1 rounded text-xs font-bold uppercase border">
                                    {{ request.payment_status || 'Unpaid' }}
                                </span>
                            </div>
                            <div v-if="request.payment_method">
                                <p class="text-xs text-zinc-500 mb-1">Method</p>
                                <p class="text-sm text-white">{{ request.payment_method }}</p>
                            </div>
                            <div v-if="request.min_payment_amount > 0">
                                <p class="text-xs text-zinc-500 mb-1">Min. Required</p>
                                <p class="text-sm text-white">৳{{ formatPrice(request.min_payment_amount) }}</p>
                            </div>
                            <div v-if="request.payment_reference">
                                <p class="text-xs text-zinc-500 mb-1">Reference</p>
                                <p class="text-sm font-mono text-zinc-300">{{ request.payment_reference }}</p>
                            </div>

                            <div v-if="request.payment_slip_url" class="pt-4 border-t border-white/10">
                                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Payment Slip
                                </p>
                                <a :href="request.payment_slip_url" target="_blank"
                                    class="block group relative overflow-hidden rounded-lg border border-white/10">
                                    <img :src="request.payment_slip_url" alt="Payment Slip"
                                        class="w-full h-auto transition-transform group-hover:scale-105">
                                    <div
                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <ExternalLink class="w-5 h-5 text-white" />
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice (Hidden, for printing) -->
            <div id="invoice-content" class="hidden">
                <div class="invoice-container bg-white text-slate-800 p-10 max-w-[210mm] mx-auto">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-12 border-b pb-8">
                        <div>
                            <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-1">INVOICE</h1>
                            <p class="text-sm text-slate-500 font-medium">#{{ request.request_number || request.id }}</p>
                        </div>
                        <div class="text-right">
                            <img src="/logo.png" alt="LuxBeyond" class="h-12 ml-auto mb-2 object-contain" />
                            <p class="text-sm text-slate-600">Dhaka, Bangladesh</p>
                            <p class="text-sm text-slate-600">support@luxbeyond.com</p>
                            <p class="text-sm text-slate-600">+880 1234 567890</p>
                        </div>
                    </div>

                    <!-- Bill To & Info -->
                    <div class="flex justify-between mb-12">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Bill To</p>
                            <h3 class="font-bold text-slate-900">{{ request.user?.name || 'Guest Customer' }}</h3>
                            <p class="text-sm text-slate-600 mb-1">{{ request.user?.email }}</p>
                            <p v-if="request.shipping_address" class="text-sm text-slate-600">
                                {{ request.shipping_address.street || '' }} {{ request.shipping_address.city ? ', ' + request.shipping_address.city : '' }}<br>
                                {{ request.shipping_address.state || '' }} {{ request.shipping_address.postal_code ? '- ' + request.shipping_address.postal_code : '' }}
                            </p>
                            <p v-else-if="request.user && (request.user.city || request.user.country)" class="text-sm text-slate-600">
                                {{ request.user.city }}<span v-if="request.user.state">, {{ request.user.state }}</span><br>
                                {{ request.user.country }}
                            </p>
                        </div>
                        <div class="text-right space-y-2">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Invoice Date</p>
                                <p class="text-sm font-semibold text-slate-900">{{ formatDate(request.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status</p>
                                <p class="text-sm font-semibold text-slate-900 capitalize">{{ request.order_status?.label || request.status }}</p>
                            </div>
                            <div v-if="request.payment_status">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payment Status</p>
                                <p class="text-sm font-semibold capitalize" :class="{
                                    'text-green-600': request.payment_status === 'paid',
                                    'text-yellow-600': request.payment_status === 'processing',
                                    'text-red-600': request.payment_status === 'unpaid'
                                }">{{ request.payment_status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <table class="w-full mb-10">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="text-left py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b">Item Details</th>
                                <th class="text-right py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b">Price</th>
                                <th class="text-center py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b">Qty</th>
                                <th class="text-right py-3 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-4 px-4 border-b border-slate-100">
                                    <p class="font-semibold text-slate-800 text-sm">Product Request</p>
                                    <p class="text-xs text-slate-500 mt-1 break-all">{{ request.url }}</p>
                                </td>
                                <td class="py-4 px-4 text-right border-b border-slate-100 text-sm text-slate-600">
                                    {{ request.currency }} {{ formatPrice(request.price) }}
                                </td>
                                <td class="py-4 px-4 text-center border-b border-slate-100 text-sm text-slate-600">
                                    {{ request.quantity }}
                                </td>
                                <td class="py-4 px-4 text-right border-b border-slate-100 text-sm font-semibold text-slate-800">
                                    {{ request.currency }} {{ formatPrice(request.price * request.quantity) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="flex flex-col items-end mb-12">
                        <div class="w-full max-w-xs space-y-3">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Subtotal (Approx. BDT)</span>
                                <span class="font-medium">৳{{ formatPrice(calculateProductTotal()) }}</span>
                            </div>
                            <div v-if="request.charges_breakdown && request.charges_breakdown.length > 0" class="border-t border-slate-100 pt-2 space-y-2">
                                <div v-for="(charge, index) in request.charges_breakdown" :key="index" class="flex justify-between text-xs text-slate-500">
                                    <span>{{ charge.charge }}</span>
                                    <span>৳{{ formatPrice(charge.amount_in_bdt) }}</span>
                                </div>
                            </div>
                            <div v-if="request.tax > 0" class="flex justify-between text-sm text-slate-600">
                                <span>Tax</span>
                                <span class="font-medium">৳{{ formatPrice(request.tax) }}</span>
                            </div>
                            <div v-if="request.delivery_charge > 0" class="flex justify-between text-sm text-slate-600">
                                <span>Delivery</span>
                                <span class="font-medium">৳{{ formatPrice(request.delivery_charge) }}</span>
                            </div>
                             <div v-if="request.additional_charges > 0" class="flex justify-between text-sm text-slate-600">
                                <span>Other Charges</span>
                                <span class="font-medium">৳{{ formatPrice(request.additional_charges) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-bold text-slate-900 pt-3 border-t-2 border-slate-200">
                                <span>Total (BDT)</span>
                                <span>৳{{ formatPrice(request.total_amount_bdt) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-slate-100 pt-8 text-center text-xs text-slate-500">
                        <p class="mb-1">Thank you for choosing LuxBeyond.</p>
                        <p>For questions concerning this invoice, please contact support@luxbeyond.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Printer, Edit, Package, User, Mail, Link2, ExternalLink, FileText, MapPin } from 'lucide-vue-next';
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
        const response = await axios.get(`/admin/requests/${requestId}`);
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

const getStatusStyle = (status) => {
    if (!status) return {};
    return {
        backgroundColor: `${status.color}20`,
        color: status.color,
        border: `1px solid ${status.color}40`,
        paddingLeft: '0.75rem',
        paddingRight: '0.75rem',
        paddingTop: '0.25rem',
        paddingBottom: '0.25rem'
    };
};

const printInvoice = () => {
    const invoiceContent = document.getElementById('invoice-content');
    if (!invoiceContent) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Invoice - #${request.value.request_number || request.value.id}</title>
                <base href="${window.location.origin}/" />
                <script src="https://cdn.tailwindcss.com"><\/script>
                <style>
                    body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; background: white; }
                    /* Restore standard fonts for printing */
                    body, h1, h2, h3, p, span { font-family: Inter, ui-sans-serif, system-ui, -apple-system, sans-serif !important; }
                </style>
            </head>
            <body class="bg-white">
                <div class="p-8">
                    ${invoiceContent.innerHTML}
                </div>
                <script>
                    window.onload = function() { window.print(); window.close(); }
                <\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
    // No explicit print() needed as it's stuck in onload to ensure Tailwind loads
};

onMounted(() => {
    fetchCurrencies();
    fetchRequest();
});
</script>
