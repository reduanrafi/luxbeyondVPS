<template>
    <div class="min-h-screen bg-black pt-20 pb-12">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-white mb-8">Traveller Dashboard</h1>

            <!-- Loading -->
            <div v-if="loading" class="animate-pulse space-y-4">
                <div class="h-40 bg-zinc-900 rounded-2xl"></div>
                <div class="h-60 bg-zinc-900 rounded-2xl"></div>
            </div>

            <div v-else class="space-y-8">
                <!-- Status Card -->
                <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                    <h2 class="text-xl font-bold text-white mb-4">Verification Status</h2>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div v-if="profile?.verification_status === 'approved'" class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
                                <CheckCircle class="w-6 h-6 text-emerald-500" />
                                <div>
                                    <p class="font-bold text-emerald-400">Verified Traveller</p>
                                    <p class="text-xs text-emerald-400/70">You can now accept orders and travel requests.</p>
                                </div>
                            </div>
                            <div v-else-if="profile?.verification_status === 'rejected'" class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl flex items-center gap-3">
                                <XCircle class="w-6 h-6 text-red-500" />
                                <div>
                                    <p class="font-bold text-red-400">Application Rejected</p>
                                    <p class="text-xs text-red-400/70">Please update your documents and try again.</p>
                                </div>
                            </div>
                            <div v-else class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center gap-3">
                                <Clock class="w-6 h-6 text-amber-500" />
                                <div>
                                    <p class="font-bold text-amber-400">Pending Verification</p>
                                    <p class="text-xs text-amber-400/70">We are reviewing your application. This usually takes 24-48 hours.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="bg-zinc-900 border border-white/10 rounded-2xl p-6">
                    <h2 class="text-xl font-bold text-white mb-6">Traveller Profile</h2>
                    
                    <form @submit.prevent="updateProfile" class="space-y-6">
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Passport Number</label>
                                <input v-model="form.passport_number" type="text" required
                                    class="w-full px-4 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Phone Number</label>
                                <input v-model="form.phone_number" type="tel" required
                                    class="w-full px-4 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            </div>
                         </div>

                         <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-2">Upload Document (Passport/ID)</label>
                            <div class="border-2 border-dashed border-white/10 rounded-lg p-6 text-center hover:bg-white/5 transition-colors cursor-pointer"
                                @click="$refs.fileInput.click()">
                                <input ref="fileInput" type="file" @change="handleFile" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                                <Upload class="w-8 h-8 text-zinc-400 mx-auto mb-2" />
                                <p class="text-sm text-zinc-300">{{ fileName || 'Click to upload' }}</p>
                                <p class="text-xs text-zinc-500 mt-1">PDF, JPG, PNG up to 2MB</p>
                            </div>
                         </div>
                         
                         <div v-if="profile?.documents && profile.documents.length > 0" class="mt-4">
                            <p class="text-xs text-zinc-500 mb-2">Uploaded Documents:</p>
                             <div class="flex gap-2">
                                <div v-for="(doc, idx) in profile.documents" :key="idx" class="px-3 py-1 bg-white/5 rounded text-xs text-zinc-300 border border-white/10">
                                    Document {{ idx + 1 }}
                                </div>
                             </div>
                         </div>

                         <div class="pt-4">
                            <button type="submit" :disabled="saving"
                                class="w-full md:w-auto px-8 py-3 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <span v-if="saving" class="animate-spin rounded-full h-4 w-4 border-b-2 border-black"></span>
                                {{ saving ? 'Saving...' : 'Update Profile' }}
                            </button>
                         </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import axios from '../axios';
import { CheckCircle, XCircle, Clock, Upload } from 'lucide-vue-next';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const profile = ref(null);
const fileInput = ref(null);
const fileName = ref('');
const form = ref({
    passport_number: '',
    phone_number: '',
    passport_image: null
});

const fetchProfile = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/traveller/profile');
        if (response.data.status !== 'not_registered') {
            profile.value = response.data.profile;
            form.value.passport_number = profile.value.passport_number;
            form.value.phone_number = profile.value.phone_number;
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
    } finally {
        loading.value = false;
    }
};

const handleFile = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.passport_image = file;
        fileName.value = file.name;
    }
};

const updateProfile = async () => {
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
        toast.success('Profile updated successfully');
        fileName.value = ''; // Clear file selection visualization
        form.value.passport_image = null;
    } catch (error) {
        console.error('Error updating profile:', error);
        toast.error('Failed to update profile');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchProfile();
});
</script>
