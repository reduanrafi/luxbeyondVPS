<template>
    <div class="min-h-screen bg-background pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8 border-b border-white/5 pb-6">
                <h1 class="text-4xl font-serif text-white tracking-wide">Checkout</h1>
                <p class="text-slate-400 mt-2 text-sm uppercase tracking-wider">Complete your order securely</p>
            </div>

            <!-- Empty Cart State -->
            <div v-if="cartStore.items.length === 0" class="bg-surface border border-white/10 p-12 text-center">
                <ShoppingCart class="w-16 h-16 text-slate-700 mx-auto mb-4" />
                <h3 class="text-xl font-serif text-white mb-2 tracking-wide">Your cart is empty</h3>
                <p class="text-slate-500 mb-6 font-light">Add some products to your cart to continue</p>
                <router-link to="/shop"
                    class="inline-block px-8 py-3 bg-primary text-slate-900 font-bold uppercase tracking-widest text-sm rounded-none hover:bg-primary-hover transition-all shadow-[0_0_20px_rgba(200,174,125,0.2)]">
                    Continue Shopping
                </router-link>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Shipping Address -->
                    <div class="bg-surface border border-white/5 p-6">
                        <h2
                            class="text-xl font-serif text-white mb-6 flex items-center gap-3 tracking-wide border-b border-white/5 pb-4">
                            <MapPin class="w-5 h-5 text-primary" />
                            Shipping Address
                        </h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="shippingForm.name" type="text" required
                                        class="w-full px-4 py-3 bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                                        placeholder="John Doe" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="shippingForm.phone" type="tel" required
                                        class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                                        placeholder="+880 1XXX-XXXXXX" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input v-model="shippingForm.email" type="email" required
                                    class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                                    placeholder="john@example.com" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-2">
                                    Address <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="shippingForm.address" rows="3" required
                                    class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                                    placeholder="House/Flat No, Road, Area"></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                                        City <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="shippingForm.city" type="text" required
                                        class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                                        placeholder="Dhaka" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                                        Postal Code
                                    </label>
                                    <input v-model="shippingForm.postal_code" type="text"
                                        class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                                        placeholder="1200" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-300 mb-2">
                                        Delivery Location <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="shippingForm.is_inside_city" required @change="calculateCharges"
                                        class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all bg-surface">
                                        <option :value="true">Inside City</option>
                                        <option :value="false">Outside City</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-surface border border-white/5 p-6">
                        <h2
                            class="text-xl font-serif text-white mb-6 flex tracking-wide border-b border-white/5 pb-4 items-center gap-2">
                            <CreditCard class="w-6 h-6 text-primary" />
                            Payment Method
                        </h2>
                        <div v-if="paymentMethodsLoading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        </div>
                        <div v-else-if="paymentMethods.length > 0" class="space-y-3">
                            <label v-for="method in paymentMethods" :key="method.id"
                                class="flex items-center gap-4 p-5 border rounded-none cursor-pointer hover:border-primary/30 transition-all relative"
                                :class="selectedPaymentMethod === method.type ? 'border-primary/50 bg-primary/5 shadow-[0_0_20px_rgba(200,174,125,0.1)]' : 'border-white/10 hover:bg-white/5'">
                                <input type="radio" v-model="selectedPaymentMethod" :value="method.type"
                                    @change="calculateCharges"
                                    class="w-5 h-5 text-primary border-white/20 focus:ring-2 focus:ring-primary/50 focus:ring-offset-0 bg-background checked:bg-primary checked:border-primary accent-primary transition-all" />
                                <div class="flex-1">
                                    <div class="font-semibold text-white">{{ method.name }}</div>
                                    <div v-if="method.description" class="text-sm text-slate-500 mt-1">
                                        {{ method.description }}
                                    </div>
                                    <div v-if="method.fee_percentage || method.fee" class="text-xs text-slate-600 mt-1">
                                        <span v-if="method.fee_percentage">Processing fee: {{ method.fee_percentage
                                            }}%</span>
                                        <span v-else-if="method.fee">Processing fee: ৳{{ method.fee }}</span>
                                    </div>
                                    <!-- Show payment instructions for manual methods -->
                                    <div v-if="!method.is_online && method.instructions"
                                        class="text-xs text-slate-500 mt-2 p-2 bg-background/50 rounded">
                                        {{ method.instructions }}
                                    </div>
                                    <!-- Show account details for manual methods -->
                                    <div v-if="!method.is_online && method.account_number"
                                        class="text-xs text-slate-500 mt-2 p-2 bg-background/50 rounded">
                                        <div><strong>Account:</strong> {{ method.account_number }}</div>
                                        <div v-if="method.account_name"><strong>Name:</strong> {{ method.account_name }}
                                        </div>
                                        <div v-if="method.bank_name"><strong>Bank:</strong> {{ method.bank_name }}</div>
                                        <div v-if="method.branch_name"><strong>Branch:</strong> {{ method.branch_name }}
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div v-else class="text-center py-8 text-slate-600">
                            No payment methods available
                        </div>
                    </div>

                    <!-- bKash Payment Amount Selection -->
                    <div v-if="selectedPaymentMethod === 'bkash'" class="bg-surface border border-white/5 p-6">
                        <h2
                            class="text-xl font-serif text-white mb-6 flex items-center gap-3 tracking-wide border-b border-white/5 pb-4">
                            <CreditCard class="w-5 h-5 text-primary" />
                            Payment Amount
                        </h2>
                        <div class="space-y-3">
                            <label
                                class="flex items-center justify-between gap-4 p-5 border rounded-none cursor-pointer hover:border-primary/30 transition-all relative"
                                :class="paymentAmount === 'partial' ? 'border-primary/50 bg-primary/5 shadow-[0_0_20px_rgba(200,174,125,0.1)]' : 'border-white/10 hover:bg-white/5'">
                                <div class="flex items-center gap-4">
                                    <input type="radio" v-model="paymentAmount" value="partial"
                                        class="w-5 h-5 text-primary border-white/20 focus:ring-2 focus:ring-primary/50 focus:ring-offset-0 bg-background transition-all accent-primary" />
                                    <div>
                                        <div class="font-serif text-white tracking-wide">
                                            Partial Payment ({{ checkoutSettings.min_payment_percentage_shop }}%)
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Pay {{ checkoutSettings.min_payment_percentage_shop }}% now, {{ 100 -
                                                checkoutSettings.min_payment_percentage_shop }}% on delivery
                                        </div>
                                    </div>
                                </div>
                                <div class="text-primary font-bold text-lg">৳{{ formatPrice(bkashPaymentAmount) }}
                                </div>
                            </label>
                            <label
                                class="flex items-center justify-between gap-4 p-5 border rounded-none cursor-pointer hover:border-primary/30 transition-all relative"
                                :class="paymentAmount === 100 ? 'border-primary/50 bg-primary/5 shadow-[0_0_20px_rgba(200,174,125,0.1)]' : 'border-white/10 hover:bg-white/5'">
                                <div class="flex items-center gap-4">
                                    <input type="radio" v-model="paymentAmount" :value="100"
                                        class="w-5 h-5 text-primary border-white/20 focus:ring-2 focus:ring-primary/50 focus:ring-offset-0 bg-background checked:bg-primary checked:border-primary accent-primary transition-all" />
                                    <div>
                                        <div class="font-serif text-white tracking-wide">Full Payment (100%)</div>
                                        <div class="text-xs text-slate-500 mt-1">Pay complete amount now</div>
                                    </div>
                                </div>
                                <div class="text-primary font-bold text-lg">৳{{ formatPrice(orderSummary.total) }}</div>
                            </label>
                        </div>
                        <div v-if="paymentAmount === 60"
                            class="mt-4 p-3 bg-primary/10 border border-primary/20 rounded-none">
                            <p class="text-xs text-slate-300">
                                <strong class="text-primary">Note:</strong> Remaining ৳{{ formatPrice(orderSummary.total
                                    * 0.4) }} will be collected on delivery.
                            </p>
                        </div>
                    </div>

                    <!-- Payment Slip Upload (for bank transfer) -->
                    <div v-if="selectedPaymentMethod === 'bank_transfer'" class="bg-surface border border-white/5 p-6">
                        <h2
                            class="text-xl font-serif text-white mb-6 flex items-center gap-3 tracking-wide border-b border-white/5 pb-4">
                            <Upload class="w-5 h-5 text-primary" />
                            Upload Payment Slip
                        </h2>
                        <div v-if="!paymentSlipFile" class="space-y-4">
                            <div
                                class="border-2 border-dashed border-primary/30 rounded-none p-8 text-center bg-primary/5">
                                <input type="file" ref="paymentSlipInput" @change="handlePaymentSlipUpload"
                                    accept="image/*" class="hidden" />
                                <Upload class="w-12 h-12 text-primary mx-auto mb-4" />
                                <p class="text-white mb-2 font-semibold">Upload your payment slip</p>
                                <p class="text-xs text-slate-500 mb-4">Required before placing order • JPG, PNG, GIF
                                    (Max 5MB)</p>
                                <button @click="$refs.paymentSlipInput?.click()"
                                    class="px-6 py-2 bg-primary text-slate-900 font-bold uppercase tracking-widest text-sm rounded-none hover:bg-primary-hover transition-all shadow-[0_0_20px_rgba(200,174,125,0.2)]">
                                    Choose File
                                </button>
                            </div>
                        </div>
                        <div v-else class="p-4 bg-green-500/10 border border-green-500/30 rounded-none">
                            <div class="flex items-center gap-2 text-green-400 mb-3">
                                <CheckCircle class="w-5 h-5" />
                                <span class="font-semibold">Payment slip ready</span>
                            </div>
                            <img :src="paymentSlipPreview" alt="Payment slip"
                                class="max-w-full h-48 object-contain rounded-none border border-white/10 mt-2 bg-background" />
                            <button @click="removePaymentSlip"
                                class="mt-3 text-sm text-red-400 hover:text-red-300 font-semibold">
                                Remove and upload different slip
                            </button>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="bg-surface border border-white/5 p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Order Notes (Optional)</h2>
                        <textarea v-model="orderNotes" rows="3"
                            class="w-full px-4 py-3 border bg-background border border-white/10 text-white placeholder-slate-600 rounded-none focus:ring-1 focus:ring-primary/50 focus:border-primary/50 transition-all outline-none"
                            placeholder="Special instructions for delivery..."></textarea>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-surface border border-white/5 p-6 sticky top-4">
                        <h2 class="text-xl font-bold text-white mb-6">Order Summary</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-6 max-h-64 overflow-y-auto">
                            <div v-for="item in cartStore.items" :key="item.id"
                                class="flex items-center gap-3 pb-4 border-b border-white/5 last:border-0">
                                <div
                                    class="w-16 h-16 rounded-none overflow-hidden border border-white/10 flex-shrink-0">
                                    <img :src="getProductImage(item)" :alt="item.name"
                                        class="w-full h-full object-contain" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-white truncate">{{ item.name }}</h3>
                                    <p class="text-xs text-slate-600">Qty: {{ item.quantity }}</p>
                                    <p class="text-sm font-bold text-primary mt-1">
                                        ৳{{ formatPrice(getItemPrice(item) * item.quantity) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Coupon Code -->
                        <div class="mb-6 p-4 bg-background/50 rounded-none border border-white/10">
                            <div v-if="!appliedCoupon" class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-300 mb-2">Have a coupon
                                    code?</label>
                                <div class="flex flex-wrap gap-2">
                                    <input v-model="couponCode" type="text" placeholder="Enter coupon code"
                                        class="flex-1 px-4 py-2 border border-primary/50 rounded-none focus:outline-none focus:ring-2 focus:ring-primary/20 outline-none"
                                        @keyup.enter="applyCoupon" />
                                    <button @click="applyCoupon" :disabled="applyingCoupon || !couponCode.trim()"
                                        class="px-6 py-2 bg-primary text-slate-900 font-semibold rounded-none hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                        <span v-if="applyingCoupon">Applying...</span>
                                        <span v-else>Apply</span>
                                    </button>
                                </div>
                                <p v-if="couponError" class="text-xs text-red-600 mt-1">{{ couponError }}</p>
                            </div>
                            <div v-else class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <CheckCircle class="w-5 h-5 text-green-600" />
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ appliedCoupon.code }}</p>
                                        <p class="text-xs text-slate-500">{{ appliedCoupon.description || `Coupon
                                            applied` }}</p>
                                    </div>
                                </div>
                                <button @click="removeCoupon"
                                    class="text-sm text-red-600 hover:text-red-700 font-semibold">
                                    Remove
                                </button>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Subtotal</span>
                                <span class="font-semibold text-white">৳{{ formatPrice(orderSummary.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Total Weight</span>
                                <span class="font-semibold text-white">{{ cartWeight.toFixed(2) }} kg</span>
                            </div>
                            <!-- Hub Charges -->
                            <template v-for="(charge, index) in chargesBreakdown" :key="index">
                                <div v-if="charge.type === 'hub'" class="flex justify-between text-sm">
                                    <span class="text-slate-500">
                                        {{ charge.charge }}
                                        <span v-if="charge.currency !== 'BDT'" class="text-xs ml-1">
                                            ({{ charge.currency }} {{ charge.amount_in_currency }})
                                        </span>
                                    </span>
                                    <span class="font-semibold text-white">৳{{ formatPrice(charge.amount_in_bdt)
                                    }}</span>
                                </div>
                            </template>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Delivery Charge</span>
                                <span class="font-semibold text-white">
                                    <span v-if="orderSummary.delivery_charge > 0">৳{{
                                        formatPrice(orderSummary.delivery_charge) }}</span>
                                    <span v-else class="text-green-600">Free</span>
                                </span>
                            </div>
                            <div v-if="orderSummary.payment_processing_fee > 0" class="flex justify-between text-sm">
                                <span class="text-slate-500">Payment Processing Fee</span>
                                <span class="font-semibold text-white">৳{{
                                    formatPrice(orderSummary.payment_processing_fee) }}</span>
                            </div>
                            <div v-if="orderSummary.tax > 0" class="flex justify-between text-sm">
                                <span class="text-slate-500">Tax</span>
                                <span class="font-semibold text-white">৳{{ formatPrice(orderSummary.tax) }}</span>
                            </div>
                            <div v-if="orderSummary.discount > 0" class="flex justify-between text-sm text-green-600">
                                <span>Discount</span>
                                <span class="font-semibold">-৳{{ formatPrice(orderSummary.discount) }}</span>
                            </div>
                            <!-- bKash Payment Breakdown -->
                            <div v-if="selectedPaymentMethod === 'bkash' && paymentAmount === 'partial'"
                                class="space-y-2 py-3 border-t border-white/10">
                                <div class="flex justify-between text-sm">
                                    <span class="text-primary font-semibold">Payment Now ({{
                                        checkoutSettings.min_payment_percentage_shop
                                    }}%)</span>
                                    <span class="font-bold text-primary">৳{{ formatPrice(bkashPaymentAmount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">On Delivery ({{ 100 -
                                        checkoutSettings.min_payment_percentage_shop }}%)</span>
                                    <span class="font-semibold text-slate-400">৳{{ formatPrice(orderSummary.total -
                                        bkashPaymentAmount)
                                    }}</span>
                                </div>
                            </div>
                            <div class="border-t border-white/10 pt-3 flex justify-between">
                                <span class="text-lg font-bold text-white">Total</span>
                                <span class="text-lg font-bold text-primary">৳{{ formatPrice(orderSummary.total)
                                    }}</span>
                            </div>
                            <div v-if="orderSummary.min_payment > 0"
                                class="text-xs text-slate-600 pt-2 border-t border-white/5">
                                Minimum payment: ৳{{ formatPrice(orderSummary.min_payment) }}
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button @click="placeOrder" :disabled="!isFormValid || processing"
                            class="w-full py-4 bg-primary text-slate-900 font-semibold rounded-none hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                            <span v-if="processing" class="flex items-center gap-2">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                Processing...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Lock class="w-5 h-5" />
                                Place Order
                            </span>
                        </button>

                        <p class="text-xs text-center text-slate-600 mt-4">
                            <Lock class="w-3 h-3 inline mr-1" />
                            Your payment information is secure and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';
import { MapPin, CreditCard, ShoppingCart, Lock, Upload, CheckCircle } from 'lucide-vue-next';
import axios from '../axios';
import { trackBeginCheckout, trackPurchase } from '../utils/analytics';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const shippingForm = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    postal_code: '',
    is_inside_city: true,
});

const selectedPaymentMethod = ref('');
const paymentMethods = ref([]);
const paymentMethodsLoading = ref(false);
const orderNotes = ref('');
const processing = ref(false);
const bdtCurrencyId = ref(1); // Default, will be fetched
const currentOrder = ref(null);
const paymentSlipFile = ref(null);
const paymentSlipPreview = ref(null);
const uploadingSlip = ref(false);
const orderSummary = ref({
    subtotal: 0,
    delivery_charge: 0,
    payment_processing_fee: 0,
    tax: 0,
    discount: 0,
    total: 0,
    min_payment: 0,
});

const couponCode = ref('');
const appliedCoupon = ref(null);
const applyingCoupon = ref(false);
const couponError = ref('');
const paymentAmount = ref(100); // 60 or 100

const checkoutSettings = ref({
    min_payment_percentage_shop: 60, // Default fallback
    delivery_charge_inside_city: 90,
    delivery_charge_outside_city: 150,
    delivery_charge_per_kg: 0,
    free_delivery_threshold: 0,
});

const bkashPaymentAmount = computed(() => {
    if (selectedPaymentMethod.value !== 'bkash') return 0;

    // Use dynamic percentage if partial payment selected
    if (paymentAmount.value === 'partial') {
        const percentage = parseFloat(checkoutSettings.value.min_payment_percentage_shop) || 60;
        return orderSummary.value.total * (percentage / 100);
    }
    return orderSummary.value.total; // Full payment
});

// Calculate total weight of cart
const cartWeight = computed(() => {
    return cartStore.items.reduce((total, item) => {
        // Assume default weight 1 if not set, or 0? 
        // Ideally should come from product.weight. 
        // If product has no weight, maybe 0.
        const weight = parseFloat(item.weight || 0);
        return total + (weight * item.quantity);
    }, 0);
});

const fetchCheckoutSettings = async () => {
    try {
        const response = await axios.get('/settings/checkout');
        checkoutSettings.value = { ...checkoutSettings.value, ...response.data };
        // Update default payment amount radio value if needed
        // Actually we use 'partial' string value instead of hardcoded 60 now
    } catch (error) {
        console.error('Error fetching checkout settings:', error);
    }
};


const isFormValid = computed(() => {
    const baseValid = (
        shippingForm.value.name &&
        shippingForm.value.phone &&
        shippingForm.value.email &&
        shippingForm.value.address &&
        shippingForm.value.city &&
        selectedPaymentMethod.value &&
        cartStore.items.length > 0
    );

    // For bank transfer, payment slip is required
    if (selectedPaymentMethod.value === 'bank_transfer') {
        return baseValid && paymentSlipFile.value !== null;
    }

    return baseValid;
});

const getProductImage = (item) => {
    if (item.image_url) return item.image_url;
    if (item.image) return item.image.startsWith('http') ? item.image : `/storage/${item.image}`;
    return '/assets/placeholder.webp';
};

const getItemPrice = (item) => {
    const priceStr = item.price || '0';
    return parseFloat(priceStr.replace(/[^\d.]/g, ''));
};

const formatPrice = (price) => {
    if (!price) return '0.00';
    return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const chargesBreakdown = ref([]);

const calculateCharges = async () => {
    // Calculate subtotal
    const subtotal = cartStore.items.reduce((total, item) => {
        return total + (getItemPrice(item) * item.quantity);
    }, 0);

    orderSummary.value.subtotal = subtotal;

    // Calculate charges (hub scope)
    try {
        const response = await axios.post('/charges/calculate', {
            base_amount: subtotal,
            currency_id: bdtCurrencyId.value,
            scope: 'hub',
            additional_data: {
                is_inside_city: shippingForm.value.is_inside_city,
                payment_method: selectedPaymentMethod.value,
                weight: cartWeight.value,
            },
        });

        orderSummary.value.delivery_charge = response.data.delivery_charge || 0;
        orderSummary.value.payment_processing_fee = response.data.payment_processing_fee || 0;
        chargesBreakdown.value = response.data.breakdown || [];

        // Calculate total (subtract coupon discount if applied)
        // Note: backend total_charges includes delivery and payment fee.
        // We need to match frontend total with backend grand_total logic.
        // Backend: grand_total = base_amount + total_charges (which has delivery + fee + hub charges)
        // Frontend total should match this minus discount.

        const totalCharges = response.data.total_charges || 0;
        const discountAmount = orderSummary.value.discount || 0;
        orderSummary.value.total = subtotal + totalCharges - discountAmount;

        // Calculate minimum payment
        const minPercentage = parseFloat(checkoutSettings.value.min_payment_percentage_shop) || 0;
        orderSummary.value.min_payment = (orderSummary.value.total * minPercentage) / 100;
    } catch (error) {
        console.error('Error calculating charges:', error);
        orderSummary.value.delivery_charge = 0;
        orderSummary.value.payment_processing_fee = 0;
        orderSummary.value.total = subtotal;
        chargesBreakdown.value = [];
    }
};

const fetchPaymentMethods = async () => {
    paymentMethodsLoading.value = true;
    try {
        const response = await axios.get('/payment-methods');
        paymentMethods.value = response.data || [];
        if (paymentMethods.value.length > 0) {
            selectedPaymentMethod.value = paymentMethods.value[0].type;
            calculateCharges();
        }
    } catch (error) {
        console.error('Error fetching payment methods:', error);
    } finally {
        paymentMethodsLoading.value = false;
    }
};

const applyCoupon = async () => {
    if (!couponCode.value.trim()) return;

    applyingCoupon.value = true;
    couponError.value = '';

    try {
        const productIds = cartStore.items.map(item => item.id).filter(id => id);
        const response = await axios.post('/coupons/apply', {
            code: couponCode.value.trim(),
            subtotal: orderSummary.value.subtotal,
            product_ids: productIds.length > 0 ? productIds : null,
        });

        if (response.data.valid) {
            appliedCoupon.value = response.data.coupon;
            orderSummary.value.discount = response.data.discount || 0;
            await calculateCharges(); // Recalculate total with discount
            couponCode.value = '';
        } else {
            couponError.value = response.data.message || 'Invalid coupon code';
        }
    } catch (error) {
        console.error('Error applying coupon:', error);
        couponError.value = error.response?.data?.message || 'Failed to apply coupon';
    } finally {
        applyingCoupon.value = false;
    }
};

const removeCoupon = () => {
    appliedCoupon.value = null;
    orderSummary.value.discount = 0;
    calculateCharges(); // Recalculate total without discount
    couponError.value = '';
};

const placeOrder = async () => {
    if (!isFormValid.value) return;

    processing.value = true;
    try {
        // Prepare order items
        const items = cartStore.items.map(item => ({
            product_id: item.id,
            product_name: item.name,
            price: getItemPrice(item),
            quantity: item.quantity,
            product_sku: item.sku || null,
            image: getProductImage(item),
            variant_data: item.variant ? JSON.stringify(item.variant) : null,
        }));

        // Prepare base order data
        const orderData = {
            user_id: authStore.user?.id,
            coupon_id: appliedCoupon.value?.id || null,
            items: items,
            subtotal: orderSummary.value.subtotal,
            shipping: orderSummary.value.delivery_charge,
            tax: orderSummary.value.tax || 0,
            discount: orderSummary.value.discount || 0,
            currency: 'BDT',
            payment_method: selectedPaymentMethod.value,
            shipping_address: `${shippingForm.value.address}, ${shippingForm.value.city}${shippingForm.value.postal_code ? ', ' + shippingForm.value.postal_code : ''}`,
            shipping_name: shippingForm.value.name,
            shipping_phone: shippingForm.value.phone,
            shipping_email: shippingForm.value.email,
            notes: orderNotes.value,
        };

        let response;
        // Use FormData only for bank transfer with payment slip
        if (selectedPaymentMethod.value === 'bank_transfer' && paymentSlipFile.value) {
            const formData = new FormData();
            Object.keys(orderData).forEach(key => {
                if (orderData[key] !== null && orderData[key] !== undefined) {
                    if (key === 'items') {
                        formData.append(key, JSON.stringify(orderData[key]));
                    } else {
                        formData.append(key, orderData[key]);
                    }
                }
            });
            formData.append('payment_slip', paymentSlipFile.value);

            response = await axios.post('/orders', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
        } else {
            // Regular JSON POST for other payment methods
            response = await axios.post('/orders', orderData);
        }
        const order = response.data;

        // Store current order for payment processing
        currentOrder.value = order;

        // Handle bKash payment
        const selectedMethod = paymentMethods.value.find(m => m.type === selectedPaymentMethod.value);
        if (selectedMethod?.is_online && selectedMethod?.type === 'bkash') {
            // Initiate bKash payment
            try {
                const paymentResponse = await axios.post('/payments/bkash/initiate', {
                    order_id: order.id,
                    amount: bkashPaymentAmount.value,
                    payment_type: paymentAmount.value === 'partial' ? 'partial' : 'full',
                });

                if (paymentResponse.data.bkashURL) {
                    // Redirect to bKash payment page
                    window.location.href = paymentResponse.data.bkashURL;
                } else {
                    throw new Error('Failed to get bKash payment URL');
                }
            } catch (error) {
                console.error('Error initiating bKash payment:', error);
                alert('Failed to initiate payment. Please try again.');
                processing.value = false;
                return;
            }
        } else {
            // For manual payment methods, clear cart and redirect
            trackPurchase(order);
            cartStore.clearCart();

            // Redirect to order confirmation
            router.push(`/dashboard/orders/${order.id}`);
        }
    } catch (error) {
        console.error('Error placing order:', error);
        alert(error.response?.data?.message || 'Failed to place order. Please try again.');
        processing.value = false;
    }
};

const handlePaymentSlipUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }
        paymentSlipFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            paymentSlipPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removePaymentSlip = () => {
    paymentSlipFile.value = null;
    paymentSlipPreview.value = null;
    // Reset file input
    if (paymentSlipInput.value) {
        paymentSlipInput.value.value = '';
    }
};

const uploadPaymentSlip = async () => {
    if (!paymentSlipFile.value || !currentOrder.value) return;

    uploadingSlip.value = true;
    try {
        const formData = new FormData();
        formData.append('payment_slip', paymentSlipFile.value);
        formData.append('order_id', currentOrder.value.id);

        const response = await axios.post(`/orders/${currentOrder.value.id}/upload-payment-slip`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        currentOrder.value = response.data.order;
        paymentSlipFile.value = null;
        paymentSlipPreview.value = null;

        // Clear cart after successful upload
        cartStore.clearCart();

        alert('Payment slip uploaded successfully! Your order is pending verification.');

        // Redirect to order page after a delay
        setTimeout(() => {
            router.push(`/dashboard/orders/${currentOrder.value.id}`);
        }, 2000);
    } catch (error) {
        console.error('Error uploading payment slip:', error);
        alert(error.response?.data?.message || 'Failed to upload payment slip. Please try again.');
    } finally {
        uploadingSlip.value = false;
    }
};

const getPaymentSlipUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
};

const fetchBDTCurrency = async () => {
    try {
        const response = await axios.get('/currencies', { params: { all: true } });
        const currencies = response.data.data || response.data || [];
        const bdt = currencies.find(c => c.code === 'BDT');
        if (bdt) {
            bdtCurrencyId.value = bdt.id;
        }
    } catch (error) {
        console.error('Error fetching currencies:', error);
    }
};

// Watch for changes to recalculate charges
watch(
    [() => shippingForm.value.is_inside_city, () => selectedPaymentMethod.value, () => cartStore.items],
    () => {
        if (bdtCurrencyId.value) {
            calculateCharges();
        }
    },
    { deep: true }
);

// Handle payment callback from URL params
const handlePaymentCallback = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const paymentStatus = urlParams.get('payment');
    const orderId = urlParams.get('order_id');

    if (paymentStatus === 'success' && orderId) {
        // Clear cart on successful payment
        cartStore.clearCart();
        // Redirect to order page
        router.push(`/dashboard/orders/${orderId}`);
    } else if (paymentStatus === 'failed' || paymentStatus === 'error') {
        alert('Payment failed. Please try again.');
    }
};

// Load user data if available
onMounted(async () => {
    if (authStore.user) {
        shippingForm.value.email = authStore.user.email || '';
        shippingForm.value.name = authStore.user.name || '';
    }
    await fetchBDTCurrency();
    await fetchCheckoutSettings();
    await calculateCharges();
    fetchPaymentMethods();
    handlePaymentCallback();

    if (cartStore.items.length > 0) {
        trackBeginCheckout(cartStore.items, orderSummary.value.total);
    }
});
</script>
