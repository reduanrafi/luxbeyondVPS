<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Order Status Management</h2>
                <p class="text-sm text-slate-600 mt-1">Manage order statuses and their workflow</p>
            </div>
            <button @click="showStatusModal = true; editingStatus = null"
                class="px-4 py-2 bg-primary text-slate-900 font-semibold rounded-lg hover:bg-primary/90 transition-all shadow-sm flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Status
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search statuses..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select v-model="filters.status" @change="fetchStatuses(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Statuses List -->
        <div class="bg-surface rounded-xl shadow-md border border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-white/10">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Name</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Properties</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Orders</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-if="statuses.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No statuses found</td>
                        </tr>
                        <tr v-for="status in statuses" :key="status.id" class="hover:bg-gray-50">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: status.color }"></div>
                                    <span class="font-medium text-slate-900">{{ status.label }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <code class="text-sm bg-gray-100 px-2 py-1 rounded text-slate-300">{{ status.name }}</code>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-2">
                                    <span v-if="status.is_default"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">
                                        Default
                                    </span>
                                    <span v-if="status.is_final"
                                        class="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full font-medium">
                                        Final
                                    </span>
                                    <span v-if="!status.is_active"
                                        class="px-2 py-1 bg-gray-100 text-slate-300 text-xs rounded-full font-medium">
                                        Inactive
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-600">
                                {{ status.orders_count || 0 }} orders
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <button @click="editStatus(status)"
                                        class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                        <Edit class="w-4 h-4 text-blue-600" />
                                    </button>
                                    <button @click="deleteStatus(status.id)"
                                        class="p-2 hover:bg-red-50 rounded-lg transition-colors">
                                        <Trash2 class="w-4 h-4 text-red-600" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Status Modal -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showStatusModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
                <div class="relative bg-surface rounded-lg shadow-xl max-w-2xl w-full p-6" @click.stop>
                    <h3 class="text-lg font-bold text-slate-900 mb-4">
                        {{ editingStatus ? 'Edit Order Status' : 'Add Order Status' }}
                    </h3>
                    <form @submit.prevent="saveStatus">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Status Name (Code) *</label>
                                    <input v-model="statusForm.name" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="pending" :disabled="!!editingStatus"
                                        @input="handleNameInput">
                                    <p class="text-xs text-gray-500 mt-1">Unique identifier (lowercase, underscores only)</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Display Label *</label>
                                    <input v-model="statusForm.label" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="Pending"
                                        @input="isLabelManual = true">
                                    <p class="text-xs text-gray-500 mt-1">User-friendly name shown in the UI</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Color *</label>
                                    <div class="flex items-center gap-2">
                                        <input v-model="statusForm.color" type="color"
                                            class="w-16 h-10 border border-gray-300 rounded-lg cursor-pointer">
                                        <input v-model="statusForm.color" type="text" maxlength="7"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                            placeholder="#6B7280">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Sort Order</label>
                                    <input v-model.number="statusForm.sort_order" type="number" min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        placeholder="0">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                                <textarea v-model="statusForm.description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="Describe what this status means and when it should be used..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">Optional: Explain the purpose of this status</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Allowed Next Statuses</label>
                                    <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                        <label v-for="status in allStatuses" :key="status.id" class="flex items-center gap-2 py-1">
                                            <input type="checkbox" :value="status.id" v-model="selectedNextStatuses"
                                                class="rounded border-gray-300 text-primary focus:ring-primary">
                                            <span class="text-sm text-slate-300">{{ status.label }}</span>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Leave empty to allow all transitions</p>
                                </div>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input v-model="statusForm.is_default" type="checkbox"
                                            class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-slate-300">Set as Default Status</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input v-model="statusForm.is_final" type="checkbox"
                                            class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-slate-300">Final Status (No further transitions)</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input v-model="statusForm.is_active" type="checkbox"
                                            class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-slate-300">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="showStatusModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary/90 transition-colors">
                                {{ editingStatus ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import axios from '../../axios';

const showStatusModal = ref(false);
const editingStatus = ref(null);
const statuses = ref([]);
const allStatuses = ref([]);
const selectedNextStatuses = ref([]);
const isLabelManual = ref(false);

const filters = ref({
    search: '',
    status: '',
});

const statusForm = ref({
    name: '',
    label: '',
    color: '#6B7280',
    icon: '',
    description: '',
    sort_order: 0,
    is_default: false,
    is_final: false,
    is_active: true,
    allowed_next_statuses: [],
});

const fetchStatuses = async () => {
    try {
        const params = {};
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status) params.status = filters.value.status;

        const response = await axios.get('/admin/order-statuses', { params });
        statuses.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching statuses:', error);
    }
};

const fetchAllStatuses = async () => {
    try {
        const response = await axios.get('/admin/order-statuses', { params: { all: true } });
        allStatuses.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching all statuses:', error);
    }
};

const saveStatus = async () => {
    try {
        // Prepare form data
        const formData = {
            name: statusForm.value.name.trim().toLowerCase().replace(/\s+/g, '_'),
            label: statusForm.value.label.trim(),
            color: statusForm.value.color,
            icon: statusForm.value.icon || null,
            description: statusForm.value.description?.trim() || null,
            sort_order: statusForm.value.sort_order || 0,
            is_default: statusForm.value.is_default || false,
            is_final: statusForm.value.is_final || false,
            is_active: statusForm.value.is_active !== false,
            allowed_next_statuses: selectedNextStatuses.value.length > 0 ? selectedNextStatuses.value : null,
        };

        if (editingStatus.value) {
            await axios.put(`/admin/order-statuses/${editingStatus.value.id}`, formData);
        } else {
            await axios.post('/admin/order-statuses', formData);
        }
        
        showStatusModal.value = false;
        resetStatusForm();
        fetchStatuses();
        fetchAllStatuses();
        alert(editingStatus.value ? 'Status updated successfully!' : 'Status created successfully!');
    } catch (error) {
        console.error('Error saving status:', error);
        const errorMessage = error.response?.data?.message || 
                           error.response?.data?.errors?.name?.[0] ||
                           'Error saving status';
        alert(errorMessage);
    }
};

const handleNameInput = (event) => {
    // Format the name (lowercase, underscores only)
    const formattedName = event.target.value.toLowerCase().replace(/[^a-z0-9_]/g, '_');
    statusForm.value.name = formattedName;
    
    // Auto-complete display label if not manually edited
    if (!isLabelManual.value && !editingStatus.value) {
        // Convert snake_case or lowercase to Title Case
        statusForm.value.label = formattedName
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }
};

const editStatus = (status) => {
    editingStatus.value = status;
    statusForm.value = { ...status };
    selectedNextStatuses.value = status.allowed_next_statuses || [];
    isLabelManual.value = true; // Don't auto-update when editing
    showStatusModal.value = true;
};

const deleteStatus = async (id) => {
    if (!confirm('Are you sure you want to delete this status?')) return;
    try {
        await axios.delete(`/admin/order-statuses/${id}`);
        fetchStatuses();
        fetchAllStatuses();
    } catch (error) {
        console.error('Error deleting status:', error);
        alert(error.response?.data?.message || 'Error deleting status');
    }
};

const resetStatusForm = () => {
    statusForm.value = {
        name: '',
        label: '',
        color: '#6B7280',
        icon: '',
        description: '',
        sort_order: 0,
        is_default: false,
        is_final: false,
        is_active: true,
        allowed_next_statuses: [],
    };
    selectedNextStatuses.value = [];
    editingStatus.value = null;
    isLabelManual.value = false;
};

const handleSearch = () => {
    fetchStatuses();
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
    };
    fetchStatuses();
};

onMounted(() => {
    fetchStatuses();
    fetchAllStatuses();
});
</script>

