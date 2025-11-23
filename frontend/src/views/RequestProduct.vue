<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8 mt-15">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-slate-900 mb-3">Request Any Product</h1>
                <p class="text-lg text-slate-600">From anywhere in the world, delivered to your doorstep</p>
            </div>

            <!-- How It Works Section -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-slate-900 text-center mb-8">How It Works</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <Link class="w-8 h-8 text-white" />
                        </div>
                        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                            <h3 class="font-bold text-slate-900 mb-2">1. Paste Link</h3>
                            <p class="text-sm text-slate-600">Share the product URL from any online store</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <Search class="w-8 h-8 text-white" />
                        </div>
                        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                            <h3 class="font-bold text-slate-900 mb-2">2. We Source</h3>
                            <p class="text-sm text-slate-600">We verify and calculate total cost</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <CheckCircle class="w-8 h-8 text-white" />
                        </div>
                        <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                            <h3 class="font-bold text-slate-900 mb-2">3. You Approve</h3>
                            <p class="text-sm text-slate-600">Review quote and make payment</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <Truck class="w-8 h-8 text-white" />
                        </div>
                        <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
                            <h3 class="font-bold text-slate-900 mb-2">4. We Deliver</h3>
                            <p class="text-sm text-slate-600">Product arrives at your doorstep</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Request Details</h2>
                <form @submit.prevent="submitRequest" class="space-y-6">
                    <!-- Product URL -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Product URL <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.url" type="url" required placeholder="https://amazon.com/product-link"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        <p class="mt-1.5 text-xs text-slate-500">Example: Amazon, eBay, AliExpress, or any online store
                        </p>
                    </div>

                    <!-- Price & Currency -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Price <span
                                    class="text-red-500">*</span></label>
                            <input v-model="form.price" type="number" step="0.01" required placeholder="99.99"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Currency <span
                                    class="text-red-500">*</span></label>
                            <select v-model="form.currency"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white">
                                <option value="USD">USD - US Dollar</option>
                                <option value="CNY">CNY - Chinese Yuan</option>
                                <option value="BDT">BDT - Bangladeshi Taka</option>
                            </select>
                        </div>
                    </div>

                    <!-- Quantity & Weight -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Quantity <span
                                    class="text-red-500">*</span></label>
                            <input v-model="form.quantity" type="number" min="1" required placeholder="1"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Weight <span class="text-xs font-normal text-slate-500">(kg, Optional)</span>
                            </label>
                            <input v-model="form.weight" type="number" step="0.01" placeholder="0.5"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>
                    </div>

                    <!-- Shipping Cost -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Shipping Cost <span class="text-xs font-normal text-slate-500">(Optional)</span>
                        </label>
                        <input v-model="form.declared_shipping_cost" type="number" step="0.01" placeholder="0.00"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        <p class="mt-1.5 text-xs text-slate-500">If you know the shipping cost from the seller</p>
                    </div>

                    <!-- Inside City Delivery -->
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <input v-model="form.is_inside_city" type="checkbox"
                            class="w-5 h-5 text-primary rounded focus:ring-2 focus:ring-primary/20 border-gray-300">
                        <div>
                            <span class="text-sm font-semibold text-slate-900">Inside City Delivery</span>
                            <p class="text-xs text-slate-600">Delivery within Dhaka city limits</p>
                        </div>
                    </div>

                    <!-- Estimated Total -->
                    <div v-if="estimatedTotal" class="p-5 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-green-900">Estimated Total</span>
                            <span class="text-2xl font-bold text-green-700">৳{{ estimatedTotal }}</span>
                        </div>
                        <p class="text-xs text-green-700 mt-2">*Final amount may vary based on actual weight and
                            exchange rates</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" :disabled="requestStore.loading"
                        class="w-full py-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg">
                        <span v-if="requestStore.loading" class="flex items-center justify-center gap-2">
                            <span class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                            Submitting...
                        </span>
                        <span v-else>Submit Request</span>
                    </button>

                    <!-- Error Message -->
                    <div v-if="requestStore.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700 text-sm">{{ requestStore.error }}</p>
                    </div>
                </form>
            </div>

            <!-- Trust Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 text-center">
                    <ShieldCheck class="w-10 h-10 text-green-600 mx-auto mb-3" />
                    <p class="font-semibold text-slate-900 text-sm">100% Secure</p>
                    <p class="text-xs text-slate-500 mt-1">Protected Payment</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 text-center">
                    <Clock class="w-10 h-10 text-blue-600 mx-auto mb-3" />
                    <p class="font-semibold text-slate-900 text-sm">Fast Processing</p>
                    <p class="text-xs text-slate-500 mt-1">24-48 hours</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 text-center">
                    <Globe class="w-10 h-10 text-purple-600 mx-auto mb-3" />
                    <p class="font-semibold text-slate-900 text-sm">Worldwide</p>
                    <p class="text-xs text-slate-500 mt-1">Any country</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 text-center">
                    <Users class="w-10 h-10 text-orange-600 mx-auto mb-3" />
                    <p class="font-semibold text-slate-900 text-sm">5000+ Customers</p>
                    <p class="text-xs text-slate-500 mt-1">Trusted by many</p>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Frequently Asked Questions</h2>
                <div class="space-y-4">
                    <div v-for="(faq, index) in faqs" :key="index" class="border-b border-gray-200 pb-4 last:border-0">
                        <button @click="toggleFaq(index)" class="w-full flex items-center justify-between text-left">
                            <span class="font-semibold text-slate-900">{{ faq.question }}</span>
                            <ChevronDown class="w-5 h-5 text-slate-400 transition-transform"
                                :class="{ 'rotate-180': faq.open }" />
                        </button>
                        <div v-show="faq.open" class="mt-3 text-sm text-slate-600">
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRequestStore } from '../stores/request';
import { useRouter } from 'vue-router';
import { Link, Search, CheckCircle, Truck, ShieldCheck, Clock, Globe, Users, ChevronDown } from 'lucide-vue-next';

