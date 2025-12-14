<template>
    <div class="min-h-screen bg-primary font-sans text-zinc-100">
        <!-- Main Content -->
        <div class="flex flex-col lg:flex-row min-h-screen">
            <!-- Left Side: Image & Content -->
            <div class="lg:w-5/12 relative min-h-[400px] lg:min-h-screen overflow-hidden group border-r border-white/10">
                <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/40 to-transparent z-10"></div>
                <!-- Animated scaling background image -->
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?ixlib=rb-4.0.3&auto=format&fit=crop&w=2021&q=80')] bg-cover bg-center transition-transform duration-[10s] ease-in-out group-hover:scale-110 opacity-60"></div>
                
                <div class="relative z-20 h-full flex flex-col justify-end p-8 lg:p-16 text-white pb-20">
                    <div class="mb-6 inline-flex items-center gap-2 bg-amber-500/10 backdrop-blur-md px-4 py-1.5 rounded-full border border-amber-500/20 text-amber-400 text-sm font-semibold tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Trusted by 10k+ Travellers
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-extrabold mb-6 leading-tight tracking-tight">
                        Turn Miles<br>
                        Into <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-amber-600">Gold.</span>
                    </h1>
                    <p class="text-lg text-zinc-300 max-w-md leading-relaxed mb-8">
                        Your extra luggage space is valuable. Join the world's largest crowd-shipping network and fund your next adventure.
                    </p>
                    
                    <!-- Dynamic Earnings Preview -->
                    <div class="bg-white/5 backdrop-blur-xl p-6 rounded-2xl border border-white/10 transform transition-all hover:border-amber-500/30 duration-300">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-zinc-400 font-medium">Potential Earnings</span>
                            <DollarSign class="w-5 h-5 text-amber-500" />
                        </div>
                        <div class="text-3xl font-bold text-white tracking-tight">
                            ${{ estimatedEarnings }} <span class="text-base font-normal text-zinc-500">/ trip</span>
                        </div>
                        <div class="w-full bg-white/10 h-1.5 mt-4 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-full rounded-full transition-all duration-500 ease-out" 
                                 :style="{ width: Math.min((form.luggage_count / 5) * 100, 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Registration Form -->
            <div class="lg:w-7/12 flex items-center justify-center p-6 lg:p-16 bg-primary relative overflow-hidden">
                <!-- Decoration -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-accent/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-secondary/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

                <div class="max-w-xl w-full relative z-10">
                    <div class="mb-10">
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-3 tracking-tight">Start Your Journey</h2>
                        <p class="text-zinc-400 text-lg">Create your traveller profile and start receiving orders.</p>
                    </div>

                    <form @submit.prevent="handleRegister" class="space-y-6">
                        <!-- Two Column Grid for Desktop -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Personal Info Group -->
                            <div class="md:col-span-2 space-y-6">
                                <h3 class="text-sm font-semibold text-zinc-300 uppercase tracking-wider border-b border-white/10 pb-2 mb-4">Personal Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5 transition-colors group-focus-within:text-amber-500">Full Name</label>
                                        <input v-model="form.name" type="text" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="e.g. John Doe" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5 transition-colors group-focus-within:text-amber-500">Passport Number</label>
                                        <input v-model="form.passport_no" type="text" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="Enter passport ID" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5 transition-colors group-focus-within:text-amber-500">Email Address</label>
                                        <input v-model="form.email" type="email" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="john@example.com" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5 transition-colors group-focus-within:text-amber-500">Phone Number</label>
                                        <input v-model="form.phone" type="tel" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="+1 (555) 000-0000" />
                                    </div>
                                </div>
                            </div>

                            <!-- Location Info Group -->
                            <div class="md:col-span-2 space-y-6 pt-2">
                                <h3 class="text-sm font-semibold text-zinc-300 uppercase tracking-wider border-b border-white/10 pb-2 mb-4">Location</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5">Country</label>
                                        <input v-model="form.country" type="text" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="Country" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5">State</label>
                                        <input v-model="form.state" type="text" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="State" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5">City</label>
                                        <input v-model="form.city" type="text" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="City" />
                                    </div>
                                </div>
                            </div>

                            <!-- Trip Info Group -->
                            <div class="md:col-span-2 space-y-6 pt-2">
                                <h3 class="text-sm font-semibold text-zinc-300 uppercase tracking-wider border-b border-white/10 pb-2 mb-4 flex justify-between items-center">
                                    Trip Details
                                    <span class="text-amber-500 text-xs normal-case font-bold bg-amber-950/30 px-2 py-0.5 rounded border border-amber-500/20">High Demand</span>
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5">Travel Date</label>
                                        <input v-model="form.travel_date" type="date" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200 text-zinc-300 block w-full [color-scheme:dark]" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5 flex justify-between">
                                            Luggage Count
                                            <span class="text-xs text-amber-500 font-bold" v-if="form.luggage_count">
                                                ~${{ form.luggage_count * 500 }} payout
                                            </span>
                                        </label>
                                        <div class="relative">
                                            <input v-model="form.luggage_count" type="number" min="1" max="10" required 
                                                class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200 pr-12" placeholder="Qty" />
                                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm font-medium">Bags</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Group -->
                            <div class="md:col-span-2 space-y-6 pt-2">
                                <h3 class="text-sm font-semibold text-zinc-300 uppercase tracking-wider border-b border-white/10 pb-2 mb-4">Security</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5">Password</label>
                                        <input v-model="form.password" type="password" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="••••••••" />
                                    </div>
                                    <div class="group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1.5">Confirm Password</label>
                                        <input v-model="form.password_confirmation" type="password" required class="w-full px-5 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all duration-200" placeholder="••••••••" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="error" class="p-4 bg-red-900/10 text-red-400 rounded-xl text-sm border border-red-900/20 flex items-start gap-3">
                            <span class="mt-0.5">⚠️</span> 
                            <span class="whitespace-pre-wrap">{{ error }}</span>
                        </div>

                        <div class="pt-4">
                            <button type="submit" :disabled="loading"
                                class="w-full py-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-bold text-lg rounded-xl hover:shadow-[0_0_20px_rgba(245,158,11,0.3)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span v-else>Register & Start Earning</span>
                            </button>
                            
                            <p class="text-center text-zinc-500 mt-6">
                                Already a traveller? 
                                <button type="button" @click="openLoginModal" class="text-amber-500 font-bold hover:text-amber-400 hover:underline transition-colors">Sign In here</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useModalStore } from '../stores/modal';
import { DollarSign } from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();
const modalStore = useModalStore();

const loading = ref(false);
const error = ref('');

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    passport_no: '',
    phone: '',
    country: '',
    state: '',
    city: '',
    travel_date: '',
    luggage_count: '',
    role: 'Traveller'
});

// Static payout calculation: $500 per luggage
const estimatedEarnings = computed(() => {
    const count = parseInt(form.value.luggage_count) || 0;
    return (count * 500).toLocaleString();
});

const handleRegister = async () => {
    loading.value = true;
    error.value = '';

    try {
        await authStore.register(form.value);
        router.push('/traveller-dashboard');
    } catch (e) {
        if (e.response?.data?.errors) {
            error.value = Object.values(e.response.data.errors).flat().join('\n');
        } else {
            error.value = e.response?.data?.message || 'Registration failed. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};

const openLoginModal = () => {
    modalStore.openModal('login');
};
</script>
