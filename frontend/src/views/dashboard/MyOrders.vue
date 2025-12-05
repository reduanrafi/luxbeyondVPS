<template>
    <div>
        <!-- Orders Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Order ID</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Date</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Items</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Total</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Status</th>
                            <th class="p-3 font-semibold text-slate-700 text-xs uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                                <p class="mt-2 text-xs">Loading orders...</p>
                            </td>
                        </tr>
                        <tr v-else-if="orders.length === 0">
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                <p class="text-sm font-semibold mb-1">No orders found</p>
                                <p class="text-xs">You haven't placed any orders yet.</p>
                            </td>
                        </tr>
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-blue-50/50 transition-all group">
                            <td class="p-3 font-semibold text-primary group-hover:text-primary-hover transition-colors text-xs">#{{ order.id }}</td>
                            <td class="p-3 text-slate-600 text-xs">{{ order.date }}</td>
                            <td class="p-3 text-slate-600 text-xs max-w-xs truncate" :title="order.items">{{ order.items }}</td>
                            <td class="p-3 font-semibold text-slate-900 text-sm">{{ order.total }}</td>
                            <td class="p-3">
                                <span :class="statusClass(order.status)" class="px-2 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide shadow-sm">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="p-3">
                                <button @click="viewOrderDetails(order)" class="text-xs font-semibold text-primary hover:text-primary-hover hover:underline transition-all">
                                    View →
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
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../../axios';

const router = useRouter();
const orders = ref([]);
const loading = ref(false);

const fetchOrders = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/orders');
        const ordersData = response.data.data || response.data;
        const ordersArray = Array.isArray(ordersData) ? ordersData : (ordersData.data || []);
        
        orders.value = ordersArray.map(order => {
            // Format items list
            const itemsList = order.items?.map(item => item.product_name || 'Product').join(', ') || 'No items';
            
            // Format total amount
            const total = parseFloat(order.total || order.total_amount || 0);
            const currencySymbol = order.currency?.symbol || '৳';
            const formattedTotal = `${currencySymbol}${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            
            // Format date
            const date = new Date(order.created_at).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            
            // Get status label
            const statusLabel = order.status?.label || order.status || 'Pending';
            
            return {
                id: order.order_number || `ORD-${order.id}`,
                orderId: order.id,
                date: date,
                items: itemsList,
                total: formattedTotal,
                status: statusLabel,
                rawStatus: order.status
            };
        });
    } catch (error) {
        console.error('Error fetching orders:', error);
        orders.value = [];
    } finally {
        loading.value = false;
    }
};

const statusClass = (status) => {
    const statusStr = typeof status === 'string' ? status : (status?.label || 'Pending');
    switch (statusStr) {
        case 'Processing':
        case 'Pending':
            return 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300';
        case 'Shipped':
            return 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300';
        case 'Delivered':
        case 'Completed':
            return 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-300';
        case 'Cancelled':
            return 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300';
        default:
            return 'bg-gray-100 text-gray-700';
    }
};

const viewOrderDetails = (order) => {
    // Navigate to order details page using order_number instead of id
    router.push(`/dashboard/orders/${order.id}`);
};

onMounted(() => {
    fetchOrders();
});
</script>
