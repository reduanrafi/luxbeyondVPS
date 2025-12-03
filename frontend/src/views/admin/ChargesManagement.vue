<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Charges Management</h2>
                <p class="text-sm text-slate-600 mt-1">Manage currencies, shipping charges, taxes, and other fees</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button
                        @click="activeTab = 'currencies'"
                        :class="activeTab === 'currencies' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                    >
                        <DollarSign class="w-4 h-4 inline mr-2" />
                        Currencies
                    </button>
                    <button
                        @click="activeTab = 'charges'"
                        :class="activeTab === 'charges' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors"
                    >
                        <Receipt class="w-4 h-4 inline mr-2" />
                        Charges
                    </button>
                </nav>
            </div>

            <!-- Currencies Tab -->
            <div v-if="activeTab === 'currencies'" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-slate-900">Currency Management</h3>
                    <button @click="showCurrencyModal = true; editingCurrency = null"
                        class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-all shadow-sm flex items-center gap-2">
                        <Plus class="w-5 h-5" />
                        Add Currency
                    </button>
                </div>

                <!-- Currencies Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Symbol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate to Base</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="currencies.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No currencies found</td>
                            </tr>
                            <tr v-for="currency in currencies" :key="currency.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-900">{{ currency.code }}</span>
                                    <span v-if="currency.is_base" class="ml-2 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full">Base</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ currency.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ currency.symbol }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ currency.rate_to_base }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="currency.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                        {{ currency.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="editCurrency(currency)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <Edit class="w-4 h-4 inline" />
                                    </button>
                                    <button @click="deleteCurrency(currency.id)" class="text-red-600 hover:text-red-900">
                                        <Trash2 class="w-4 h-4 inline" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charges Tab -->
            <div v-if="activeTab === 'charges'" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-slate-900">Charges Management</h3>
                    <button @click="showChargeModal = true; editingCharge = null"
                        class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-all shadow-sm flex items-center gap-2">
                        <Plus class="w-5 h-5" />
                        Add Charge
                    </button>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <input type="text" v-model="chargeFilters.search" @input="handleChargeSearch" placeholder="Search charges..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <select v-model="chargeFilters.type" @change="fetchCharges(1)"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                        <option value="">All Types</option>
                        <option value="shipping">Shipping</option>
                        <option value="weight">Weight</option>
                        <option value="tax">Tax</option>
                        <option value="service_fee">Service Fee</option>
                        <option value="handling">Handling</option>
                        <option value="custom">Custom</option>
                    </select>
                    <select v-model="chargeFilters.currency_id" @change="fetchCharges(1)"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                        <option value="">All Currencies</option>
                        <option v-for="currency in allCurrencies" :key="currency.id" :value="currency.id">
                            {{ currency.code }}
                        </option>
                    </select>
                    <button @click="resetChargeFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Reset
                    </button>
                </div>

                <!-- Charges Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calculation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="charges.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No charges found</td>
                            </tr>
                            <tr v-for="charge in charges" :key="charge.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ charge.name }}</div>
                                    <div v-if="charge.description" class="text-xs text-gray-500 mt-1">{{ charge.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">
                                        {{ charge.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ charge.currency?.code || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                    {{ charge.calculation_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span v-if="charge.calculation_type === 'percentage'">{{ charge.value }}%</span>
                                    <span v-else>{{ charge.currency?.symbol || '$' }}{{ charge.value }}</span>
                                    <span v-if="charge.min_value || charge.max_value" class="text-xs text-gray-500 block">
                                        ({{ charge.min_value ? `Min: ${charge.min_value}` : '' }}{{ charge.min_value && charge.max_value ? ', ' : '' }}{{ charge.max_value ? `Max: ${charge.max_value}` : '' }})
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="charge.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                        {{ charge.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="editCharge(charge)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <Edit class="w-4 h-4 inline" />
                                    </button>
                                    <button @click="deleteCharge(charge.id)" class="text-red-600 hover:text-red-900">
                                        <Trash2 class="w-4 h-4 inline" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Currency Modal -->
        <div v-if="showCurrencyModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showCurrencyModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">
                        {{ editingCurrency ? 'Edit Currency' : 'Add Currency' }}
                    </h3>
                    <form @submit.prevent="saveCurrency">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency Code *</label>
                                <input v-model="currencyForm.code" type="text" maxlength="3" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="USD">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency Name *</label>
                                <input v-model="currencyForm.name" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="United States Dollar">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Symbol *</label>
                                <input v-model="currencyForm.symbol" type="text" maxlength="10" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="$">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rate to Base Currency *</label>
                                <input v-model.number="currencyForm.rate_to_base" type="number" step="0.0001" min="0" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="1.0000">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center">
                                    <input v-model="currencyForm.is_base" type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Set as Base Currency</span>
                                </label>
                                <label class="flex items-center">
                                    <input v-model="currencyForm.is_active" type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showCurrencyModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                {{ editingCurrency ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Charge Modal -->
        <div v-if="showChargeModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showChargeModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6" @click.stop>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">
                        {{ editingCharge ? 'Edit Charge' : 'Add Charge' }}
                    </h3>
                    <form @submit.prevent="saveCharge">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Charge Name *</label>
                                    <input v-model="chargeForm.name" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="Shipping Charge">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                                    <select v-model="chargeForm.type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                        <option value="">Select Type</option>
                                        <option value="shipping">Shipping</option>
                                        <option value="weight">Weight</option>
                                        <option value="tax">Tax</option>
                                        <option value="service_fee">Service Fee</option>
                                        <option value="handling">Handling</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
                                <select v-model="chargeForm.currency_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                    <option value="">Select Currency</option>
                                    <option v-for="currency in allCurrencies" :key="currency.id" :value="currency.id">
                                        {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
                                    </option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Calculation Type *</label>
                                    <select v-model="chargeForm.calculation_type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Value *</label>
                                    <input v-model.number="chargeForm.value" type="number" step="0.01" min="0" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Value (Optional)</label>
                                    <input v-model.number="chargeForm.min_value" type="number" step="0.01" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Value (Optional)</label>
                                    <input v-model.number="chargeForm.max_value" type="number" step="0.01" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                <textarea v-model="chargeForm.description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="Charge description..."></textarea>
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input v-model="chargeForm.is_active" type="checkbox"
                                        class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showChargeModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                {{ editingCharge ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Plus, Edit, Trash2, DollarSign, Receipt } from 'lucide-vue-next';
import axios from '../../axios';

const activeTab = ref('currencies');
const showCurrencyModal = ref(false);
const showChargeModal = ref(false);
const editingCurrency = ref(null);
const editingCharge = ref(null);

const currencies = ref([]);
const charges = ref([]);
const allCurrencies = ref([]);
const loading = ref(false);

const currencyForm = ref({
    code: '',
    name: '',
    symbol: '',
    rate_to_base: 1,
    is_base: false,
    is_active: true,
    sort_order: 0,
});

const chargeForm = ref({
    name: '',
    type: '',
    currency_id: '',
    calculation_type: 'fixed',
    value: 0,
    min_value: null,
    max_value: null,
    conditions: null,
    is_active: true,
    sort_order: 0,
    description: '',
});

const chargeFilters = ref({
    search: '',
    type: '',
    currency_id: '',
    status: '',
});

const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/admin/currencies', { params: { all: true } });
        currencies.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching currencies:', error);
    }
};

const fetchAllCurrencies = async () => {
    try {
        const response = await axios.get('/admin/currencies', { params: { all: true } });
        allCurrencies.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching currencies:', error);
    }
};

const fetchCharges = async () => {
    loading.value = true;
    try {
        const params = {};
        if (chargeFilters.value.search) params.search = chargeFilters.value.search;
        if (chargeFilters.value.type) params.type = chargeFilters.value.type;
        if (chargeFilters.value.currency_id) params.currency_id = chargeFilters.value.currency_id;
        if (chargeFilters.value.status) params.status = chargeFilters.value.status;

        const response = await axios.get('/admin/charges', { params });
        charges.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching charges:', error);
    } finally {
        loading.value = false;
    }
};

const saveCurrency = async () => {
    try {
        if (editingCurrency.value) {
            await axios.put(`/admin/currencies/${editingCurrency.value.id}`, currencyForm.value);
        } else {
            await axios.post('/admin/currencies', currencyForm.value);
        }
        showCurrencyModal.value = false;
        resetCurrencyForm();
        fetchCurrencies();
        fetchAllCurrencies();
    } catch (error) {
        console.error('Error saving currency:', error);
        alert(error.response?.data?.message || 'Error saving currency');
    }
};

const saveCharge = async () => {
    try {
        if (editingCharge.value) {
            await axios.put(`/admin/charges/${editingCharge.value.id}`, chargeForm.value);
        } else {
            await axios.post('/admin/charges', chargeForm.value);
        }
        showChargeModal.value = false;
        resetChargeForm();
        fetchCharges();
    } catch (error) {
        console.error('Error saving charge:', error);
        alert(error.response?.data?.message || 'Error saving charge');
    }
};

const editCurrency = (currency) => {
    editingCurrency.value = currency;
    currencyForm.value = { ...currency };
    showCurrencyModal.value = true;
};

const editCharge = (charge) => {
    editingCharge.value = charge;
    chargeForm.value = { ...charge };
    showChargeModal.value = true;
};

const deleteCurrency = async (id) => {
    if (!confirm('Are you sure you want to delete this currency?')) return;
    try {
        await axios.delete(`/admin/currencies/${id}`);
        fetchCurrencies();
        fetchAllCurrencies();
    } catch (error) {
        console.error('Error deleting currency:', error);
        alert(error.response?.data?.message || 'Error deleting currency');
    }
};

const deleteCharge = async (id) => {
    if (!confirm('Are you sure you want to delete this charge?')) return;
    try {
        await axios.delete(`/admin/charges/${id}`);
        fetchCharges();
    } catch (error) {
        console.error('Error deleting charge:', error);
        alert(error.response?.data?.message || 'Error deleting charge');
    }
};

const resetCurrencyForm = () => {
    currencyForm.value = {
        code: '',
        name: '',
        symbol: '',
        rate_to_base: 1,
        is_base: false,
        is_active: true,
        sort_order: 0,
    };
    editingCurrency.value = null;
};

const resetChargeForm = () => {
    chargeForm.value = {
        name: '',
        type: '',
        currency_id: '',
        calculation_type: 'fixed',
        value: 0,
        min_value: null,
        max_value: null,
        conditions: null,
        is_active: true,
        sort_order: 0,
        description: '',
    };
    editingCharge.value = null;
};

const handleChargeSearch = () => {
    fetchCharges();
};

const resetChargeFilters = () => {
    chargeFilters.value = {
        search: '',
        type: '',
        currency_id: '',
        status: '',
    };
    fetchCharges();
};

onMounted(() => {
    fetchCurrencies();
    fetchAllCurrencies();
    fetchCharges();
});
</script>

