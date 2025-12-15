<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-white">Admin Settings</h2>
            <p class="text-sm text-zinc-400 mt-1">Configure your store settings, payment methods, and notifications</p>
        </div>

        <!-- Tabs -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden">
            <div class="border-b border-white/5">
                <nav class="flex -mb-px px-2">
                    <button
                        @click="activeTab = 'general'"
                        :class="activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-zinc-400 hover:text-white hover:border-white/10'"
                        class="px-6 py-4 text-sm font-bold border-b-2 transition-colors flex items-center gap-2"
                    >
                        <Settings class="w-4 h-4" />
                        General
                    </button>
                    <button
                        @click="activeTab = 'payments'"
                        :class="activeTab === 'payments' ? 'border-primary text-primary' : 'border-transparent text-zinc-400 hover:text-white hover:border-white/10'"
                        class="px-6 py-4 text-sm font-bold border-b-2 transition-colors flex items-center gap-2"
                    >
                        <CreditCard class="w-4 h-4" />
                        Payment Methods
                    </button>
                    <button
                        @click="activeTab = 'notifications'"
                        :class="activeTab === 'notifications' ? 'border-primary text-primary' : 'border-transparent text-zinc-400 hover:text-white hover:border-white/10'"
                        class="px-6 py-4 text-sm font-bold border-b-2 transition-colors flex items-center gap-2"
                    >
                        <Bell class="w-4 h-4" />
                        Notifications
                    </button>
                </nav>
            </div>

            <!-- General Settings Tab -->
            <div v-if="activeTab === 'general'" class="p-8">
                <div class="space-y-8">
                    <div>
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-1 h-6 bg-primary rounded-full"></span>
                            Store Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Store Name</label>
                                <input v-model="generalSettings.site_name" type="text"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Store Email</label>
                                <input v-model="generalSettings.site_email" type="email"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Store Phone</label>
                                <input v-model="generalSettings.site_phone" type="tel"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Default Currency</label>
                                <select v-model="generalSettings.default_currency"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50">
                                    <option value="BDT" class="bg-zinc-900">BDT (৳)</option>
                                    <option value="USD" class="bg-zinc-900">USD ($)</option>
                                    <option value="CNY" class="bg-zinc-900">CNY (¥)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-white/5 pt-8">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-1 h-6 bg-primary rounded-full"></span>
                            Payment Settings
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    Minimum Payment % (Request Orders)
                                </label>
                                <div class="relative">
                                    <input v-model.number="generalSettings.min_payment_percentage_request" type="number" min="0" max="100" step="0.01"
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all pr-12">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 font-medium">%</span>
                                </div>
                                <p class="text-xs text-zinc-500 mt-2">Minimum percentage of total amount required for request orders (0-100%)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    Minimum Payment % (Shop Orders)
                                </label>
                                <div class="relative">
                                    <input v-model.number="generalSettings.min_payment_percentage_shop" type="number" min="0" max="100" step="0.01"
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all pr-12">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 font-medium">%</span>
                                </div>
                                <p class="text-xs text-zinc-500 mt-2">Minimum percentage of total amount required for shop orders (0-100%)</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-white/5 pt-8">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="w-1 h-6 bg-primary rounded-full"></span>
                            Delivery/Shipping Charges
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    Inside City Delivery Charge (৳)
                                </label>
                                <input v-model.number="generalSettings.delivery_charge_inside_city" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all"
                                    placeholder="0.00">
                                <p class="text-xs text-zinc-500 mt-2">Fixed delivery charge for orders within the city</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    Outside City Delivery Charge (৳)
                                </label>
                                <input v-model.number="generalSettings.delivery_charge_outside_city" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all"
                                    placeholder="0.00">
                                <p class="text-xs text-zinc-500 mt-2">Fixed delivery charge for orders outside the city</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    Free Delivery Threshold (৳)
                                </label>
                                <input v-model.number="generalSettings.free_delivery_threshold" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all"
                                    placeholder="0.00">
                                <p class="text-xs text-zinc-500 mt-2">Order amount above which delivery is free</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    Delivery Charge Per Kg (৳)
                                </label>
                                <input v-model.number="generalSettings.delivery_charge_per_kg" type="number" min="0" step="0.01"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary/50 transition-all"
                                    placeholder="0.00">
                                <p class="text-xs text-zinc-500 mt-2">Additional charge per kilogram (if weight-based)</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button @click="saveGeneralSettings" :disabled="saving"
                            class="px-8 py-3 bg-primary text-black font-bold rounded-xl hover:bg-primary transition-all shadow-lg shadow-primary-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <Settings class="w-5 h-5" />
                            {{ saving ? 'Saving Changes...' : 'Save All Changes' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Tab -->
            <div v-if="activeTab === 'payments'" class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-white">Payment Methods</h3>
                        <p class="text-sm text-zinc-400 mt-1">Manage accepted payment gateways and bank accounts</p>
                    </div>
                    <button @click="openPaymentModal()"
                        class="px-5 py-2.5 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-all shadow-lg shadow-primary-500/20 flex items-center gap-2">
                        <Plus class="w-5 h-5" />
                        Add Payment Method
                    </button>
                </div>

                <!-- Payment Methods List -->
                <div class="space-y-4">
                    <div v-for="method in paymentMethods" :key="method.id"
                        class="bg-white/5 border border-white/10 rounded-xl p-6 hover:border-primary/30 transition-all duration-300 group">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-bold text-white group-hover:text-primary transition-colors">{{ method.name }}</h4>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full border"
                                        :class="method.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'">
                                        {{ method.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span v-if="method.is_online" class="px-2.5 py-1 text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-full">
                                        API
                                    </span>
                                    <span v-else class="px-2.5 py-1 text-xs font-bold bg-orange-500/10 text-orange-400 border border-orange-500/20 rounded-full">
                                        Manual
                                    </span>
                                </div>
                                <p class="text-sm text-zinc-400 mb-4">{{ method.description || 'No description provided' }}</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm bg-black/20 p-4 rounded-lg border border-white/5">
                                    <div v-if="method.account_number">
                                        <span class="text-zinc-500 block text-xs uppercase tracking-wider mb-1">Account</span>
                                        <span class="font-mono text-white">{{ method.account_number }}</span>
                                    </div>
                                    <div v-if="method.bank_name">
                                        <span class="text-zinc-500 block text-xs uppercase tracking-wider mb-1">Bank</span>
                                        <span class="font-medium text-white">{{ method.bank_name }}</span>
                                    </div>
                                    <div v-if="method.branch_name">
                                        <span class="text-zinc-500 block text-xs uppercase tracking-wider mb-1">Branch</span>
                                        <span class="font-medium text-white">{{ method.branch_name }}</span>
                                    </div>
                                    <div v-if="method.fee > 0 || method.fee_percentage > 0">
                                        <span class="text-zinc-500 block text-xs uppercase tracking-wider mb-1">Fee</span>
                                        <span class="font-bold text-primary">
                                            <span v-if="method.fee > 0">৳{{ method.fee }}</span>
                                            <span v-if="method.fee_percentage > 0">{{ method.fee_percentage }}%</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 ml-6 self-start">
                                <button @click="openPaymentModal(method)"
                                    class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deletePaymentMethod(method.id)"
                                    class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="paymentMethods.length === 0" class="text-center py-16 bg-white/5 rounded-xl border border-dashed border-white/10">
                        <CreditCard class="w-12 h-12 text-zinc-600 mx-auto mb-4" />
                        <h3 class="text-lg font-bold text-white mb-2">No Payment Methods</h3>
                        <p class="text-zinc-500 mb-4">You haven't added any payment methods yet.</p>
                        <button @click="openPaymentModal()" class="text-primary hover:text-primary-400 font-bold text-sm">Add your first method</button>
                    </div>
                </div>
            </div>

            <!-- Notification Settings Tab -->
            <div v-if="activeTab === 'notifications'" class="p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-white">Notification Settings</h3>
                        <p class="text-sm text-zinc-400 mt-1">Configure automated alerts and messaging rules</p>
                    </div>
                    <button @click="openNotificationModal()"
                        class="px-5 py-2.5 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-all shadow-lg shadow-primary-500/20 flex items-center gap-2">
                        <Plus class="w-5 h-5" />
                        Add Notification Rule
                    </button>
                </div>

                <!-- Notification Settings List -->
                <div class="space-y-4">
                    <div v-for="setting in notificationSettings" :key="setting.id"
                        class="bg-white/5 border border-white/10 rounded-xl p-6 hover:border-primary/30 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-bold text-white">{{ formatEventType(setting.event_type) }}</h4>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full border border-white/10 capitalize"
                                        :class="getChannelClass(setting.channel)">
                                        {{ setting.channel }}
                                    </span>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full border capitalize"
                                        :class="setting.enabled ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'">
                                        {{ setting.enabled ? 'Enabled' : 'Disabled' }}
                                    </span>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full border border-white/10 capitalize"
                                        :class="getPriorityClass(setting.priority)">
                                        {{ setting.priority }}
                                    </span>
                                </div>
                                <p class="text-sm text-zinc-400 mb-2">
                                    <span class="font-bold text-zinc-300">Recipients:</span> {{ setting.recipient_type }}
                                    <span v-if="setting.delay_minutes > 0" class="ml-3">
                                        <span class="font-bold text-zinc-300">Delay:</span> {{ setting.delay_minutes }} minutes
                                    </span>
                                </p>
                                <p v-if="setting.description" class="text-sm text-zinc-500">{{ setting.description }}</p>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <button @click="openNotificationModal(setting)"
                                    class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deleteNotificationSetting(setting.id)"
                                    class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="notificationSettings.length === 0" class="text-center py-16 bg-white/5 rounded-xl border border-dashed border-white/10">
                        <Bell class="w-12 h-12 text-zinc-600 mx-auto mb-4" />
                        <h3 class="text-lg font-bold text-white mb-2">No Notification Rules</h3>
                        <p class="text-zinc-500 mb-4">You haven't configured any notification rules yet.</p>
                        <button @click="openNotificationModal()" class="text-primary hover:text-primary-400 font-bold text-sm">Create your first rule</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPaymentModal = false">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="relative bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <h3 class="text-xl font-bold text-white mb-6">
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
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="relative bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <h3 class="text-xl font-bold text-white mb-6">
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
    // Note: We're not setting the PaymentMethodForm component here, it is rendered in the template
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
        email: 'bg-blue-500/10 text-blue-400',
        sms: 'bg-green-500/10 text-green-400',
        push: 'bg-purple-500/10 text-purple-400',
        in_app: 'bg-orange-500/10 text-orange-400',
        webhook: 'bg-zinc-500/10 text-zinc-400',
    };
    return classes[channel] || 'bg-zinc-500/10 text-zinc-400';
};

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-zinc-500/10 text-zinc-400',
        normal: 'bg-blue-500/10 text-blue-400',
        high: 'bg-orange-500/10 text-orange-400',
        urgent: 'bg-red-500/10 text-red-400',
    };
    return classes[priority] || 'bg-zinc-500/10 text-zinc-400';
};

onMounted(() => {
    fetchGeneralSettings();
    fetchPaymentMethods();
    fetchNotificationSettings();
});
</script>
