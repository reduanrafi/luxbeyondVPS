<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Customers Management</h2>
                <p class="text-sm text-zinc-400 mt-1">View and manage customer accounts</p>
            </div>
            <button @click="openModal()"
                class="px-4 py-2 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Add Customer
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="stat in customerStats" :key="stat.label"
                class="bg-[#111111] rounded-lg shadow-md border border-white/5 p-4 hover:border-primary/20 transition-colors">
                <p class="text-sm text-zinc-400">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ stat.value }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-4">
            <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search customers by name or email..."
                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
        </div>

        <!-- Loading State -->
        <div v-if="loading"
            class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-8 text-center text-zinc-400">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2">Loading customers...</p>
        </div>

        <!-- Customers Table -->
        <div v-else class="bg-[#111111] rounded-xl shadow-md border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Customer</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Email</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Phone</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Orders</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Total Spent</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Joined</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="customers.length === 0">
                            <td colspan="7" class="py-8 text-center text-zinc-500">No customers found</td>
                        </tr>
                        <tr v-for="customer in customers" :key="customer.id"
                            class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-black font-bold">
                                        {{ customer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white text-sm">{{ customer.name }}</p>
                                        <p class="text-xs text-zinc-500">ID: {{ customer.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ customer.email }}</td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ customer.phone }}</td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ customer.orders }}</td>
                            <td class="py-4 px-6 text-sm font-semibold text-primary">৳{{
                                customer.totalSpent.toLocaleString() }}</td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ customer.joined }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/customers/${customer.id}`"
                                        class="p-2 hover:bg-blue-500/10 rounded-lg transition-colors group">
                                        <Eye class="w-4 h-4 text-blue-500 group-hover:text-blue-400" />
                                    </router-link>
                                    <button @click="openModal(customer)"
                                        class="p-2 hover:bg-yellow-500/10 rounded-lg transition-colors group">
                                        <Edit class="w-4 h-4 text-yellow-500 group-hover:text-yellow-400" />
                                    </button>
                                    <button class="p-2 hover:bg-red-500/10 rounded-lg transition-colors group">
                                        <Ban class="w-4 h-4 text-red-500 group-hover:text-red-400" />
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
            <p class="text-sm text-zinc-500">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} customers
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-sm font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                    Previous
                </button>
                <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                    :class="page === pagination.current_page ? 'bg-primary text-slate-900 border-primary' : 'border border-white/10 text-zinc-400 hover:bg-white/5 hover:text-white'"
                    class="px-4 py-2 rounded-lg font-medium text-sm transition-colors border">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-sm font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                    Next
                </button>
            </div>
        </div>

        <!-- Customer Modal -->
        <CustomerForm v-if="showModal" :customer="selectedCustomer" @close="closeModal" @refresh="handleRefresh" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Eye, Ban, Plus, Edit } from 'lucide-vue-next';
import axios from '../../axios';
import CustomerForm from './CustomerForm.vue';

const loading = ref(true);
const customers = ref([]);
const searchQuery = ref('');
const statsData = ref({
    total_customers: 0,
    active_this_month: 0,
    new_this_week: 0,
    blocked: 0
});

// Modal state
const showModal = ref(false);
const selectedCustomer = ref(null);

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
});

const customerStats = computed(() => [
    { label: 'Total Customers', value: statsData.value.total_customers },
    { label: 'Active This Month', value: statsData.value.active_this_month },
    { label: 'New This Week', value: statsData.value.new_this_week },
    { label: 'Blocked', value: statsData.value.blocked },
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

const fetchStats = async () => {
    try {
        const response = await axios.get('/admin/customers-stats');
        statsData.value = response.data;
        console.log(statsData.value)
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};

const fetchCustomers = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/customers', {
            params: {
                page,
                search: searchQuery.value,
                per_page: 15
            }
        });

        // Handle both paginated and non-paginated responses just in case, though controller returns paginated
        const data = response.data.data || response.data;
        const meta = response.data; // pagination info usually on top level

        customers.value = data;
        pagination.value = {
            current_page: meta.current_page || 1,
            last_page: meta.last_page || 1,
            per_page: meta.per_page || 15,
            total: meta.total || 0,
            from: meta.from || 0,
            to: meta.to || 0
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

// Modal handlers
const openModal = (customer = null) => {
    selectedCustomer.value = customer;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedCustomer.value = null;
};

const handleRefresh = () => {
    fetchCustomers(pagination.value.current_page);
    fetchStats();
};

onMounted(() => {
    fetchCustomers();
    fetchStats();
});
</script>
