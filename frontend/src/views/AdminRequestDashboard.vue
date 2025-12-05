<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Product Requests Management</h2>
            <p class="text-sm text-slate-600 mt-1">Manage and track customer product requests</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="stat in requestStats" :key="stat.label"
                class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <p class="text-sm text-slate-600">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ stat.value }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input v-model="filters.search" @input="fetchRequests" type="text" placeholder="Search requests..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select v-model="filters.status" @change="fetchRequests"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Status</option>
                    <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                        {{ status.label }}
                    </option>
                </select>
                <input v-model="filters.date_from" @change="fetchRequests" type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <input v-model="filters.date_to" @change="fetchRequests" type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <button @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Reset</button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-slate-600 mt-2">Loading requests...</p>
        </div>

        <!-- Requests Table -->
        <div v-else class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Request ID</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Customer</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Product URL</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Price</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Quantity</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Total (BDT)</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Date</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="requests.length === 0">
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">No requests found</td>
                        </tr>
                        <tr v-for="req in requests" :key="req.id" class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-6 text-sm font-medium text-slate-900">#{{ req.id }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                {{ req.user?.name || 'N/A' }}
                                <p class="text-xs text-slate-400">{{ req.user?.email || '' }}</p>
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                <a :href="req.url" target="_blank" 
                                    class="text-blue-600 hover:text-blue-800 hover:underline truncate block max-w-xs"
                                    :title="req.url">
                                    {{ req.url }}
                                </a>
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                {{ req.currency }} {{ parseFloat(req.price || 0).toLocaleString() }}
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ req.quantity }}</td>
                            <td class="py-4 px-6 text-sm font-semibold text-slate-900">
                                ৳{{ parseFloat(req.total_amount_bdt || 0).toLocaleString() }}
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                {{ new Date(req.created_at).toLocaleDateString() }}
                            </td>
                            <td class="py-4 px-6">
                                <select
                                    :value="req.status_id || req.order_status?.id"
                                    @change="updateRequestStatus(req.id, $event.target.value)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold border-0 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    :class="getStatusClass(req.order_status || req.status)"
                                    :disabled="updatingStatus === req.id">
                                    <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/requests/${req.id}`" 
                                        class="p-2 hover:bg-green-50 rounded-lg transition-colors inline-block"
                                        title="View Details">
                                        <Eye class="w-4 h-4 text-green-600" />
                                    </router-link>
                                    <router-link :to="`/admin/requests/${req.id}/edit`" 
                                        class="p-2 hover:bg-blue-50 rounded-lg transition-colors inline-block"
                                        title="Edit Request">
                                        <Edit class="w-4 h-4 text-blue-600" />
                                    </router-link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="editingRequest" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                    @click.self="editingRequest = null">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-900">Edit Request #{{ editingRequest.id }}</h2>
                            <button @click="editingRequest = null"
                                class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <form @submit.prevent="updateRequest" class="p-6 space-y-4">
                            <!-- Product URL -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Product URL <span class="text-red-500">*</span></label>
                                <input v-model="editForm.url" type="url" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="https://example.com/product">
                            </div>

                            <!-- Price and Currency -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Price <span class="text-red-500">*</span></label>
                                    <input v-model.number="editForm.price" type="number" step="0.01" min="0" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Currency <span class="text-red-500">*</span></label>
                                    <input v-model="editForm.currency" type="text" maxlength="3" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 uppercase"
                                        placeholder="USD">
                                </div>
                            </div>

                            <!-- Quantity and Shipping -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Quantity <span class="text-red-500">*</span></label>
                                    <input v-model.number="editForm.quantity" type="number" min="1" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="1">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Declared Shipping Cost</label>
                                    <input v-model.number="editForm.declared_shipping_cost" type="number" step="0.01" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="0.00">
                                </div>
                            </div>

                            <!-- Location and Status -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Location</label>
                                    <select v-model="editForm.is_inside_city"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                                        <option :value="true">Inside City</option>
                                        <option :value="false">Outside City</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span class="text-red-500">*</span></label>
                                    <select v-model="editForm.status_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                                        <option value="">Select Status</option>
                                        <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Admin Image URL -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Admin Image URL</label>
                                <input v-model="editForm.admin_image_url" type="url" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="https://example.com/image.jpg">
                                <p class="text-xs text-slate-500 mt-1">Image URL for the product from admin's perspective</p>
                            </div>

                            <!-- Admin Note -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Admin Note</label>
                                <textarea v-model="editForm.admin_note" rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="Add admin notes here..."></textarea>
                            </div>

                            <!-- Read-only Total -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Total Amount (BDT)</label>
                                <p class="text-lg font-bold text-slate-900">৳{{ parseFloat(editingRequest?.total_amount_bdt || 0).toLocaleString() }}</p>
                                <p class="text-xs text-slate-500 mt-1">This is calculated automatically based on price, quantity, and charges</p>
                            </div>
                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                                <button type="button" @click="editingRequest = null"
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-semibold">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors font-semibold">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Eye, Edit, X } from 'lucide-vue-next';
import axios from '../axios';

const loading = ref(false);
const updatingStatus = ref(null);
const requests = ref([]);
const orderStatuses = ref([]);
const editingRequest = ref(null);
const editForm = ref({
    url: '',
    price: 0,
    quantity: 1,
    currency: '',
    declared_shipping_cost: 0,
    is_inside_city: false,
    status_id: null,
    admin_image_url: '',
    admin_note: ''
});

const filters = ref({
    search: '',
    status: '',
    date_from: '',
    date_to: '',
});

const requestStats = computed(() => {
    const total = requests.value.length;
    const pending = requests.value.filter(r => {
        const status = r.order_status || r.status;
        return (typeof status === 'object' ? status.name : status) === 'pending';
    }).length;
    const approved = requests.value.filter(r => {
        const status = r.order_status || r.status;
        return (typeof status === 'object' ? status.name : status) === 'approved';
    }).length;
    const completed = requests.value.filter(r => {
        const status = r.order_status || r.status;
        return (typeof status === 'object' ? status.name : status) === 'completed';
    }).length;
    
    return [
        { label: 'Total Requests', value: total.toString() },
        { label: 'Pending', value: pending.toString() },
        { label: 'Approved', value: approved.toString() },
        { label: 'Completed', value: completed.toString() },
    ];
});

const fetchRequests = async () => {
    loading.value = true;
    try {
        const params = {};
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status) params.status = filters.value.status;
        if (filters.value.date_from) params.date_from = filters.value.date_from;
        if (filters.value.date_to) params.date_to = filters.value.date_to;

        // Fetch all requests (admin endpoint should return all requests)
        const response = await axios.get('/requests', { params });
        const requestsData = response.data.data || response.data;
        requests.value = Array.isArray(requestsData) ? requestsData : (requestsData.data || []);
    } catch (error) {
        console.error('Error fetching requests:', error);
        requests.value = [];
    } finally {
        loading.value = false;
    }
};

const updateRequestStatus = async (requestId, statusId) => {
    if (!confirm('Are you sure you want to update the request status?')) {
        return;
    }

    updatingStatus.value = requestId;
    try {
        await axios.put(`/requests/${requestId}`, { status_id: statusId });
        await fetchRequests();
        alert('Request status updated successfully');
    } catch (error) {
        console.error('Error updating request status:', error);
        alert(error.response?.data?.message || 'Error updating request status');
        await fetchRequests();
    } finally {
        updatingStatus.value = null;
    }
};



const getStatusClass = (status) => {
    // Handle both object (from order_status) and string (legacy)
    const statusName = typeof status === 'object' ? (status.name || status.label) : status;
    const statusColor = typeof status === 'object' ? status.color : null;
    
    // If status has a color, use it
    if (statusColor) {
        return `bg-[${statusColor}]/10 text-[${statusColor}]`;
    }
    
    // Fallback to default classes
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'paid': 'bg-blue-100 text-blue-800',
        'processing': 'bg-purple-100 text-purple-800',
        'completed': 'bg-gray-100 text-gray-800'
    };
    return classes[statusName] || 'bg-gray-100 text-gray-800';
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        date_from: '',
        date_to: '',
    };
    fetchRequests();
};

const fetchOrderStatuses = async () => {
    try {
        const response = await axios.get('/order-statuses?all=true');
        orderStatuses.value = response.data || [];
    } catch (error) {
        console.error('Error fetching order statuses:', error);
    }
};

const updateRequest = async () => {
    if (!editingRequest.value) return;
    
    try {
        await axios.put(`/requests/${editingRequest.value.id}`, {
            ...editForm.value,
            status_id: editForm.value.status_id
        });
        await fetchRequests();
        editingRequest.value = null;
        alert('Request updated successfully');
    } catch (error) {
        console.error('Error updating request:', error);
        alert(error.response?.data?.message || 'Error updating request');
    }
};

// Watch editingRequest to populate editForm
import { watch } from 'vue';
watch(editingRequest, (newVal) => {
    if (newVal) {
        editForm.value = {
            url: newVal.url || '',
            price: parseFloat(newVal.price || 0),
            quantity: parseInt(newVal.quantity || 1),
            currency: newVal.currency || '',
            declared_shipping_cost: parseFloat(newVal.declared_shipping_cost || 0),
            is_inside_city: newVal.is_inside_city || false,
            status_id: newVal.status_id || newVal.order_status?.id || null,
            admin_image_url: newVal.admin_image_url || '',
            admin_note: newVal.admin_note || ''
        };
    }
});

onMounted(() => {
    fetchOrderStatuses();
    fetchRequests();
});
</script>
