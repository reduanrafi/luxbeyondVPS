<template>
    <div>
        <div class="space-y-8">
            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-2xl font-serif font-bold text-white uppercase tracking-widest">Settings</h2>
                <p class="text-xs text-slate-400 mt-2 font-light tracking-wide">Manage your profile and preferences</p>
            </div>

            <!-- Profile Settings -->
            <div class="bg-surface border border-white/5 p-8 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <User class="w-24 h-24 text-primary" />
                </div>

                <h3
                    class="text-lg font-serif text-white uppercase tracking-widest mb-8 flex items-center gap-3 border-b border-white/5 pb-4">
                    <User class="w-5 h-5 text-primary" />
                    Profile Information
                </h3>

                <form @submit.prevent="updateProfile" class="space-y-6 relative z-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Full
                                Name</label>
                            <input v-model="form.name" type="text"
                                class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email
                                Address</label>
                            <input v-model="form.email" type="email"
                                class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/5">
                        <h4 class="text-sm font-bold text-white uppercase tracking-wide mb-4">Change Password <span
                                class="text-slate-500 text-[10px] normal-case ml-2">(Optional)</span></h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Current
                                    Password</label>
                                <input v-model="form.current_password" type="password" placeholder="••••••••"
                                    class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">New
                                    Password</label>
                                <input v-model="form.password" type="password" placeholder="••••••••"
                                    class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm
                                    Password</label>
                                <input v-model="form.password_confirmation" type="password" placeholder="••••••••"
                                    class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <p v-if="message" :class="messageType === 'success' ? 'text-green-500' : 'text-red-500'"
                            class="text-xs font-bold uppercase tracking-wider animate-pulse">
                            {{ message }}
                        </p>
                        <button type="submit" :disabled="loading"
                            class="px-8 py-3 bg-primary text-slate-900 font-bold uppercase tracking-widest hover:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-xs">
                            {{ loading ? 'Updating...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Shipping Address -->
            <div class="bg-surface border border-white/5 p-8 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <Truck class="w-24 h-24 text-white" />
                </div>
                <h3
                    class="text-lg font-serif text-white uppercase tracking-widest mb-8 flex items-center gap-3 border-b border-white/5 pb-4">
                    <Truck class="w-5 h-5 text-slate-400" />
                    Shipping Address
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Street
                            Address</label>
                        <input v-model="shippingAddress.street" type="text" placeholder="123 Luxury Lane"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">City</label>
                        <input v-model="shippingAddress.city" type="text" placeholder="Dhaka"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">State/Province</label>
                        <input v-model="shippingAddress.state" type="text" placeholder="Dhaka Division"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Postal
                            Code</label>
                        <input v-model="shippingAddress.postalCode" type="text" placeholder="1200"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Country</label>
                        <input v-model="shippingAddress.country" type="text" placeholder="Bangladesh"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phone
                            Number</label>
                        <input v-model="shippingAddress.phone" type="tel" placeholder="+880 1234 567890"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                </div>
                <div class="mt-8 flex justify-end relative z-10">
                    <button @click="saveShippingAddress"
                        class="px-8 py-3 border border-white/10 text-white font-bold uppercase tracking-widest hover:bg-white hover:text-slate-900 transition-all text-xs">
                        Save Address
                    </button>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="bg-surface border border-white/5 p-8 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-8 border-b border-white/5 pb-4">
                    <h3 class="text-lg font-serif text-white uppercase tracking-widest flex items-center gap-3">
                        <CreditCard class="w-5 h-5 text-slate-400" />
                        Billing Address
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer group/check">
                        <input v-model="sameAsShipping" type="checkbox"
                            class="w-4 h-4 bg-background border-white/20 rounded-none text-primary focus:ring-offset-0 focus:ring-primary">
                        <span
                            class="text-xs font-bold text-slate-400 uppercase tracking-wide group-hover/check:text-white transition-colors">Same
                            as shipping</span>
                    </label>
                </div>

                <div v-show="!sameAsShipping" class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    <!-- Billing Form Fields (Same structure as shipping) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Street
                            Address</label>
                        <input v-model="billingAddress.street" type="text" placeholder="123 Luxury Lane"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <!-- Other billing fields... kept succinct for brevity, functionality mimics shipping -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">City</label>
                        <input v-model="billingAddress.city" type="text" placeholder="Dhaka"
                            class="w-full px-4 py-3 bg-background/50 border border-white/10 text-white rounded-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder-slate-600">
                    </div>
                    <!-- ... -->
                </div>

                <div v-if="sameAsShipping" class="p-6 bg-white/5 border border-white/5 text-center">
                    <p class="text-sm text-slate-400 flex items-center justify-center gap-2">
                        <CheckCircle class="w-5 h-5 text-primary" />
                        Billing address matches shipping address
                    </p>
                </div>

                <div v-if="!sameAsShipping" class="mt-8 flex justify-end">
                    <button @click="saveBillingAddress"
                        class="px-8 py-3 border border-white/10 text-white font-bold uppercase tracking-widest hover:bg-white hover:text-slate-900 transition-all text-xs">
                        Save Billing
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-500/5 border border-red-500/20 p-8 relative overflow-hidden">
                <h3 class="text-lg font-serif text-red-500 uppercase tracking-widest mb-2 flex items-center gap-3">
                    <AlertTriangle class="w-5 h-5" />
                    Danger Zone
                </h3>
                <p class="text-xs text-red-400/70 mb-6 font-light">Irreversible actions regarding your account.</p>
                <div class="flex justify-end">
                    <button
                        class="px-6 py-3 bg-red-500/10 text-red-500 border border-red-500/20 font-bold uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all text-xs">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { User, Truck, CreditCard, Bell, AlertTriangle, CheckCircle } from 'lucide-vue-next';
import axios from '../../axios';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const loading = ref(false);
const message = ref('');
const messageType = ref('');

const form = ref({
    name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: ''
});

// Initialize form with user data
onMounted(() => {
    if (user.value) {
        form.value.name = user.value.name;
        form.value.email = user.value.email;
    }
});

// Watch for user data changes (e.g. on page reload)
watch(user, (newUser) => {
    if (newUser) {
        form.value.name = newUser.name;
        form.value.email = newUser.email;
    }
});

const updateProfile = async () => {
    loading.value = true;
    message.value = '';

    try {
        const response = await axios.put('/auth/profile', form.value);

        // Update auth store with new user data
        authStore.user = response.data.user;

        message.value = 'Profile updated successfully';
        messageType.value = 'success';

        // Clear password fields
        form.value.current_password = '';
        form.value.password = '';
        form.value.password_confirmation = '';

    } catch (error) {
        console.error('Update error:', error);
        message.value = error.response?.data?.message || 'Failed to update profile';
        messageType.value = 'error';

        // If validation errors exist, maybe append them (simplified for now)
        if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            if (errors.length > 0) message.value = errors[0];
        }
    } finally {
        loading.value = false;

        // Clear success message after 3 seconds
        if (messageType.value === 'success') {
            setTimeout(() => {
                message.value = '';
            }, 3000);
        }
    }
};

const shippingAddress = ref({
    street: '',
    city: '',
    state: '',
    postalCode: '',
    country: '',
    phone: ''
});

const billingAddress = ref({
    street: '',
    city: '',
    state: '',
    postalCode: '',
    country: '',
    phone: ''
});

const sameAsShipping = ref(true);

watch(sameAsShipping, (newValue) => {
    if (newValue) {
        billingAddress.value = { ...shippingAddress.value };
    }
});

const saveShippingAddress = () => {
    // Placeholder for future implementation
    console.log('Saving shipping address:', shippingAddress.value);

    // If same as shipping is checked, update billing address too
    if (sameAsShipping.value) {
        billingAddress.value = { ...shippingAddress.value };
    }
};

const saveBillingAddress = () => {
    const addressToSave = sameAsShipping.value ? shippingAddress.value : billingAddress.value;
    console.log('Saving billing address:', addressToSave);
};
</script>
