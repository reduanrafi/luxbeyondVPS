<template>
    <div class="bg-background min-h-screen">
        <!-- Hero Section -->
        <section class="relative bg-background overflow-hidden">
            <!-- Background Image with Overlay -->
            <div
                class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80')] bg-cover bg-center opacity-40">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-background via-background/80 to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
                <div class="md:w-2/3 lg:w-1/2">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 text-primary text-sm font-medium mb-8 backdrop-blur-sm">
                        <Plane class="w-4 h-4" />
                        <span class="uppercase tracking-wider text-xs font-bold">Travel more, earn more</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 leading-tight">
                        Turn Your Luggage Space Into <span class="text-primary italic">Cash</span>

                    </h1>
                    <p class="text-xl text-slate-300 mb-10 leading-relaxed font-light">
                        Connect with shoppers who need products from your destination. Deliver items, earn commissions,
                        and fund your next adventure.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="openRegisterModal"
                            class="inline-flex justify-center items-center gap-2 bg-primary text-slate-900 font-bold py-4 px-8 rounded-none hover:bg-primary-hover transition-all uppercase tracking-widest text-sm">
                            Start Earning
                            <ArrowRight class="w-5 h-5" />
                        </button>
                        <a href="#how-it-works"
                            class="inline-flex justify-center items-center gap-2 bg-transparent border border-white/20 text-white font-bold py-4 px-8 rounded-none hover:bg-white/5 transition-all uppercase tracking-widest text-sm">
                            How it Works
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Banner -->
            <div class="relative border-y border-primary/5 bg-surface/50 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-white/5">
                        <div>
                            <p class="text-4xl font-serif text-white mb-1">10k+</p>
                            <p class="text-primary text-xs uppercase tracking-widest">Active Travellers</p>
                        </div>
                        <div>
                            <p class="text-4xl font-serif text-white mb-1">$2M+</p>
                            <p class="text-primary text-xs uppercase tracking-widest">Paid Out</p>
                        </div>
                        <div>
                            <p class="text-4xl font-serif text-white mb-1">50+</p>
                            <p class="text-primary text-xs uppercase tracking-widest">Countries</p>
                        </div>
                        <div>
                            <p class="text-4xl font-serif text-white mb-1">4.9/5</p>
                            <p class="text-primary text-xs uppercase tracking-widest">User Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section id="how-it-works" class="py-24 bg-background">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-serif text-white mb-4">Simple Steps to Start Earning</h2>
                    <p class="text-xl text-slate-400 max-w-2xl mx-auto font-light">We've made the process seamless and
                        secure, so
                        you can focus on your journey.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    <!-- Connecting Line (Desktop) -->
                    <div class="hidden md:block absolute top-12 left-1/6 right-1/6 h-px bg-white/10 -z-10">

                    </div>

                    <!-- Step 1 -->
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-surface border border-white/10 rounded-full flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300 shadow-xl group-hover:border-primary/50 relative z-10">
                            <MapPin class="w-10 h-10 text-primary" />
                        </div>
                        <div
                            class="bg-surface p-8 border border-primary/5 hover:border-primary/20 transition-colors h-full">
                            <h3 class="text-xl font-serif text-white mb-3 text-center">1. Post Your Trip</h3>
                            <p class="text-slate-400 text-center leading-relaxed text-sm">
                                Tell us where you're going and how much luggage space you have available.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-surface border border-white/10 rounded-full flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300 shadow-xl group-hover:border-primary/50 relative z-10">
                            <Package class="w-10 h-10 text-primary" />
                        </div>
                        <div
                            class="bg-surface p-8 border border-primary/5 hover:border-primary/20 transition-colors h-full">
                            <h3 class="text-xl font-serif text-white mb-3 text-center">2. Get Matched</h3>
                            <p class="text-slate-400 text-center leading-relaxed text-sm">
                                Receive requests from shoppers wanting items from your destination.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-surface border border-white/10 rounded-full flex items-center justify-center mx-auto mb-8 group-hover:scale-110 transition-transform duration-300 shadow-xl group-hover:border-primary/50 relative z-10">
                            <DollarSign class="w-10 h-10 text-primary" />
                        </div>
                        <div
                            class="bg-surface p-8 border border-primary/5 hover:border-primary/20 transition-colors h-full">
                            <h3 class="text-xl font-serif text-white mb-3 text-center">3. Deliver & Earn</h3>
                            <p class="text-slate-400 text-center leading-relaxed text-sm">
                                Buy the item, deliver it upon arrival, and get paid instantly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-24 bg-surface border-t border-primary/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-serif text-white mb-6">Travel Smart, Earn Smart</h2>
                        <div class="space-y-8">
                            <div class="flex gap-4">
                                <div
                                    class="flex-shrink-0 w-12 h-12 bg-primary/10 border border-primary/20 rounded-full flex items-center justify-center">
                                    <ShieldCheck class="w-6 h-6 text-primary" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-serif text-white mb-2">Secure Payments</h3>
                                    <p class="text-slate-400 font-light">We hold the shopper's payment in escrow until
                                        you
                                        successfully deliver the item.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    class="flex-shrink-0 w-12 h-12 bg-primary/10 border border-primary/20 rounded-full flex items-center justify-center">
                                    <Globe class="w-6 h-6 text-primary" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-serif text-white mb-2">Global Community</h3>
                                    <p class="text-slate-400 font-light">Join thousands of travellers and shoppers
                                        connecting
                                        worldwide every day.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <!-- Mock Calculator Card -->
                        <div class="bg-background p-8 rounded-sm shadow-2xl border border-white/10 relative z-10">
                            <h3 class="text-2xl font-serif text-white mb-6">Estimate Earnings</h3>
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-400 mb-2 uppercase tracking-wide">
                                        Luggage Space Available
                                    </label>
                                    <input type="range" min="1" max="10" step="1" v-model="calculatorLuggage"
                                        class="w-full h-2 bg-surface rounded-lg appearance-none cursor-pointer accent-primary border border-white/10">
                                    <div class="flex justify-between text-sm text-slate-500 mt-1">
                                        <span>1 Bag</span>
                                        <span class="font-bold text-primary">{{ calculatorLuggage }} Bags</span>
                                        <span>10 Bags</span>
                                    </div>
                                </div>
                                <div class="p-4 bg-surface rounded-sm border border-white/10">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-slate-400 uppercase tracking-wide text-xs">Potential
                                            Earnings</span>
                                        <span class="text-2xl font-serif text-primary">{{ calculatedEarnings }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500">*Based on standard rewards ($500/bag)</p>
                                </div>
                                <button @click="openRegisterModal"
                                    class="w-full py-4 bg-primary text-slate-900 font-bold rounded-none hover:bg-primary-hover transition-colors cursor-pointer uppercase tracking-widest text-sm">
                                    Calculate for My Trip
                                </button>
                            </div>
                        </div>
                        <!-- Decorative Elements -->
                        <div class="absolute -top-10 -right-10 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-0">
                        </div>
                        <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl -z-0">
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
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="closeRegisterModal">
                </div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-sm bg-surface text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-white/10">
                        <!-- Close Button -->
                        <button @click="closeRegisterModal"
                            class="absolute top-4 right-4 text-slate-400 hover:text-white focus:outline-none z-10 transition-colors">
                            <X class="w-6 h-6" />
                        </button>

                        <div class="px-4 pb-4 pt-5 sm:p-8 sm:pb-4">
                            <div class="text-center mb-8 mt-2">
                                <span class="text-primary font-bold tracking-widest uppercase text-xs">Join Us
                                    Today</span>
                                <h2 class="text-2xl md:text-3xl font-serif text-white mt-2">Start Your Journey as a
                                    Traveler</h2>
                            </div>

                            <form class="space-y-6" @submit.prevent="handleRegister">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Full
                                            Name</label>
                                        <input v-model="name" type="text" placeholder="Enter full name"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.name }" @input="clearError('name')" />
                                        <p v-if="errors.name" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.name }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Passport
                                            No</label>
                                        <input v-model="passport_no" type="text" placeholder="Enter passport no"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.passport_no }"
                                            @input="clearError('passport_no')" />
                                        <p v-if="errors.passport_no" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.passport_no }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Email</label>
                                        <input v-model="email" type="email" placeholder="Enter email"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.email }" @input="clearError('email')" />
                                        <p v-if="errors.email" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.email }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Phone</label>
                                        <input v-model="phone" type="tel" placeholder="Enter phone number"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.phone }" @input="clearError('phone')" />
                                        <p v-if="errors.phone" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.phone }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Country</label>
                                        <input v-model="country" type="text" placeholder="Enter country"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.country }"
                                            @input="clearError('country')" />
                                        <p v-if="errors.country" class="text-red-500 text-xs mt-1 text-left">
                                            {{ errors.country }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">State</label>
                                        <input v-model="state" type="text" placeholder="Enter state"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.state }" @input="clearError('state')" />
                                        <p v-if="errors.state" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.state }}</p>
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">City</label>
                                        <input v-model="city" type="text" placeholder="Enter city"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.city }" @input="clearError('city')" />
                                        <p v-if="errors.city" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.city }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Travel
                                            Date</label>
                                        <input v-model="travel_date" type="date"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.travel_date }"
                                            @input="clearError('travel_date')" />
                                        <p v-if="errors.travel_date" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.travel_date }}</p>
                                    </div>
                                    <div class="col-span-1">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">No.
                                            of
                                            Luggage</label>
                                        <input v-model="luggage_count" type="number" placeholder="Enter no. of luggage"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600"
                                            :class="{ 'border-red-500': errors.luggage_count }"
                                            @input="clearError('luggage_count')" />
                                        <p v-if="errors.luggage_count" class="text-red-500 text-xs mt-1 text-left">{{
                                            errors.luggage_count }}</p>
                                    </div>

                                    <!-- Password Fields -->
                                    <div class="col-span-1 relative">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Password</label>
                                        <input v-model="password" :type="showPassword ? 'text' : 'password'"
                                            placeholder="Create password"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600 pr-10"
                                            :class="{ 'border-red-500': errors.password }"
                                            @input="clearError('password')" />
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 top-7 pr-3 flex items-center text-slate-500 hover:text-white focus:outline-none">
                                            <Eye v-if="!showPassword" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                        <p v-if="errors.password" class="text-red-500 text-xs mt-1 text-left">
                                            {{ errors.password }}</p>
                                    </div>
                                    <div class="col-span-1 relative">
                                        <label
                                            class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wide">Confirm
                                            Password</label>
                                        <input v-model="password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            placeholder="Confirm password"
                                            class="w-full px-4 py-3 rounded-none border border-white/10 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all bg-background text-white placeholder-slate-600 pr-10"
                                            :class="{ 'border-red-500': errors.password_confirmation }"
                                            @input="clearError('password_confirmation')" />
                                        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute inset-y-0 right-0 top-7 pr-3 flex items-center text-slate-500 hover:text-white focus:outline-none">
                                            <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                                            <EyeOff v-else class="h-5 w-5" />
                                        </button>
                                        <p v-if="errors.password_confirmation"
                                            class="text-red-500 text-xs mt-1 text-left">{{
                                                errors.password_confirmation }}</p>
                                    </div>

                                    <div
                                        class="col-span-1 md:col-span-2 bg-primary/10 border border-primary/20 p-4 rounded-sm flex items-center gap-3">
                                        <div class="bg-primary/20 p-2 rounded-full">
                                            <DollarSign class="w-5 h-5 text-primary" />
                                        </div>
                                        <p class="text-sm font-medium text-primary">You can earn upto <span
                                                class="font-bold">$500</span> per luggage on popular routes!</p>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <button type="submit" :disabled="loading"
                                        class="group relative w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent text-lg font-bold rounded-none text-slate-900 bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 uppercase tracking-widest text-sm disabled:opacity-70 disabled:cursor-not-allowed">
                                        <span v-if="loading"
                                            class="animate-spin rounded-full h-5 w-5 border-b-2 border-slate-900"></span>
                                        <span v-else>Register as Traveler</span>
                                        <ArrowRight v-if="!loading"
                                            class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                                    </button>
                                </div>
                            </form>
                            <p v-if="error"
                                class="text-red-500 text-sm text-center mt-4 bg-red-500/10 border border-red-500/20 p-3 rounded-sm">
                                {{
                                error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { Plane, ArrowRight, MapPin, Package, DollarSign, ShieldCheck, Globe, X, Eye, EyeOff } from 'lucide-vue-next';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');
const errors = ref({}); // Local JS validation errors
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

// Calculator State
const calculatorLuggage = ref(5);
const calculatedEarnings = computed(() => {
    return (calculatorLuggage.value * 500).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0
    });
});

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

