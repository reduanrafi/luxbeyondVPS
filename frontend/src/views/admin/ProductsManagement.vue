<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Products Management</h2>
                <p class="text-sm text-zinc-400 mt-1">Manage your product inventory</p>
            </div>
            <router-link to="/admin/products/create"
                class="px-4 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-all shadow-lg shadow-primary-500/20 flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Add Product
            </router-link>
        </div>

        <!-- Filters -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search products..."
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                <select v-model="filters.category" @change="fetchProducts(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                    <option value="all" class="bg-zinc-900">All Categories</option>
                    <option value="Electronics" class="bg-zinc-900">Electronics</option>
                    <option value="Clothing" class="bg-zinc-900">Clothing</option>
                    <option value="Home & Garden" class="bg-zinc-900">Home & Garden</option>
                    <option value="Accessories" class="bg-zinc-900">Accessories</option>
                </select>
                <select v-model="filters.status" @change="fetchProducts(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                    <option value="" class="bg-zinc-900">All Status</option>
                    <option value="in_stock" class="bg-zinc-900 text-nowrap">In Stock</option>
                    <option value="low_stock" class="bg-zinc-900 text-nowrap">Low Stock</option>
                    <option value="out_of_stock" class="bg-zinc-900 text-nowrap">Out of Stock</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2 border border-white/10 text-zinc-400 rounded-lg hover:bg-white/5 hover:text-white transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 bg-primary"></div>
            <p class="text-zinc-500 mt-4">Loading products...</p>
        </div>

        <!-- Products Table -->
        <div v-else class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Product</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">SKU</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Category</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Price</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Stock</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="products.length === 0">
                            <td colspan="7" class="py-12 text-center text-zinc-500">No products found</td>
                        </tr>
                        <tr v-for="product in products" :key="product.id"
 class="hover:bg-white/5 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <img :src="product.image_url || 'https://via.placeholder.com/100'"
                                        :alt="product.name" class="w-12 h-12 rounded-lg object-contain bg-white/5">
                                    <div>
                                        <p class="font-semibold text-white text-sm">{{ product.name }}</p>
                                        <p class="text-xs text-zinc-500">{{ product.brand || 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ product.sku }}</td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ product.category }}</td>
                            <td class="py-4 px-6 text-sm font-mono text-primary-500">৳{{
                                Number(product.price).toLocaleString()
                                }}</td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ product.total_stock || 0 }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border text-nowrap"
                                    :class="getStockClass(product.total_stock || 0)">
                                    {{ getStockLabel(product.total_stock || 0) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/products/${product.id}/edit`"
                                        class="p-2 hover:bg-white/10 rounded-lg transition-colors text-blue-400 hover:text-blue-300">
                                        <Edit class="w-4 h-4" />
                                    </router-link>
                                    <button @click="deleteProduct(product.id)"
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
                Showing <span class="font-medium text-white">{{ pagination.from }}</span> to <span
                    class="font-medium text-white">{{ pagination.to }}</span> of <span class="font-medium text-white">{{
                        pagination.total }}</span> products
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="px-3 py-1.5 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-xs font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                    :class="page === pagination.current_page ? 'bg-primary text-black bg-primary font-bold' : 'border-white/10 text-zinc-400 hover:bg-white/5'"
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
    if (stock > 10) return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
    if (stock > 0) return 'bg-primary/10 text-primary-500 bg-primary/20';
    return 'bg-red-500/10 text-red-400 border-red-500/20';
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
