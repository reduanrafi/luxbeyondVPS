<template>
    <div class="space-y-8">
        <!-- Loading State -->
        <div v-if="loading" class="min-h-[400px] flex flex-col items-center justify-center">
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-white/10 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-primary border-t-transparent rounded-full animate-spin">
                </div>
            </div>
            <p class="text-zinc-500 mt-4 font-medium animate-pulse">Loading traveller profile...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-8 text-center">
            <p class="text-red-400 font-semibold">{{ error }}</p>
            <button @click="$router.push('/admin/travellers')"
                class="mt-4 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                Back to Travellers
            </button>
        </div>

        <template v-else-if="traveller">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-2xl font-bold text-black border-2 border-white/10">
                        {{ traveller.name?.charAt(0).toUpperCase() || '?' }}
                    </div>
                     <div>
                        <h2 class="text-3xl font-serif font-bold text-white tracking-wide">{{ traveller.name }}</h2>
                        <div class="flex items-center gap-3 mt-1">
                            <span :class="getStatusClass(traveller.traveller_profile?.verification_status)"
                                class="px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide border">
                                {{ traveller.traveller_profile?.verification_status || 'Unverified' }}
                            </span>
                             <span class="text-zinc-500 text-sm">•</span>
                             <p class="text-zinc-400 text-sm">{{ traveller.email }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3">
                     <!-- Verification Actions -->
                     <template v-if="traveller.traveller_profile?.verification_status === 'pending'">
                        <button @click="updateStatus('approved')" :disabled="processingAction"
                            class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50">
                            <CheckCircle class="w-4 h-4" />
                            Approve
                        </button>
                        <button @click="updateStatus('rejected')" :disabled="processingAction"
                            class="flex items-center gap-2 px-4 py-2 bg-red-500 text-white font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50">
                            <XCircle class="w-4 h-4" />
                            Reject
                        </button>
                    </template>
                    
                    <!-- Status specific actions (e.g., Suspend if approved) can go here -->
                    
                    <button @click="$router.push('/admin/travellers')"
                        class="flex items-center gap-2 px-4 py-2 bg-white/5 text-zinc-300 font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-white/10 hover:text-white transition-colors border border-white/10">
                        <ArrowLeft class="w-4 h-4" />
                        Back
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
             <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Total Trips</p>
                            <p class="text-2xl font-bold text-white mt-1">{{ traveller.trips?.length || 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-primary">
                            <Plane class="w-5 h-5" />
                        </div>
                    </div>
                </div>
                 <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Completed Orders</p>
                            <!-- Placeholder: Would need order stats in API response -->
                            <p class="text-2xl font-bold text-white mt-1">0</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-green-400">
                            <ShoppingBag class="w-5 h-5" />
                        </div>
                    </div>
                </div>
                <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Pending Payout</p>
                            <p class="text-2xl font-bold text-white mt-1">৳0.00</p>
                        </div>
                         <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-yellow-400">
                            <Wallet class="w-5 h-5" />
                        </div>
                    </div>
                </div>
                 <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">Rating</p>
                            <p class="text-2xl font-bold text-white mt-1">N/A</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-purple-400">
                            <Star class="w-5 h-5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Tabs -->
             <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden min-h-[500px]">
                <div class="border-b border-white/5 px-6">
                    <div class="flex gap-8">
                        <button v-for="tab in tabs" :key="tab.id"
                            @click="activeTab = tab.id"
                            class="py-4 text-sm font-bold uppercase tracking-wider border-b-2 transition-colors"
                            :class="activeTab === tab.id ? 'text-primary border-primary' : 'text-zinc-500 border-transparent hover:text-white'">
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Profile Tab -->
                    <div v-if="activeTab === 'profile'" class="space-y-8">
                        <!-- Personal Info -->
                        <div>
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <User class="w-5 h-5 text-primary" />
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="bg-white/[0.02] p-4 rounded-lg border border-white/5">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Passport Number</p>
                                    <p class="text-white font-medium">{{ traveller.traveller_profile?.passport_number || 'N/A' }}</p>
                                </div>
                                <div class="bg-white/[0.02] p-4 rounded-lg border border-white/5">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Phone Number</p>
                                    <p class="text-white font-medium">{{ traveller.traveller_profile?.phone_number || traveller.phone || 'N/A' }}</p>
                                </div>
                                <div class="bg-white/[0.02] p-4 rounded-lg border border-white/5">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Joined Date</p>
                                    <p class="text-white font-medium">{{ formatDate(traveller.created_at) }}</p>
                                </div>
                                <div class="bg-white/[0.02] p-4 rounded-lg border border-white/5 md:col-span-2">
                                    <p class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Address</p>
                                    <p class="text-white font-medium">
                                        {{ traveller.city }}<span v-if="traveller.state">, {{ traveller.state }}</span>
                                        <span v-if="traveller.country">, {{ traveller.country }}</span>
                                        <span v-if="!traveller.city && !traveller.country">N/A</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div>
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <FileText class="w-5 h-5 text-primary" />
                                Verification Documents
                            </h3>
                            <div v-if="traveller.traveller_profile?.document_urls?.length" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div v-for="(doc, index) in traveller.traveller_profile.document_urls" :key="index"
                                    class="group relative aspect-[4/3] bg-black rounded-lg border border-white/10 overflow-hidden">
                                     <img :src="doc" class="w-full h-full object-cover transition-transform group-hover:scale-105" alt="Document">
                                     <a :href="doc" target="_blank" 
                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 text-white font-medium">
                                        <Eye class="w-5 h-5" />
                                        View Full
                                     </a>
                                </div>
                            </div>
                            <div v-else class="text-zinc-500 text-sm italic py-8 border border-dashed border-white/10 rounded-lg text-center">
                                No verification documents uploaded.
                            </div>
                        </div>
                    </div>

                    <!-- Trips Tab -->
                    <div v-if="activeTab === 'trips'">
                        <div v-if="traveller.trips && traveller.trips.length > 0" class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-white/5 border-b border-white/5">
                                    <tr>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Destination</th>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Travel Date</th>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Capacity</th>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <tr v-for="trip in traveller.trips" :key="trip.id" class="hover:bg-white/[0.02]">
                                        <td class="py-4 px-6 text-sm text-white font-medium">{{ trip.destination }}</td>
                                        <td class="py-4 px-6 text-sm text-zinc-300">{{ formatDate(trip.travel_date) }}</td>
                                         <td class="py-4 px-6 text-sm text-zinc-300">{{ trip.available_luggage_capacity }} kg</td>
                                        <td class="py-4 px-6">
                                            <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wide border bg-white/5 border-white/10 text-zinc-400">
                                                {{ trip.status || 'Active' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-zinc-500 text-sm italic py-12 text-center">
                            No trips found for this traveller.
                        </div>
                    </div>

                    <!-- Payouts Tab -->
                     <div v-if="activeTab === 'payouts'">
                         <div v-if="traveller.payouts && traveller.payouts.length > 0" class="overflow-x-auto">
                             <table class="w-full">
                                <thead class="bg-white/5 border-b border-white/5">
                                    <tr>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Amount</th>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Date</th>
                                        <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payout in traveller.payouts" :key="payout.id" class="hover:bg-white/[0.02]">
                                        <td class="py-4 px-6 text-sm text-white font-medium">৳{{ payout.amount }}</td>
                                        <td class="py-4 px-6 text-sm text-zinc-300">{{ formatDate(payout.created_at) }}</td>
                                         <td class="py-4 px-6">
                                             <span class="px-2 py-1 rounded text-xs font-bold uppercase tracking-wide border"
                                                :class="payout.status === 'completed' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'">
                                                {{ payout.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                             </table>
                         </div>
                         <div v-else class="text-zinc-500 text-sm italic py-12 text-center">
                            No payout history available.
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { 
    ArrowLeft, 
    CheckCircle, 
    XCircle, 
    User, 
    FileText, 
    Plane, 
    ShoppingBag, 
    Wallet, 
    Star,
    Eye
} from 'lucide-vue-next';
import axios from '../../axios';

const route = useRoute();
const router = useRouter();
const travellerId = route.params.id;

const loading = ref(true);
const error = ref(null);
const traveller = ref(null);
const activeTab = ref('profile');
const processingAction = ref(false);

const tabs = [
    { id: 'profile', label: 'Profile Overview' },
    { id: 'trips', label: 'Trips History' },
    { id: 'payouts', label: 'Payouts' }
];

const fetchTraveller = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/admin/travellers/${travellerId}`);
        traveller.value = response.data;
    } catch (err) {
        console.error('Error fetching traveller:', err);
        error.value = err.response?.data?.message || 'Failed to load traveller data';
    } finally {
        loading.value = false;
    }
};

const updateStatus = async (status) => {
    if (!confirm(`Are you sure you want to ${status} this traveller?`)) return;
    
    processingAction.value = true;
    try {
        await axios.post(`/admin/travellers/${travellerId}/status`, { status });
        // Refresh data
        await fetchTraveller();
        alert(`Traveller ${status} successfully.`);
    } catch (err) {
        console.error('Error updating status:', err);
        alert(err.response?.data?.message || 'Failed to update status');
    } finally {
        processingAction.value = false;
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'approved': return 'bg-green-500/10 text-green-400 border-green-500/20';
        case 'rejected': return 'bg-red-500/10 text-red-400 border-red-500/20';
        case 'pending': return 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20';
        default: return 'bg-white/5 text-zinc-400 border-white/10';
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

onMounted(() => {
    fetchTraveller();
});
</script>
