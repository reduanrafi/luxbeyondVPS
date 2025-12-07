<template>
    <div>
        <div class="space-y-6">
            <!-- Profile Settings -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                    <User class="w-6 h-6 text-primary" />
                    Profile Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                        <input type="text" :value="user?.name" disabled
                            class="w-full px-4 py-3 rounded-lg border border-white/10 bg-gray-50 text-slate-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                        <input type="email" :value="user?.email" disabled
                            class="w-full px-4 py-3 rounded-lg border border-white/10 bg-gray-50 text-slate-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Role</label>
                        <input type="text" :value="user?.roles?.[0]?.name" disabled
                            class="w-full px-4 py-3 rounded-lg border border-white/10 bg-gray-50 text-slate-600">
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                    <Truck class="w-6 h-6 text-blue-600" />
                    Shipping Address
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Street Address</label>
                        <input v-model="shippingAddress.street" type="text" placeholder="123 Main Street"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">City</label>
                        <input v-model="shippingAddress.city" type="text" placeholder="Dhaka"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">State/Province</label>
                        <input v-model="shippingAddress.state" type="text" placeholder="Dhaka Division"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Postal Code</label>
                        <input v-model="shippingAddress.postalCode" type="text" placeholder="1200"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Country</label>
                        <input v-model="shippingAddress.country" type="text" placeholder="Bangladesh"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                        <input v-model="shippingAddress.phone" type="tel" placeholder="+880 1234 567890"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                </div>
                <button @click="saveShippingAddress"
                    class="mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg">
                    Save Shipping Address
                </button>
            </div>

            <!-- Billing Address -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <CreditCard class="w-6 h-6 text-green-600" />
                        Billing Address
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="sameAsShipping" type="checkbox"
                            class="w-5 h-5 text-primary rounded focus:ring-primary">
                        <span class="text-sm font-medium text-slate-700">Same as shipping</span>
                    </label>
                </div>
                <div v-if="!sameAsShipping" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Street Address</label>
                        <input v-model="billingAddress.street" type="text" placeholder="123 Main Street"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">City</label>
                        <input v-model="billingAddress.city" type="text" placeholder="Dhaka"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">State/Province</label>
                        <input v-model="billingAddress.state" type="text" placeholder="Dhaka Division"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Postal Code</label>
                        <input v-model="billingAddress.postalCode" type="text" placeholder="1200"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Country</label>
                        <input v-model="billingAddress.country" type="text" placeholder="Bangladesh"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                        <input v-model="billingAddress.phone" type="tel" placeholder="+880 1234 567890"
                            class="w-full px-4 py-3 rounded-lg border border-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                </div>
                <div v-else class="p-6 bg-gray-50 rounded-xl border border-white/10">
                    <p class="text-sm text-slate-600 flex items-center gap-2">
                        <CheckCircle class="w-5 h-5 text-green-600" />
                        Billing address will be the same as your shipping address
                    </p>
                </div>
                <button @click="saveBillingAddress"
                    class="mt-6 px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors shadow-md hover:shadow-lg">
                    Save Billing Address
                </button>
            </div>

            <!-- Notification Preferences -->
            <div class="bg-surface rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                    <Bell class="w-6 h-6 text-purple-600" />
                    Notification Preferences
                </h3>
                <div class="space-y-3">
                    <label
                        class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <input type="checkbox" checked class="w-5 h-5 text-primary rounded focus:ring-primary">
                        <span class="text-slate-700">Email notifications for order updates</span>
                    </label>
                    <label
                        class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <input type="checkbox" checked class="w-5 h-5 text-primary rounded focus:ring-primary">
                        <span class="text-slate-700">Email notifications for request approvals</span>
                    </label>
                    <label
                        class="flex items-center gap-3 cursor-pointer p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <input type="checkbox" class="w-5 h-5 text-primary rounded focus:ring-primary">
                        <span class="text-slate-700">Promotional emails and offers</span>
                    </label>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl border border-red-200 p-6 shadow-lg">
                <h3 class="text-xl font-bold text-red-900 mb-2 flex items-center gap-2">
                    <AlertTriangle class="w-6 h-6" />
                    Danger Zone
                </h3>
                <p class="text-sm text-red-700 mb-4">These actions are irreversible. Please be careful.</p>
                <button
                    class="px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-all shadow-md hover:shadow-lg hover:scale-105">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { User, Truck, CreditCard, Bell, AlertTriangle, CheckCircle } from 'lucide-vue-next';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

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

// Watch for changes in sameAsShipping checkbox
watch(sameAsShipping, (newValue) => {
    if (newValue) {
        billingAddress.value = { ...shippingAddress.value };
    }
});

const saveShippingAddress = () => {
    // TODO: Implement API call to save shipping address
    console.log('Saving shipping address:', shippingAddress.value);
    alert('Shipping address saved successfully!');

    // If same as shipping is checked, update billing address too
    if (sameAsShipping.value) {
        billingAddress.value = { ...shippingAddress.value };
    }
};

const saveBillingAddress = () => {
    // TODO: Implement API call to save billing address
    const addressToSave = sameAsShipping.value ? shippingAddress.value : billingAddress.value;
    console.log('Saving billing address:', addressToSave);
    alert('Billing address saved successfully!');
};
</script>
