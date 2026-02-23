<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-serif font-bold text-white tracking-wide">Orders Management</h2>
                <p class="text-zinc-400 mt-2 text-sm">Track and manage customer orders and shipments</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="fetchOrders"
                    class="p-2.5 text-zinc-400 hover:text-primary hover:bg-white/5 rounded-lg transition-colors border border-white/10">
                    <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': loading }" />
                </button>
                <button
                    class="flex items-center gap-2 px-4 py-2.5 bg-primary text-black font-bold uppercase tracking-wider text-xs rounded-lg hover:bg-primary-hover transition-colors">
                    <Download class="w-4 h-4" />
                    Export
                </button>
            </div>
        </div>

        <!-- Stats -->
        <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="(stat, index) in orderStats" :key="stat.label"
                class="bg-[#111111] p-6 rounded-xl border border-white/5 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-full -mr-12 -mt-12 transition-transform group-hover:scale-110">
                </div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-zinc-500 text-xs font-semibold uppercase tracking-wider">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ stat.value }}</p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 text-primary">
                        <component :is="stat.icon" class="w-5 h-5" />
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Filters & Actions -->
        <div class="bg-[#111111] p-5 rounded-xl border border-white/5 space-y-4">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1 relative group">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 group-focus-within:text-primary transition-colors" />
                    <input v-model="filters.search" type="text" placeholder="Search order ID, customer, etc..."
                        class="w-full pl-10 pr-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600">
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap gap-3">
                    <select v-model="filters.status_id" @change="fetchOrders"
                        class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer min-w-[140px]">
                        <option value="" class="bg-[#111111]">All Status</option>
                        <option v-for="status in orderStatuses" :key="status.id" :value="status.id"
                            class="bg-[#111111]">
                            {{ status.label }}
                        </option>
                    </select>

                    <select v-model="filters.event_id" @change="fetchOrders"
                        class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer min-w-[140px]">
                        <option value="" class="bg-[#111111]">All Events</option>
                        <option v-for="event in events" :key="event.id" :value="event.id" class="bg-[#111111]">
                            {{ event.name }}
                        </option>
                    </select>

                    <div
                        class="flex items-center gap-2 bg-black/20 border border-white/10 rounded-lg p-1.5 order-last lg:order-none w-full lg:w-auto">
                        <input v-model="filters.date_from" @change="fetchOrders" type="date"
                            class="bg-transparent text-white text-xs px-2 py-1 focus:outline-none [&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:opacity-50 hover:[&::-webkit-calendar-picker-indicator]:opacity-100 cursor-pointer">
                        <span class="text-zinc-600">-</span>
                        <input v-model="filters.date_to" @change="fetchOrders" type="date"
                            class="bg-transparent text-white text-xs px-2 py-1 focus:outline-none [&::-webkit-calendar-picker-indicator]:invert [&::-webkit-calendar-picker-indicator]:opacity-50 hover:[&::-webkit-calendar-picker-indicator]:opacity-100 cursor-pointer">
                    </div>

                    <button @click="resetFilters"
                        class="px-4 py-2.5 bg-white/5 border border-white/10 text-white rounded-lg hover:bg-white/10 hover:border-white/20 transition-all"
                        title="Reset Filters">
                        <RotateCcw class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading && orders.length === 0" class="min-h-[400px] flex flex-col items-center justify-center">
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-white/10 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-primary border-t-transparent rounded-full animate-spin">
                </div>
            </div>
            <p class="text-zinc-500 mt-4 font-medium animate-pulse">Loading orders...</p>
        </div>

        <!-- Orders Table -->
        <div v-else class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-black/20 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Order Info</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Customer</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Date</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Items</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Total</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Status</th>
                            <th class="text-right py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="orders.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <Ghost class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-lg font-medium text-zinc-400">No orders found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="order in orders" :key="order.id"
                            class="group hover:bg-white/[0.02] transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-white font-mono">#{{ order.order_number
                                    }}</span>
                                    <span v-if="order.event" class="text-[10px] text-primary mt-1">{{ order.event.name
                                    }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-xs font-bold text-zinc-400">
                                        {{ order.user?.name?.charAt(0) || 'U' }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm text-white font-medium">{{ order.user?.name || 'Guest User'
                                        }}</span>
                                        <span class="text-xs text-zinc-500">{{ order.user?.email || 'No email' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-zinc-400">
                                {{ new Date(order.created_at).toLocaleDateString('en-GB', {
                                    day: 'numeric', month:
                                        'short', year: 'numeric'
                                }) }}
                                <div class="text-[10px] text-zinc-600">{{ new
                                    Date(order.created_at).toLocaleTimeString() }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-white/5 text-zinc-300 border border-white/5">
                                    {{ order.items?.length || 0 }} items
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm font-bold text-primary">
                                    {{ order.currency?.symbol || '৳' }}{{ parseFloat(order.total || order.total_amount
                                        || 0).toLocaleString() }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="relative inline-block w-full">
                                    <select :value="order.status_id"
                                        @change="updateOrderStatus(order.id, $event.target.value)"
                                        class="appearance-none px-3 py-1.5 pl-8 rounded-full text-xs font-bold border focus:outline-none focus:ring-1 focus:ring-primary/50 cursor-pointer transition-colors w-full min-w-[140px]"
                                        :style="getStatusStyle(order.status)" :disabled="updatingStatus === order.id">
                                        <option v-for="status in availableStatuses(order)" :key="status.id"
                                            :value="status.id" class="bg-[#111111] text-white">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full hidden sm:block shadow-sm"
                                        :style="{ backgroundColor: order.status?.color || '#52525b' }"></div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <router-link :to="`/admin/orders/${order.id}`"
                                        class="p-2 text-zinc-400 hover:text-primary hover:bg-white/5 rounded-lg transition-colors"
                                        title="View Details">
                                        <Eye class="w-4 h-4" />
                                    </router-link>
                                    <button @click="printOrder(order.id)"
                                        class="p-2 text-zinc-400 hover:text-green-400 hover:bg-white/5 rounded-lg transition-colors"
                                        title="Print Invoice">
                                        <Printer class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import {
    Eye,
    Printer,
    Search,
    Filter,
    Download,
    RefreshCw,
    RotateCcw,
    Package,
    Clock,
    CheckCircle,
    XCircle,
    Ghost
} from 'lucide-vue-next';
import axios from '../../axios';
import { useAuthStore } from '../../stores/auth';
import { debounce } from 'lodash';

const authStore = useAuthStore();
const loading = ref(false);
const updatingStatus = ref(null);
const orders = ref([]);
const orderStatuses = ref([]);
const events = ref([]);

const filters = ref({
    search: '',
    status_id: '',
    event_id: '',
    date_from: '',
    date_to: '',
});

const orderStats = ref([]);

const fetchOrders = async () => {
    loading.value = true;
    try {
        const params = {};
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status_id) params.status_id = filters.value.status_id;
        if (filters.value.event_id) params.event_id = filters.value.event_id;
        if (filters.value.date_from) params.date_from = filters.value.date_from;
        if (filters.value.date_to) params.date_to = filters.value.date_to;

        const response = await axios.get('/admin/orders', { params });
        orders.value = response.data.data || response.data;

        // Refresh stats when filters change or just once? Usually stats are global, so maybe just on mount or status update.
        // If we want stats to reflect filters, we'd need to pass params to stats endpoint.
        // But user request said "from order_statuses table not static", usually meaning the overview cards.
        // We will fetch stats separately.
    } catch (error) {
        console.error('Error fetching orders:', error);
    } finally {
        loading.value = false;
    }
};

// Watch search filter with debounce
watch(() => filters.value.search, debounce(() => {
    fetchOrders();
}, 500));

const fetchOrderStatuses = async () => {
    try {
        const response = await axios.get('/admin/order-statuses', { params: { all: true } });
        orderStatuses.value = response.data.data || response.data;
    } catch (error) {
        console.error('Error fetching order statuses:', error);
    }
};

const fetchEvents = async () => {
    try {
        const response = await axios.get('/admin/events');
        events.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error fetching events:', error);
    }
};

const updateOrderStatus = async (orderId, statusId) => {
    if (!confirm('Are you sure you want to update the order status?')) {
        return;
    }

    updatingStatus.value = orderId;
    try {
        await axios.post(`/admin/orders/${orderId}/update-status`, {
            status_id: parseInt(statusId),
            note: 'Status updated by admin via Dashboard'
        });

        await fetchOrders();
        await fetchStats();
    } catch (error) {
        console.error('Error updating order status:', error);
        alert(error.response?.data?.message || 'Error updating order status');
        await fetchOrders();
    } finally {
        updatingStatus.value = null;
    }
};

const availableStatuses = (order) => {
    // Return all active statuses as requested to allow admin override
    return orderStatuses.value.filter(s => s.is_active);
};

const getStatusStyle = (status) => {
    if (!status || !status.color) {
        return {
            backgroundColor: 'rgba(255, 255, 255, 0.05)',
            color: '#a1a1aa',
            borderColor: 'rgba(255, 255, 255, 0.1)'
        };
    }

    return {
        backgroundColor: `${status.color}15`, // ~8% opacity
        color: status.color,
        borderColor: `${status.color}33` // ~20% opacity
    };
};

const fetchStats = async () => {
    try {
        const response = await axios.get('/admin/orders/stats');
        const data = response.data;

        // Transform API data to stats card format
        // API returns { total: 10, statuses: [{ label: 'Pending', value: 5, icon: '...', color: '...' }] }

        const stats = [
            {
                label: 'Total Orders',
                value: data.total.toString(),
                icon: Package
            }
        ];

        // Map specific statuses or all? The user said "from order_statuses table".
        // Let's take the first 3-4 most important ones or map widely used ones.
        // Or better, dynamic iteration. But UI is grid-cols-4.
        // Let's map strict ones for now to fit the UI, or just mapping the dynamic ones.

        // Start with 'Pending', 'Processing', 'Completed' if they exist in dynamic data, else dynamic.
        // Actually, we can just map the data returned.

        data.statuses.forEach(status => {
            // We can try to map string icon name to component if passed, but for now specific icons:
            let icon = CheckCircle;
            const lowerLabel = status.label.toLowerCase();
            if (lowerLabel.includes('pending')) icon = Clock;
            else if (lowerLabel.includes('processing')) icon = RefreshCw;
            else if (lowerLabel.includes('shipped')) icon = Package;

            stats.push({
                label: status.label,
                value: status.value.toString(),
                icon: icon,
                color: status.color
            });
        });

        // Limit to 4 cards if we want to keep layout or use scroll?
        // Let's just set it. The grid is responsive.
        orderStats.value = stats;

    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};


const printOrder = (orderId) => {
    // TODO: Implement order printing
    console.log('Print order:', orderId);
};

const resetFilters = () => {
    filters.value = {
        search: '',
        status_id: '',
        event_id: '',
        date_from: '',
        date_to: '',
    };
    fetchOrders();
};

onMounted(() => {
    fetchOrderStatuses();
    fetchEvents();
    fetchStats();
    fetchOrders();
});
</script>
