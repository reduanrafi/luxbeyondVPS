<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Categories Management</h2>
                <p class="text-sm text-zinc-400 mt-1">Manage product categories</p>
            </div>
            <button @click="showAddModal = true"
                class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Category
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search categories..."
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                <select v-model="filters.status" @change="fetchCategories(1)"
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
            <p class="text-zinc-500 mt-4">Loading categories...</p>
        </div>

        <!-- Categories Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="categories.length === 0"
                class="col-span-full bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-12 text-center text-zinc-500">
                No categories found
            </div>
            <div v-for="category in categories" :key="category.id"
                class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden hover:border-amber-500/20 transition-all group">
                <div
                    class="h-40 bg-white/5 border-b border-white/5 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-50"></div>
                    <img v-if="category.image_url" :src="category.image_url" :alt="category.name"
                        class="h-24 w-auto max-w-[80%] object-contain relative z-10 filter drop-shadow-2xl">
                    <Tag v-else class="w-16 h-16 text-zinc-700 relative z-10" />
                </div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-lg text-white group-hover:text-amber-500 transition-colors">{{
                                category.name }}</h3>
                            <p v-if="category.parent" class="text-xs text-zinc-500 mt-1 flex items-center gap-1">
                                <span class="text-amber-500 font-medium">{{ category.parent.name }}</span>
                                <span class="text-zinc-600">→</span>
                                <span class="text-zinc-400">{{ category.name }}</span>
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                            :class="category.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'">
                            {{ category.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="text-sm text-zinc-400 mb-6 line-clamp-2 h-10">{{ category.description || `No description
                        provided.` }}
                    </p>
                    <div class="flex items-center justify-between pt-4 border-t border-white/5">
                        <span class="text-xs text-zinc-500 font-medium">{{ category.products_count || 0 }}
                            Products</span>
                        <div class="flex items-center gap-2">
                            <button @click="editCategory(category)"
                                class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                <Edit class="w-4 h-4" />
                            </button>
                            <button @click="deleteCategory(category.id)"
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
                        pagination.total }}</span> categories
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
                <h3 class="text-xl font-bold text-white mb-6">{{ showEditModal ? 'Edit Category' : 'Add Category' }}
                </h3>
                <form @submit.prevent="showEditModal ? updateCategory() : createCategory()" class="space-y-4">
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
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Parent Category</label>
                        <select v-model="form.parent_id"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50"
                            :class="{ 'border-red-500/50': errors.parent_id }">
                            <option :value="null" class="bg-zinc-900">None (Root Category)</option>
                            <option v-for="cat in availableParentCategories" :key="cat.id" :value="cat.id"
                                class="bg-zinc-900">
                                {{ cat.name }}
                            </option>
                        </select>
                        <p v-if="errors.parent_id" class="text-xs text-red-400 mt-1">{{ errors.parent_id[0] }}</p>
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
import { Plus, Edit, Trash2, Tag } from 'lucide-vue-next';
import axios from '../../axios';

const loading = ref(true);
const categories = ref([]);
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
    is_active: true,
    parent_id: null
});
const imageFile = ref(null);
const imagePreview = ref(null);
const allCategories = ref([]);

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const availableParentCategories = computed(() => {
    console.log(allCategories.value);

    // Always show all categories, but exclude self when editing
    if (showEditModal.value && editingId.value) {
        return allCategories.value.filter(cat => cat.id !== editingId.value);
        // return allCategories.value;
    }
    return allCategories.value;
});

const fetchCategories = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/categories', {
            params: {
                page,
                search: filters.value.search,
                status: filters.value.status,
                per_page: 15
            }
        });

        categories.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } catch (error) {
        console.error('Error fetching categories:', error);
    } finally {
        loading.value = false;
    }
};

const fetchAllCategories = async () => {
    try {
        const response = await axios.get('/admin/categories?all=true');
        allCategories.value = response.data;
    } catch (error) {
        console.error('Error fetching all categories:', error);
    }
};

const handleSearch = () => {
    fetchCategories(1);
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status: ''
    };
    fetchCategories(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCategories(page);
    }
};

const errors = ref({});

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const createCategory = async () => {
    errors.value = {};
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('description', form.value.description || '');
    formData.append('is_active', form.value.is_active ? 1 : 0);
    if (form.value.parent_id) {
        formData.append('parent_id', form.value.parent_id);
    }
    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    try {
        await axios.post('/admin/categories', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        closeModal();
        fetchCategories(pagination.value.current_page);
        fetchAllCategories();
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error creating category:', error);
            alert('Failed to create category');
        }
    }
};

const editCategory = (category) => {
    editingId.value = category.id;
    form.value = {
        name: category.name,
        description: category.description || '',
        is_active: category.is_active,
        parent_id: category.parent_id || null
    };
    imagePreview.value = category.image_url || category.image;
    imageFile.value = null;
    errors.value = {};
    showEditModal.value = true;
};

const updateCategory = async () => {
    errors.value = {};
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', form.value.name);
    formData.append('description', form.value.description || '');
    formData.append('is_active', form.value.is_active ? 1 : 0);
    if (form.value.parent_id) {
        formData.append('parent_id', form.value.parent_id);
    }
    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    try {
        await axios.post(`/admin/categories/${editingId.value}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        closeModal();
        fetchCategories(pagination.value.current_page);
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error updating category:', error);
            alert('Failed to update category');
        }
    }
};

const deleteCategory = async (id) => {
    if (!confirm('Are you sure you want to delete this category?')) return;

    try {
        await axios.delete(`/admin/categories/${id}`);
        fetchCategories(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting category:', error);
        if (error.response?.status === 422) {
            alert('Cannot delete category with existing products');
        } else {
            alert('Failed to delete category');
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
        is_active: true,
        parent_id: null
    };
    imageFile.value = null;
    imagePreview.value = null;
    errors.value = {};
};

onMounted(() => {
    fetchCategories();
    fetchAllCategories();
});
</script>
