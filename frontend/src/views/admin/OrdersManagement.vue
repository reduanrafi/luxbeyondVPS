<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Orders Management</h2>
            <p class="text-sm text-slate-600 mt-1">Track and manage customer orders</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="stat in orderStats" :key="stat.label"
                class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
                <p class="text-sm text-slate-600">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ stat.value }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <input v-model="filters.search" @input="fetchOrders" type="text" placeholder="Search orders..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select v-model="filters.status_id" @change="fetchOrders"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Status</option>
                    <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                        {{ status.label }}
                    </option>
                </select>
                <select v-model="filters.event_id" @change="fetchOrders"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Events</option>
                    <option v-for="event in events" :key="event.id" :value="event.id">
                        {{ event.name }}
                    </option>
                </select>
                <input v-model="filters.date_from" @change="fetchOrders" type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <input v-model="filters.date_to" @change="fetchOrders" type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <button @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Reset</button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-slate-600 mt-2">Loading orders...</p>
        </div>

        <!-- Orders Table -->
        <div v-else class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Order Number</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Customer</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Date</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Items</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Total</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Event</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="orders.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No orders found</td>
                        </tr>
                        <tr v-for="order in orders" :key="order.id" class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-6 text-sm font-medium text-slate-900">#{{ order.order_number }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                {{ order.user?.name || 'N/A' }}
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                {{ new Date(order.created_at).toLocaleDateString() }}
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-600">
                                {{ order.items?.length || 0 }} items
                            </td>
                            <td class="py-4 px-6 text-sm font-semibold text-slate-900">
                                {{ order.currency?.symbol || '৳' }}{{ parseFloat(order.total || order.total_amount || 0).toLocaleString() }}
                            </td>
                            <td class="py-4 px-6">
                                <select
                                    :value="order.status_id"
                                    @change="updateOrderStatus(order.id, $event.target.value)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold border-0 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    :class="getStatusClass(order.status)"
                                    :disabled="updatingStatus === order.id">
                                    <option v-for="status in availableStatuses(order)" :key="status.id" :value="status.id">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/orders/${order.id}`" 
                                        class="p-2 hover:bg-blue-50 rounded-lg transition-colors inline-block"
                                        title="View Details">
                                        <Eye class="w-4 h-4 text-blue-600" />
                                    </router-link>
                                    <button @click="printOrder(order.id)" class="p-2 hover:bg-green-50 rounded-lg transition-colors">
                                        <Printer class="w-4 h-4 text-green-600" />
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
import { ref, onMounted, computed } from 'vue';
import { Eye, Printer } from 'lucide-vue-next';
import axios from '../../axios';

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

const orderStats = ref([
    { label: 'Total Orders', value: '0' },
    { label: 'Pending', value: '0' },
    { label: 'Processing', value: '0' },
    { label: 'Completed', value: '0' },
]);

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
        
        // Update stats
        updateStats();
    } catch (error) {
        console.error('Error fetching orders:', error);
    } finally {
        loading.value = false;
    }
};

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
            note: 'Status updated by admin'
        });
        
        // Refresh orders
        await fetchOrders();
        alert('Order status updated successfully');
    } catch (error) {
        console.error('Error updating order status:', error);
        alert(error.response?.data?.message || 'Error updating order status');
        // Refresh to reset the select
        await fetchOrders();
    } finally {
        updatingStatus.value = null;
    }
};

const availableStatuses = (order) => {
    const currentStatus = orderStatuses.value.find(s => s.id === order.status_id);
    if (!currentStatus) return orderStatuses.value.filter(s => s.is_active);
    
    // If current status has allowed transitions, show only those
    if (currentStatus.allowed_next_statuses && currentStatus.allowed_next_statuses.length > 0) {
        return orderStatuses.value.filter(s => 
            s.is_active && (
                s.id === order.status_id || 
                currentStatus.allowed_next_statuses.includes(s.id)
            )
        );
    }
    
    // Otherwise show all active statuses
    return orderStatuses.value.filter(s => s.is_active);
};

const getStatusClass = (status) => {
    if (!status) return 'bg-gray-100 text-gray-700';
    
    const statusObj = orderStatuses.value.find(s => s.id === status.id || s.label === status.label);
    if (!statusObj) return 'bg-gray-100 text-gray-700';
    
    // Use the color from the status, or default classes
    return {
        'Completed': 'bg-green-100 text-green-700',
        'Processing': 'bg-blue-100 text-blue-700',
        'Shipped': 'bg-purple-100 text-purple-700',
        'Pending': 'bg-yellow-100 text-yellow-700',
        'Cancelled': 'bg-red-100 text-red-700'
    }[statusObj.label] || 'bg-gray-100 text-gray-700';
};

const updateStats = () => {
    orderStats.value = [
        { label: 'Total Orders', value: orders.value.length.toString() },
        { label: 'Pending', value: orders.value.filter(o => o.status?.label === 'Pending').length.toString() },
        { label: 'Processing', value: orders.value.filter(o => o.status?.label === 'Processing').length.toString() },
        { label: 'Completed', value: orders.value.filter(o => o.status?.label === 'Completed').length.toString() },
    ];
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
    fetchOrders();
});
</script>
