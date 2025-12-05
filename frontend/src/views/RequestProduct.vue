<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                    <Link class="w-8 h-8 text-primary" />
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-2">Request Any Product</h1>
                <p class="text-base text-slate-600 max-w-2xl mx-auto">
                    From anywhere in the world, delivered to your doorstep. Fill out the form below to get started.
                </p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-6">
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-primary to-primary/90 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Product Request Form</h2>
                    <p class="text-white/90 text-xs mt-1">Please provide accurate information for faster processing</p>
                </div>

                <form @submit.prevent="submitRequest" class="p-6">
                    <!-- Step 1: Product Information -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center justify-center w-8 h-8 bg-primary/10 text-primary rounded-full font-bold text-sm">
                                1
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Product Information</h3>
                        </div>

                        <div class="space-y-4 pl-12">
                            <!-- Product URL -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Product URL <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <Link class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                    <input 
                                        v-model="form.url" 
                                        type="url" 
                                        required 
                                        placeholder="https://amazon.com/product-link"
                                        class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                                    />
                                </div>
                                <p class="mt-2 text-xs text-slate-500 flex items-center gap-1">
                                    <Globe class="w-3 h-3" />
                                    Supports Amazon, eBay, AliExpress, and most online stores
                                </p>
                            </div>

                            <!-- Price & Currency -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Product Price <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">
                                            {{ selectedCurrency?.symbol || '$' }}
                                        </span>
                                        <input 
                                            v-model.number="form.price" 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            required 
                                            placeholder="0.00"
                                            @input="calculateEstimate"
                                            class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Currency <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        v-model="form.currency"
                                        @change="onCurrencyChange"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white"
                                    >
                                        <option value="">Select Currency</option>
                                        <option v-for="currency in currencies" :key="currency.id" :value="currency.code">
                                            {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
                                        </option>
                                    </select>
                                    <p v-if="selectedCurrency && !selectedCurrency.is_base" class="mt-1 text-xs text-slate-500">
                                        Rate: 1 {{ selectedCurrency.code }} = {{ selectedCurrency.rate_to_base }} BDT
                                    </p>
                                </div>
                            </div>

                            <!-- Quantity & Weight -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input 
                                            v-model.number="form.quantity" 
                                            type="number" 
                                            min="1" 
                                            required 
                                            placeholder="1"
                                            @input="calculateEstimate"
                                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Weight (kg) <span class="text-xs font-normal text-slate-500">Optional</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">kg</span>
                                        <input 
                                            v-model.number="form.weight" 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            placeholder="0.00"
                                            @input="calculateEstimate"
                                            class="w-full px-4 pr-12 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                                        />
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">Required for accurate shipping calculation</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-6"></div>

                    <!-- Step 2: Shipping & Delivery -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center justify-center w-8 h-8 bg-primary/10 text-primary rounded-full font-bold text-sm">
                                2
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Shipping & Delivery</h3>
                        </div>

                        <div class="space-y-4 pl-12">
                            <!-- Shipping Cost -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Declared Shipping Cost <span class="text-xs font-normal text-slate-500">(Optional)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">
                                        {{ selectedCurrency?.symbol || '$' }}
                                    </span>
                                    <input 
                                        v-model.number="form.declared_shipping_cost" 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        placeholder="0.00"
                                        @input="calculateEstimate"
                                        class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                                    />
                                </div>
                                <p class="mt-2 text-xs text-slate-500">
                                    If the seller has provided shipping cost information
                                </p>
                            </div>

                            <!-- Delivery Location -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">
                                    Delivery Location <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <button
                                        type="button"
                                        @click="form.is_inside_city = true"
                                        :class="form.is_inside_city 
                                            ? 'border-2 border-primary bg-primary/5' 
                                            : 'border-2 border-gray-200 hover:border-gray-300'"
                                        class="p-4 rounded-lg transition-all text-left group"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div :class="form.is_inside_city 
                                                ? 'bg-primary text-white' 
                                                : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200'"
                                                class="w-5 h-5 rounded-full flex items-center justify-center transition-all">
                                                <CheckCircle v-if="form.is_inside_city" class="w-3 h-3" />
                                            </div>
                                            <div>
                                                <div class="font-semibold text-slate-900">Inside City</div>
                                                <div class="text-xs text-slate-500 mt-0.5">Dhaka city limits</div>
                                            </div>
                                        </div>
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.is_inside_city = false"
                                        :class="!form.is_inside_city 
                                            ? 'border-2 border-primary bg-primary/5' 
                                            : 'border-2 border-gray-200 hover:border-gray-300'"
                                        class="p-4 rounded-lg transition-all text-left group"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div :class="!form.is_inside_city 
                                                ? 'bg-primary text-white' 
                                                : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200'"
                                                class="w-5 h-5 rounded-full flex items-center justify-center transition-all">
                                                <CheckCircle v-if="!form.is_inside_city" class="w-3 h-3" />
                                            </div>
                                            <div>
                                                <div class="font-semibold text-slate-900">Outside City</div>
                                                <div class="text-xs text-slate-500 mt-0.5">Outside Dhaka</div>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Preferred Payment Method <span class="text-xs font-normal text-slate-500">(Optional)</span>
                                </label>
                                <select 
                                    v-model="form.payment_method"
                                    @change="calculateEstimate"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white"
                                >
                                    <option value="">Select Payment Method</option>
                                    <option v-for="method in paymentMethods" :key="method.id" :value="method.type">
                                        {{ method.name }} {{ method.sub_type ? `(${method.sub_type})` : '' }}
                                    </option>
                                </select>
                                <p class="mt-2 text-xs text-slate-500">
                                    Delivery charges may vary based on payment method
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-6"></div>

                    <!-- Step 3: Cost Summary -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center justify-center w-8 h-8 bg-primary/10 text-primary rounded-full font-bold text-sm">
                                3
                            </div>
                            <h3 class="text-lg font-bold text-slate-900">Cost Summary</h3>
                        </div>

                        <div class="pl-12">
                            <div v-if="calculationLoading" class="p-6 bg-gray-50 rounded-lg border border-gray-200 text-center">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary mb-2"></div>
                                <p class="text-sm text-gray-600">Calculating charges...</p>
                            </div>
                            <div v-else-if="costBreakdown" class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-200 p-4">
                                <div class="space-y-3 mb-4">
                                    <!-- Product Cost -->
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Product Cost:</span>
                                        <div class="text-right">
                                            <div class="font-medium">{{ selectedCurrency?.symbol || '$' }}{{ (form.price * form.quantity).toFixed(2) }}</div>
                                            <div v-if="selectedCurrency && !selectedCurrency.is_base" class="text-xs text-gray-500 mt-0.5">
                                                = ৳{{ costBreakdown.product_total.toFixed(2) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Declared Shipping -->
                                    <div v-if="form.declared_shipping_cost > 0" class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Declared Shipping:</span>
                                        <div class="text-right">
                                            <div class="font-medium">{{ selectedCurrency?.symbol || '$' }}{{ form.declared_shipping_cost.toFixed(2) }}</div>
                                            <div v-if="selectedCurrency && !selectedCurrency.is_base && costBreakdown.declared_shipping" class="text-xs text-gray-500 mt-0.5">
                                                = ৳{{ costBreakdown.declared_shipping.toFixed(2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delivery Charge -->
                                    <div v-if="costBreakdown.delivery_charge > 0" class="flex justify-between items-center text-sm mt-3 pt-3 border-t border-green-200">
                                        <div>
                                            <span class="text-gray-600 font-medium">Delivery Charge:</span>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ form.is_inside_city ? 'Inside City' : 'Outside City' }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium">৳{{ costBreakdown.delivery_charge.toFixed(2) }}</div>
                                        </div>
                                    </div>

                                    <!-- Payment Processing Fee -->
                                    <div v-if="costBreakdown.payment_processing_fee > 0" class="flex justify-between items-center text-sm mt-3 pt-3 border-t border-green-200">
                                        <div>
                                            <span class="text-gray-600 font-medium">Payment Processing Fee:</span>
                                            <p class="text-xs text-gray-500 mt-0.5" v-if="form.payment_method">
                                                {{ getPaymentMethodName(form.payment_method) }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium">৳{{ costBreakdown.payment_processing_fee.toFixed(2) }}</div>
                                        </div>
                                    </div>

                                    <!-- Additional Charges Breakdown -->
                                    <div v-if="costBreakdown.breakdown && costBreakdown.breakdown.length > 0" class="mt-3 pt-3 border-t border-green-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-xs font-semibold text-green-800 uppercase tracking-wide">Additional Charges</span>
                                            <span class="text-xs text-green-700 font-bold">Total: ৳{{ (costBreakdown.total_charges - (costBreakdown.delivery_charge || 0) - (costBreakdown.payment_processing_fee || 0)).toFixed(2) }}</span>
                                        </div>
                                        <div class="space-y-2.5">
                                            <div 
                                                v-for="(charge, index) in costBreakdown.breakdown" 
                                                :key="index"
                                                class="flex items-start justify-between text-xs bg-white/70 rounded-lg p-3 hover:bg-white transition-colors border border-green-100"
                                            >
                                                <div class="flex-1 min-w-0 pr-3">
                                                    <div class="font-bold text-gray-900 truncate mb-1">{{ charge.charge }}</div>
                                                    <div class="text-gray-500 capitalize text-[10px] mb-1">{{ charge.type }}</div>
                                                    <div v-if="charge.calculation_type === 'percentage'" class="text-gray-400 text-[10px]">
                                                        {{ charge.value }}% of base amount
                                                    </div>
                                                    <div v-else class="text-gray-400 text-[10px]">
                                                        Fixed charge
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0 border-l border-green-200 pl-3 min-w-[100px]">
                                                    <div class="font-bold text-gray-900 mb-1">
                                                        {{ charge.currency_symbol || charge.currency }} {{ parseFloat(charge.amount_in_currency || charge.amount || 0).toFixed(2) }}
                                                    </div>
                                                    <div v-if="charge.amount_in_bdt && charge.currency !== 'BDT'" class="text-[10px] text-gray-600 font-medium">
                                                        = ৳{{ parseFloat(charge.amount_in_bdt).toFixed(2) }}
                                                    </div>
                                                    <div v-else-if="charge.currency === 'BDT'" class="text-[10px] text-gray-400">
                                                        (Base currency)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else-if="costBreakdown.total_charges > 0" class="flex justify-between text-sm">
                                        <span class="text-gray-600">Additional Charges:</span>
                                        <span class="font-medium">৳{{ costBreakdown.total_charges.toFixed(2) }}</span>
                                    </div>

                                    <!-- Grand Total -->
                                    <div class="border-t-2 border-green-300 pt-3 mt-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-green-900">Estimated Total:</span>
                                            <span class="text-3xl font-bold text-green-700">৳{{ costBreakdown.grand_total.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-green-700/80 mt-3 flex items-center gap-1">
                                    <Info class="w-3 h-3" />
                                    Final amount may vary based on actual weight and exchange rates
                                </p>
                            </div>
                            <div v-else class="p-6 bg-gray-50 rounded-lg border border-gray-200 text-center text-gray-500">
                                Fill in product details to see cost estimate
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                        <button 
                            type="submit" 
                            :disabled="requestStore.loading || !isFormValid"
                            class="flex-1 py-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                        >
                            <span v-if="requestStore.loading" class="flex items-center gap-2">
                                <span class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                                Submitting Request...
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <CheckCircle class="w-5 h-5" />
                                Submit Request
                            </span>
                        </button>
                        <router-link 
                            to="/dashboard/requests"
                            class="px-6 py-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all text-center"
                        >
                            View My Requests
                        </router-link>
                    </div>

                    <!-- Error Message -->
                    <div v-if="requestStore.error" class="mt-6 p-4 bg-red-50 border-2 border-red-200 rounded-lg">
                        <div class="flex items-center gap-2">
                            <AlertCircle class="w-5 h-5 text-red-600" />
                            <p class="text-red-700 text-sm font-medium">{{ requestStore.error }}</p>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Trust Indicators -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 text-center hover:shadow-lg transition-shadow">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <CheckCircle class="w-5 h-5 text-green-600" />
                    </div>
                    <p class="font-semibold text-slate-900 text-sm">100% Secure</p>
                    <p class="text-xs text-slate-500 mt-0.5">Protected Payment</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 text-center hover:shadow-lg transition-shadow">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <CheckCircle class="w-5 h-5 text-blue-600" />
                    </div>
                    <p class="font-semibold text-slate-900 text-sm">Fast Processing</p>
                    <p class="text-xs text-slate-500 mt-0.5">24-48 hours</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 text-center hover:shadow-lg transition-shadow">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <Globe class="w-5 h-5 text-purple-600" />
                    </div>
                    <p class="font-semibold text-slate-900 text-sm">Worldwide</p>
                    <p class="text-xs text-slate-500 mt-0.5">Any country</p>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 text-center hover:shadow-lg transition-shadow">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <CheckCircle class="w-5 h-5 text-orange-600" />
                    </div>
                    <p class="font-semibold text-slate-900 text-sm">5000+ Customers</p>
                    <p class="text-xs text-slate-500 mt-1">Trusted by many</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRequestStore } from '../stores/request';
import { useRouter } from 'vue-router';
import { Link, CheckCircle, Globe, Info, AlertCircle } from 'lucide-vue-next';
import axios from '../axios';

const requestStore = useRequestStore();
const router = useRouter();

const form = ref({
    url: '',
    price: 0,
    quantity: 1,
    currency: '',
    weight: 0,
    declared_shipping_cost: 0,
    is_inside_city: false,
    payment_method: ''
});

const currencies = ref([]);
const paymentMethods = ref([]);
const selectedCurrency = ref(null);
const costBreakdown = ref(null);
const calculationLoading = ref(false);
const calculationTimeout = ref(null);

const isFormValid = computed(() => {
    return form.value.url && 
           form.value.price > 0 && 
           form.value.quantity > 0 && 
           form.value.currency;
});

const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/currencies', { params: { all: true } });
        currencies.value = response.data.data || response.data;
        
        // Set default currency to BDT if available
        const bdt = currencies.value.find(c => c.code === 'BDT');
        if (bdt) {
            form.value.currency = 'BDT';
            selectedCurrency.value = bdt;
        }
    } catch (error) {
        console.error('Error fetching currencies:', error);
        // Fallback to admin endpoint if public fails
        try {
            const response = await axios.get('/admin/currencies', { params: { all: true } });
            currencies.value = response.data.data || response.data;
            const bdt = currencies.value.find(c => c.code === 'BDT');
            if (bdt) {
                form.value.currency = 'BDT';
                selectedCurrency.value = bdt;
            }
        } catch (e) {
            console.error('Error fetching currencies from admin endpoint:', e);
        }
    }
};

const onCurrencyChange = () => {
    selectedCurrency.value = currencies.value.find(c => c.code === form.value.currency);
    calculateEstimate();
};

const calculateEstimate = async () => {
    if (!form.value.price || !form.value.quantity || !form.value.currency) {
        costBreakdown.value = null;
        return;
    }

    // Clear previous timeout
    if (calculationTimeout.value) {
        clearTimeout(calculationTimeout.value);
    }

    // Debounce calculation
    calculationTimeout.value = setTimeout(async () => {
        calculationLoading.value = true;
        try {
            // Convert product price to base currency (BDT)
            const productTotal = form.value.price * form.value.quantity;
            let baseAmount = productTotal;
            
            if (selectedCurrency.value && !selectedCurrency.value.is_base) {
                baseAmount = productTotal * selectedCurrency.value.rate_to_base;
            }

            // Calculate charges using the charges API
            const response = await axios.post('/charges/calculate', {
                base_amount: baseAmount,
                currency_id: selectedCurrency.value?.id,
                additional_data: {
                    weight: form.value.weight || 0,
                    is_inside_city: form.value.is_inside_city,
                    payment_method: form.value.payment_method || null,
                }
            });

            // Add declared shipping if provided
            let declaredShippingBDT = 0;
            if (form.value.declared_shipping_cost > 0) {
                declaredShippingBDT = form.value.declared_shipping_cost;
                if (selectedCurrency.value && !selectedCurrency.value.is_base) {
                    declaredShippingBDT = form.value.declared_shipping_cost * selectedCurrency.value.rate_to_base;
                }
            }

            costBreakdown.value = {
                product_total: baseAmount,
                declared_shipping: declaredShippingBDT,
                total_charges: response.data.total_charges || 0,
                delivery_charge: response.data.delivery_charge || 0,
                payment_processing_fee: response.data.payment_processing_fee || 0,
                grand_total: baseAmount + declaredShippingBDT + (response.data.total_charges || 0),
                breakdown: response.data.breakdown || [],
                base_amount: response.data.base_amount || baseAmount,
            };
        } catch (error) {
            console.error('Error calculating charges:', error);
            // Fallback calculation
            const productTotal = form.value.price * form.value.quantity;
            let baseAmount = productTotal;
            if (selectedCurrency.value && !selectedCurrency.value.is_base) {
                baseAmount = productTotal * selectedCurrency.value.rate_to_base;
            }
            costBreakdown.value = {
                product_total: baseAmount,
                declared_shipping: 0,
                total_charges: 0,
                grand_total: baseAmount,
                breakdown: [],
            };
        } finally {
            calculationLoading.value = false;
        }
    }, 500);
};

const submitRequest = async () => {
    if (!isFormValid.value) {
        return;
    }

    try {
        const requestData = {
            url: form.value.url,
            price: form.value.price,
            quantity: form.value.quantity,
            currency: form.value.currency,
            weight: form.value.weight || null,
            declared_shipping_cost: form.value.declared_shipping_cost || null,
            is_inside_city: form.value.is_inside_city,
            payment_method: form.value.payment_method || null,
        };

        await requestStore.createRequest(requestData);
        router.push('/dashboard/requests');
    } catch (e) {
        // Error handled in store
    }
};

const fetchPaymentMethods = async () => {
    try {
        const response = await axios.get('/payment-methods');
        paymentMethods.value = response.data || [];
    } catch (error) {
        console.error('Error fetching payment methods:', error);
    }
};

const getPaymentMethodName = (type) => {
    const method = paymentMethods.value.find(m => m.type === type);
    return method ? method.name : type;
};

// Watch for form changes to recalculate
watch([() => form.value.price, () => form.value.quantity, () => form.value.weight, () => form.value.declared_shipping_cost, () => form.value.is_inside_city, () => form.value.payment_method], () => {
    calculateEstimate();
});

onMounted(() => {
    fetchCurrencies();
    fetchPaymentMethods();
});
</script>
