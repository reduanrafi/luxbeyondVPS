<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Brands Management</h2>
                <p class="text-sm text-zinc-400 mt-1">Manage product brands</p>
            </div>
            <button @click="showAddModal = true"
                class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Brand
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search brands..."
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                <select v-model="filters.status" @change="fetchBrands(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                    <option value="" class="bg-zinc-900">All Status</option>
                    <option value="active" class="bg-zinc-900">Active</option>
                    <option value="inactive" class="bg-zinc-900">Inactive</option>
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
            <p class="text-zinc-500 mt-4">Loading brands...</p>
        </div>

        <!-- Brands Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="brands.length === 0"
                class="col-span-full bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-12 text-center text-zinc-500">
                No brands found
            </div>
            <div v-for="brand in brands" :key="brand.id"
                class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden hover:border-amber-500/20 transition-all group">
                <div
                    class="h-40 bg-white/5 border-b border-white/5 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-50"></div>
                    <img v-if="brand.image_url" :src="brand.image_url" :alt="brand.name"
                        class="h-24 w-auto max-w-[80%] object-contain relative z-10 filter drop-shadow-2xl">
                    <Crown v-else class="w-16 h-16 text-zinc-700 relative z-10" />
                </div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="font-bold text-lg text-white group-hover:text-amber-500 transition-colors">{{
                            brand.name }}</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                            :class="brand.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'">
                            {{ brand.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="text-sm text-zinc-400 mb-6 line-clamp-2 h-10">{{ brand.description || `No description
                        provided for this brand.` }}
                    </p>
                    <div class="flex items-center justify-between pt-4 border-t border-white/5">
                        <span class="text-xs text-zinc-500 font-medium">{{ brand.products_count || 0 }} Products</span>
                        <div class="flex items-center gap-2">
                            <button @click="editBrand(brand)"
                                class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                <Edit class="w-4 h-4" />
                            </button>
                            <button @click="deleteBrand(brand.id)"
                                class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="flex items-center justify-between pt-6 border-t border-white/5">
            <p class="text-xs text-zinc-500">
                Showing <span class="font-medium text-white">{{ pagination.from }}</span> to <span
                    class="font-medium text-white">{{ pagination.to }}</span> of <span class="font-medium text-white">{{
                        pagination.total }}</span> brands
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
            class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-white mb-6">{{ showEditModal ? 'Edit Brand' : 'Add Brand' }}
                </h3>
                <form @submit.prevent="showEditModal ? updateBrand() : createBrand()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Name *</label>
                        <input type="text" v-model="form.name" required
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                            :class="{ 'border-red-500/50': errors.name }">
                        <p v-if="errors.name" class="text-xs text-red-400 mt-1">{{ errors.name[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Description</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                            :class="{ 'border-red-500/50': errors.description }"></textarea>
                        <p v-if="errors.description" class="text-xs text-red-400 mt-1">{{ errors.description[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Brand URL</label>
                        <input type="url" v-model="form.url" placeholder="https://example.com"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                            :class="{ 'border-red-500/50': errors.url }">
                        <p v-if="errors.url" class="text-xs text-red-400 mt-1">{{ errors.url[0] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Image</label>
                        <div class="flex gap-4 items-start">
                            <div class="flex-1">
                                <input type="file" @change="handleImageUpload" accept="image/*"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-500 file:text-black hover:file:bg-amber-400"
                                    :class="{ 'border-red-500/50': errors.image }">
                                <p v-if="errors.image" class="text-xs text-red-400 mt-1">{{ errors.image[0] }}</p>
                            </div>
                            <div v-if="imagePreview"
                                class="w-16 h-16 bg-white/5 rounded-lg border border-white/10 overflow-hidden flex-shrink-0 p-2">
                                <img :src="imagePreview" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="form.is_active" id="is_active"
                            class="w-4 h-4 text-amber-500 rounded border-white/20 bg-white/5 focus:ring-amber-500">
                        <label for="is_active" class="text-sm font-medium text-zinc-300">Active</label>
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
import { Plus, Edit, Trash2, Crown } from 'lucide-vue-next';
import axios from '../../axios';

const loading = ref(true);
const brands = ref([]);
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingId = ref(null);
const filters = ref({
    search: '',
    status: ''
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
    name: '',
    description: '',
    url: '',
    is_active: true
});
const imageFile = ref(null);
const imagePreview = ref(null);
const errors = ref({});

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const fetchBrands = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/brands', {
            params: {
                page,
                search: filters.value.search,
                status: filters.value.status,
                per_page: 15
            }
        });

        brands.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } catch (error) {
        console.error('Error fetching brands:', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    fetchBrands(1);
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: ''
    };
    fetchBrands(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchBrands(page);
    }
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const createBrand = async () => {
    errors.value = {};
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('description', form.value.description || '');
    formData.append('url', form.value.url || '');
    formData.append('is_active', form.value.is_active ? 1 : 0);
    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    try {
        await axios.post('/admin/brands', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        closeModal();
        fetchBrands(pagination.value.current_page);
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error creating brand:', error);
            alert('Failed to create brand');
        }
    }
};

const editBrand = (brand) => {
    editingId.value = brand.id;
    form.value = {
        name: brand.name,
        description: brand.description || '',
        url: brand.url || '',
        is_active: brand.is_active
    };
    imagePreview.value = brand.image_url || brand.image;
    imageFile.value = null;
    errors.value = {};
    showEditModal.value = true;
};

const updateBrand = async () => {
    errors.value = {};
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', form.value.name);
    formData.append('description', form.value.description || '');
    formData.append('url', form.value.url || '');
    formData.append('is_active', form.value.is_active ? 1 : 0);
    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    try {
        await axios.post(`/admin/brands/${editingId.value}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        closeModal();
        fetchBrands(pagination.value.current_page);
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error updating brand:', error);
            alert('Failed to update brand');
        }
    }
};

const deleteBrand = async (id) => {
    if (!confirm('Are you sure you want to delete this brand?')) return;

    try {
        await axios.delete(`/admin/brands/${id}`);
        fetchBrands(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting brand:', error);
        if (error.response?.status === 422) {
            alert('Cannot delete brand with existing products');
        } else {
            alert('Failed to delete brand');
        }
    }
};

const closeModal = () => {
    showAddModal.value = false;
    showEditModal.value = false;
    editingId.value = null;
    form.value = {
        name: '',
        description: '',
        url: '',
        is_active: true
    };
    imageFile.value = null;
    imagePreview.value = null;
    errors.value = {};
};

onMounted(() => {
    fetchBrands();
});
</script>
