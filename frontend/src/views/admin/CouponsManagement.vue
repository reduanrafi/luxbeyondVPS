<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Coupons Management</h2>
                <p class="text-sm text-slate-600 mt-1">Manage discount coupons and promotional codes</p>
            </div>
            <button @click="showAddModal = true"
                class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary-hover transition-all shadow-md flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Coupon
            </button>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div v-if="selectedCoupons.length > 0"
            class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-blue-900">{{ selectedCoupons.length }} coupon(s) selected</span>
                <button @click="clearSelection" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Clear
                    Selection</button>
            </div>
            <div class="flex items-center gap-2">
                <button @click="bulkActivate"
                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Activate
                </button>
                <button @click="bulkDeactivate"
                    class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Deactivate
                </button>
                <button @click="bulkDelete"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search coupons..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select v-model="filters.status" @change="fetchCoupons(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="expired">Expired</option>
                </select>
                <select v-model="filters.type" @change="fetchCoupons(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Types</option>
                    <option value="fixed">Fixed Amount</option>
                    <option value="percent">Percentage</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-slate-600 mt-2">Loading coupons...</p>
        </div>

        <!-- Coupons Table -->
        <div v-else class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                                    class="w-4 h-4 text-primary rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Validity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="coupons.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-slate-500">No coupons found</td>
                        </tr>
                        <tr v-for="coupon in coupons" :key="coupon.id" class="hover:bg-gray-50"
                            :class="{ 'bg-blue-50': isSelected(coupon.id) }">
                            <td class="px-6 py-4">
                                <input type="checkbox" :checked="isSelected(coupon.id)"
                                    @change="toggleSelection(coupon.id)" class="w-4 h-4 text-primary rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-primary">{{ coupon.code }}</span>
                                    <span v-if="coupon.is_featured"
                                        class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded">Featured</span>
                                    <span v-if="coupon.is_private"
                                        class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded">Private</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="capitalize">{{ coupon.type }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="coupon.type === 'fixed'">৳{{ coupon.value }}</span>
                                <span v-else>{{ coupon.value }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <div>{{ coupon.usage_count || 0 }} / {{ coupon.usage_limit || '∞' }}</div>
                                    <div class="text-xs text-gray-500" v-if="coupon.usage_limit_per_user">
                                        {{ coupon.usage_limit_per_user }} per user
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div v-if="coupon.starts_at || coupon.expires_at">
                                    <div v-if="coupon.starts_at">From: {{ formatDate(coupon.starts_at) }}</div>
                                    <div v-if="coupon.expires_at">Until: {{ formatDate(coupon.expires_at) }}</div>
                                </div>
                                <span v-else class="text-gray-400">No expiry</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                    :class="coupon.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                    {{ coupon.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="editCoupon(coupon)"
                                        class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                        <Edit class="w-4 h-4 text-blue-600" />
                                    </button>
                                    <button @click="deleteCoupon(coupon.id)"
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

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="flex items-center justify-between">
            <p class="text-sm text-slate-600">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} coupons
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                    :class="page === pagination.current_page ? 'bg-primary text-white' : 'border border-gray-300 hover:bg-gray-50'"
                    class="px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showAddModal || showEditModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full p-6 my-8">
                <h3 class="text-xl font-bold text-slate-900 mb-4">{{ showEditModal ? 'Edit Coupon' : 'Add Coupon' }}
                </h3>

                <!-- Tabs -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button v-for="tab in tabs" :key="tab.id" @click="currentTab = tab.id"
                        class="px-4 py-2 text-sm font-medium transition-colors relative"
                        :class="currentTab === tab.id ? 'text-primary' : 'text-slate-600 hover:text-slate-900'">
                        {{ tab.label }}
                        <div v-if="currentTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary">
                        </div>
                    </button>
                </div>

                <form @submit.prevent="showEditModal ? updateCoupon() : createCoupon()" class="space-y-6">
                    <!-- General Tab -->
                    <div v-show="currentTab === 'general'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Coupon Code *</label>
                                <input type="text" v-model="form.code" required
                                    @input="form.code = form.code.toUpperCase()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 font-mono uppercase"
                                    placeholder="SAVE20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Type *</label>
                                <select v-model="form.type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                    <option value="percent">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Value *</label>
                            <input type="number" v-model="form.value" required min="0" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                :placeholder="form.type === 'percent' ? '10 (for 10%)' : '100 (for ৳100)'">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 text-primary">
                                <span class="text-sm font-medium text-slate-700">Active</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_featured" class="w-4 h-4 text-primary">
                                <span class="text-sm font-medium text-slate-700">Featured</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_private" class="w-4 h-4 text-primary">
                                <span class="text-sm font-medium text-slate-700">Private</span>
                            </label>
                        </div>
                    </div>

                    <!-- Usage Tab -->
                    <div v-show="currentTab === 'usage'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Total Usage Limit</label>
                                <input type="number" v-model="form.usage_limit" min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="Leave empty for unlimited">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Per User Limit</label>
                                <input type="number" v-model="form.usage_limit_per_user" min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    placeholder="Leave empty for unlimited">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                                <input type="datetime-local" v-model="form.starts_at"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Expiry Date</label>
                                <input type="datetime-local" v-model="form.expires_at"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>
                    </div>

                    <!-- Restrictions Tab -->
                    <div v-show="currentTab === 'restrictions'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Minimum Spend (৳)</label>
                                <input type="number" v-model="form.min_spend" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Maximum Discount
                                    (৳)</label>
                                <input type="number" v-model="form.max_discount" min="0" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Product and user restrictions can be managed after creating
                            the coupon.</p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="button" @click="closeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors font-medium">
                            {{ showEditModal ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import axios from '../../axios';

const loading = ref(true);
const coupons = ref([]);
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingId = ref(null);
const currentTab = ref('general');
const selectedCoupons = ref([]);

const tabs = [
    { id: 'general', label: 'General' },
    { id: 'usage', label: 'Usage Limits' },
    { id: 'restrictions', label: 'Restrictions' },
];

const filters = ref({
    search: '',
    status: '',
    type: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
});

const form = ref({
    code: '',
    type: 'percent',
    value: 0,
    description: '',
    usage_limit: null,
    usage_limit_per_user: null,
    min_spend: null,
    max_discount: null,
    starts_at: '',
    expires_at: '',
    is_active: true,
    is_featured: false,
    is_private: false
});

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const isAllSelected = computed(() => {
    return coupons.value.length > 0 && selectedCoupons.value.length === coupons.value.length;
});

const isSelected = (id) => {
    return selectedCoupons.value.includes(id);
};

const toggleSelection = (id) => {
    const index = selectedCoupons.value.indexOf(id);
    if (index > -1) {
        selectedCoupons.value.splice(index, 1);
    } else {
        selectedCoupons.value.push(id);
    }
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedCoupons.value = [];
    } else {
        selectedCoupons.value = coupons.value.map(c => c.id);
    }
};

const clearSelection = () => {
    selectedCoupons.value = [];
};

const bulkActivate = async () => {
    if (!confirm(`Activate ${selectedCoupons.value.length} coupon(s)?`)) return;

    try {
        await Promise.all(
            selectedCoupons.value.map(id =>
                axios.put(`/coupons/${id}`, { is_active: true })
            )
        );
        clearSelection();
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        console.error('Error activating coupons:', error);
        alert('Failed to activate some coupons');
    }
};

const bulkDeactivate = async () => {
    if (!confirm(`Deactivate ${selectedCoupons.value.length} coupon(s)?`)) return;

    try {
        await Promise.all(
            selectedCoupons.value.map(id =>
                axios.put(`/coupons/${id}`, { is_active: false })
            )
        );
        clearSelection();
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        console.error('Error deactivating coupons:', error);
        alert('Failed to deactivate some coupons');
    }
};

const bulkDelete = async () => {
    if (!confirm(`Delete ${selectedCoupons.value.length} coupon(s)? This action cannot be undone.`)) return;

    try {
        await Promise.all(
            selectedCoupons.value.map(id =>
                axios.delete(`/coupons/${id}`)
            )
        );
        clearSelection();
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting coupons:', error);
        alert('Failed to delete some coupons. Coupons that have been used cannot be deleted.');
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const fetchCoupons = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/coupons', {
            params: {
                page,
                search: filters.value.search,
                status: filters.value.status,
                type: filters.value.type,
                per_page: 15
            }
        });

        coupons.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } catch (error) {
        console.error('Error fetching coupons:', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    fetchCoupons(1);
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        type: ''
    };
    fetchCoupons(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCoupons(page);
    }
};