const clearError = (field) => {
    if (errors.value[field]) {
        delete errors.value[field];
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!name.value.trim()) {
        errors.value.name = "Full Name is required";
        isValid = false;
    }
    if (!passport_no.value.trim()) {
        errors.value.passport_no = "Passport Number is required";
        isValid = false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email.value.trim()) {
        errors.value.email = "Email is required";
        isValid = false;
    } else if (!emailRegex.test(email.value)) {
        errors.value.email = "Please enter a valid email address";
        isValid = false;
    }

    if (!phone.value.trim()) {
        errors.value.phone = "Phone Number is required";
        isValid = false;
    }
    if (!country.value.trim()) {
        errors.value.country = "Country is required";
        isValid = false;
    }
    if (!state.value.trim()) {
        errors.value.state = "State is required";
        isValid = false;
    }
    if (!city.value.trim()) {
        errors.value.city = "City is required";
        isValid = false;
    }
    if (!travel_date.value) {
        errors.value.travel_date = "Travel Date is required";
        isValid = false;
    }
    if (!luggage_count.value) {
        errors.value.luggage_count = "Number of luggage is required";
        isValid = false;
    } else if (luggage_count.value < 1) {
        errors.value.luggage_count = "At least 1 luggage is required";
        isValid = false;
    }

    if (!password.value) {
        errors.value.password = "Password is required";
        isValid = false;
    } else if (password.value.length < 8) {
        errors.value.password = "Password must be at least 8 characters";
        isValid = false;
    }

    if (password.value != password_confirmation.value) {
        errors.value.password_confirmation = "Passwords do not match";
        isValid = false;
    }

    return isValid;
};

const handleRegister = async () => {
    if (!validateForm()) {
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
        router.push('/traveller-dashboard');
    } catch (e) {
        // Handle backend errors
        if (e.response && e.response.data && e.response.data.errors) {
            // Map backend errors to local error object if possible, or simple show generic
            // Here we prioritize local display, but backend validation is ultimate source of truth
            // Ideally authStore populates its own errors, we can use that too
             if (authStore.errors) {
                 // Sync generic store errors to our local error display if keys match
                 // But authStore might give errors differently. 
                 // We will rely on existing error display pattern for backend errors logic in the template if authStore.errors is used
                 // but here we used local 'errors' object for JS validation. 
                 // The template currently checks `authStore.errors.name` AND `errors.name`? 
                 // No, I replaced `authStore.errors` binding with `errors` binding in this rewrite.
                 // So I need to map backend errors to `errors.value`!
                 Object.assign(errors.value, authStore.errors);
             }
        } else {
             error.value = 'Registration failed. Please check your inputs.';
        }
    } finally {
        loading.value = false;
    }
};
</script>