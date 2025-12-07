<template>
    <div>
        <!-- Orders Table -->
        <div class="bg-surface border border-white/5 overflow-hidden hover:border-primary/30 transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface border-b border-white/5">
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Order ID</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Date</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Items</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Total</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Status</th>
                            <th class="p-4 font-semibold text-slate-400 text-xs uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary">
                                </div>
                                <p class="mt-2 text-xs uppercase tracking-wider">Loading orders...</p>
                            </td>
                        </tr>
                        <tr v-else-if="orders.length === 0">
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                <p class="text-sm font-semibold mb-1">No orders found</p>
                                <p class="text-xs">You haven't placed any orders yet.</p>
                            </td>
                        </tr>
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-white/5 transition-colors group">
                            <td
                                class="p-4 font-semibold text-primary group-hover:text-white transition-colors text-xs tracking-wide">
                                #{{ order.id }}</td>
                            <td class="p-4 text-slate-400 text-xs">{{ order.date }}</td>
                            <td class="p-4 text-slate-400 text-xs max-w-xs truncate" :title="order.items">{{ order.items
                                }}</td>
                            <td class="p-4 font-serif text-white text-sm tracking-wide">{{ order.total }}</td>
                            <td class="p-4">
                                <span :class="statusClass(order.status)"
                                    class="px-2 py-1 border text-[10px] font-bold uppercase tracking-widest">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <button @click="viewOrderDetails(order)"
                                    class="text-xs font-bold uppercase tracking-wider text-primary hover:text-white transition-colors">
                                    View
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
            const currencySymbol = order.currency?.symbol || '৳';
            const formatMoney = (amount) => `${currencySymbol}${parseFloat(amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            const total = formatMoney(order.total || order.total_amount);
            const paid = formatMoney(order.paid_amount);
            const due = formatMoney(order.due_amount);
            
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
                total: total,
                paid: paid,
                due: due,
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
            return 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
        case 'Shipped':
            return 'bg-blue-500/10 text-blue-500 border-blue-500/20';
        case 'Delivered':
        case 'Completed':
            return 'bg-green-500/10 text-green-500 border-green-500/20';
        case 'Cancelled':
            return 'bg-red-500/10 text-red-500 border-red-500/20';
        default:
            return 'bg-slate-500/10 text-slate-400 border-white/10';
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
