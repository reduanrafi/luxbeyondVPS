<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Charges Management</h2>
                <p class="text-sm text-zinc-400 mt-1">Manage currencies, shipping charges, taxes, and other fees</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden">
            <div class="border-b border-white/5">
                <nav class="flex -mb-px">
                    <button
                        @click="activeTab = 'currencies'"
                        :class="activeTab === 'currencies' ? 'border-amber-500 text-amber-500' : 'border-transparent text-zinc-400 hover:text-white hover:border-white/10'"
                        class="px-6 py-4 text-sm font-bold border-b-2 transition-colors flex items-center gap-2"
                    >
                        <DollarSign class="w-4 h-4" />
                        Currencies
                    </button>
                    <button
                        @click="activeTab = 'charges'"
                        :class="activeTab === 'charges' ? 'border-amber-500 text-amber-500' : 'border-transparent text-zinc-400 hover:text-white hover:border-white/10'"
                        class="px-6 py-4 text-sm font-bold border-b-2 transition-colors flex items-center gap-2"
                    >
                        <Receipt class="w-4 h-4" />
                        Charges
                    </button>
                </nav>
            </div>

            <!-- Currencies Tab -->
            <div v-if="activeTab === 'currencies'" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">Currency Management</h3>
                    <button @click="showCurrencyModal = true; editingCurrency = null"
                        class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                        <Plus class="w-5 h-5" />
                        Add Currency
                    </button>
                </div>

                <!-- Currencies Table -->
                <div class="overflow-x-auto rounded-xl border border-white/10">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-white/5">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Symbol</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Rate to Base</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-zinc-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-if="currencies.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-zinc-500">No currencies found</td>
                            </tr>
                            <tr v-for="currency in currencies" :key="currency.id" class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-amber-500">{{ currency.code }}</span>
                                    <span v-if="currency.is_base" class="ml-2 px-2 py-0.5 bg-amber-500/10 text-amber-500 text-xs rounded border border-amber-500/20">Base</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ currency.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ currency.symbol }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-300 font-mono">{{ currency.rate_to_base }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium border"
                                        :class="currency.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'">
                                        {{ currency.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editCurrency(currency)" class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCurrency(currency.id)" class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charges Tab -->
            <div v-if="activeTab === 'charges'" class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">Charges Management</h3>
                    <button @click="showChargeModal = true; editingCharge = null"
                        class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                        <Plus class="w-5 h-5" />
                        Add Charge
                    </button>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <input type="text" v-model="chargeFilters.search" @input="handleChargeSearch" placeholder="Search charges..."
                        class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                    <select v-model="chargeFilters.type" @change="fetchCharges(1)"
                        class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                        <option value="" class="bg-zinc-900">All Types</option>
                        <option value="shipping" class="bg-zinc-900">Shipping</option>
                        <option value="weight" class="bg-zinc-900">Weight</option>
                        <option value="tax" class="bg-zinc-900">Tax</option>
                        <option value="service_fee" class="bg-zinc-900">Service Fee</option>
                        <option value="handling" class="bg-zinc-900">Handling</option>
                        <option value="custom" class="bg-zinc-900">Custom</option>
                    </select>
                    <select v-model="chargeFilters.currency_id" @change="fetchCharges(1)"
                        class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                        <option value="" class="bg-zinc-900">All Currencies</option>
                        <option v-for="currency in allCurrencies" :key="currency.id" :value="currency.id" class="bg-zinc-900">
                            {{ currency.code }}
                        </option>
                    </select>
                    <button @click="resetChargeFilters"
                        class="px-4 py-2 border border-white/10 text-zinc-400 rounded-lg hover:bg-white/5 hover:text-white transition-colors">
                        Reset
                    </button>
                </div>

                <!-- Charges Table -->
                <div class="overflow-x-auto rounded-xl border border-white/10">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-white/5">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Currency</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Calculation</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Value</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-zinc-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-if="charges.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-zinc-500">No charges found</td>
                            </tr>
                            <tr v-for="charge in charges" :key="charge.id" class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">{{ charge.name }}</div>
                                    <div v-if="charge.description" class="text-xs text-zinc-500 mt-1">{{ charge.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 bg-blue-500/10 text-blue-400 text-xs rounded border border-blue-500/20 font-medium">
                                        {{ charge.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ charge.currency?.code || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-300 capitalize">
                                    {{ charge.calculation_type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-medium">
                                    <span v-if="charge.calculation_type === 'percentage'">{{ charge.value }}%</span>
                                    <span v-else>{{ charge.currency?.symbol || '$' }}{{ charge.value }}</span>
                                    <span v-if="charge.min_value || charge.max_value" class="text-xs text-zinc-500 block">
                                        ({{ charge.min_value ? `Min: ${charge.min_value}` : '' }}{{ charge.min_value && charge.max_value ? ', ' : '' }}{{ charge.max_value ? `Max: ${charge.max_value}` : '' }})
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium border"
                                        :class="charge.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'">
                                        {{ charge.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editCharge(charge)" class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCharge(charge.id)" class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Currency Modal -->
        <div v-if="showCurrencyModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showCurrencyModal = false">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="relative bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-md w-full p-6" @click.stop>
                    <h3 class="text-xl font-bold text-white mb-6">
                        {{ editingCurrency ? 'Edit Currency' : 'Add Currency' }}
                    </h3>
                    <form @submit.prevent="saveCurrency">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Currency Code *</label>
                                <input v-model="currencyForm.code" type="text" maxlength="3" required
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="USD">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Currency Name *</label>
                                <input v-model="currencyForm.name" type="text" required
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="United States Dollar">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Symbol *</label>
                                <input v-model="currencyForm.symbol" type="text" maxlength="10" required
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="$">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Rate to Base Currency *</label>
                                <input v-model.number="currencyForm.rate_to_base" type="number" step="0.0001" min="0" required
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="1.0000">
                            </div>
                            <div class="flex items-center gap-6 pt-2">
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="currencyForm.is_base" type="checkbox"
                                        class="rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-500">
                                    <span class="ml-2 text-sm text-zinc-300">Set as Base Currency</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="currencyForm.is_active" type="checkbox"
                                        class="rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-500">
                                    <span class="ml-2 text-sm text-zinc-300">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end gap-3">
                            <button type="button" @click="showCurrencyModal = false"
                                class="px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 text-zinc-400 hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-colors">
                                {{ editingCurrency ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Charge Modal -->
        <div v-if="showChargeModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showChargeModal = false">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="relative bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-2xl w-full p-6" @click.stop>
                    <h3 class="text-xl font-bold text-white mb-6">
                        {{ editingCharge ? 'Edit Charge' : 'Add Charge' }}
                    </h3>
                    <form @submit.prevent="saveCharge">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Charge Name *</label>
                                    <input v-model="chargeForm.name" type="text" required
                                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                        placeholder="Shipping Charge">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Type *</label>
                                    <select v-model="chargeForm.type" required
                                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                                        <option value="" class="bg-zinc-900">Select Type</option>
                                        <option value="hub" class="bg-zinc-900">Shop Order (Hub Invoice)</option>
                                        <option value="request" class="bg-zinc-900">Product Request (General)</option>
                                        <option value="shipping" class="bg-zinc-900">Shipping (Request)</option>
                                        <option value="weight" class="bg-zinc-900">Weight (Request)</option>
                                        <option value="tax" class="bg-zinc-900">Tax (Request)</option>
                                        <option value="service_fee" class="bg-zinc-900">Service Fee (Request)</option>
                                        <option value="custom" class="bg-zinc-900">Custom (Request)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Currency *</label>
                                <select v-model="chargeForm.currency_id" required
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                                    <option value="" class="bg-zinc-900">Select Currency</option>
                                    <option v-for="currency in allCurrencies" :key="currency.id" :value="currency.id" class="bg-zinc-900">
                                        {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
                                    </option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Calculation Type *</label>
                                    <select v-model="chargeForm.calculation_type" required
                                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                                        <option value="fixed" class="bg-zinc-900">Fixed Amount</option>
                                        <option value="percentage" class="bg-zinc-900">Percentage</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Value *</label>
                                    <input v-model.number="chargeForm.value" type="number" step="0.01" min="0" required
                                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Min Value (Optional)</label>
                                    <input v-model.number="chargeForm.min_value" type="number" step="0.01" min="0"
                                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Max Value (Optional)</label>
                                    <input v-model.number="chargeForm.max_value" type="number" step="0.01" min="0"
                                        class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Description (Optional)</label>
                                <textarea v-model="chargeForm.description" rows="3"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="Charge description..."></textarea>
                            </div>
                            <div class="flex items-center pt-2">
                                <label class="flex items-center cursor-pointer">
                                    <input v-model="chargeForm.is_active" type="checkbox"
                                        class="rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-500">
                                    <span class="ml-2 text-sm text-zinc-300">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end gap-3">
                            <button type="button" @click="showChargeModal = false"
                                class="px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 text-zinc-400 hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-colors">
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