const createCoupon = async () => {
    try {
        await axios.post('/admin/coupons', form.value);
        closeModal();
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        console.error('Error creating coupon:', error);
        alert('Failed to create coupon');
    }
};

const editCoupon = (coupon) => {
    editingId.value = coupon.id;
    form.value = {
        code: coupon.code,
        type: coupon.type,
        value: coupon.value,
        description: coupon.description || '',
        usage_limit: coupon.usage_limit,
        usage_limit_per_user: coupon.usage_limit_per_user,
        min_spend: coupon.min_spend,
        max_discount: coupon.max_discount,
        starts_at: coupon.starts_at ? coupon.starts_at.slice(0, 16) : '',
        expires_at: coupon.expires_at ? coupon.expires_at.slice(0, 16) : '',
        is_active: coupon.is_active,
        is_featured: coupon.is_featured,
        is_private: coupon.is_private
    };
    showEditModal.value = true;
};

const updateCoupon = async () => {
    try {
        await axios.put(`/coupons/${editingId.value}`, form.value);
        closeModal();
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        console.error('Error updating coupon:', error);
        alert('Failed to update coupon');
    }
};

const deleteCoupon = async (id) => {
    if (!confirm('Are you sure you want to delete this coupon?')) return;

    try {
        await axios.delete(`/coupons/${id}`);
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting coupon:', error);
        if (error.response?.status === 422) {
            alert(error.response.data.message);
        } else {
            alert('Failed to delete coupon');
        }
    }
};

const closeModal = () => {
    showAddModal.value = false;
    showEditModal.value = false;
    editingId.value = null;
    currentTab.value = 'general';
    form.value = {
        code: '',
        type: 'percent',
        value: 0,
        description: '',
        usage_limit: null,
        usage_limit_per_user: null,
        min_spend: null,
        max_discount: null,
        starts_at: '',
        expires_at: '',
        is_active: true,
        is_featured: false,
        is_private: false
    };
};

onMounted(() => {
    fetchCoupons();
});
</script>
