<template>
    <div class="space-y-8">
        <!-- Header -->
        <div>
            <h2 class="text-3xl font-serif font-bold text-white tracking-wide">Product Requests</h2>
            <p class="text-zinc-400 mt-2 text-sm">Manage and track customer product inquiries</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div v-for="stat in requestStats" :key="stat.label"
                class="bg-[#111111] rounded-xl border border-white/5 p-6 hover:border-primary/30 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ stat.label }}</p>
                        <p class="text-3xl font-serif font-bold text-white">{{ stat.value }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center">
                        <component :is="stat.icon" class="w-5 h-5 text-primary" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-[#111111] rounded-xl border border-white/5 p-5">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="relative group">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 group-focus-within:text-primary transition-colors" />
                    <input v-model="filters.search" @input="fetchRequests" type="text" placeholder="Search requests..."
                        class="w-full pl-10 pr-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600">
                </div>

                <select v-model="filters.status" @change="fetchRequests"
                    class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer">
                    <option value="" class="bg-[#111111]">All Status</option>
                    <option v-for="status in orderStatuses" :key="status.id" :value="status.id" class="bg-[#111111]">
                        {{ status.label }}
                    </option>
                </select>

                <input v-model="filters.date_from" @change="fetchRequests" type="date"
                    class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all [color-scheme:dark]">

                <input v-model="filters.date_to" @change="fetchRequests" type="date"
                    class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all [color-scheme:dark]">

                <button @click="resetFilters"
                    class="px-4 py-2.5 bg-white/5 border border-white/10 text-white rounded-lg hover:bg-white/10 hover:border-white/20 transition-all flex items-center justify-center gap-2">
                    <RotateCcw class="w-4 h-4" />
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-[#111111] rounded-xl border border-white/5 p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-zinc-400 mt-4">Loading requests...</p>
        </div>

        <!-- Requests Table -->
        <div v-else class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-black/20 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Request ID</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Customer</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Product URL</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Price Details</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Total (BDT)</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Date</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Status</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="requests.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <Ghost class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-lg font-medium text-zinc-400">No requests found</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="req in requests" :key="req.id" class="group hover:bg-white/[0.02] transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-mono text-sm text-primary">{{ req.request_number || '#' + req.id }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-xs font-bold text-zinc-300">
                                        {{ req.user?.name?.charAt(0) || '?' }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white">{{ req.user?.name || 'Guest' }}
                                        </div>
                                        <div class="text-xs text-zinc-500">{{ req.user?.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <a :href="req.url" target="_blank" 
                                    class="text-sm text-blue-400 hover:text-blue-300 hover:underline truncate block max-w-[200px] flex items-center gap-1">
                                    <Link2 class="w-3 h-3" />
                                    {{ req.url }}
                                </a>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm text-white">{{ req.currency }} {{ parseFloat(req.price ||
                                    0).toLocaleString() }}</div>
                                <div class="text-xs text-zinc-500">Qty: {{ req.quantity }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm font-bold text-white">৳{{ parseFloat(req.total_amount_bdt ||
                                    0).toLocaleString() }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm text-zinc-400">{{ new Date(req.created_at).toLocaleDateString()
                                    }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <select
                                    :value="req.status_id || req.order_status?.id"
                                    @change="updateRequestStatus(req.id, $event.target.value)"
                                    class="px-3 py-1 bg-black/20 border border-white/10 rounded-full text-xs font-semibold text-white focus:outline-none focus:border-primary/50 cursor-pointer"
                                    :style="getStatusStyle(req.order_status || req.status)"
                                    :disabled="updatingStatus === req.id">
                                    <option v-for="status in orderStatuses" :key="status.id" :value="status.id"
                                        class="bg-[#111111]">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </td>
                            <td class="py-4 px-6">
                                <div
                                    class="flex items-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <router-link :to="`/admin/requests/${req.id}`" 
                                        class="p-2 bg-white/5 hover:bg-white/10 text-emerald-400 rounded-lg transition-colors"
                                        title="View Details">
                                        <Eye class="w-4 h-4" />
                                    </router-link>
                                    <button @click="editRequest(req)"
                                        class="p-2 bg-white/5 hover:bg-white/10 text-blue-400 rounded-lg transition-colors"
                                        title="Edit Request">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <div v-if="editingRequest" class="fixed inset-0 z-50 overflow-y-auto"
                    @click.self="editingRequest = null">
                    <div
                        class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" aria-hidden="true">
                        </div>

                        <div
                            class="inline-block w-full max-w-2xl p-0 my-8 overflow-hidden text-left align-middle transition-all transform bg-[#111111] border border-white/10 shadow-2xl rounded-2xl">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
                                <div>
                                    <h2 class="text-xl font-serif font-bold text-white">Edit Request</h2>
                                    <p class="text-sm text-zinc-400">{{ editingRequest.request_number || 'ID: #' + editingRequest.id }}</p>
                                </div>
                                <button @click="editingRequest = null"
                                    class="p-2 text-zinc-400 hover:text-white hover:bg-white/5 rounded-full transition-colors">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <form @submit.prevent="updateRequest" class="p-6 space-y-6">
                                <!-- Product URL -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Product
                                        URL *</label>
                                    <div class="relative group">
                                        <Link2
                                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 group-focus-within:text-primary transition-colors" />
                                        <input v-model="editForm.url" type="url" required
                                            class="w-full pl-10 pr-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600"
                                            placeholder="https://example.com/product">
                                    </div>
                                </div>

                                <!-- Price and Currency -->
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Price
                                            *</label>
                                        <input v-model.number="editForm.price" type="number" step="0.01" min="0"
                                            required
                                            class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600"
                                            placeholder="0.00">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Currency
                                            *</label>
                                        <input v-model="editForm.currency" type="text" maxlength="3" required
                                            class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all uppercase placeholder:text-zinc-600"
                                            placeholder="USD">
                                    </div>
                                </div>

                                <!-- Quantity and Shipping -->
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Quantity
                                            *</label>
                                        <input v-model.number="editForm.quantity" type="number" min="1" required
                                            class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600"
                                            placeholder="1">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Declared
                                            Shipping</label>
                                        <input v-model.number="editForm.declared_shipping_cost" type="number"
                                            step="0.01" min="0"
                                            class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600"
                                            placeholder="0.00">
                                    </div>
                                </div>

                                <!-- Location and Status -->
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Location</label>
                                        <select v-model="editForm.is_inside_city"
                                            class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer">
                                            <option :value="true" class="bg-[#111111]">Inside City</option>
                                            <option :value="false" class="bg-[#111111]">Outside City</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Status
                                            *</label>
                                        <div class="relative">
                                            <select v-model="editForm.status_id" required
                                                class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer appearance-none">
                                                <option value="" class="bg-[#111111]">Select Status</option>
                                                <option v-for="status in orderStatuses" :key="status.id"
                                                    :value="status.id" class="bg-[#111111]"
                                                    :style="{ color: status.color }">
                                                    {{ status.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Admin Image URL -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Admin
                                        Image URL</label>
                                    <input v-model="editForm.admin_image_url" type="url"
                                        class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600"
                                        placeholder="https://example.com/image.jpg">
                                </div>

                                <!-- Admin Note -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Admin
                                        Note</label>
                                    <textarea v-model="editForm.admin_note" rows="3"
                                        class="w-full px-4 py-3 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600 resize-none"
                                        placeholder="Internal notes..."></textarea>
                                </div>

                                <!-- Read-only Total -->
                                <div class="bg-primary/5 p-4 rounded-lg border border-primary/10">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-white">Estimated Total (BDT)</span>
                                        <span class="text-xl font-bold text-primary">৳{{
                                            parseFloat(editingRequest?.total_amount_bdt || 0).toLocaleString() }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-500 mt-1">Calculated automatically based on current
                                        rates.</p>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                                    <button type="button" @click="editingRequest = null"
                                        class="px-6 py-2.5 text-sm font-medium text-zinc-400 hover:text-white transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-6 py-2.5 bg-primary text-black font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-primary-hover transition-colors shadow-lg shadow-primary/20">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Eye, Edit, X, Search, RotateCcw, Ghost, FileText, CheckCircle, Clock } from 'lucide-vue-next'; // Imported icons
import { Link2 } from 'lucide-vue-next';
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
        { label: 'Total Requests', value: total.toString(), icon: FileText },
        { label: 'Pending', value: pending.toString(), icon: Clock },
        { label: 'Approved', value: approved.toString(), icon: CheckCircle },
        { label: 'Completed', value: completed.toString(), icon: CheckCircle },
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
        const response = await axios.get('/admin/requests', { params });
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
        await axios.put(`/admin/requests/${requestId}`, { status_id: statusId });
        await fetchRequests();
        // alert('Request status updated successfully'); // Disabled for smoother UX
    } catch (error) {
        console.error('Error updating request status:', error);
        alert(error.response?.data?.message || 'Error updating request status');
        await fetchRequests();
    } finally {
        updatingStatus.value = null;
    }
};

const getStatusStyle = (status) => {
    const statusColor = typeof status === 'object' ? status.color : null;
    if (statusColor) {
        return {
            backgroundColor: `${statusColor}20`, // 20% opacity using hex
            color: statusColor,
            borderColor: `${statusColor}40`
        };
    }
    return {
        backgroundColor: '#ffffff10',
        color: '#9ca3af',
        borderColor: '#ffffff20'
    };
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
        const response = await axios.get('/admin/order-statuses?all=true'); // Ensure hitting admin endpoint
        orderStatuses.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error fetching order statuses:', error);
    }
};

const editRequest = (req) => {
    editingRequest.value = req;
};

const updateRequest = async () => {
    if (!editingRequest.value) return;
    
    try {
        await axios.put(`/admin/requests/${editingRequest.value.id}`, {
            ...editForm.value,
            status_id: editForm.value.status_id
        });
        await fetchRequests();
        editingRequest.value = null;
        // alert('Request updated successfully');
    } catch (error) {
        console.error('Error updating request:', error);
        alert(error.response?.data?.message || 'Error updating request');
    }
};

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
