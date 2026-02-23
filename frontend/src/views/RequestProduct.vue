<template>
    <div class="min-h-screen bg-background py-8 px-4 sm:px-6 lg:px-8 font-sans text-slate-200">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-3xl md:text-4xl font-serif font-bold text-white mb-3 tracking-wide">Request Product</h1>
                <p class="text-base text-slate-400 max-w-2xl mx-auto font-light">
                    Global shopping, delivered. We handle purchase, shipping, and customs.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column: Wizard Steps (8/12) -->
                <div class="lg:col-span-8">
                    <!-- Progress Bar -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-2">
                            <div v-for="step in 3" :key="step" class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-300"
                                    :class="getStepCircleClass(step)">
                                    <template v-if="step < currentStep">
                                        <Check class="w-5 h-5" />
                                    </template>
                                    <template v-else>{{ step }}</template>
                                </div>
                                <span class="ml-2 text-sm font-medium hidden sm:block"
                                    :class="step === currentStep ? 'text-white' : 'text-slate-500'">
                                    {{ getStepLabel(step) }}
                                </span>
                            </div>
                        </div>
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-primary transition-all duration-500 ease-out"
                                :style="{ width: `${((currentStep - 1) / 2) * 100}%` }"></div>
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div
                        class="bg-surface border border-white/5 shadow-2xl overflow-hidden rounded-xl transition-all duration-500">

                        <!-- Step 1: All Inputs (Product & Shipping) -->
                        <div v-if="currentStep === 1" class="p-8 space-y-8 animate-fadeIn">

                            <!-- Product Info Section -->
                            <div class="space-y-6">
                                <h2 class="text-xl font-serif font-bold text-white border-b border-white/10 pb-2">
                                    Product Information</h2>

                                <!-- Product URL -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Product
                                        URL <span class="text-red-500">*</span></label>
                                    <div class="relative group/input">
                                        <LinkIcon
                                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 group-focus-within/input:text-primary transition-colors" />
                                        <input v-model="form.url" type="url" required
                                            placeholder="https://store.com/product"
                                            class="w-full pl-11 pr-4 py-4 bg-background/50 border border-white/10 text-white placeholder-slate-600 rounded-lg focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Price -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Price
                                            <span class="text-red-500">*</span></label>
                                        <div class="flex">
                                            <div class="relative flex-1 group/input">
                                                <span
                                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-serif">
                                                    {{ selectedCurrency?.symbol || '$' }}
                                                </span>
                                                <input v-model.number="form.price" type="number" step="0.01" min="0"
                                                    required
                                                    class="w-full pl-10 pr-4 py-4 bg-background/50 border border-white/10 rounded-l-lg text-white placeholder-slate-600 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm" />
                                            </div>
                                            <select v-model="form.currency" @change="onCurrencyChange"
                                                class="w-24 px-4 py-4 bg-background/50 border border-white/10 border-l-0 rounded-r-lg text-white font-bold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm bg-surface">
                                                <option value="" disabled>Select</option>
                                                <option v-for="currency in currencies" :key="currency.id"
                                                    :value="currency.code">
                                                    {{ currency.code }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Quantity -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Quantity
                                            <span class="text-red-500">*</span></label>
                                        <input v-model.number="form.quantity" type="number" min="1" required
                                            class="w-full px-4 py-4 bg-background/50 border border-white/10 rounded-lg text-white placeholder-slate-600 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm" />
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping & Calculations Section -->
                            <div class="space-y-6">
                                <h2 class="text-xl font-serif font-bold text-white border-b border-white/10 pb-2">
                                    Shipping Details</h2>

                                <!-- Location -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Delivery
                                        Location <span class="text-red-500">*</span></label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <button @click="setCity(true)"
                                            :class="form.is_inside_city ? 'border-primary bg-primary/10 ring-1 ring-primary' : 'border-white/10 bg-background/30 hover:bg-white/5'"
                                            class="p-4 border rounded-xl flex flex-col items-center gap-3 transition-all group">
                                            <CheckCircle
                                                :class="form.is_inside_city ? 'text-primary' : 'text-slate-600'"
                                                class="w-6 h-6" />
                                            <span class="font-bold text-sm text-white">Inside Dhaka</span>
                                        </button>
                                        <button @click="setCity(false)"
                                            :class="!form.is_inside_city ? 'border-primary bg-primary/10 ring-1 ring-primary' : 'border-white/10 bg-background/30 hover:bg-white/5'"
                                            class="p-4 border rounded-xl flex flex-col items-center gap-3 transition-all group">
                                            <CheckCircle
                                                :class="!form.is_inside_city ? 'text-primary' : 'text-slate-600'"
                                                class="w-6 h-6" />
                                            <span class="font-bold text-sm text-white">Outside Dhaka</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Weight -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Approx.
                                            Weight </label>
                                        <input v-model.number="form.weight" type="number" step="0.01" min="0"
                                            placeholder="0.5"
                                            class="w-full px-4 py-4 bg-background/50 border border-white/10 rounded-lg text-white placeholder-slate-600 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm" />
                                    </div>
                                    <!-- Seller Shipping -->
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Seller
                                            Shipping Fee</label>
                                        <div class="relative group/input">
                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-serif">{{
                                                    selectedCurrency?.symbol || '$' }}</span>
                                            <input v-model.number="form.declared_shipping_cost" type="number"
                                                step="0.01" min="0" placeholder="0.00"
                                                class="w-full pl-10 pr-4 py-4 bg-background/50 border border-white/10 rounded-lg text-white placeholder-slate-600 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all text-sm" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Estimated Cost (View Only) -->
                        <div v-if="currentStep === 2" class="p-8 space-y-6 animate-fadeIn">
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-serif font-bold text-white mb-2">Estimated Cost</h2>
                                <p class="text-slate-400">Based on the details provided</p>
                            </div>

                            <div class="bg-white/5 border border-white/10 rounded-2xl p-8 max-w-lg mx-auto">
                                <div v-if="costBreakdown" class="space-y-6">
                                    <!-- Product Cost -->
                                    <div class="flex justify-between items-center text-slate-300">
                                        <span>Product Price ({{ form.quantity }}x)</span>
                                        <div class="text-right">
                                            <div class="font-medium text-white">{{ selectedCurrency?.symbol }}{{
                                                (form.price * form.quantity).toFixed(2) }}</div>
                                            <div v-if="selectedCurrency && !selectedCurrency.is_base"
                                                class="text-xs text-slate-500">
                                                ≈ ৳{{ costBreakdown.product_total.toFixed(2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Charges -->
                                    <div class="space-y-3 pt-4 border-t border-white/10 text-slate-300">
                                        <!-- Breakdown Loop -->
                                        <template v-if="costBreakdown.breakdown && costBreakdown.breakdown.length > 0">
                                            <div v-for="(charge, index) in costBreakdown.breakdown" :key="index"
                                                class="flex justify-between">
                                                <span>
                                                    {{ charge.charge }}
                                                    <span v-if="charge.currency !== 'BDT'"
                                                        class="text-xs text-slate-500 ml-1">
                                                        ({{ charge.currency }} {{ charge.amount_in_currency }})
                                                    </span>
                                                </span>
                                                <span>৳{{ charge.amount_in_bdt.toFixed(2) }}</span>
                                            </div>
                                        </template>
                                        <div v-else class="flex justify-between">
                                            <span>Platform Fees & Charges</span>
                                            <span>৳{{ (costBreakdown.total_charges).toFixed(2) }}</span>
                                        </div>
                                        <div v-if="costBreakdown.declared_shipping > 0" class="flex justify-between">
                                            <span>Seller Shipping (Domestic)</span>
                                            <span>৳{{ costBreakdown.declared_shipping.toFixed(2) }}</span>
                                        </div>
                                    </div>

                                    <!-- Grand Total -->
                                    <div class="pt-6 border-t border-white/10 flex justify-between items-baseline">
                                        <span class="text-white font-bold text-lg">Total Estimate</span>
                                        <span class="text-3xl font-bold text-primary font-serif">৳{{
                                            costBreakdown.grand_total.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="max-w-lg mx-auto text-center">
                                <p
                                    class="text-sm text-slate-500 bg-blue-500/10 border border-blue-500/20 p-4 rounded-lg">
                                    <Info class="w-4 h-4 inline-block mr-1 -mt-0.5 text-blue-400" />
                                    This is an estimate. Final amount including tax and international shipping will be
                                    confirmed by Admin upon approval.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3: Review & Submit -->
                        <div v-if="currentStep === 3" class="p-8 space-y-6 animate-fadeIn">
                            <h2 class="text-2xl font-serif font-bold text-white mb-6">Confirm Review</h2>

                            <div class="bg-white/5 rounded-xl border border-white/10 p-6 space-y-4">
                                <div class="flex justify-between border-b border-white/10 pb-4">
                                    <div class="text-slate-400 text-sm">Product URL</div>
                                    <div class="text-white text-sm font-medium truncate w-1/2 text-right">{{ form.url }}
                                    </div>
                                </div>
                                <div class="flex justify-between border-b border-white/10 pb-4">
                                    <div class="text-slate-400 text-sm">Configuration</div>
                                    <div class="text-white text-sm font-medium">
                                        Weight: {{ form.weight || 'N/A' }} kg | Location: {{ form.is_inside_city ?
                                            'Inside Dhaka' : 'Outside Dhaka' }}
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <div class="text-slate-400 text-sm">Est. Total Cost</div>
                                    <div class="text-primary font-bold text-lg">৳{{
                                        costBreakdown?.grand_total?.toFixed(2) }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 bg-primary/10 border border-primary/20 p-4 rounded-lg">
                                <CheckCircle class="w-6 h-6 text-primary shrink-0" />
                                <div class="text-sm text-primary/80">
                                    By placing this request, you agree to our terms. An admin will review this request
                                    shortly.
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="p-8 border-t border-white/5 flex justify-between bg-black/20">
                            <button v-if="currentStep > 1" @click="currentStep--"
                                class="px-6 py-3 text-slate-400 hover:text-white font-medium transition-colors">
                                Back
                            </button>
                            <div v-else class="w-1"></div> <!-- Spacer -->

                            <!-- Step 1: Calculate Button -->
                            <button v-if="currentStep === 1" @click="handleCalculateAndNext"
                                :disabled="!isStepValid(1) || calculationLoading"
                                class="px-8 py-3 bg-primary text-black font-bold rounded-lg hover:bg-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <span v-if="calculationLoading"
                                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                {{ calculationLoading ? 'Calculating...' : 'Calculate Estimate' }}
                            </button>

                            <!-- Step 2: Next Button -->
                            <button v-if="currentStep === 2" @click="nextStep" :disabled="!isStepValid(currentStep)"
                                class="px-8 py-3 bg-primary text-black font-bold rounded-lg hover:bg-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Continue to Review
                            </button>

                            <!-- Step 3: Submit Button -->
                            <button v-if="currentStep === 3" @click="submitRequest" :disabled="submitting"
                                class="px-8 py-3 bg-primary text-slate-900 font-bold rounded-lg hover:bg-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <span v-if="submitting"
                                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-slate-900"></span>
                                {{ submitting ? 'Submitting...' : 'Place Order' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Info Cards (4/12) -->
                <div class="lg:col-span-4 space-y-6">

                    <!-- How It Works -->
                    <div class="bg-surface border border-white/5 rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-serif font-bold text-white mb-6 border-b border-white/10 pb-4">How It
                            Works</h3>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div
                                    class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-sm font-bold text-primary shrink-0">
                                    1</div>
                                <div>
                                    <h4 class="text-white font-medium mb-1">Request Product</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">Fill in the product and shipping
                                        details to get an estimated cost.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-sm font-bold text-primary shrink-0">
                                    2</div>
                                <div>
                                    <h4 class="text-white font-medium mb-1">Admin Approval</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">Our team reviews your request and
                                        confirms the final price.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-sm font-bold text-primary shrink-0">
                                    3</div>
                                <div>
                                    <h4 class="text-white font-medium mb-1">Payment & Delivery</h4>
                                    <p class="text-xs text-slate-400 leading-relaxed">Pay securely via bKash or Bank
                                        Transfer. We handle the rest!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Need Help / Support Card -->
                    <div
                        class="bg-gradient-to-br from-primary/10 to-surface border border-primary/20 rounded-xl shadow-lg p-6 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-lg font-serif font-bold text-white mb-2">Need Assistance?</h3>
                            <p class="text-sm text-slate-400 mb-4">Can't find what you're looking for or have questions
                                about shipping?</p>
                            <a href="#"
                                class="inline-flex items-center text-sm font-bold text-primary hover:text-white transition-colors">
                                Contact Support
                                <d class="w-4 h-4 ml-1" />
                            </a>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-primary/20 blur-2xl rounded-full"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Link as LinkIcon, Check, CheckCircle, Info } from 'lucide-vue-next';
import axios from '../axios';

import { useToast } from 'vue-toastification';
import { trackPurchase, trackBeginCheckout as trackBeginCheckoutGA4 } from '../utils/analytics';

const router = useRouter();
const toast = useToast();

// State
const currentStep = ref(1);
const form = ref({
    url: '',
    price: null,
    quantity: 1,
    currency: '',
    weight: null,
    declared_shipping_cost: null,
    is_inside_city: true
});

const currencies = ref([]);
const selectedCurrency = ref(null);
const costBreakdown = ref(null);
const calculationLoading = ref(false);
const submitting = ref(false);

// Methods
const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/currencies', { params: { all: true } });
        currencies.value = response.data.data || response.data;
        const bdt = currencies.value.find(c => c.code === 'BDT');
        if (bdt) {
            form.value.currency = 'BDT';
            selectedCurrency.value = bdt;
        } else if (currencies.value.length > 0) {
            form.value.currency = currencies.value[0].code;
            selectedCurrency.value = currencies.value[0];
        }
    } catch (e) {
        console.error(e);
    }
};

const onCurrencyChange = () => {
    selectedCurrency.value = currencies.value.find(c => c.code === form.value.currency);
};

const setCity = (val) => {
    form.value.is_inside_city = val;
};

// Merged Step 1 Calculation
const calculateEstimate = async () => {
    // Moved validation to handleCalculateAndNext for better UX feedback

    calculationLoading.value = true;
    try {
        const productTotal = form.value.price * form.value.quantity;
        let baseAmount = productTotal;
        if (selectedCurrency.value && !selectedCurrency.value.is_base) {
            baseAmount = productTotal * selectedCurrency.value.rate_to_base;
        }

        const response = await axios.post('/charges/calculate', {
            base_amount: baseAmount,
            currency_id: selectedCurrency.value?.id,
            scope: 'request',
            additional_data: {
                weight: form.value.weight || 0,
                is_inside_city: form.value.is_inside_city,
            }
        });

        let declaredShipping = 0;
        if (form.value.declared_shipping_cost > 0) {
            declaredShipping = form.value.declared_shipping_cost;
            if (selectedCurrency.value && !selectedCurrency.value.is_base) {
                declaredShipping *= selectedCurrency.value.rate_to_base;
            }
        }

        costBreakdown.value = {
            product_total: baseAmount,
            total_charges: response.data.total_charges,
            delivery_charge: response.data.delivery_charge,
            payment_processing_fee: response.data.payment_processing_fee,
            declared_shipping: declaredShipping,
            grand_total: baseAmount + response.data.total_charges + declaredShipping,
            breakdown: response.data.breakdown || [],
        };
        return true;
    } catch (e) {
        console.error(e);
        toast.error('Failed to calculate estimate. Please check your inputs.');
        return false;
    } finally {
        calculationLoading.value = false;
    }
};

const handleCalculateAndNext = async () => {
    // Validate inputs
    if (!form.value.url) {
        toast.error('Please enter a Product URL.');
        return;
    }
    try {
        new URL(form.value.url);
    } catch (_) {
        toast.error('Please enter a valid URL (e.g., https://example.com/product).');
        return;
    }

    if (!form.value.price || form.value.price <= 0) {
        toast.error('Please enter a valid Price.');
        return;
    }
    if (!form.value.quantity || form.value.quantity <= 0) {
        toast.error('Please enter a valid Quantity.');
        return;
    }
    if (!form.value.currency) {
        toast.error('Please select a Currency.');
        return;
    }

    if (await calculateEstimate()) {
        currentStep.value = 2; // Go to Estimate Step

        // Track Begin Checkout for the request
        trackBeginCheckoutGA4([{
            product_id: 'request-' + Date.now(),
            product_name: 'Product Request: ' + form.value.url,
            price: costBreakdown.value?.grand_total || 0,
            quantity: form.value.quantity
        }], costBreakdown.value?.grand_total || 0);
    }
};

// Wizard Logic
const getStepLabel = (step) => {
    switch (step) {
        case 1: return 'Details';
        case 2: return 'Estimate';
        case 3: return 'Confirm';
        default: return '';
    }
};

const getStepCircleClass = (step) => {
    if (step < currentStep.value) return 'bg-primary text-slate-900 border-primary';
    if (step === currentStep.value) return 'bg-primary text-slate-900 border-primary ring-4 ring-primary/20';
    return 'bg-transparent text-slate-500 border-white/10';
};

const isStepValid = (step) => {
    if (step === 1) return form.value.url && form.value.price > 0 && form.value.quantity > 0 && form.value.currency;
    return true;
};

const nextStep = () => {
    currentStep.value++;
};

const submitRequest = async () => {
    submitting.value = true;
    try {
        const payload = {
            ...form.value,
            payment_status: 'pending',
            total_amount_bdt: costBreakdown.value?.grand_total || 0,
            delivery_charge: costBreakdown.value?.delivery_charge || 0,
            payment_processing_fee: costBreakdown.value?.payment_processing_fee || 0,
            charges_breakdown: costBreakdown.value?.breakdown || [],
            // Extract tax if possible, or leave null for admin to set
        };
        const response = await axios.post('/product-requests', payload);
        const requestData = response.data.data || response.data;

        trackPurchase({
            order_number: requestData.request_number || ('REQ-' + requestData.id),
            total: costBreakdown.value?.grand_total || 0,
            currency: 'BDT',
            items: [{
                product_id: requestData.id,
                product_name: form.value.url,
                price: costBreakdown.value?.grand_total / form.value.quantity,
                quantity: form.value.quantity
            }]
        });

        toast.success('Product request submitted successfully!');
        router.push('/dashboard/requests');
    } catch (e) {
        toast.error(e.response?.data?.message || 'Error submitting request');
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    fetchCurrencies();
});
</script>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
