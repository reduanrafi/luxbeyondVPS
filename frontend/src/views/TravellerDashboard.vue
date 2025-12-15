<template>
    <div class="bg-black rounded-2xl">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-white mb-8">Traveller Dashboard</h1>

            <!-- Loading -->
            <div v-if="loading" class="animate-pulse space-y-4">
                <div class="h-40 bg-zinc-900 rounded-2xl"></div>
                <div class="h-60 bg-zinc-900 rounded-2xl"></div>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Verification & Profile -->
                <div class="space-y-8 lg:col-span-1">
                    <!-- Status Card -->
                    <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Verification Status</h2>
                        <div v-if="profile?.verification_status === 'approved'"
                            class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
                            <CheckCircle class="w-6 h-6 text-emerald-500" />
                            <div>
                                <p class="font-bold text-emerald-400">Verified Traveller</p>
                                <p class="text-xs text-emerald-400/70">You can now accept orders.</p>
                            </div>
                        </div>
                        <div v-else-if="profile?.verification_status === 'rejected'"
                            class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl flex items-center gap-3">
                            <XCircle class="w-6 h-6 text-red-500" />
                            <div>
                                <p class="font-bold text-red-400">Application Rejected</p>
                                <p class="text-xs text-red-400/70">Update documents and retry.</p>
                            </div>
                        </div>
                        <div v-else
                            class="p-4 bg-primary/10 border bg-primary/20 rounded-xl flex items-center gap-3">
                            <Clock class="w-6 h-6 text-primary-500" />
                            <div>
                                <p class="font-bold text-primary-400">Pending Verification</p>
                                <p class="text-xs text-primary-400/70">Review in progress (24-48h).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Form (Mini) -->
                    <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                        <h2 class="text-lg font-bold text-white mb-4">Identity Details</h2>
                        <form @submit.prevent="updateProfile" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-400 mb-1">Passport Number</label>
                                <input v-model="form.passport_number" type="text"
                                    class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded text-sm text-white focus:outline-none"
                                    disabled>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-400 mb-1">Phone Number</label>
                                <input v-model="form.phone_number" type="tel"
                                    class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded text-sm text-white focus:outline-none"
                                    disabled>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-zinc-400 mb-2">Upload Passport <span
                                        class="text-red-500">*</span></label>
                                <div class="border-2 border-dashed border-white/10 rounded-lg p-4 text-center hover:bg-white/5 transition-colors cursor-pointer"
                                    :class="{ 'border-red-500/50 bg-red-500/5': error }"
                                    @click="$refs.fileInput.click()">
                                    <input ref="fileInput" type="file" @change="handleFile" class="hidden"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                    <Upload class="w-6 h-6 text-zinc-400 mx-auto mb-2" />
                                    <p class="text-xs text-zinc-300 truncate">{{ fileName || 'Click to upload' }}</p>
                                </div>
                                <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
                            </div>

                            <button type="submit" :disabled="saving"
                                class="w-full py-2 bg-primary text-black font-bold rounded hover:bg-primary transition-colors disabled:opacity-50 text-sm">
                                {{ saving ? 'Saving...' : 'Update Documents' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: My Trips -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Earnings Summary (Placeholder for now, implementation plan mentions it but let's focus on Trips first or simple static) -->
                    <!-- My Trips -->
                    <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-white">My Trips</h2>
                            <button @click="openTripModal"
                                class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
                                <Plus class="w-4 h-4" /> Add Trip
                            </button>
                        </div>

                        <div v-if="tripsLoading" class="text-center py-8">
                            <span class="animate-spin inline-block w-6 h-6 border-2 bg-primary border-t-white rounded-full"></span>
                        </div>

                        <div v-else-if="trips.length === 0" class="text-center py-12 border border-dashed border-white/10 rounded-xl">
                            <Plane class="w-12 h-12 text-zinc-500 mx-auto mb-3" />
                            <h3 class="text-lg font-bold text-white mb-1">No trips scheduled</h3>
                            <p class="text-zinc-400 text-sm mb-4">Add your first trip to start receiving requests.</p>
                            <button @click="openTripModal" class="text-primary-500 hover:text-primary-400 text-sm font-bold">Add a Trip</button>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="trip in trips" :key="trip.id" class="p-4 bg-white/5 border border-white/5 rounded-xl hover:bg-primary/30 transition-colors">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white">{{ trip.origin }}</span>
                                        <ArrowRight class="w-4 h-4 text-zinc-500" />
                                        <span class="font-bold text-white">{{ trip.destination }}</span>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded border" :class="getTripStatusColor(trip.status)">
                                        {{ trip.status }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm text-zinc-400">
                                    <div class="flex gap-4">
                                        <span class="flex items-center gap-1"><Calendar class="w-4 h-4"/> {{ formatDate(trip.travel_date) }}</span>
                                        <span class="flex items-center gap-1"><Briefcase class="w-4 h-4"/> {{ trip.available_capacity }} / {{ trip.luggage_capacity }} items</span>
                                    </div>
                                    <button v-if="trip.status === 'scheduled'" @click="cancelTrip(trip.id)" class="text-red-400 hover:text-red-300 text-xs">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Trip Modal -->
        <Teleport to="body">
            <div v-if="showTripModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closeTripModal"></div>
                <div class="relative bg-zinc-900 border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6">Add New Trip</h3>
                    <form @submit.prevent="createTrip" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-400 mb-1">Origin</label>
                                <input v-model="newTrip.origin" type="text" required placeholder="e.g. New York"
                                    class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded text-white focus:outline-none focus:bg-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-400 mb-1">Destination</label>
                                <input v-model="newTrip.destination" type="text" required placeholder="e.g. London"
                                    class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded text-white focus:outline-none focus:bg-primary">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-400 mb-1">Travel Date</label>
                            <input v-model="newTrip.travel_date" type="date" required :min="minDate"
                                class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded text-white focus:outline-none focus:bg-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-400 mb-1">Total Capacity (Items/Kg)</label>
                            <input v-model="newTrip.luggage_capacity" type="number" min="1" required
                                class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded text-white focus:outline-none focus:bg-primary">
                        </div>
                        
                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="closeTripModal" class="flex-1 py-2 border border-white/10 rounded text-white hover:bg-white/5">Cancel</button>
                            <button type="submit" :disabled="tripSaving" class="flex-1 py-2 bg-primary text-black font-bold rounded hover:bg-primary disabled:opacity-50">
                                {{ tripSaving ? 'Creating...' : 'Create Trip' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'vue-toastification';
import axios from '../axios';
import { useAuthStore } from '../stores/auth';
import { CheckCircle, XCircle, Clock, Upload, Plus, Calendar, Briefcase, ArrowRight, Plane } from 'lucide-vue-next';

const toast = useToast();
const authStore = useAuthStore();
const loading = ref(true);
const saving = ref(false);
const tripsLoading = ref(false);
const tripSaving = ref(false);

const profile = ref(null);
const trips = ref([]);
const fileInput = ref(null);
const fileName = ref('');
const error = ref('');
const showTripModal = ref(false);

// Form States
const form = ref({
    passport_number: '',
    phone_number: '',
    passport_image: null
});

const newTrip = ref({
    origin: '',
    destination: '',
    travel_date: '',
    luggage_capacity: 5
});

const minDate = computed(() => {
    const today = new Date();
    today.setDate(today.getDate() + 1); // Tomorrow
    return today.toISOString().split('T')[0];
});

// Fetch Data
const fetchProfile = async () => {
    try {
        const response = await axios.get('/traveller/profile');
        profile.value = response.data.profile;
        const user = response.data.user;
        form.value.passport_number = profile.value.passport_number || user.passport_no;
        form.value.phone_number = profile.value.phone_number || user.phone;
    } catch (e) {
        console.error('Profile fetch error', e);
    }
};

const fetchTrips = async () => {
    tripsLoading.value = true;
    try {
        const response = await axios.get('/traveller/trips');
        trips.value = response.data;
    } catch (e) {
        console.error('Trips fetch error', e);
    } finally {
        tripsLoading.value = false;
    }
};

const init = async () => {
    loading.value = true;
    await Promise.all([fetchProfile(), fetchTrips()]);
    loading.value = false;
};

// Actions
const handleFile = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.passport_image = file;
        fileName.value = file.name;
        error.value = '';
    }
};

const updateProfile = async () => {
    if (!form.value.passport_image && (!profile.value.documents || profile.value.documents.length === 0)) {
        error.value = "Please upload a passport copy.";
        return;
    }
    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('passport_number', form.value.passport_number);
        formData.append('phone_number', form.value.phone_number);
        if (form.value.passport_image) {
            formData.append('passport_image', form.value.passport_image);
        }
        const response = await axios.post('/traveller/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        profile.value = response.data.profile;
        toast.success('Profile updated');
        fileName.value = '';
        form.value.passport_image = null;
    } catch (e) {
        toast.error('Failed to update profile');
    } finally {
        saving.value = false;
    }
};

// Trip Actions
const openTripModal = () => {
    newTrip.value = { origin: '', destination: '', travel_date: '', luggage_capacity: 5 };
    showTripModal.value = true;
};
const closeTripModal = () => showTripModal.value = false;

const createTrip = async () => {
    tripSaving.value = true;
    try {
        const payload = {
            ...newTrip.value,
            available_capacity: newTrip.value.luggage_capacity // Initially fully available
        };
        await axios.post('/traveller/trips', payload);
        toast.success('Trip scheduled successfully');
        closeTripModal();
        fetchTrips();
    } catch (e) {
        toast.error('Failed to create trip');
    } finally {
        tripSaving.value = false;
    }
};

const cancelTrip = async (id) => {
    if (!confirm('Are you sure you want to cancel this trip?')) return;
    try {
        await axios.delete(`/traveller/trips/${id}`); // Or patch to cancelled status
        toast.success('Trip cancelled');
        fetchTrips();
    } catch (e) {
        toast.error('Failed to cancel trip');
    }
};

// Utilities
const formatDate = (date) => new Date(date).toLocaleDateString();
const getTripStatusColor = (status) => {
    switch(status) {
        case 'scheduled': return 'text-primary-400 bg-primary/20';
        case 'ongoing': return 'text-emerald-400 border-emerald-500/20 bg-emerald-500/10';
        case 'completed': return 'text-zinc-400 border-white/10';
        case 'cancelled': return 'text-red-400 border-red-500/20';
        default: return 'text-white border-white/10';
    }
};

onMounted(() => {
    init();
});
</script>
