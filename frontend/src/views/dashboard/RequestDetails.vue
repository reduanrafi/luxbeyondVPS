<template>
    <div class="min-h-screen bg-black py-8 px-4">
        <div class=" mx-auto space-y-6">
            <!-- Loading State -->
            <div v-if="loading"
                class="bg-slate-900 bg-surface rounded-xl shadow-lg p-8 text-center border border-white/5 hover:border-primary/30">
                <div
                    class="inline-block animate-spin bg-surface rounded-full h-8 w-8 border-4 border-primary border-t-transparent">
                </div>
                <p class="mt-4 text-sm text-slate-400">Loading request details...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error"
                class="bg-slate-900 bg-surface rounded-xl shadow-lg p-8 text-center border border-red-900">
                <p class="text-red-400 font-semibold mb-4">{{ error }}</p>
                <button @click="$router.push('/dashboard/requests')"
                    class="px-6 py-2 bg-primary text-white bg-surface rounded-lg hover:bg-primary/90 transition-colors">
                    ← Back to Requests
                </button>
            </div>

            <!-- Request Details -->
            <div v-else-if="request" class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Confirm Your Request</h1>
                        <p class="text-sm text-slate-600 mt-1">Request #{{ request.request_number || request.id }}</p>
                    </div>
                    <button @click="$router.push('/dashboard/requests')"
                        class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-900 bg-surface rounded-lg hover:bg-slate-700 transition-colors border border-white/5 hover:border-primary/30">
                        ← Back
                    </button>
                </div>

                <!-- Status Badge -->
                <div v-if="request.status"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-surface rounded-lg text-sm font-semibold" :class="{
                        'bg-yellow-100 text-yellow-800': request.status === 'pending',
                        'bg-green-100 text-green-800': request.status === 'request_accepted' || request.status === 'accepted',
                        'bg-blue-100 text-blue-800': request.status === 'completed',
                        'bg-gray-100 text-gray-800': !['pending', 'request_accepted', 'accepted', 'completed'].includes(request.status)
                    }">
                    {{ request.status.replace('_', ' ').toUpperCase() }}
                </div>

                <!-- Product Summary Card -->
                <div
                    class="bg-surface rounded-xl shadow-lg overflow-hidden border border-white/5 hover:border-primary/30">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Product Image -->
                            <div v-if="request.admin_image_url" class="flex-shrink-0">
                                <img :src="request.admin_image_url" alt="Product" referrerpolicy="no-referrer"
                                    class="w-32 h-32 object-contain bg-surface rounded-lg border border-white/5 hover:border-primary/30 p-2"
                                    @error="handleImageError">
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 space-y-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Product Name</p>
                                        <p class="text-sm font-semibold text-white">{{ request.product_name ?? 'N/A'
                                        }}</p>
                                    </div>
                                    <div v-if="request.status === 'request_accepted' || request.status === 'accepted'"
                                        class="flex gap-2">
                                        <button @click="openAddressModal"
                                            class="px-6 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/70 transition-all shadow-[0_0_20px_rgba(255,215,0,0.2)] uppercase tracking-widest">
                                            Confirm Request
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Product URL</p>
                                    <a :href="request.url" target="_blank"
                                        class="text-sm text-blue-600 hover:text-blue-700 hover:underline break-all">
                                        {{ request.url.substring(0, 100) + '...' }}
                                    </a>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div
                                        class="bg-surface rounded-lg p-3 border border-white/5 hover:border-primary/30">
                                        <p class="text-xs text-slate-500 mb-1">Price</p>
                                        <p class="text-sm font-semibold text-white">{{ request.currency }} {{
                                            formatPrice(request.price) }}</p>
                                        <p v-if="convertedUnitPrice !== null" class="text-xs text-slate-400 mt-1">≈ ৳{{
                                            formatPrice(convertedUnitPrice) }}</p>
                                    </div>
                                    <div
                                        class="bg-surface rounded-lg p-3 border border-white/5 hover:border-primary/30">
                                        <p class="text-xs text-slate-500 mb-2">Quantity</p>
                                        <div class="flex items-center gap-2"
                                            v-if="request.status === 'request_accepted' || request.status === 'accepted'">
                                            <button @click="decreaseQuantity"
                                                :disabled="updatingQuantity || editableQuantity <= 1"
                                                class="w-7 h-7 flex items-center justify-center bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed bg-surface rounded text-white font-bold transition-colors">
                                                −
                                            </button>
                                            <input v-model.number="editableQuantity" @blur="updateQuantityIfChanged"
                                                @keyup.enter="updateQuantityIfChanged" type="number" min="1"
                                                :disabled="updatingQuantity"
                                                class="w-16 px-2 py-1 bg-slate-700 border border-slate-600 bg-surface rounded text-center text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50" />
                                            <button @click="increaseQuantity" :disabled="updatingQuantity"
                                                class="w-7 h-7 flex items-center justify-center bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed bg-surface rounded text-white font-bold transition-colors">
                                                +
                                            </button>
                                        </div>
                                        <div v-else>
                                            <p class="text-xs text-primary mt-1">{{ editableQuantity }}</p>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-surface rounded-lg p-3 border border-white/5 hover:border-primary/30">
                                        <p class="text-xs text-slate-500 mb-1">Subtotal</p>
                                        <p class="text-sm font-semibold text-white">{{ request.currency }} {{
                                            formatPrice(request.price * editableQuantity) }}</p>
                                        <p v-if="convertedUnitPrice !== null" class="text-xs text-slate-400 mt-1">≈ ৳{{
                                            formatPrice(convertedUnitPrice * editableQuantity) }}</p>
                                    </div>
                                </div>

                                <!-- Charges Breakdown -->
                                <div v-if="request.charges_breakdown && request.charges_breakdown.length > 0"
                                    class="mt-4 bg-surface rounded-lg border border-white/5 hover:border-primary/30 overflow-hidden">
                                    <div class="px-4 py-2 border-b border-white/5 hover:border-primary/30">
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Charges
                                            Breakdown</p>
                                    </div>
                                    <div class="divide-y divide-slate-700">
                                        <div v-for="(charge, index) in request.charges_breakdown" :key="index"
                                            class="flex justify-between items-center px-4 py-2 border border-white/5 hover:border-primary/30">
                                            <span class="text-xs text-slate-400">{{ charge.charge }}
                                                <span v-if="charge.calculation_type">
                                                    <span v-if="charge.calculation_type === 'fixed'">
                                                        (Fixed {{ charge.value }})
                                                    </span>
                                                    <span v-else>
                                                        (Percentage {{ charge.value }})
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="text-xs font-semibold text-white">৳{{
                                                formatPrice(((charge.amount_in_bdt ?? charge.amount ?? 0) /
                                                    (request.quantity || 1)) * editableQuantity) }}</span>
                                        </div>
                                        <div
                                            class="flex justify-between items-center px-4 py-2 border border-white/5 hover:border-primary/30">
                                            <span class="text-xs text-slate-400">Delivery Charge</span>
                                            <span class="text-xs font-semibold text-white">৳{{
                                                formatPrice(request.delivery_charge ?? 0)}}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-2 border border-white/5 hover:border-primary/30"
                                            v-if="request.tax > 0">
                                            <span class="text-xs text-slate-400">Tax</span>
                                            <span class="text-xs font-semibold text-white">৳{{ formatPrice(request.tax
                                                ?? 0)}}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-2 border border-white/5 hover:border-primary/30"
                                            v-if="request.declared_shipping_cost > 0">
                                            <span class="text-xs text-slate-400">Shipping Charge</span>
                                            <span class="text-xs font-semibold text-white">৳{{ formatPrice(request.declared_shipping_cost
                                                ?? 0)}}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-2 border border-white/5 hover:border-primary/30"
                                            v-if="request.payment_processing_fee > 0">
                                            <span class="text-xs text-slate-400">Payment Processing Fee</span>
                                            <span class="text-xs font-semibold text-white">৳{{ formatPrice(request.payment_processing_fee
                                                ?? 0)}}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Amount -->
                                <div
                                    class="bg-surface rounded-lg p-4 mt-4 border border-white/5 hover:border-primary/30 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-semibold text-white">Total Amount (BDT)</span>
                                        <span class="text-xl font-bold text-primary">৳{{
                                            formatPrice((request.total_amount_bdt / (request.quantity || 1)) *
                                                editableQuantity) }}</span>
                                    </div>
                                    <div v-if="request.min_payment_amount && request.min_payment_amount > 0"
                                        class="flex justify-between items-center text-xs pt-2 border-t border-white/5 hover:border-primary/30">
                                        <span class="text-slate-400">Minimum Payment (60%)</span>
                                        <span class="font-semibold text-yellow-400">৳{{
                                            formatPrice((request.min_payment_amount / (request.quantity || 1)) *
                                                editableQuantity) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AddressConfirmationModal :is-open="showAddressModal" :selected-requests="[request]" :loading="confirmingOrder"
            :initial-address="shippingAddress" @close="showAddressModal = false" @confirm="handleConfirmOrder" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../../axios';
import AddressConfirmationModal from '../../components/AddressConfirmationModal.vue';
import { useToast } from "vue-toastification";

const toast = useToast();
const route = useRoute();
const router = useRouter();
const requestId = route.params.id;

const loading = ref(false);
const error = ref(null);
const request = ref(null);
const confirmingOrder = ref(false);
const showAddressModal = ref(false);
const editableQuantity = ref(1);
const updatingQuantity = ref(false);
const currencies = ref([]);

const convertedUnitPrice = computed(() => {
    if (!request.value || !currencies.value.length) return null;
    const currency = currencies.value.find(c => c.code === request.value.currency);
    if (!currency) return null;
    if (currency.is_base) return request.value.price;
    return request.value.price * currency.rate_to_base;
});

const shippingAddress = ref({
    name: '',
    division: '',
    thana: '',
    street: '',
    phone: ''
});

const fetchRequest = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/product-requests/${requestId}`);
        request.value = response.data.product_request || response.data;
        editableQuantity.value = request.value.quantity; // Initialize editable quantity

        // Pre-fill address if available
        if (!request.value.shipping_address && request.value.user?.shipping_address) {
            shippingAddress.value = { ...request.value.user.shipping_address };
        } else if (request.value.shipping_address) {
            shippingAddress.value = { ...request.value.shipping_address };
        }

        if (!shippingAddress.value.name && request.value.user?.name) {
            shippingAddress.value.name = request.value.user.name;
        }
    } catch (err) {
        console.error('Error fetching request:', err);
        error.value = err.response?.data?.message || 'Failed to load request details';
    } finally {
        loading.value = false;
    }
};

const openAddressModal = () => {
    showAddressModal.value = true;
};

const handleConfirmOrder = async (orderPayload) => {
    confirmingOrder.value = true;
    try {
        let payload;
        let config = {};

        if (orderPayload.payment_slip) {
            // Bank transfer with slip — use FormData
            payload = new FormData();
            payload.append('request_id', request.value.request_number || request.value.id);
            payload.append('payment_method', orderPayload.payment_method);
            if (orderPayload.payment_type) {
                payload.append('payment_type', orderPayload.payment_type);
            }
            Object.keys(orderPayload.shipping_address).forEach(key => {
                payload.append(`shipping_address[${key}]`, orderPayload.shipping_address[key]);
            });
            payload.append('payment_slip', orderPayload.payment_slip);
            config = { headers: { 'Content-Type': 'multipart/form-data' } };
        } else {
            // JSON payload
            payload = {
                request_id: request.value.request_number || request.value.id,
                payment_method: orderPayload.payment_method,
                payment_type: orderPayload.payment_type,
                shipping_address: orderPayload.shipping_address,
            };
        }

        const response = await axios.post('/product-requests/create-order', payload, config);

        // showAddressModal.value = false;

        // Handle bKash redirection
        if (response.data.initiate_bkash && response.data.order_id) {
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
            toast.success('Order created successfully!');
        }

        // Redirect to the new order
        if (response.data.order_number) {
            router.push(`/dashboard/orders/${response.data.order_number}`);
        } else {
            await fetchRequest();
        }
    } catch (err) {
        console.error('Error creating order:', err);
        toast.error(err.response?.data?.message || 'Failed to create order');
    } finally {
        confirmingOrder.value = false;
    }
};

const increaseQuantity = () => {
    editableQuantity.value++;
    updateQuantityIfChanged();
};

const decreaseQuantity = () => {
    if (editableQuantity.value > 1) {
        editableQuantity.value--;
        updateQuantityIfChanged();
    }
};

const updateQuantityIfChanged = async () => {
    if (editableQuantity.value === request.value.quantity || editableQuantity.value < 1) {
        editableQuantity.value = request.value.quantity; // Reset if invalid
        return;
    }

    updatingQuantity.value = true;
    try {
        await axios.post(`/product-requests/${requestId}/update-quantity`, {
            quantity: editableQuantity.value
        });

        // Refresh request to get updated totals
        await fetchRequest();
    } catch (err) {
        console.error('Error updating quantity:', err);
        toast.error(err.response?.data?.message || 'Failed to update quantity');
        editableQuantity.value = request.value.quantity; // Reset on error
    } finally {
        updatingQuantity.value = false;
    }
};

const handleImageError = (event) => {
    event.target.src = '/assets/placeholder.webp';
};

const formatPrice = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/currencies');
        currencies.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Error fetching currencies:', err);
    }
};

onMounted(() => {
    fetchCurrencies();
    fetchRequest();
});
</script>

<style scoped>
.shadow-gold {
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
}
</style>
