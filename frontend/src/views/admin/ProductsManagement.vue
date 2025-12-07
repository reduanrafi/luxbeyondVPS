<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Products Management</h2>
                <p class="text-sm text-slate-600 mt-1">Manage your product inventory</p>
            </div>
            <router-link to="/admin/products/create"
                class="px-4 py-2 bg-primary text-slate-900 font-semibold rounded-lg hover:bg-primary-hover transition-all shadow-md flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Product
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search products..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select v-model="filters.category" @change="fetchProducts(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                    <option value="all">All Categories</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Home & Garden">Home & Garden</option>
                    <option value="Accessories">Accessories</option>
                </select>
                <select v-model="filters.status" @change="fetchProducts(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-surface rounded-xl shadow-md border border-white/10 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-slate-600 mt-2">Loading products...</p>
        </div>

        <!-- Products Table -->
        <div v-else class="bg-surface rounded-xl shadow-md border border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-white/10">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Product</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">SKU</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Category</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Price</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Stock</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="products.length === 0">
                            <td colspan="7" class="py-8 text-center text-slate-500">No products found</td>
                        </tr>
                        <tr v-for="product in products" :key="product.id"
                            class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <img :src="product.image_url || 'https://via.placeholder.com/100'" :alt="product.name"
                                        class="w-12 h-12 rounded-lg object-cover">
                                    <div>
                                        <p class="font-semibold text-slate-900 text-sm">{{ product.name }}</p>
                                        <p class="text-xs text-slate-500">{{ product.brand || 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ product.sku }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ product.category }}</td>
                            <td class="py-4 px-6 text-sm font-mono text-slate-900">৳{{ Number(product.price).toLocaleString()
                                }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ product.total_stock || 0 }}</td>
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="getStockClass(product.total_stock || 0)">
                                    {{ getStockLabel(product.total_stock || 0) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/products/${product.id}/edit`"
                                        class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                        <Edit class="w-4 h-4 text-blue-600" />
                                    </router-link>
                                    <button @click="deleteProduct(product.id)"
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
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} products
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                    :class="page === pagination.current_page ? 'bg-primary text-slate-900' : 'border border-gray-300 hover:bg-gray-50'"
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import axios from '../../axios';

const loading = ref(true);
const products = ref([]);
const filters = ref({
    search: '',
    category: 'all',
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

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const fetchProducts = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/products', {
            params: {
                page,
                search: filters.value.search,
                category: filters.value.category !== 'all' ? filters.value.category : null,
                status: filters.value.status,
                per_page: 15
            }
        });

        products.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    fetchProducts(1);
};

const resetFilters = () => {
    filters.value = {
        search: '',
        category: 'all',
        status: ''
    };
    fetchProducts(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchProducts(page);
    }
};

const deleteProduct = async (id) => {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        await axios.delete(`/products/${id}`);
        fetchProducts(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting product:', error);
    }
};

const getStockClass = (stock) => {
    if (stock > 10) return 'bg-green-100 text-green-700';
    if (stock > 0) return 'bg-yellow-100 text-yellow-700';
    return 'bg-red-100 text-red-700';
};

const getStockLabel = (stock) => {
    if (stock > 10) return 'In Stock';
    if (stock > 0) return 'Low Stock';
    return 'Out of Stock';
};

onMounted(() => {
    fetchProducts();
});
</script>
