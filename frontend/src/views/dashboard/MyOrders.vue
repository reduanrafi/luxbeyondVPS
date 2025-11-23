<template>
    <div>
        <!-- Orders Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <th class="p-5 font-bold text-slate-700 text-sm uppercase tracking-wide">Order ID</th>
                            <th class="p-5 font-bold text-slate-700 text-sm uppercase tracking-wide">Date</th>
                            <th class="p-5 font-bold text-slate-700 text-sm uppercase tracking-wide">Items</th>
                            <th class="p-5 font-bold text-slate-700 text-sm uppercase tracking-wide">Total</th>
                            <th class="p-5 font-bold text-slate-700 text-sm uppercase tracking-wide">Status</th>
                            <th class="p-5 font-bold text-slate-700 text-sm uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-blue-50/50 transition-all group">
                            <td class="p-5 font-bold text-primary group-hover:text-primary-hover transition-colors">#{{ order.id }}</td>
                            <td class="p-5 text-slate-600 text-sm">{{ order.date }}</td>
                            <td class="p-5 text-slate-600 text-sm max-w-xs truncate">{{ order.items }}</td>
                            <td class="p-5 font-bold text-slate-900 text-lg">{{ order.total }}</td>
                            <td class="p-5">
                                <span :class="statusClass(order.status)" class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide shadow-sm">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="p-5">
                                <button class="text-sm font-bold text-primary hover:text-primary-hover hover:underline transition-all">
                                    View Details →
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
const orders = [
    { id: 'ORD-7829', date: 'Nov 22, 2024', items: 'Wireless Headphones, USB-C Cable', total: '৳13,500', status: 'Processing' },
    { id: 'ORD-7828', date: 'Nov 15, 2024', items: 'Smart Watch Series 5', total: '৳4,200', status: 'Shipped' },
    { id: 'ORD-7827', date: 'Oct 30, 2024', items: 'Leather Backpack', total: '৳8,500', status: 'Delivered' },
    { id: 'ORD-7826', date: 'Oct 12, 2024', items: 'Mechanical Keyboard', total: '৳6,500', status: 'Delivered' },
];

const statusClass = (status) => {
    switch (status) {
        case 'Processing': return 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300';
        case 'Shipped': return 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300';
        case 'Delivered': return 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>
