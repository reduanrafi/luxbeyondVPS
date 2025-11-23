<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Customers Management</h2>
            <p class="text-sm text-slate-600 mt-1">View and manage customer accounts</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="stat in customerStats" :key="stat.label"
                class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <p class="text-sm text-slate-600">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ stat.value }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
            <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search customers by name or email..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-slate-600 mt-2">Loading customers...</p>
        </div>

        <!-- Customers Table -->
        <div v-else class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Customer</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Email</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Phone</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Orders</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Total Spent</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Joined</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="customers.length === 0">
                            <td colspan="7" class="py-8 text-center text-slate-500">No customers found</td>
                        </tr>
                        <tr v-for="customer in customers" :key="customer.id"
                            class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                                        {{ customer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 text-sm">{{ customer.name }}</p>
                                        <p class="text-xs text-slate-500">ID: {{ customer.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ customer.email }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ customer.phone }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ customer.orders }}</td>
                            <td class="py-4 px-6 text-sm font-semibold text-slate-900">৳{{
                                customer.totalSpent.toLocaleString() }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ customer.joined }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <button class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                        <Eye class="w-4 h-4 text-blue-600" />
                                    </button>
                                    <button class="p-2 hover:bg-red-50 rounded-lg transition-colors">
                                        <Ban class="w-4 h-4 text-red-600" />
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
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} customers
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Eye, Ban } from 'lucide-vue-next';
import axios from '../../axios';

const loading = ref(true);
const customers = ref([]);
const searchQuery = ref('');
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
});

const customerStats = computed(() => [
    { label: 'Total Customers', value: pagination.value.total },
    { label: 'Active This Month', value: '234' }, // TODO: Calculate from backend
    { label: 'New This Week', value: '45' }, // TODO: Calculate from backend
    { label: 'Blocked', value: '3' }, // TODO: Calculate from backend
]);

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const fetchCustomers = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/customers', {
            params: {
                page,
                search: searchQuery.value,
                per_page: 15
            }
        });

        customers.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } catch (error) {
        console.error('Error fetching customers:', error);
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    fetchCustomers(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchCustomers(page);
    }
};

onMounted(() => {
    fetchCustomers();
});
</script>
