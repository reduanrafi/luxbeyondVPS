<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Edit Product Request #{{ requestId }}</h2>
                <p class="text-sm text-zinc-400 mt-1">Update product request details, charges, and calculations</p>
            </div>
            <button @click="$router.push('/admin/requests')"
                class="px-4 py-2 text-sm font-semibold text-zinc-400 hover:text-white border border-white/10 rounded-lg hover:bg-white/5 transition-colors">
                ← Back to Requests
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-[#111111] rounded-xl shadow-md border border-white/10 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-zinc-400 mt-2">Loading request details...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-[#111111] rounded-xl shadow-md border border-red-500/20 p-8 text-center">
            <p class="text-red-500 font-semibold">{{ error }}</p>
            <button @click="$router.push('/admin/requests')" class="mt-4 px-4 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary-hover transition-colors font-bold">
                Back to Requests
            </button>
        </div>

        <!-- Edit Form -->
        <form v-else @submit.prevent="updateRequest" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-[#111111] rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Product URL <span class="text-red-500">*</span></label>
                        <input v-model="form.url" type="url" required
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="https://example.com/product">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Customer</label>
                        <input :value="request?.user?.name || 'N/A'" type="text" disabled
                            class="w-full px-4 py-2 border border-white/10 rounded-lg bg-white/5 text-zinc-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Price <span class="text-red-500">*</span></label>
                        <input v-model.number="form.price" type="number" step="0.01" min="0" required
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Quantity <span class="text-red-500">*</span></label>
                        <input v-model.number="form.quantity" type="number" min="1" required
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="1">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Currency <span class="text-red-500">*</span></label>
                        <select v-model="form.currency" required
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                            <option value="">Select Currency</option>
                            <option v-for="currency in currencies" :key="currency.id" :value="currency.code">
                                {{ currency.code }} - {{ currency.name }} ({{ currency.symbol }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Status <span class="text-red-500">*</span></label>
                        <select v-model="form.status_id" required
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                            <option value="">Select Status</option>
                            <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Shipping & Location -->
            <div class="bg-[#111111] rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Shipping & Location</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Declared Shipping Cost</label>
                        <input v-model.number="form.declared_shipping_cost" type="number" step="0.01" min="0"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Location</label>
                        <select v-model="form.is_inside_city"
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                            <option :value="true">Inside City</option>
                            <option :value="false">Outside City</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Weight (kg)</label>
                        <input v-model.number="form.weight" type="number" step="0.01" min="0"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Payment Method</label>
                        <input v-model="form.payment_method" type="text"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="e.g., bKash, Nagad, Cash">
                    </div>
                </div>
            </div>

            <!-- Charges & Fees -->
            <div class="bg-[#111111] rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Charges & Fees</h3>
                <div class="space-y-6">
                    <!-- Standard Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Tax Amount (BDT)</label>
                            <input v-model.number="form.tax" type="number" step="0.01" min="0"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                                placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Additional Charges (BDT)</label>
                            <input v-model.number="form.additional_charges" type="number" step="0.01" min="0"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                                placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Delivery Charge (BDT)</label>
                            <input v-model.number="form.delivery_charge" type="number" step="0.01" min="0"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                                placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-2">Payment Processing Fee (BDT)</label>
                            <input v-model.number="form.payment_processing_fee" type="number" step="0.01" min="0"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                                placeholder="0.00">
                        </div>
                    </div>

                    <!-- Detailed Breakdown Editor -->
                    <div class="border-t border-white/10 pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-sm font-bold text-zinc-300">Charges Breakdown (Detailed)</label>
                            <button type="button" @click="addChargeItem" class="text-sm text-primary font-bold hover:underline">+ Add Charge</button>
                        </div>
                        
                        <div v-if="form.charges_breakdown.length > 0" class="space-y-3">
                            <div v-for="(charge, index) in form.charges_breakdown" :key="index" class="flex gap-2 items-start bg-white/5 p-3 rounded-lg border border-white/10">
                                <div class="flex-1 space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input v-model="charge.charge" type="text" placeholder="Charge Name" class="w-full px-2 py-1 text-xs bg-black/20 border border-white/10 text-white rounded focus:border-primary/50 focus:outline-none">
                                        <select v-model="charge.currency" class="w-full px-2 py-1 text-xs bg-zinc-900 border border-white/10 text-white rounded focus:border-primary/50 focus:outline-none">
                                            <option value="BDT">BDT</option>
                                            <option v-for="curr in currencies" :key="curr.code" :value="curr.code">{{ curr.code }}</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input v-model.number="charge.amount_in_currency" type="number" step="0.01" placeholder="Amt (Curr)" class="w-full px-2 py-1 text-xs bg-black/20 border border-white/10 text-white rounded focus:border-primary/50 focus:outline-none">
                                        <input v-model.number="charge.amount_in_bdt" type="number" step="0.01" placeholder="Amt (BDT)" class="w-full px-2 py-1 text-xs bg-black/20 border border-white/10 text-white rounded font-bold focus:border-primary/50 focus:outline-none">
                                    </div>
                                </div>
                                <button type="button" @click="removeChargeItem(index)" class="text-red-500 hover:text-red-400 p-1">
                                    ✕
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-sm text-zinc-500 italic bg-white/5 rounded-lg border border-dashed border-white/10">
                            No detailed breakdown items.
                        </div>
                    </div>

                    <div class="bg-black/20 p-4 rounded-lg border border-white/10">
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Total Amount (BDT)</label>
                        <p class="text-2xl font-bold text-primary">৳{{ calculatedTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                        <p class="text-xs text-zinc-500 mt-1">Calculated: Product Total + Charges + Shipping + Tax</p>
                    </div>
                </div>
            </div>


            <!-- Payment Settings -->
            <div class="bg-[#111111] rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Payment Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Payment Status</label>
                        <select v-model="form.payment_status"
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                            <option value="unpaid">Unpaid</option>
                            <option value="processing">Processing</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Minimum Payment Amount</label>
                        <input v-model.number="form.min_payment_amount" type="number" step="0.01" min="0"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="0.00">
                        <p class="text-xs text-zinc-500 mt-1">Leave 0 for full payment required</p>
                    </div>
                </div>
            </div>

            <!-- Admin Information -->
            <div class="bg-[#111111] rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Admin Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Admin Image URL</label>
                        <input v-model="form.admin_image_url" type="url"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="https://example.com/image.jpg">
                        <p class="text-xs text-zinc-500 mt-1">Image URL for the product from admin's perspective</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Admin Note</label>
                        <textarea v-model="form.admin_note" rows="4"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50"
                            placeholder="Add admin notes here..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
                <button type="button" @click="$router.push('/admin/requests')"
                    class="px-6 py-2 border border-white/10 text-white rounded-lg hover:bg-white/5 transition-colors font-semibold">
                    Cancel
                </button>
                <button type="submit" :disabled="saving"
                    class="px-6 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary-hover transition-colors font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="saving">Saving...</span>
                    <span v-else>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</template>>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../../axios';

const route = useRoute();
const router = useRouter();
const requestId = route.params.id;

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const request = ref(null);
const currencies = ref([]);
const orderStatuses = ref([]);

const form = ref({
    url: '',
    price: 0,
    quantity: 1,
    currency: '',
    declared_shipping_cost: 0,
    is_inside_city: false,
    weight: 0,
    payment_method: '',
    tax: 0,
    additional_charges: 0,
    delivery_charge: 0,
    payment_processing_fee: 0,
    status_id: null,
    admin_image_url: '',
    admin_image_url: '',
    admin_note: '',
    payment_status: 'unpaid',
    payment_status: 'unpaid',
    min_payment_amount: 0,
    charges_breakdown: []
});

const addChargeItem = () => {
    form.value.charges_breakdown.push({
        charge: 'New Charge',
        currency: 'BDT',
        amount_in_currency: 0,
        amount_in_bdt: 0
    });
};

const removeChargeItem = (index) => {
    form.value.charges_breakdown.splice(index, 1);
};

const calculatedTotal = computed(() => {
    // Calculate product total in BDT
    const currency = currencies.value.find(c => c.code === form.value.currency);
    let productTotal = form.value.price * form.value.quantity;
    
    if (currency && !currency.is_base) {
        productTotal = productTotal * currency.rate_to_base;
    }
    
    // Add declared shipping
    let total = productTotal + (form.value.declared_shipping_cost || 0);

    // Add charges from breakdown if available
    if (form.value.charges_breakdown && form.value.charges_breakdown.length > 0) {
        const chargesTotal = form.value.charges_breakdown.reduce((sum, item) => sum + (item.amount_in_bdt || 0), 0);
        total += chargesTotal;
    } else {
        // Fallback to manual fields if no breakdown
        total += (form.value.tax || 0)
            + (form.value.additional_charges || 0)
            + (form.value.delivery_charge || 0)
            + (form.value.payment_processing_fee || 0);
    }
    
    return total;
});

const fetchRequest = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/admin/requests/${requestId}`);
        request.value = response.data;
        
        // Populate form
        form.value = {
            url: request.value.url || '',
            price: parseFloat(request.value.price || 0),
            quantity: parseInt(request.value.quantity || 1),
            currency: request.value.currency || '',
            declared_shipping_cost: parseFloat(request.value.declared_shipping_cost || 0),
            is_inside_city: request.value.is_inside_city || false,
            weight: parseFloat(request.value.weight || 0),
            payment_method: request.value.payment_method || '',
            tax: parseFloat(request.value.tax || 0),
            additional_charges: parseFloat(request.value.additional_charges || 0),
            delivery_charge: parseFloat(request.value.delivery_charge || 0),
            payment_processing_fee: parseFloat(request.value.payment_processing_fee || 0),
            status_id: request.value.status_id || request.value.order_status?.id || null,
            admin_image_url: request.value.admin_image_url || '',
            status_id: request.value.status_id || request.value.order_status?.id || null,
            admin_image_url: request.value.admin_image_url || '',
            admin_note: request.value.admin_note || '',
            payment_status: request.value.payment_status || 'unpaid',
            min_payment_amount: parseFloat(request.value.min_payment_amount || 0),
            charges_breakdown: request.value.charges_breakdown || []
        };
    } catch (err) {
        console.error('Error fetching request:', err);
        error.value = err.response?.data?.message || 'Failed to load request details';
    } finally {
        loading.value = false;
    }
};

const fetchCurrencies = async () => {
    try {
        const response = await axios.get('/currencies');
        currencies.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Error fetching currencies:', err);
    }
};

const fetchOrderStatuses = async () => {
    try {
        const response = await axios.get('/order-statuses?all=true');
        orderStatuses.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Error fetching order statuses:', err);
    }
};

const updateRequest = async () => {
    saving.value = true;
    try {
        // Calculate total amount
        const currency = currencies.value.find(c => c.code === form.value.currency);
        let productTotal = form.value.price * form.value.quantity;
        
        if (currency && !currency.is_base) {
            productTotal = productTotal * currency.rate_to_base;
        }
        
        const totalAmountBDT = calculatedTotal.value;
        
        const updateData = {
            ...form.value,
            total_amount_bdt: totalAmountBDT,
            is_inside_city: Boolean(form.value.is_inside_city)
        };
        
        await axios.put(`/admin/requests/${requestId}`, updateData);
        router.push('/admin/requests');
    } catch (err) {
        console.error('Error updating request:', err);
        alert(err.response?.data?.message || 'Failed to update request');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchCurrencies();
    fetchOrderStatuses();
    fetchRequest();
});
</script>