const requestStore = useRequestStore();
const router = useRouter();

const form = ref({
    url: '',
    price: '',
    quantity: 1,
    currency: 'USD',
    weight: '',
    declared_shipping_cost: '',
    is_inside_city: false
});

const faqs = ref([
    {
        question: 'How long does the process take?',
        answer: 'Typically 7-14 days from request approval to delivery, depending on the product location and shipping method.',
        open: false
    },
    {
        question: 'What payment methods do you accept?',
        answer: 'We accept bKash, bank transfers, and cash on delivery for local customers. International payments via PayPal or bank transfer.',
        open: false
    },
    {
        question: 'Can I request products from any country?',
        answer: 'Yes! We can source products from anywhere in the world including USA, UK, China, Japan, and more.',
        open: false
    },
    {
        question: 'What if the product is out of stock?',
        answer: 'We will notify you immediately and help you find alternatives or issue a full refund.',
        open: false
    },
    {
        question: 'Are there any hidden fees?',
        answer: 'No hidden fees. The estimated total includes product cost, shipping, and our service fee. Final amount is confirmed before payment.',
        open: false
    }
]);

// Mock conversion rates for estimation (should ideally come from backend settings)
const rates = { USD: 120, CNY: 18, BDT: 1 };

const estimatedTotal = computed(() => {
    if (!form.value.price || !form.value.quantity) return 0;
    const rate = rates[form.value.currency];
    const productTotal = form.value.price * form.value.quantity * rate;
    const shipping = (form.value.declared_shipping_cost || 0) * (form.value.currency === 'BDT' ? 1 : rate);
    return (productTotal + shipping).toFixed(2);
});

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open;
};

const submitRequest = async () => {
    try {
        await requestStore.createRequest(form.value);
        router.push('/dashboard/requests');
    } catch (e) {
        // Error handled in store
    }
};
</script>
