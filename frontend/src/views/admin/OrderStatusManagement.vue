<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-serif font-bold text-white tracking-wide">Order Statuses</h2>
                <p class="text-zinc-400 mt-2 text-sm">Configure workflow statuses and transitions</p>
            </div>
            <button @click="openCreateModal"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary text-black font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-primary-hover transition-colors">
                <Plus class="w-4 h-4" />
                Add Status
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-[#111111] p-5 rounded-xl border border-white/5">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative group">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 group-focus-within:text-primary transition-colors" />
                    <input v-model="filters.search" @input="handleSearch" type="text" placeholder="Search statuses..."
                        class="w-full pl-10 pr-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600">
                </div>
                <select v-model="filters.status" @change="fetchStatuses"
                    class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer min-w-[150px]">
                    <option value="" class="bg-[#111111]">All Status</option>
                    <option value="active" class="bg-[#111111]">Active</option>
                    <option value="inactive" class="bg-[#111111]">Inactive</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2.5 bg-white/5 border border-white/10 text-white rounded-lg hover:bg-white/10 hover:border-white/20 transition-all"
                    title="Reset Filters">
                    <RotateCcw class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Status List -->
        <div class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-black/20 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Status</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Properties</th>
                            <th class="text-center py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Orders</th>
                            <th class="text-center py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Sort</th>
                            <th class="text-right py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="statuses.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <Ghost class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-lg font-medium text-zinc-400">No statuses found</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="status in statuses" :key="status.id"
                            class="group hover:bg-white/[0.02] transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full shadow-lg shadow-black/50"
                                        :style="{ backgroundColor: status.color }"></div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-white">{{ status.label }}</span>
                                        <span class="text-xs text-zinc-500 font-mono">{{ status.name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-2">
                                    <span v-if="status.is_default"
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        Default
                                    </span>
                                    <span v-if="status.is_final"
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        Final
                                    </span>
                                    <span v-if="!status.is_active"
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-red-500/10 text-red-400 border border-red-500/20">
                                        Inactive
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="text-sm text-zinc-400">{{ status.orders_count || 0 }}</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="text-xs font-mono text-zinc-600">{{ status.sort_order }}</span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editStatus(status)"
                                        class="p-2 text-zinc-400 hover:text-primary hover:bg-white/5 rounded-lg transition-colors"
                                        title="Edit Status">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteStatus(status.id)"
                                        class="p-2 text-zinc-400 hover:text-red-400 hover:bg-white/5 rounded-lg transition-colors"
                                        title="Delete Status">
                                        <Trash2 class="w-4 h-4" />
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
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm" aria-hidden="true"></div>

                <div
                    class="relative transform overflow-hidden rounded-2xl bg-[#0a0a0a] border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 bg-[#111111]">
                        <div>
                            <h3 class="text-xl font-serif font-bold text-white">
                                {{ editingStatus ? 'Edit Order Status' : 'Add Order Status' }}
                            </h3>
                            <p class="text-sm text-zinc-400 mt-1">Configure status properties and rules</p>
                        </div>
                        <button @click="showStatusModal = false"
                            class="p-2 text-zinc-400 hover:text-white hover:bg-white/5 rounded-full transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="saveStatus"
                        class="p-6 space-y-6 overflow-y-auto custom-scrollbar max-h-[calc(100vh-20rem)]">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                        Status Name (Code) *
                                    </label>
                                    <input v-model="statusForm.name" type="text" required
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        placeholder="pending" :disabled="!!editingStatus" @input="handleNameInput">
                                    <p class="text-[10px] text-zinc-500 mt-1">Unique identifier (lowercase, underscores
                                        only)</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                        Display Label *
                                    </label>
                                    <input v-model="statusForm.label" type="text" required
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-500"
                                        placeholder="Pending" @input="isLabelManual = true">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                        Sort Order
                                    </label>
                                    <input v-model.number="statusForm.sort_order" type="number" min="0"
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-500"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Color & Options -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                        Status Color *
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-12 h-12 rounded-lg border border-white/10 overflow-hidden shrink-0"
                                            :style="{ backgroundColor: statusForm.color }">
                                            <input v-model="statusForm.color" type="color"
                                                class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                                        </div>
                                        <input v-model="statusForm.color" type="text" maxlength="7"
                                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all uppercase placeholder:text-zinc-500"
                                            placeholder="#000000">
                                    </div>
                                </div>

                                <div class="space-y-3 pt-2">
                                    <label
                                        class="flex items-center gap-3 p-3 rounded-lg border border-white/5 bg-white/[0.02] cursor-pointer hover:bg-white/[0.05] transition-colors">
                                        <input v-model="statusForm.is_default" type="checkbox"
                                            class="w-4 h-4 rounded border-white/10 bg-black/40 text-primary focus:ring-primary/50 focus:ring-offset-0">
                                        <div>
                                            <span class="block text-sm font-medium text-white">Default Status</span>
                                            <span class="block text-xs text-zinc-500">Initial status for new
                                                orders</span>
                                        </div>
                                    </label>
                                    <label
                                        class="flex items-center gap-3 p-3 rounded-lg border border-white/5 bg-white/[0.02] cursor-pointer hover:bg-white/[0.05] transition-colors">
                                        <input v-model="statusForm.is_final" type="checkbox"
                                            class="w-4 h-4 rounded border-white/10 bg-black/40 text-primary focus:ring-primary/50 focus:ring-offset-0">
                                        <div>
                                            <span class="block text-sm font-medium text-white">Final Status</span>
                                            <span class="block text-xs text-zinc-500">End of order workflow</span>
                                        </div>
                                    </label>
                                    <label
                                        class="flex items-center gap-3 p-3 rounded-lg border border-white/5 bg-white/[0.02] cursor-pointer hover:bg-white/[0.05] transition-colors">
                                        <input v-model="statusForm.is_active" type="checkbox"
                                            class="w-4 h-4 rounded border-white/10 bg-black/40 text-primary focus:ring-primary/50 focus:ring-offset-0">
                                        <div>
                                            <span class="block text-sm font-medium text-white">Active</span>
                                            <span class="block text-xs text-zinc-500">Available for use</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                Description
                            </label>
                            <textarea v-model="statusForm.description" rows="2"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-500 resize-none"
                                placeholder="Describe what this status means..."></textarea>
                        </div>

                        <!-- Allowed Transitions -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">
                                Allowed Next Statuses
                            </label>
                            <div
                                class="bg-white/5 border border-white/10 rounded-lg p-4 max-h-48 overflow-y-auto custom-scrollbar">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <label v-for="status in allStatuses" :key="status.id"
                                        class="flex items-center gap-2 p-2 rounded hover:bg-white/5 transition-colors cursor-pointer">
                                        <input type="checkbox" :value="status.id" v-model="selectedNextStatuses"
                                            class="w-4 h-4 rounded border-white/10 bg-black/40 text-primary focus:ring-primary/50 focus:ring-offset-0">
                                        <span class="text-sm text-zinc-300">{{ status.label }}</span>
                                    </label>
                                </div>
                            </div>
                            <p class="text-[10px] text-zinc-500 mt-2">Selecting none allows transition to ANY status</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                            <button type="button" @click="showStatusModal = false"
                                class="px-6 py-2.5 text-sm font-medium text-zinc-400 hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 bg-primary text-black font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-primary-hover transition-colors shadow-lg shadow-primary/20">
                                {{ editingStatus ? 'Update Status' : 'Create Status' }}
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
import { Plus, Edit, Trash2, Search, RotateCcw, Ghost, X } from 'lucide-vue-next';
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

const openCreateModal = () => {
    resetStatusForm();
    showStatusModal.value = true;
};

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
            is_active: statusForm.value.is_active != false,
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
        // Removed alert to be less intrusive, or could use toast if available
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
        await fetchStatuses();
        await fetchAllStatuses();
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

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
