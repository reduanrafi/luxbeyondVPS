<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Coupons Management</h2>
                <p class="text-sm text-zinc-400 mt-1">Manage discount coupons and promotional codes</p>
            </div>
            <button @click="showAddModal = true"
                class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Coupon
            </button>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div v-if="selectedCoupons.length > 0"
            class="bg-zinc-900 border border-amber-500/20 rounded-2xl p-4 flex items-center justify-between shadow-lg shadow-amber-500/5">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-amber-500">{{ selectedCoupons.length }} coupon(s) selected</span>
                <button @click="clearSelection" class="text-sm text-zinc-400 hover:text-white font-medium transition-colors">Clear
                    Selection</button>
            </div>
            <div class="flex items-center gap-2">
                <button @click="bulkActivate"
                    class="px-4 py-2 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-sm font-medium rounded-lg hover:bg-emerald-500/20 transition-colors">
                    Activate
                </button>
                <button @click="bulkDeactivate"
                    class="px-4 py-2 bg-zinc-700/50 text-zinc-300 border border-zinc-600 text-sm font-medium rounded-lg hover:bg-zinc-700 transition-colors">
                    Deactivate
                </button>
                <button @click="bulkDelete"
                    class="px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/20 text-sm font-medium rounded-lg hover:bg-red-500/20 transition-colors">
                    Delete
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search coupons..."
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                <select v-model="filters.status" @change="fetchCoupons(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                    <option value="" class="bg-zinc-900">All Status</option>
                    <option value="active" class="bg-zinc-900">Active</option>
                    <option value="inactive" class="bg-zinc-900">Inactive</option>
                    <option value="expired" class="bg-zinc-900">Expired</option>
                </select>
                <select v-model="filters.type" @change="fetchCoupons(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                    <option value="" class="bg-zinc-900">All Types</option>
                    <option value="fixed" class="bg-zinc-900">Fixed Amount</option>
                    <option value="percent" class="bg-zinc-900">Percentage</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2 border border-white/10 text-zinc-400 rounded-lg hover:bg-white/5 hover:text-white transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>
            <p class="text-zinc-500 mt-4">Loading coupons...</p>
        </div>

        <!-- Coupons Table -->
        <div v-else class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                                    class="w-4 h-4 text-amber-500 rounded border-white/20 bg-white/5 focus:ring-amber-500">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Code</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Value</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Usage</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Validity</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="coupons.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-zinc-500">No coupons found</td>
                        </tr>
                        <tr v-for="coupon in coupons" :key="coupon.id" class="hover:bg-white/5 transition-colors"
                            :class="{ 'bg-amber-500/5': isSelected(coupon.id) }">
                            <td class="px-6 py-4">
                                <input type="checkbox" :checked="isSelected(coupon.id)"
                                    @change="toggleSelection(coupon.id)" class="w-4 h-4 text-amber-500 rounded border-white/20 bg-white/5 focus:ring-amber-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-amber-500">{{ coupon.code }}</span>
                                    <span v-if="coupon.is_featured"
                                        class="px-2 py-0.5 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 text-xs rounded">Featured</span>
                                    <span v-if="coupon.is_private"
                                        class="px-2 py-0.5 bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs rounded">Private</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="capitalize text-zinc-300">{{ coupon.type }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-white font-medium">
                                <span v-if="coupon.type === 'fixed'">৳{{ coupon.value }}</span>
                                <span v-else>{{ coupon.value }}%</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-300">
                                    <div>{{ coupon.usage_count || 0 }} / {{ coupon.usage_limit || '∞' }}</div>
                                    <div class="text-xs text-zinc-500" v-if="coupon.usage_limit_per_user">
                                        {{ coupon.usage_limit_per_user }} per user
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-400">
                                <div v-if="coupon.starts_at || coupon.expires_at">
                                    <div v-if="coupon.starts_at">From: {{ formatDate(coupon.starts_at) }}</div>
                                    <div v-if="coupon.expires_at">Until: {{ formatDate(coupon.expires_at) }}</div>
                                </div>
                                <span v-else class="text-zinc-600">No expiry</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                                    :class="coupon.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'">
                                    {{ coupon.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="editCoupon(coupon)"
                                        class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteCoupon(coupon.id)"
                                        class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="flex items-center justify-between pt-6 border-t border-white/5">
            <p class="text-xs text-zinc-500">
                Showing <span class="font-medium text-white">{{ pagination.from }}</span> to <span class="font-medium text-white">{{ pagination.to }}</span> of <span class="font-medium text-white">{{ pagination.total }}</span> coupons
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="px-3 py-1.5 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-xs font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                    :class="page === pagination.current_page ? 'bg-amber-500 text-black border-amber-500 font-bold' : 'border-white/10 text-zinc-400 hover:bg-white/5'"
                    class="px-3 py-1.5 border rounded-lg text-xs transition-colors">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-1.5 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-xs font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showAddModal || showEditModal"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-3xl w-full p-6 my-8">
                <h3 class="text-xl font-bold text-white mb-6">{{ showEditModal ? 'Edit Coupon' : 'Add Coupon' }}
                </h3>

                <!-- Tabs -->
                <div class="flex border-b border-white/10 mb-6">
                    <button v-for="tab in tabs" :key="tab.id" @click="currentTab = tab.id"
                        class="px-4 py-2 text-sm font-medium transition-colors relative"
                        :class="currentTab === tab.id ? 'text-amber-500' : 'text-zinc-400 hover:text-white'">
                        {{ tab.label }}
                        <div v-if="currentTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-500">
                        </div>
                    </button>
                </div>

                <form @submit.prevent="showEditModal ? updateCoupon() : createCoupon()" class="space-y-6">
                    <!-- General Tab -->
                    <div v-show="currentTab === 'general'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Coupon Code *</label>
                                <input type="text" v-model="form.code" required
                                    @input="form.code = form.code.toUpperCase()"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50 font-mono uppercase"
                                    placeholder="SAVE20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Type *</label>
                                <select v-model="form.type" required
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                                    <option value="percent" class="bg-zinc-900">Percentage</option>
                                    <option value="fixed" class="bg-zinc-900">Fixed Amount</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Value *</label>
                            <input type="number" v-model="form.value" required min="0" step="0.01"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                :placeholder="form.type === 'percent' ? '10 (for 10%)' : '100 (for ৳100)'">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Description</label>
                            <textarea v-model="form.description" rows="3"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"></textarea>
                        </div>
                        <div class="flex gap-4 border-t border-white/5 pt-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 text-amber-500 rounded border-white/20 bg-white/5 focus:ring-amber-500">
                                <span class="text-sm font-medium text-zinc-300">Active</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.is_featured" class="w-4 h-4 text-amber-500 rounded border-white/20 bg-white/5 focus:ring-amber-500">
                                <span class="text-sm font-medium text-zinc-300">Featured</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.is_private" class="w-4 h-4 text-amber-500 rounded border-white/20 bg-white/5 focus:ring-amber-500">
                                <span class="text-sm font-medium text-zinc-300">Private</span>
                            </label>
                        </div>
                    </div>

                    <!-- Usage Tab -->
                    <div v-show="currentTab === 'usage'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Total Usage Limit</label>
                                <input type="number" v-model="form.usage_limit" min="1"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="Leave empty for unlimited">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Per User Limit</label>
                                <input type="number" v-model="form.usage_limit_per_user" min="1"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                                    placeholder="Leave empty for unlimited">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Start Date</label>
                                <input type="datetime-local" v-model="form.starts_at"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50 dark-calendar">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Expiry Date</label>
                                <input type="datetime-local" v-model="form.expires_at"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50 dark-calendar">
                            </div>
                        </div>
                    </div>

                    <!-- Restrictions Tab -->
                    <div v-show="currentTab === 'restrictions'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Minimum Spend (৳)</label>
                                <input type="number" v-model="form.min_spend" min="0" step="0.01"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Maximum Discount
                                    (৳)</label>
                                <input type="number" v-model="form.max_discount" min="0" step="0.01"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                            </div>
                        </div>
                        <p class="text-xs text-zinc-500">Product and user restrictions can be managed after creating
                            the coupon.</p>
                    </div>

                    <div class="flex gap-3 pt-6 border-t border-white/5">
                        <button type="button" @click="closeModal"
                            class="flex-1 px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors font-medium text-zinc-400">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-amber-500 text-black rounded-lg hover:bg-amber-400 transition-colors font-bold">
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

<style>
.dark-calendar {
    color-scheme: dark;
}
</style>
