<template>
    <div class="space-y-6">
        <!-- Loading State -->
        <div v-if="loading" class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-slate-300">Loading request details...</p>
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
                    <p class="text-sm text-slate-300 mt-1">Request #{{ request.request_number || request.id }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="$router.push('/dashboard/requests')"
                        class="px-4 py-2 text-sm font-semibold text-primary border border-gray-300 rounded-lg hover:bg-primary/10 transition-colors">
                        ← Back to Requests
                    </button>
                </div>
            </div>

            <!-- Address Confirmation Section -->
            <div v-if="request.status === 'request_accepted' && !request.shipping_address" 
                class="bg-[#111111] rounded-2xl shadow-lg border border-white/10 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-4">Confirm Delivery Address</h3>
                    <p class="text-sm text-zinc-400 mb-6">Please confirm your shipping address to proceed with the payment.</p>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-1">Street Address</label>
                                <input v-model="shippingAddress.street" type="text" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary placeholder-zinc-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-1">City</label>
                                <input v-model="shippingAddress.city" type="text" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary placeholder-zinc-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-1">State/Division</label>
                                <input v-model="shippingAddress.state" type="text" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary placeholder-zinc-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-1">Postal Code</label>
                                <input v-model="shippingAddress.postal_code" type="text" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary placeholder-zinc-600">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-zinc-300 mb-1">Phone Number</label>
                                <input v-model="shippingAddress.phone" type="text" class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary placeholder-zinc-600">
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button @click="confirmOrder" :disabled="confirmingOrder" 
                                class="px-6 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary-hover disabled:opacity-50 transition-colors">
                                {{ confirmingOrder ? 'Confirming...' : 'Confirm Order' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Action Section (Visible only after address is confirmed) -->
            <div v-if="request.status === 'request_accepted' && request.payment_status !== 'paid' && request.shipping_address"
                class="bg-black rounded-2xl shadow-lg border border-primary/20 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-primary">Payment Required</h3>
                            <p class="text-sm text-zinc-400 mt-1">Please complete the payment to proceed with your
                                order.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-zinc-400">Amount Due</p>
                            <p class="text-2xl font-bold text-primary">৳{{ formatPrice(request.total_amount_bdt) }}</p>
                        </div>
                    </div>

                    <div v-if="request.payment_status === 'processing'"
                        class="bg-primary/10 text-primary-500 p-4 rounded-lg flex items-center gap-3 border bg-primary/20">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 bg-primary"></div>
                         <div>
                            <p class="font-semibold">Payment Verification Pending</p>
                            <p class="text-sm">We are verifying your payment details. This may take a while.</p>
                        </div>
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Show Confirmed Address Summary -->
                        <div class="bg-zinc-900 p-4 rounded-lg border border-white/10">
                             <p class="text-xs font-bold text-zinc-500 uppercase mb-2">Shipping To</p>
                             <p class="text-sm text-white">{{ request.shipping_address.street }}, {{ request.shipping_address.city }}</p>
                             <p class="text-sm text-zinc-400">{{ request.shipping_address.state }} - {{ request.shipping_address.postal_code }}</p>
                             <p class="text-sm text-zinc-400">Phone: {{ request.shipping_address.phone }}</p>
                        </div>

                        <!-- Payment Methods -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button v-for="method in paymentMethods" :key="method.id"
                                @click="method.type === 'bkash' ? initiateBkashPayment() : (method.type === 'bank_transfer' ? showBankTransfer = !showBankTransfer : null)"
                                :disabled="method.type === 'bkash' && processingBkash"
                                class="flex items-center justify-center gap-3 p-4 border rounded-xl hover:bg-white/5 transition-all bg-transparent shadow-sm"
                                :class="method.type === 'bkash' ? (processingBkash ? 'opacity-70 cursor-not-allowed border-white/10' : 'border-white/10') : 'border-white/10 hover:border-primary'">

                                <span v-if="method.type === 'bkash'" class="font-bold text-pink-500">Pay with {{
                                    method.name }}</span>
                                <span v-else class="font-bold text-white">{{ method.name }}</span>
                                <span v-if="method.type === 'bkash' && processingBkash"
                                    class="animate-spin ml-2 text-white">...</span>
                            </button>
                        </div>

                        <!-- Bank Transfer Form -->
                        <div v-if="showBankTransfer && bankTransferMethod"
                            class="bg-background p-6 rounded-xl space-y-4 border border-primary/20">
                            <!-- Bank Info -->
                            <div class="text-sm text-slate-400 mb-4 p-4 bg-background rounded border border-primary/20">
                                <p class="font-semibold text-slate-700 mb-2">Bank Details:</p>
                                <template v-if="bankTransferMethod.bank_name">
                                    <p class="font-medium">Bank: {{ bankTransferMethod.bank_name }}</p>
                                    <p>Account Name: {{ bankTransferMethod.account_name }}</p>
                                    <p>Account No: {{ bankTransferMethod.account_number }}</p>
                                    <p>Branch: {{ bankTransferMethod.branch_name }}</p>
                                    <p v-if="bankTransferMethod.routing_number">Routing: {{
                                        bankTransferMethod.routing_number }}</p>
                                </template>
                                <template v-else>
                                    <p class="whitespace-pre-wrap">{{ bankTransferMethod.description }}</p>
                                </template>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Reference</label>
                                <input v-model="bankTransferForm.reference" type="text"
                                    placeholder="Enter Transaction ID or Reference"
                                    class="w-full px-4 py-2 bg-background border border-primary/20 rounded-lg focus:outline-none focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Payment Slip</label>
                                <input @change="handleFileChange" type="file" accept="image/*,.pdf"
                                    class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-slate-900 hover:file:bg-primary-hover">
                            </div>

                            <div class="flex justify-end">
                                <button @click="submitBankTransfer" :disabled="submittingBank"
                                    class="px-6 py-2 bg-slate-900 text-white font-semibold rounded-lg hover:bg-slate-800 transition-colors disabled:opacity-50">
                                    {{ submittingBank ? 'Submitting...' : 'Submit Payment Details' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-300 mb-1">Request Status</p>
                    <span
                        :style="{ backgroundColor: request.order_status?.color + '20', color: request.order_status?.color }"
                        class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                        {{ request.order_status?.label || request.status || 'N/A' }}
                    </span>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-300 mb-1">Request Date</p>
                    <p class="text-sm font-semibold text-primary">{{ formatDate(request.created_at) }}</p>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-300 mb-1">Currency</p>
                    <p class="text-sm font-semibold text-primary">{{ request.currency || 'N/A' }}</p>
                </div>
                <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
                    <p class="text-xs text-slate-300 mb-1">Total Amount (BDT)</p>
                    <p class="text-sm font-semibold text-primary">৳{{ formatPrice(request.total_amount_bdt) }}</p>
                </div>
            </div>

            <!-- Timeline -->
            <!-- <div class="bg-background rounded-2xl shadow-lg border border-primary/20 overflow-hidden">
                <div class="p-4 border-b border-primary/20 bg-background">
                    <h3 class="text-lg font-bold text-slate-900">Request Timeline</h3>
                </div>
                <div class="p-6">
                    <div class="relative pl-4 border-l-2 border-gray-200 space-y-8">
                        <div v-for="(log, index) in request.timeline" :key="index" class="relative">
                            <div class="absolute -left-[21px] bg-primary border-2 border-primary rounded-full w-4 h-4"></div>
                            <div class="mb-1">
                                <span class="font-bold text-slate-800">{{ log.status }}</span>
                                <span class="text-xs text-slate-300 ml-2">{{ formatDate(log.created_at) }}</span>
                            </div>
                            <p class="text-sm text-slate-400">{{ log.note }}</p>
                            <p v-if="log.creator" class="text-xs text-slate-400 mt-1">by {{ log.creator.name }}</p>
                        </div>
                        <div v-if="!request.timeline || request.timeline.length === 0" class="text-sm text-slate-300 italic">
                            No timeline history available.
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Product Information -->
            <div class="bg-background rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-background/50">
                    <h3 class="text-lg font-bold text-primary">Product Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs text-slate-300 mb-1">Product URL</p>
                        <a :href="request.url" target="_blank" class="text-sm font-semibold text-primary hover:underline">
                            {{ request.url }}
                        </a>
                    </div>
                    <div v-if="request.admin_image_url" class="mt-4">
                        <p class="text-xs text-slate-300 mb-2">Product Image</p>
                        <img :src="request.admin_image_url" 
                             alt="Product Image" 
                             referrerpolicy="no-referrer"
                             class="w-48 h-48 object-contain rounded-lg border border-white/10 bg-white/5"
                             @error="handleImageError">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Price</p>
                            <p class="text-sm font-semibold text-primary">{{ request.currency }} {{
                                formatPrice(request.price) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Quantity</p>
                            <div class="flex items-center gap-2">
                                <template v-if="editingQuantity">
                                    <input v-model.number="tempQuantity" type="number" min="1" class="w-16 px-2 py-1 text-sm bg-black/20 border border-white/10 rounded text-white focus:outline-none focus:border-primary">
                                    <button @click="saveQuantity" class="p-1 text-green-500 hover:text-green-400">
                                        <Check class="w-4 h-4" />
                                    </button>
                                    <button @click="cancelEditQuantity" class="p-1 text-red-500 hover:text-red-400">
                                        <X class="w-4 h-4" />
                                    </button>
                                </template>
                                <template v-else>
                                    <p class="text-sm font-semibold text-primary">{{ request.quantity }}</p>
                                    <button v-if="canEditQuantity" @click="startEditQuantity" class="p-1 text-slate-400 hover:text-white transition-colors" title="Edit Quantity">
                                        <Edit2 class="w-3 h-3" />
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Subtotal</p>
                            <p class="text-sm font-semibold text-primary">{{ request.currency }} {{
                                formatPrice(request.price * request.quantity) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Charges -->
            <div class="bg-background rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-background/50">
                    <h3 class="text-lg font-bold text-slate-900">Shipping & Charges</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Location</p>
                            <p class="text-sm font-semibold text-primary">{{ request.is_inside_city ? 'Inside City' :
                                'Outside City' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Weight (kg)</p>
                            <p class="text-sm font-semibold text-primary">{{ request.weight || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Payment Method</p>
                            <p class="text-sm font-semibold text-primary">{{ request.payment_method || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-300 mb-1">Declared Shipping Cost</p>
                            <p class="text-sm font-semibold text-primary">{{ request.currency }} {{
                                formatPrice(request.declared_shipping_cost) }}</p>
                        </div>
                    </div>

                    <!-- Detailed Charges Breakdown -->
                    <div v-if="request.charges_breakdown && request.charges_breakdown.length > 0" class="border-t border-white/10 pt-4 mb-4">
                         <h4 class="text-sm font-bold text-slate-700 mb-3">Charges Breakdown</h4>
                         <div class="space-y-2">
                            <div v-for="(charge, index) in request.charges_breakdown" :key="index" class="flex justify-between text-sm">
                                <span>
                                    {{ charge.charge }} 
                                    <span v-if="charge.currency !== 'BDT'" class="text-xs text-slate-300">
                                        ({{ charge.currency }} {{ formatPrice(charge.amount_in_currency) }})
                                    </span>
                                </span>
                                <span class="font-semibold text-slate-700">৳{{ formatPrice(charge.amount_in_bdt) }}</span>
                            </div>
                         </div>
                    </div>

                    <div class="border-t border-white/10 pt-4 space-y-2">
                        <div v-if="request.tax" class="flex justify-between text-sm">
                            <span class="text-slate-400">Tax:</span>
                            <span class="font-semibold text-primary">৳{{ formatPrice(request.tax) }}</span>
                        </div>
                        <div v-if="request.additional_charges" class="flex justify-between text-sm">
                            <span class="text-slate-400">Additional Charges:</span>
                            <span class="font-semibold text-primary">৳{{ formatPrice(request.additional_charges)
                            }}</span>
                        </div>
                        <div v-if="request.delivery_charge" class="flex justify-between text-sm">
                            <span class="text-slate-400">Delivery Charge:</span>
                            <span class="font-semibold text-primary">৳{{ formatPrice(request.delivery_charge) }}</span>
                        </div>
                        <div v-if="request.payment_processing_fee" class="flex justify-between text-sm">
                            <span class="text-slate-400">Payment Processing Fee:</span>
                            <span class="font-semibold text-primary">৳{{ formatPrice(request.payment_processing_fee)
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Summary -->
            <div class="bg-background rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-white/10 bg-background/50">
                    <h3 class="text-lg font-bold text-primary">Request Summary</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Product Subtotal</span>
                        <span class="font-semibold text-primary">৳{{ formatPrice(calculateProductTotal()) }}</span>
                    </div>
                    <div v-if="request.declared_shipping_cost" class="flex justify-between text-sm">
                        <span class="text-slate-400">Declared Shipping</span>
                        <span class="font-semibold text-primary">৳{{ formatPrice(request.declared_shipping_cost)
                        }}</span>
                    </div>
                    <div v-if="request.tax" class="flex justify-between text-sm">
                        <span class="text-slate-400">Tax</span>
                        <span class="font-semibold text-primary">৳{{ formatPrice(request.tax) }}</span>
                    </div>
                    <div v-if="request.additional_charges" class="flex justify-between text-sm">
                        <span class="text-slate-400">Additional Charges</span>
                        harg <span class="font-semibold text-primary">৳{{ formatPrice(request.additional_charges)
                            }}</span>
                    </div>
                    <div v-if="request.delivery_charge" class="flex justify-between text-sm">
                        <span class="text-slate-400">Delivery Charge</span>
                        <span class="font-semibold text-primary">৳{{ formatPrice(request.delivery_charge) }}</span>
                    </div>
                    <div v-if="request.payment_processing_fee" class="flex justify-between text-sm">
                        <span class="text-slate-400">Payment Processing Fee</span>
                        <span class="font-semibold text-primary">৳{{ formatPrice(request.payment_processing_fee)
                            }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-white/10">
                        <span class="text-slate-300">Total Amount (BDT)</span>
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



        </div>

        <!-- Payment Option Modal -->
        <div v-if="showPaymentModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-surface p-6 rounded-2xl shadow-xl max-w-md w-full mx-4 border border-white/10">
                <h3 class="text-xl font-bold text-slate-900 mb-4">Select Payment Amount</h3>
                <div class="space-y-3">
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                        :class="selectedPaymentOption === 'full' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-primary/50'">
                        <input type="radio" v-model="selectedPaymentOption" value="full"
                            class="w-5 h-5 text-primary focus:ring-primary">
                        <div class="ml-3">
                            <span class="block font-semibold text-slate-900">Full Payment</span>
                            <span class="block text-sm text-slate-300">Pay the full amount of ৳{{
                                formatPrice(request.total_amount_bdt) }}</span>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                        :class="selectedPaymentOption === 'partial' ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-primary/50'">
                        <input type="radio" v-model="selectedPaymentOption" value="partial"
                            class="w-5 h-5 text-primary focus:ring-primary">
                        <div class="ml-3">
                            <span class="block font-semibold text-slate-900">Minimum Payment</span>
                            <span class="block text-sm text-slate-300">Pay minimum booking amount of ৳{{
                                formatPrice(request.min_payment_amount) }}</span>
                        </div>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showPaymentModal = false"
                        class="px-4 py-2 text-slate-400 hover:text-slate-900 font-medium">Cancel</button>
                    <button @click="confirmPaymentOption"
                        class="px-6 py-2 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary-hover">
                        Proceed to Pay
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Check, X, Edit2 } from 'lucide-vue-next';
import axios from '../../axios';

const route = useRoute();
const router = useRouter();
const requestId = route.params.id;

const loading = ref(true);
const error = ref(null);
const request = ref(null);
const currencies = ref([]);
const paymentMethods = ref([]);
const showBankTransfer = ref(false);
const showPaymentModal = ref(false);
const processingBkash = ref(false);
const submittingBank = ref(false);
const confirmingOrder = ref(false);
const selectedPaymentOption = ref('full'); // 'full' or 'partial'

// Quantity Editing
const editingQuantity = ref(false);
const tempQuantity = ref(1);

const bankTransferForm = ref({
    reference: '',
    file: null
});

const shippingAddress = ref({
    street: '',
    city: '',
    state: '',
    postal_code: '',
    phone: '',
    country: 'Bangladesh'
});

// Computed properties for selected method (Bank Transfer)
const bankTransferMethod = computed(() => {
    return paymentMethods.value.find(m => m.type === 'bank_transfer') || {};
});

const bkashMethod = computed(() => {
    return paymentMethods.value.find(m => m.type === 'bkash');
});

const fetchRequest = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/product-requests/${requestId}`);
        request.value = response.data;

        // Pre-fill address if not already set on request but available on user
        if (!request.value.shipping_address && request.value.user?.shipping_address) {
             shippingAddress.value = { ...request.value.user.shipping_address };
        }
    } catch (err) {
        console.error('Error fetching request:', err);
        error.value = err.response?.data?.message || 'Failed to load request details';
    } finally {
        loading.value = false;
    }
};

const confirmOrder = async () => {
    if (!shippingAddress.value.street || !shippingAddress.value.city || !shippingAddress.value.phone) {
        alert('Please fill in all required address fields.');
        return;
    }

    confirmingOrder.value = true;
    try {
        await axios.post(`/product-requests/${requestId}/confirm-order`, {
            shipping_address: shippingAddress.value
        });
        await fetchRequest(); 
        alert('Order confirmed! Please proceed to payment.');
    } catch (err) {
        console.error('Confirmation Error:', err);
        alert(err.response?.data?.message || 'Failed to confirm order');
    } finally {
        confirmingOrder.value = false;
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

const fetchPaymentMethods = async () => {
    try {
        const response = await axios.get('/payment-methods');
        paymentMethods.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Error fetching payment methods:', err);
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



const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        bankTransferForm.value.file = file;
    }
};

const initiateBkashPayment = async () => {
    if (!request.value) return;

    // Check if we need to show modal for partial/full selection
    // Only if min_payment_amount is set and less than total
    if (request.value.min_payment_amount > 0 && request.value.min_payment_amount < request.value.total_amount_bdt) {
        showPaymentModal.value = true;
        return;
    }

    // Otherwise proceed with full payment directly
    await processBkashPayment(request.value.total_amount_bdt);
};

const confirmPaymentOption = async () => {
    const amount = selectedPaymentOption.value === 'partial'
        ? request.value.min_payment_amount
        : request.value.total_amount_bdt;

    showPaymentModal.value = false;
    await processBkashPayment(amount);
};

const processBkashPayment = async (amount) => {
    processingBkash.value = true;
    try {
        const response = await axios.post('/product-requests/bkash/initiate', {
            product_request_id: request.value.id,
            amount: amount
        });

        if (response.data.bkashURL) {
            window.location.href = response.data.bkashURL;
        } else {
            throw new Error('Invalid bKash response');
        }
    } catch (err) {
        console.error('bKash Error:', err);
        alert(err.response?.data?.message || 'Failed to initiate bKash payment');
    } finally {
        processingBkash.value = false;
    }
};

const handleImageError = (e) => {
    e.target.src = 'https://placehold.co/400x400/1e293b/cbd5e1?text=Image+Blocked+by+Source';
};

const submitBankTransfer = async () => {
    if (!bankTransferForm.value.reference && !bankTransferForm.value.file) {
        alert('Please provide a reference or upload a slip.');
        return;
    }

    submittingBank.value = true;
    const formData = new FormData();
    formData.append('payment_method', 'bank_transfer');
    if (bankTransferForm.value.reference) formData.append('payment_reference', bankTransferForm.value.reference);
    if (bankTransferForm.value.file) formData.append('payment_slip', bankTransferForm.value.file);

    try {
        await axios.post(`/product-requests/${request.value.id}/payment`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        // Refresh request
        await fetchRequest();
        showBankTransfer.value = false;
        alert('Payment details submitted successfully!');
    } catch (err) {
        console.error('Submission Error:', err);
        alert(err.response?.data?.message || 'Failed to submit payment details');
    } finally {
        submittingBank.value = false;
    }
};

const canEditQuantity = computed(() => {
    if (!request.value) return false;
    // Allow edit if status is not paid/completed/cancelled
    const status = request.value.status;
    const paymentStatus = request.value.payment_status;
    return !['completed', 'cancelled', 'paid'].includes(status) && paymentStatus !== 'paid';
});

const startEditQuantity = () => {
    tempQuantity.value = request.value.quantity;
    editingQuantity.value = true;
};

const cancelEditQuantity = () => {
    editingQuantity.value = false;
};

const saveQuantity = async () => {
    if (tempQuantity.value < 1) return;
    try {
        const response = await axios.post(`/product-requests/${requestId}/update-quantity`, {
            quantity: tempQuantity.value
        });
        request.value = response.data.product_request;
        editingQuantity.value = false;
        alert('Quantity updated and total recalculated.');
    } catch (err) {
        console.error('Update Quantity Error:', err);
        alert(err.response?.data?.message || 'Failed to update quantity');
    }
};

onMounted(() => {
    fetchCurrencies();
    fetchPaymentMethods();
    fetchRequest();
});
</script>


