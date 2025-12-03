<template>
    <div class="bg-gray-50 min-h-screen">
        <!-- Hero Section -->
        <section class="relative bg-primary overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-primary via-purple-900 to-secondary opacity-90"></div>
            <div
                class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80')] bg-cover bg-center mix-blend-overlay opacity-50">
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
                <div class="md:w-2/3 lg:w-1/2">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white text-sm font-medium mb-6">
                        <Plane class="w-4 h-4 text-accent" />
                        <span>Travel more, earn more</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold !text-white mb-6 leading-tight tracking-tight">
                        Turn Your Luggage Space Into <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-accent">Cash</span>
                    </h1>
                    <p class="text-xl !text-blue-100 mb-10 leading-relaxed">
                        Connect with shoppers who need products from your destination. Deliver items, earn commissions,
                        and fund your next adventure.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="openRegisterModal"
                            class="inline-flex justify-center items-center gap-2 bg-accent text-white font-bold py-4 px-8 rounded-full hover:bg-pink-600 transition-all shadow-lg hover:shadow-pink-500/30 transform hover:-translate-y-1 cursor-pointer">
                            Start Earning
                            <ArrowRight class="w-5 h-5" />
                        </button>
                        <a href="#how-it-works"
                            class="inline-flex justify-center items-center gap-2 bg-white/10 backdrop-blur-md border border-white/30 text-white font-bold py-4 px-8 rounded-full hover:bg-white/20 transition-all">
                            How it Works
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Banner -->
            <div class="relative border-t border-white/10 bg-black/20 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        <div>
                            <p class="text-3xl font-bold text-white">10k+</p>
                            <p class="text-blue-200 text-sm">Active Travellers</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-white">$2M+</p>
                            <p class="text-blue-200 text-sm">Paid Out</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-white">50+</p>
                            <p class="text-blue-200 text-sm">Countries</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-white">4.9/5</p>
                            <p class="text-blue-200 text-sm">User Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section id="how-it-works" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Simple Steps to Start Earning</h2>
                    <p class="text-xl text-slate-500 max-w-2xl mx-auto">We've made the process seamless and secure, so
                        you can focus on your journey.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                    <!-- Connecting Line (Desktop) -->
                    <div
                        class="hidden md:block absolute top-12 left-1/6 right-1/6 h-0.5 bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100 -z-10">
                    </div>

                    <!-- Step 1 -->
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:shadow-md">
                            <MapPin class="w-10 h-10 text-primary" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3 text-center">1. Post Your Trip</h3>
                        <p class="text-slate-500 text-center leading-relaxed">
                            Tell us where you're going and how much luggage space you have available.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:shadow-md">
                            <Package class="w-10 h-10 text-purple-600" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3 text-center">2. Get Matched</h3>
                        <p class="text-slate-500 text-center leading-relaxed">
                            Receive requests from shoppers wanting items from your destination.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-pink-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300 shadow-sm group-hover:shadow-md">
                            <DollarSign class="w-10 h-10 text-secondary" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3 text-center">3. Deliver & Earn</h3>
                        <p class="text-slate-500 text-center leading-relaxed">
                            Buy the item, deliver it upon arrival, and get paid instantly.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-24 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">Travel Smart, Earn Smart</h2>
                        <div class="space-y-8">
                            <div class="flex gap-4">
                                <div
                                    class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <ShieldCheck class="w-6 h-6 text-green-600" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">Secure Payments</h3>
                                    <p class="text-slate-500">We hold the shopper's payment in escrow until you
                                        successfully deliver the item.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                    <Globe class="w-6 h-6 text-orange-600" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 mb-2">Global Community</h3>
                                    <p class="text-slate-500">Join thousands of travellers and shoppers connecting
                                        worldwide every day.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <!-- Mock Calculator Card -->
                        <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100 relative z-10">
                            <h3 class="text-2xl font-bold text-slate-900 mb-6">Estimate Earnings</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Luggage Space
                                        Available</label>
                                    <input type="range" min="1" max="20" value="5"
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary">
                                    <div class="flex justify-between text-sm text-slate-500 mt-1">
                                        <span>1 kg</span>
                                        <span class="font-bold text-primary">5 kg</span>
                                        <span>20 kg</span>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-slate-600">Potential Earnings</span>
                                        <span class="text-2xl font-bold text-green-600">৳15,000</span>
                                    </div>
                                    <p class="text-xs text-slate-400">*Based on average rewards for this route</p>
                                </div>
                                <button @click="openRegisterModal"
                                    class="w-full py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary-hover transition-colors cursor-pointer">
                                    Calculate for My Trip
                                </button>
                            </div>
                        </div>
                        <!-- Decorative Elements -->
                        <div class="absolute -top-10 -right-10 w-64 h-64 bg-secondary/10 rounded-full blur-3xl -z-0">
                        </div>
                        <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-primary/10 rounded-full blur-3xl -z-0">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registration Modal -->
        <Teleport to="body">
            <div v-if="isRegisterModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                role="dialog" aria-modal="true">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
                    @click="closeRegisterModal"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                        <!-- Close Button -->
                        <button @click="closeRegisterModal"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 focus:outline-none z-10">
                            <X class="w-6 h-6" />
                        </button>

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="text-center mb-8 mt-2">
                                <span class="text-accent font-bold tracking-wider uppercase text-sm">Join Us
                                    Today</span>
                                <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mt-2">Start Your Journey as a
                                    Traveler</h2>
                            </div>

                            <form class="space-y-6" @submit.prevent="handleRegister">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                                        <input v-model="name" type="text" required placeholder="John Doe"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.name }" />
                                        <p v-if="authStore.errors.name" class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.name[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Passport No</label>
                                        <input v-model="passport_no" type="text" required placeholder="A12345678"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.passport_no }" />
                                        <p v-if="authStore.errors.passport_no"
                                            class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.passport_no[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                                        <input v-model="email" type="email" required placeholder="john@example.com"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.email }" />
                                        <p v-if="authStore.errors.email" class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.email[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Phone</label>
                                        <input v-model="phone" type="tel" required placeholder="+1 234 567 890"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.phone }" />
                                        <p v-if="authStore.errors.phone" class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.phone[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Country</label>
                                        <input v-model="country" type="text" required placeholder="United States"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.country }" />
                                        <p v-if="authStore.errors.country" class="text-red-500 text-xs mt-1 text-left">
                                            {{ authStore.errors.country[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">State</label>
                                        <input v-model="state" type="text" required placeholder="New York"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.state }" />
                                        <p v-if="authStore.errors.state" class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.state[0] }}</p>
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">City</label>
                                        <input v-model="city" type="text" required placeholder="New York City"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.city }" />
                                        <p v-if="authStore.errors.city" class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.city[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Travel Date</label>
                                        <input v-model="travel_date" type="date" required
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.travel_date }" />
                                        <p v-if="authStore.errors.travel_date"
                                            class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.travel_date[0] }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">No. of
                                            Luggage</label>
                                        <input v-model="luggage_count" type="number" required placeholder="2"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white"
                                            :class="{ 'border-red-500': authStore.errors.luggage_count }" />
                                        <p v-if="authStore.errors.luggage_count"
                                            class="text-red-500 text-xs mt-1 text-left">{{
                                            authStore.errors.luggage_count[0] }}</p>
                                    </div>

                                    <!-- Password Fields -->
                                    <div class="col-span-1 relative">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                                        <input v-model="password" :type="showPassword ? 'text' : 'password'" required
                                            placeholder="Create password"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white pr-10"
                                            :class="{ 'border-red-500': authStore.errors.password }" />
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 top-7 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <Eye v-if="!showPassword" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                        <p v-if="authStore.errors.password" class="text-red-500 text-xs mt-1 text-left">
                                            {{ authStore.errors.password[0] }}</p>
                                    </div>
                                    <div class="col-span-1 relative">
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Confirm
                                            Password</label>
                                        <input v-model="password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'" required
                                            placeholder="Confirm password"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all bg-gray-50 focus:bg-white pr-10" />
                                        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute inset-y-0 right-0 top-7 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                    </div>

                                    <div
                                        class="col-span-1 md:col-span-2 bg-blue-50 p-4 rounded-xl flex items-center gap-3">
                                        <div class="bg-blue-100 p-2 rounded-full">
                                            <DollarSign class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <p class="text-sm font-medium text-blue-800">You can earn upto <span
                                                class="font-bold">$500</span> per luggage on popular routes!</p>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <button type="submit" :disabled="loading"
                                        class="group relative w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent text-lg font-bold rounded-xl text-white bg-gradient-to-r from-secondary to-accent hover:from-pink-600 hover:to-pink-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed">
                                        <span v-if="loading"
                                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                                        <span v-else>Register as Traveler</span>
                                        <ArrowRight v-if="!loading"
                                            class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                                    </button>
                                </div>
                            </form>
                            <p v-if="error" class="text-red-500 text-sm text-center mt-4 bg-red-50 p-3 rounded-lg">{{
                                error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { Plane, ArrowRight, MapPin, Package, DollarSign, ShieldCheck, Globe, X, Eye, EyeOff } from 'lucide-vue-next';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Traveller Fields
const passport_no = ref('');
const phone = ref('');
const country = ref('');
const state = ref('');
const city = ref('');
const travel_date = ref('');
const luggage_count = ref('');

const authStore = useAuthStore();
const router = useRouter();
const isRegisterModalOpen = ref(false);

const openRegisterModal = () => {
    isRegisterModalOpen.value = true;
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
};

const closeRegisterModal = () => {
    isRegisterModalOpen.value = false;
    document.body.style.overflow = ''; // Restore scrolling
};

const handleRegister = async () => {
    if (password.value !== password_confirmation.value) {
        error.value = "Passwords do not match";
        return;
    }
    loading.value = true;
    error.value = ''; // Clear previous generic errors

    try {
        const userData = {
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value,
            role: 'Traveller',
            passport_no: passport_no.value,
            phone: phone.value,
            country: country.value,
            state: state.value,
            city: city.value,
            travel_date: travel_date.value,
            luggage_count: luggage_count.value
        };

        await authStore.register(userData);
        closeRegisterModal();
        router.push('/dashboard');
    } catch (e) {
        // If it's not a validation error (which is handled by authStore.errors), show generic error
        if (!authStore.errors || Object.keys(authStore.errors).length === 0 || Array.isArray(authStore.errors)) {
            error.value = 'Registration failed. Please check your inputs.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
