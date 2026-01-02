<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Payment Method Name *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="bKash">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Type *</label>
                <select v-model="form.type" required @change="onTypeChange"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="rocket">Rocket</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cash_on_delivery">Cash on Delivery</option>
                    <option value="stripe">Stripe</option>
                    <option value="paypal">PayPal</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div v-if="form.type === 'bkash' || form.type === 'nagad' || form.type === 'rocket'" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Sub Type *</label>
                <select v-model="form.sub_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                    <option value="api">API Integration</option>
                    <option value="manual">Manual (Account Number)</option>
                </select>
            </div>
            <div v-if="form.sub_type === 'manual'">
                <label class="block text-sm font-medium text-slate-300 mb-1">Account Number *</label>
                <input v-model="form.account_number" type="text" :required="form.sub_type === 'manual'"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="01XXXXXXXXX">
            </div>
        </div>

        <!-- API Configuration Fields -->
        <div v-if="(form.type === 'bkash' || form.type === 'nagad' || form.type === 'rocket') && form.sub_type === 'api'" 
            class="border border-white/10 rounded-lg p-4 space-y-4">
            <h3 class="text-sm font-semibold text-white mb-3">API Credentials</h3>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input v-model="apiConfig.is_sandbox" type="checkbox"
                        class="rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm text-slate-300">Sandbox Mode (Testing)</span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-6">Enable for testing, disable for production</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">App Key *</label>
                    <input v-model="apiConfig.app_key" type="text" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                        placeholder="Your App Key">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">App Secret *</label>
                    <input v-model="apiConfig.app_secret" type="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                        placeholder="Your App Secret">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Username *</label>
                    <input v-model="apiConfig.username" type="text" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                        placeholder="Your Username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Password *</label>
                    <input v-model="apiConfig.password" type="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                        placeholder="Your Password">
                </div>
            </div>
        </div>

        <div v-if="form.type === 'bank_transfer'" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Bank Name *</label>
                <input v-model="form.bank_name" type="text" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="Bank Name">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Branch Name *</label>
                <input v-model="form.branch_name" type="text" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="Branch Name">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Account Number *</label>
                <input v-model="form.account_number" type="text" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="Account Number">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Account Name *</label>
                <input v-model="form.account_name" type="text" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="Account Holder Name">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Routing Number</label>
                <input v-model="form.routing_number" type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="Routing Number">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">SWIFT Code</label>
                <input v-model="form.swift_code" type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="SWIFT Code">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Processing Fee (৳)</label>
                <input v-model.number="form.fee" type="number" min="0" step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="0.00">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Fee Percentage (%)</label>
                <input v-model.number="form.fee_percentage" type="number" min="0" max="100" step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="0.00">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
            <textarea v-model="form.description" rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Payment method description..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Payment Instructions</label>
            <textarea v-model="form.instructions" rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Instructions for customers on how to pay..."></textarea>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <label class="flex items-center">
                <input v-model="form.is_active" type="checkbox"
                    class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-slate-300">Active</span>
            </label>
            <label class="flex items-center">
                <input v-model="form.is_online" type="checkbox"
                    class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-slate-300">Online/API</span>
            </label>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Sort Order</label>
                <input v-model.number="form.sort_order" type="number" min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="$emit('cancel')"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="submit" :disabled="saving"
                class="px-4 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Save' }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from '../../../axios';

const props = defineProps({
    method: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['save', 'cancel']);

const saving = ref(false);

const form = ref({
    name: '',
    type: 'bkash',
    sub_type: 'manual',
    description: '',
    account_number: '',
    account_name: '',
    bank_name: '',
    branch_name: '',
    routing_number: '',
    swift_code: '',
    instructions: '',
    is_active: true,
    is_online: false,
    sort_order: 0,
    fee: 0,
    fee_percentage: 0,
    config: null,
});

// API Configuration (stored in form.config)
const apiConfig = ref({
    is_sandbox: true,
    app_key: '',
    app_secret: '',
    username: '',
    password: '',
});

watch(() => props.method, (newMethod) => {
    if (newMethod) {
        form.value = { ...newMethod };
        // Load API config if it exists
        if (newMethod.config && typeof newMethod.config === 'object') {
            apiConfig.value = {
                is_sandbox: newMethod.config.is_sandbox ?? true,
                app_key: newMethod.config.app_key ?? '',
                app_secret: newMethod.config.app_secret ?? '',
                username: newMethod.config.username ?? '',
                password: newMethod.config.password ?? '',
            };
        } else {
            // Reset to defaults
            apiConfig.value = {
                is_sandbox: true,
                app_key: '',
                app_secret: '',
                username: '',
                password: '',
            };
        }
    } else {
        form.value = {
            name: '',
            type: 'bkash',
            sub_type: 'manual',
            description: '',
            account_number: '',
            account_name: '',
            bank_name: '',
            branch_name: '',
            routing_number: '',
            swift_code: '',
            instructions: '',
            is_active: true,
            is_online: false,
            sort_order: 0,
            fee: 0,
            fee_percentage: 0,
            config: null,
        };
        apiConfig.value = {
            is_sandbox: true,
            app_key: '',
            app_secret: '',
            username: '',
            password: '',
        };
    }
}, { immediate: true });

const onTypeChange = () => {
    if (form.value.type === 'bank_transfer') {
        form.value.sub_type = null;
    } else if (form.value.type === 'bkash' || form.value.type === 'nagad' || form.value.type === 'rocket') {
        form.value.sub_type = form.value.sub_type || 'manual';
    }
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        // Prepare form data
        const formData = { ...form.value };
        
        // If API integration is selected, include config
        if (form.value.sub_type === 'api' && (form.value.type === 'bkash' || form.value.type === 'nagad' || form.value.type === 'rocket')) {
            formData.config = { ...apiConfig.value };
        } else {
            // Clear config if not using API
            formData.config = null;
        }
        
        if (props.method) {
            await axios.put(`/admin/settings/payment-methods/${props.method.id}`, formData);
        } else {
            await axios.post('/admin/settings/payment-methods', formData);
        }
        emit('save');
    } catch (error) {
        console.error('Error saving payment method:', error);
        alert(error.response?.data?.message || 'Error saving payment method');
    } finally {
        saving.value = false;
    }
};
</script>

