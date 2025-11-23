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
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" placeholder="Search orders..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option>All Status</option>
                    <option>Pending</option>
                    <option>Processing</option>
                    <option>Shipped</option>
                    <option>Completed</option>
                    <option>Cancelled</option>
                </select>
                <input type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <input type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <button
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Reset</button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Order ID</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Customer</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Date</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Items</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Total</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in orders" :key="order.id" class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-6 text-sm font-medium text-slate-900">#{{ order.id }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ order.customer }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ order.date }}</td>
                            <td class="py-4 px-6 text-sm text-slate-600">{{ order.items }} items</td>
                            <td class="py-4 px-6 text-sm font-semibold text-slate-900">৳{{ order.total.toLocaleString()
                                }}</td>
                            <td class="py-4 px-6">
                                <select
                                    class="px-3 py-1 rounded-full text-xs font-semibold border-0 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    :class="getStatusClass(order.status)">
                                    <option>Pending</option>
                                    <option>Processing</option>
                                    <option>Shipped</option>
                                    <option>Completed</option>
                                    <option>Cancelled</option>
                                </select>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <button class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                        <Eye class="w-4 h-4 text-blue-600" />
                                    </button>
                                    <button class="p-2 hover:bg-green-50 rounded-lg transition-colors">
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
import { ref } from 'vue';
import { Eye, Printer } from 'lucide-vue-next';

const orderStats = ref([
    { label: 'Total Orders', value: '1,234' },
    { label: 'Pending', value: '45' },
    { label: 'Processing', value: '23' },
    { label: 'Completed', value: '1,156' },
]);

const orders = ref([
    { id: 'ORD-1234', customer: 'John Doe', date: '2024-01-15', items: 3, total: 12500, status: 'Completed' },
    { id: 'ORD-1235', customer: 'Jane Smith', date: '2024-01-15', items: 2, total: 8900, status: 'Processing' },
    { id: 'ORD-1236', customer: 'Mike Johnson', date: '2024-01-14', items: 5, total: 15600, status: 'Shipped' },
    { id: 'ORD-1237', customer: 'Sarah Williams', date: '2024-01-14', items: 1, total: 6700, status: 'Pending' },
    { id: 'ORD-1238', customer: 'Tom Brown', date: '2024-01-13', items: 4, total: 23400, status: 'Completed' },
    { id: 'ORD-1239', customer: 'Emily Davis', date: '2024-01-13', items: 2, total: 9800, status: 'Cancelled' },
]);

const getStatusClass = (status) => {
    const classes = {
        'Completed': 'bg-green-100 text-green-700',
        'Processing': 'bg-blue-100 text-blue-700',
        'Shipped': 'bg-purple-100 text-purple-700',
        'Pending': 'bg-yellow-100 text-yellow-700',
        'Cancelled': 'bg-red-100 text-red-700'
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};
</script>
