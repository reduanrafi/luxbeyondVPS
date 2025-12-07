<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Admin Settings</h2>
            <p class="text-sm text-slate-600 mt-1">Configure your store settings, payment methods, and notifications</p>
        </div>

        <!-- Tabs -->
        <div class="bg-surface rounded-xl shadow-md border border-white/10">
            <div class="border-b border-white/10">
                <nav class="flex -mb-px">
                    <button
                        @click="activeTab = 'general'"
                        :class="activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-slate-300 hover:border-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                    >
                        <Settings class="w-4 h-4 inline mr-2" />
                        General
                    </button>
                    <button
                        @click="activeTab = 'payments'"
                        :class="activeTab === 'payments' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-slate-300 hover:border-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                    >
                        <CreditCard class="w-4 h-4 inline mr-2" />
                        Payment Methods
                    </button>
                    <button
                        @click="activeTab = 'notifications'"
                        :class="activeTab === 'notifications' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-slate-300 hover:border-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                    >
                        <Bell class="w-4 h-4 inline mr-2" />
                        Notifications
                    </button>
                </nav>
            </div>

            <!-- General Settings Tab -->
            <div v-if="activeTab === 'general'" class="p-6">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Store Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Store Name</label>
                                <input v-model="generalSettings.site_name" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Store Email</label>
                                <input v-model="generalSettings.site_email" type="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Store Phone</label>
                                <input v-model="generalSettings.site_phone" type="tel"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Default Currency</label>
                                <select v-model="generalSettings.default_currency"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                                    <option value="BDT">BDT (৳)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="CNY">CNY (¥)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Payment Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Minimum Payment % (Request Orders)
                                </label>
                                <div class="relative">
                                    <input v-model.number="generalSettings.min_payment_percentage_request" type="number" min="0" max="100" step="0.01"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Minimum percentage of total amount required for request orders (0-100%)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Minimum Payment % (Shop Orders)
                                </label>
                                <div class="relative">
                                    <input v-model.number="generalSettings.min_payment_percentage_shop" type="number" min="0" max="100" step="0.01"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Minimum percentage of total amount required for shop orders (0-100%)</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Delivery/Shipping Charges</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Inside City Delivery Charge (৳)
                                </label>
                                <input v-model.number="generalSettings.delivery_charge_inside_city" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="0.00">
                                <p class="text-xs text-gray-500 mt-1">Fixed delivery charge for orders within the city</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Outside City Delivery Charge (৳)
                                </label>
                                <input v-model.number="generalSettings.delivery_charge_outside_city" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="0.00">
                                <p class="text-xs text-gray-500 mt-1">Fixed delivery charge for orders outside the city</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Free Delivery Threshold (৳)
                                </label>
                                <input v-model.number="generalSettings.free_delivery_threshold" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="0.00">
                                <p class="text-xs text-gray-500 mt-1">Order amount above which delivery is free</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">
                                    Delivery Charge Per Kg (৳)
                                </label>
                                <input v-model.number="generalSettings.delivery_charge_per_kg" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="0.00">
                                <p class="text-xs text-gray-500 mt-1">Additional charge per kilogram (if weight-based)</p>
                            </div>
                        </div>
                    </div>

                    <button @click="saveGeneralSettings" :disabled="saving"
                        class="px-6 py-2 bg-primary text-slate-900 font-semibold rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                        {{ saving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>

            <!-- Payment Methods Tab -->
            <div v-if="activeTab === 'payments'" class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Payment Methods</h3>
                    <button @click="openPaymentModal()"
                        class="px-4 py-2 bg-primary text-slate-900 font-semibold rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        Add Payment Method
                    </button>
                </div>

                <!-- Payment Methods List -->
                <div class="space-y-4">
                    <div v-for="method in paymentMethods" :key="method.id"
                        class="border border-white/10 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-bold text-slate-900">{{ method.name }}</h4>
                                    <span class="px-2 py-1 text-xs rounded-full"
                                        :class="method.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-slate-300'">
                                        {{ method.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span v-if="method.is_online" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">
                                        API
                                    </span>
                                    <span v-else class="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded-full">
                                        Manual
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">{{ method.description || 'No description' }}</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div v-if="method.account_number">
                                        <span class="text-gray-500">Account:</span>
                                        <span class="font-medium ml-1">{{ method.account_number }}</span>
                                    </div>
                                    <div v-if="method.bank_name">
                                        <span class="text-gray-500">Bank:</span>
                                        <span class="font-medium ml-1">{{ method.bank_name }}</span>
                                    </div>
                                    <div v-if="method.branch_name">
                                        <span class="text-gray-500">Branch:</span>
                                        <span class="font-medium ml-1">{{ method.branch_name }}</span>
                                    </div>
                                    <div v-if="method.fee > 0 || method.fee_percentage > 0">
                                        <span class="text-gray-500">Fee:</span>
                                        <span class="font-medium ml-1">
                                            <span v-if="method.fee > 0">৳{{ method.fee }}</span>
                                            <span v-if="method.fee_percentage > 0">{{ method.fee_percentage }}%</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <button @click="openPaymentModal(method)"
                                    class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                    <Edit class="w-4 h-4 text-blue-600" />
                                </button>
                                <button @click="deletePaymentMethod(method.id)"
                                    class="p-2 hover:bg-red-50 rounded-lg transition-colors">
                                    <Trash2 class="w-4 h-4 text-red-600" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="paymentMethods.length === 0" class="text-center py-8 text-gray-500">
                        No payment methods added yet. Click "Add Payment Method" to get started.
                    </div>
                </div>
            </div>

            <!-- Notification Settings Tab -->
            <div v-if="activeTab === 'notifications'" class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Notification Settings</h3>
                    <button @click="openNotificationModal()"
                        class="px-4 py-2 bg-primary text-slate-900 font-semibold rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        Add Notification Rule
                    </button>
                </div>

                <!-- Notification Settings List -->
                <div class="space-y-4">
                    <div v-for="setting in notificationSettings" :key="setting.id"
                        class="border border-white/10 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-bold text-slate-900">{{ formatEventType(setting.event_type) }}</h4>
                                    <span class="px-2 py-1 text-xs rounded-full capitalize"
                                        :class="getChannelClass(setting.channel)">
                                        {{ setting.channel }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full capitalize"
                                        :class="setting.enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-slate-300'">
                                        {{ setting.enabled ? 'Enabled' : 'Disabled' }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full capitalize"
                                        :class="getPriorityClass(setting.priority)">
                                        {{ setting.priority }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">
                                    <span class="font-medium">Recipients:</span> {{ setting.recipient_type }}
                                    <span v-if="setting.delay_minutes > 0" class="ml-3">
                                        <span class="font-medium">Delay:</span> {{ setting.delay_minutes }} minutes
                                    </span>
                                </p>
                                <p v-if="setting.description" class="text-sm text-gray-500">{{ setting.description }}</p>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <button @click="openNotificationModal(setting)"
                                    class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                    <Edit class="w-4 h-4 text-blue-600" />
                                </button>
                                <button @click="deleteNotificationSetting(setting.id)"
                                    class="p-2 hover:bg-red-50 rounded-lg transition-colors">
                                    <Trash2 class="w-4 h-4 text-red-600" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="notificationSettings.length === 0" class="text-center py-8 text-gray-500">
                        No notification rules configured. Click "Add Notification Rule" to get started.
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPaymentModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
                <div class="relative bg-surface rounded-lg shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">
                        {{ editingPayment ? 'Edit Payment Method' : 'Add Payment Method' }}
                    </h3>
                    <PaymentMethodForm
                        :method="editingPayment"
                        @save="handlePaymentSave"
                        @cancel="showPaymentModal = false"
                    />
                </div>
            </div>
        </div>

        <!-- Notification Setting Modal -->
        <div v-if="showNotificationModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showNotificationModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
                <div class="relative bg-surface rounded-lg shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">
                        {{ editingNotification ? 'Edit Notification Rule' : 'Add Notification Rule' }}
                    </h3>
                    <NotificationForm
                        :setting="editingNotification"
                        @save="handleNotificationSave"
                        @cancel="showNotificationModal = false"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Settings, CreditCard, Bell, Plus, Edit, Trash2 } from 'lucide-vue-next';
import axios from '../../axios';
import PaymentMethodForm from './components/PaymentMethodForm.vue';
import NotificationForm from './components/NotificationForm.vue';

const activeTab = ref('general');
const saving = ref(false);
const showPaymentModal = ref(false);
const showNotificationModal = ref(false);
const editingPayment = ref(null);
const editingNotification = ref(null);

const generalSettings = ref({
    site_name: '',
    site_email: '',
    site_phone: '',
    default_currency: 'BDT',
    min_payment_percentage_request: 0,
    min_payment_percentage_shop: 0,
    delivery_charge_inside_city: 0,
    delivery_charge_outside_city: 0,
    free_delivery_threshold: 0,
    delivery_charge_per_kg: 0,
});

const paymentMethods = ref([]);
const notificationSettings = ref([]);

const fetchGeneralSettings = async () => {
    try {
        const response = await axios.get('/admin/settings', { params: { group: 'general' } });
        const settings = response.data;
        settings.forEach(setting => {
            if (generalSettings.value.hasOwnProperty(setting.key)) {
                generalSettings.value[setting.key] = setting.value;
            }
        });
    } catch (error) {
        console.error('Error fetching general settings:', error);
    }
};

const saveGeneralSettings = async () => {
    saving.value = true;
    try {
        const settings = Object.keys(generalSettings.value).map(key => ({
            key,
            value: generalSettings.value[key]
        }));
        await axios.post('/admin/settings/update', { settings });
        alert('Settings saved successfully!');
    } catch (error) {
        console.error('Error saving settings:', error);
        alert('Error saving settings');
    } finally {
        saving.value = false;
    }
};

const fetchPaymentMethods = async () => {
    try {
        const response = await axios.get('/admin/settings/payment-methods');
        paymentMethods.value = response.data;
    } catch (error) {
        console.error('Error fetching payment methods:', error);
    }
};

const openPaymentModal = (method = null) => {
    editingPayment.value = method;
    showPaymentModal.value = true;
};

const handlePaymentSave = async () => {
    await fetchPaymentMethods();
    showPaymentModal.value = false;
    editingPayment.value = null;
};

const deletePaymentMethod = async (id) => {
    if (!confirm('Are you sure you want to delete this payment method?')) return;
    try {
        await axios.delete(`/admin/settings/payment-methods/${id}`);
        await fetchPaymentMethods();
    } catch (error) {
        console.error('Error deleting payment method:', error);
        alert('Error deleting payment method');
    }
};

const fetchNotificationSettings = async () => {
    try {
        const response = await axios.get('/admin/settings/notifications');
        notificationSettings.value = response.data;
    } catch (error) {
        console.error('Error fetching notification settings:', error);
    }
};

const openNotificationModal = (setting = null) => {
    editingNotification.value = setting;
    showNotificationModal.value = true;
};

const handleNotificationSave = async () => {
    await fetchNotificationSettings();
    showNotificationModal.value = false;
    editingNotification.value = null;
};

const deleteNotificationSetting = async (id) => {
    if (!confirm('Are you sure you want to delete this notification rule?')) return;
    try {
        await axios.delete(`/admin/settings/notifications/${id}`);
        await fetchNotificationSettings();
    } catch (error) {
        console.error('Error deleting notification setting:', error);
        alert('Error deleting notification setting');
    }
};

const formatEventType = (type) => {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getChannelClass = (channel) => {
    const classes = {
        email: 'bg-blue-100 text-blue-700',
        sms: 'bg-green-100 text-green-700',
        push: 'bg-purple-100 text-purple-700',
        in_app: 'bg-orange-100 text-orange-700',
        webhook: 'bg-gray-100 text-slate-300',
    };
    return classes[channel] || 'bg-gray-100 text-slate-300';
};

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-gray-100 text-slate-300',
        normal: 'bg-blue-100 text-blue-700',
        high: 'bg-orange-100 text-orange-700',
        urgent: 'bg-red-100 text-red-700',
    };
    return classes[priority] || 'bg-gray-100 text-slate-300';
};

onMounted(() => {
    fetchGeneralSettings();
    fetchPaymentMethods();
    fetchNotificationSettings();
});
</script>
