<template>
    <div class="min-h-screen bg-gray-50 pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
                <p class="text-gray-600 mt-2">Complete your order securely</p>
            </div>

            <!-- Empty Cart State -->
            <div v-if="cartStore.items.length === 0" class="bg-white rounded-2xl shadow-md border border-gray-200 p-12 text-center">
                <ShoppingCart class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Add some products to your cart to continue</p>
                <router-link
                    to="/shop"
                    class="inline-block px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-all"
                >
                    Continue Shopping
                </router-link>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Shipping Address -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <MapPin class="w-6 h-6 text-primary" />
                            Shipping Address
                        </h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="shippingForm.name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        placeholder="John Doe"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="shippingForm.phone"
                                        type="tel"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        placeholder="+880 1XXX-XXXXXX"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="shippingForm.email"
                                    type="email"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="john@example.com"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Address <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="shippingForm.address"
                                    rows="3"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="House/Flat No, Road, Area"
                                ></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        City <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="shippingForm.city"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        placeholder="Dhaka"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Postal Code
                                    </label>
                                    <input
                                        v-model="shippingForm.postal_code"
                                        type="text"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        placeholder="1200"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Delivery Location <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="shippingForm.is_inside_city"
                                        required
                                        @change="calculateCharges"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent bg-white"
                                    >
                                        <option :value="true">Inside City</option>
                                        <option :value="false">Outside City</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <CreditCard class="w-6 h-6 text-primary" />
                            Payment Method
                        </h2>
                        <div v-if="paymentMethodsLoading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        </div>
                        <div v-else-if="paymentMethods.length > 0" class="space-y-3">
                            <label
                                v-for="method in paymentMethods"
                                :key="method.id"
                                class="flex items-center gap-4 p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-all"
                                :class="selectedPaymentMethod === method.type ? 'border-primary bg-primary/5' : 'border-gray-200'"
                            >
                                <input
                                    type="radio"
                                    v-model="selectedPaymentMethod"
                                    :value="method.type"
                                    @change="calculateCharges"
                                    class="w-5 h-5 text-primary focus:ring-primary"
                                />
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">{{ method.name }}</div>
                                    <div v-if="method.description" class="text-sm text-gray-600 mt-1">
                                        {{ method.description }}
                                    </div>
                                    <div v-if="method.fee_percentage || method.fee" class="text-xs text-gray-500 mt-1">
                                        <span v-if="method.fee_percentage">Processing fee: {{ method.fee_percentage }}%</span>
                                        <span v-else-if="method.fee">Processing fee: ৳{{ method.fee }}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No payment methods available
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Order Notes (Optional)</h2>
                        <textarea
                            v-model="orderNotes"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Special instructions for delivery..."
                        ></textarea>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>

                        <!-- Cart Items -->
                        <div class="space-y-4 mb-6 max-h-64 overflow-y-auto">
                            <div
                                v-for="item in cartStore.items"
                                :key="item.id"
                                class="flex items-center gap-3 pb-4 border-b border-gray-100 last:border-0"
                            >
                                <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200 flex-shrink-0">
                                    <img
                                        :src="getProductImage(item)"
                                        :alt="item.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ item.name }}</h3>
                                    <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                                    <p class="text-sm font-bold text-primary mt-1">
                                        ৳{{ formatPrice(getItemPrice(item) * item.quantity) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold text-gray-900">৳{{ formatPrice(orderSummary.subtotal) }}</span>
                            </div>
                            <div v-if="orderSummary.delivery_charge > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Charge</span>
                                <span class="font-semibold text-gray-900">৳{{ formatPrice(orderSummary.delivery_charge) }}</span>
                            </div>
                            <div v-if="orderSummary.payment_processing_fee > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Payment Processing Fee</span>
                                <span class="font-semibold text-gray-900">৳{{ formatPrice(orderSummary.payment_processing_fee) }}</span>
                            </div>
                            <div v-if="orderSummary.tax > 0" class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-semibold text-gray-900">৳{{ formatPrice(orderSummary.tax) }}</span>
                            </div>
                            <div v-if="orderSummary.discount > 0" class="flex justify-between text-sm text-green-600">
                                <span>Discount</span>
                                <span class="font-semibold">-৳{{ formatPrice(orderSummary.discount) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 flex justify-between">
                                <span class="text-lg font-bold text-gray-900">Total</span>
                                <span class="text-lg font-bold text-primary">৳{{ formatPrice(orderSummary.total) }}</span>
                            </div>
                            <div v-if="orderSummary.min_payment > 0" class="text-xs text-gray-500 pt-2 border-t border-gray-100">
                                Minimum payment: ৳{{ formatPrice(orderSummary.min_payment) }}
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button
                            @click="placeOrder"
                            :disabled="!isFormValid || processing"
                            class="w-full py-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                        >
                            <span v-if="processing" class="flex items-center gap-2">
                                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                Processing...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <Lock class="w-5 h-5" />
                                Place Order
                            </span>
                        </button>

                        <p class="text-xs text-center text-gray-500 mt-4">
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
import { MapPin, CreditCard, ShoppingCart, Lock } from 'lucide-vue-next';
import axios from '../axios';

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
const orderSummary = ref({
    subtotal: 0,
    delivery_charge: 0,
    payment_processing_fee: 0,
    tax: 0,
    discount: 0,
    total: 0,
    min_payment: 0,
});

const isFormValid = computed(() => {
    return (
        shippingForm.value.name &&
        shippingForm.value.phone &&
        shippingForm.value.email &&
        shippingForm.value.address &&
        shippingForm.value.city &&
        selectedPaymentMethod.value &&
        cartStore.items.length > 0
    );
});

const getProductImage = (item) => {
    if (item.image_url) return item.image_url;
    if (item.image) return item.image.startsWith('http') ? item.image : `/storage/${item.image}`;
    return '/assets/placeholder.png';
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

const calculateCharges = async () => {
    // Calculate subtotal
    const subtotal = cartStore.items.reduce((total, item) => {
        return total + (getItemPrice(item) * item.quantity);
    }, 0);

    orderSummary.value.subtotal = subtotal;

    // Calculate delivery charge and payment fee
    try {
        const response = await axios.post('/charges/calculate', {
            base_amount: subtotal,
            currency_id: bdtCurrencyId.value,
            additional_data: {
                is_inside_city: shippingForm.value.is_inside_city,
                payment_method: selectedPaymentMethod.value,
                weight: 0, // Could calculate from products if needed
            },
        });

        orderSummary.value.delivery_charge = response.data.delivery_charge || 0;
        orderSummary.value.payment_processing_fee = response.data.payment_processing_fee || 0;

        // Calculate total
        const totalCharges = response.data.total_charges || 0;
        orderSummary.value.total = subtotal + totalCharges;

        // Calculate minimum payment
        const minPaymentPercentage = 0; // Get from settings if needed
        orderSummary.value.min_payment = (orderSummary.value.total * minPaymentPercentage) / 100;
    } catch (error) {
        console.error('Error calculating charges:', error);
        orderSummary.value.delivery_charge = 0;
        orderSummary.value.payment_processing_fee = 0;
        orderSummary.value.total = subtotal;
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

        // Prepare order data
        const orderData = {
            user_id: authStore.user?.id,
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

        const response = await axios.post('/orders', orderData);

        // Clear cart
        cartStore.clearCart();

        // Redirect to order confirmation
        router.push(`/dashboard/orders/${response.data.id || response.data.order?.id}`);
    } catch (error) {
        console.error('Error placing order:', error);
        alert(error.response?.data?.message || 'Failed to place order. Please try again.');
    } finally {
        processing.value = false;
    }
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

// Load user data if available
onMounted(() => {
    if (authStore.user) {
        shippingForm.value.email = authStore.user.email || '';
        shippingForm.value.name = authStore.user.name || '';
    }
    fetchBDTCurrency().then(() => {
        calculateCharges();
    });
    fetchPaymentMethods();
});
</script>

