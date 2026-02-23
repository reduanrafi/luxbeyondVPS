<template>
    <div class="min-h-screen bg-black py-8 px-4">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Loading State -->
            <div v-if="loading" class="bg-slate-900 rounded-xl shadow-lg p-8 text-center border border-slate-800">
                <div
                    class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent">
                </div>
                <p class="mt-4 text-sm text-slate-400">Loading request details...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="bg-slate-900 rounded-xl shadow-lg p-8 text-center border border-red-900">
                <p class="text-red-400 font-semibold mb-4">{{ error }}</p>
                <button @click="$router.push('/dashboard/requests')"
                    class="px-6 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary/90 transition-colors">
                    ← Back to Requests
                </button>
            </div>

            <!-- Request Details -->
            <div v-else-if="request" class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Confirm Your Request</h1>
                        <p class="text-sm text-slate-600 mt-1">Request #{{ request.request_number || request.id }}</p>
                    </div>
                    <button @click="$router.push('/dashboard/requests')"
                        class="px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors border border-slate-700">
                        ← Back
                    </button>
                </div>

                <!-- Status Badge -->
                <div v-if="request.status"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold" :class="{
                        'bg-yellow-100 text-yellow-800': request.status === 'pending',
                        'bg-green-100 text-green-800': request.status === 'request_accepted' || request.status === 'accepted',
                        'bg-blue-100 text-blue-800': request.status === 'completed',
                        'bg-gray-100 text-gray-800': !['pending', 'request_accepted', 'accepted', 'completed'].includes(request.status)
                    }">
                    {{ request.status.replace('_', ' ').toUpperCase() }}
                </div>

                <!-- Product Summary Card -->
                <div class="bg-slate-900 rounded-xl shadow-lg overflow-hidden border border-slate-800">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <!-- Product Image -->
                            <div v-if="request.admin_image_url" class="flex-shrink-0">
                                <img :src="request.admin_image_url" alt="Product" referrerpolicy="no-referrer"
                                    class="w-32 h-32 object-contain rounded-lg border border-slate-700 bg-slate-800 p-2"
                                    @error="handleImageError">
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 space-y-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Product Name</p>
                                        <p class="text-base font-semibold text-white">{{ request.product_name }}</p>
                                    </div>
                                    <div v-if="request.status === 'request_accepted' || request.status === 'accepted'"
                                        class="flex gap-2">
                                        <button @click="openAddressModal"
                                            class="px-6 py-2 bg-primary text-slate-900 text-sm font-bold rounded-lg hover:bg-white transition-all shadow-[0_0_20px_rgba(255,215,0,0.2)] uppercase tracking-widest">
                                            Confirm & Create Order
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 mb-1">Product URL</p>
                                    <a :href="request.url" target="_blank"
                                        class="text-sm text-blue-600 hover:text-blue-700 hover:underline break-all">
                                        {{ request.url }}
                                    </a>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700">
                                        <p class="text-xs text-slate-500 mb-1">Price</p>
                                        <p class="text-sm font-semibold text-white">{{ request.currency }} {{
                                            formatPrice(request.price) }}</p>
                                    </div>
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700">
                                        <p class="text-xs text-slate-500 mb-2">Quantity</p>
                                        <div class="flex items-center gap-2">
                                            <button @click="decreaseQuantity"
                                                :disabled="updatingQuantity || editableQuantity <= 1"
                                                class="w-7 h-7 flex items-center justify-center bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed rounded text-white font-bold transition-colors">
                                                −
                                            </button>
                                            <input v-model.number="editableQuantity" @blur="updateQuantityIfChanged"
                                                @keyup.enter="updateQuantityIfChanged" type="number" min="1"
                                                :disabled="updatingQuantity"
                                                class="w-16 px-2 py-1 bg-slate-700 border border-slate-600 rounded text-center text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50" />
                                            <button @click="increaseQuantity" :disabled="updatingQuantity"
                                                class="w-7 h-7 flex items-center justify-center bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed rounded text-white font-bold transition-colors">
                                                +
                                            </button>
                                        </div>
                                        <p v-if="updatingQuantity" class="text-xs text-primary mt-1">Updating...</p>
                                    </div>
                                    <div class="bg-slate-800 rounded-lg p-3 border border-slate-700">
                                        <p class="text-xs text-slate-500 mb-1">Subtotal</p>
                                        <p class="text-sm font-semibold text-white">{{ request.currency }} {{
                                            formatPrice(request.price * editableQuantity) }}</p>
                                    </div>
                                </div>

                                <!-- Total Amount -->
                                <div class="bg-slate-800 rounded-lg p-4 mt-4 border border-slate-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-semibold text-white">Total Amount</span>
                                        <span class="text-xl font-bold text-primary">৳{{
                                            formatPrice(request.total_amount_bdt) }}</span>
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

const shippingAddress = ref({
    street: '',
    city: '',
    state: '',
    postal_code: '',
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
        const response = await axios.post('/product-requests/create-order', orderPayload);

        showAddressModal.value = false;

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
                alert('Order created, but bKash initiation failed. Please pay from Order Details.');
            }
        } else {
            alert('Order created successfully!');
        }

        // Redirect to the new order
        if (response.data.order_number) {
            router.push(`/dashboard/orders/${response.data.order_number}`);
        } else {
            await fetchRequest();
        }
    } catch (err) {
        console.error('Error creating order:', err);
        alert(err.response?.data?.message || 'Failed to create order');
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
        alert(err.response?.data?.message || 'Failed to update quantity');
        editableQuantity.value = request.value.quantity; // Reset on error
    } finally {
        updatingQuantity.value = false;
    }
};

const handleImageError = (event) => {
    event.target.src = '/placeholder-product.png';
};

const formatPrice = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

onMounted(() => {
    fetchRequest();
});
</script>

<style scoped>
.shadow-gold {
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
}
</style>
