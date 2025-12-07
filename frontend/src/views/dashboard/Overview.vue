<template>
    <div>
        <!-- Profile Summary -->
        <div class="bg-surface border border-white/5 p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-slate-900 font-bold text-xl shadow-md">
                    {{ userInitials }}
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-400">Welcome back,</p>
                    <p class="text-xl font-bold text-white leading-tight">
                        {{ user?.name || 'Lux Customer' }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <Mail class="w-3 h-3" />
                        <span>{{ user?.email || 'Add your email in profile settings' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <router-link
                    to="/dashboard/orders"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-sm text-sm font-bold text-slate-900 bg-primary hover:bg-white transition-colors">
                    View My Orders
                    <span class="text-xs">→</span>
                </router-link>
                <router-link
                    to="/dashboard/requests"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-sm text-sm font-semibold text-slate-300 bg-surface border border-white/10 hover:border-primary hover:text-primary transition-colors">
                    Active Requests
                </router-link>
                <router-link
                    to="/dashboard/settings"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-sm text-sm font-semibold text-slate-300 bg-surface border border-white/10 hover:border-primary hover:text-primary transition-colors">
                    Manage Account
                </router-link>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Orders -->
            <div class="relative bg-surface p-6 border border-white/5 group hover:border-primary/50 transition-colors">
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-widest font-bold mb-2">Total Orders</p>
                        <p class="text-3xl font-serif text-white">{{ stats.totalOrders }}</p>
                        <p class="text-primary text-xs mt-2 font-medium">{{ stats.ordersThisMonth > 0 ? `+${stats.ordersThisMonth} this month` : 'No orders this month' }}</p>
                    </div>
                    <div class="p-4 bg-primary/10 rounded-full">
                        <ShoppingBag class="w-6 h-6 text-primary" />
                    </div>
                </div>
            </div>

            <!-- Active Requests -->
            <div class="relative bg-surface p-6 border border-white/5 group hover:border-primary/50 transition-colors">
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-widest font-bold mb-2">Active Requests</p>
                        <p class="text-3xl font-serif text-white">{{ stats.activeRequests }}</p>
                        <p class="text-primary text-xs mt-2 font-medium">{{ stats.pendingRequests > 0 ? `${stats.pendingRequests} pending` : 'No pending requests' }}</p>
                    </div>
                    <div class="p-4 bg-primary/10 rounded-full">
                        <Package class="w-6 h-6 text-primary" />
                    </div>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="relative bg-surface p-6 border border-white/5 group hover:border-primary/50 transition-colors">
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-widest font-bold mb-2">Total Spent</p>
                        <p class="text-3xl font-serif text-white">৳{{ stats.totalSpent.toLocaleString() }}</p>
                        <p class="text-primary text-xs mt-2 font-medium">Lifetime Value</p>
                    </div>
                    <div class="p-4 bg-primary/10 rounded-full">
                        <DollarSign class="w-6 h-6 text-primary" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-surface border border-white/5 p-6 hover:border-primary/30 transition-colors">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-serif font-bold text-white">Recent Activity</h3>
                <router-link to="/dashboard/orders" class="text-xs font-semibold text-primary hover:text-white transition-colors">View All</router-link>
            </div>
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                <p class="mt-2 text-xs text-slate-500">Loading activity...</p>
            </div>
            <div v-else-if="activities.length === 0" class="text-center py-8">
                <p class="text-sm text-slate-500">No recent activity</p>
            </div>
            <div v-else class="space-y-3">
                <div v-for="activity in activities" :key="activity.id" 
                    class="flex items-start gap-4 p-3 rounded-none hover:bg-white/5 transition-colors group border-b border-white/5 last:border-0">
                    <div class="mt-0.5 p-2 rounded-full bg-primary/10 transition-all group-hover:bg-primary group-hover:text-slate-900 text-primary">
                        <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-300 group-hover:text-primary transition-colors">{{ activity.title }}</p>
                        <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wider">{{ activity.time }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { ShoppingBag, Package, DollarSign, CheckCircle, Truck, Tag, Mail } from 'lucide-vue-next';
import axios from '../../axios';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
const loading = ref(false);

const stats = ref({
    totalOrders: 0,
    ordersThisMonth: 0,
    activeRequests: 0,
    pendingRequests: 0,
    totalSpent: 0
});

const activities = ref([]);

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .filter(Boolean)
        .map((n) => n[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
});

const fetchStats = async () => {
    loading.value = true;
    try {
        // Fetch orders
        const ordersResponse = await axios.get('/orders');
        const ordersData = ordersResponse.data.data || ordersResponse.data;
        const ordersArray = Array.isArray(ordersData) ? ordersData : (ordersData.data || []);
        
        // Calculate stats
        const now = new Date();
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        
        stats.value.totalOrders = ordersArray.length;
        stats.value.ordersThisMonth = ordersArray.filter(order => {
            const orderDate = new Date(order.created_at);
            return orderDate >= startOfMonth;
        }).length;
        
        stats.value.totalSpent = ordersArray.reduce((sum, order) => {
            return sum + parseFloat(order.total || order.total_amount || 0);
        }, 0);
        
        // Fetch requests
        const requestsResponse = await axios.get('/requests');
        const requestsData = requestsResponse.data.data || requestsResponse.data;
        const requestsArray = Array.isArray(requestsData) ? requestsData : (requestsData.data || []);
        
        stats.value.activeRequests = requestsArray.length;
        stats.value.pendingRequests = requestsArray.filter(req => req.status === 'pending').length;
        
        // Generate recent activity from orders and requests
        const recentOrders = ordersArray.slice(0, 3).map(order => ({
            id: `order-${order.id}`,
            type: 'order',
            title: `Order #${order.order_number || order.id} ${getOrderStatusText(order.status)}`,
            time: formatTimeAgo(order.created_at)
        }));
        
        const recentRequests = requestsArray.slice(0, 2).map(request => ({
            id: `request-${request.id}`,
            type: 'request',
            title: `Request #${request.id} ${getRequestStatusText(request.status)}`,
            time: formatTimeAgo(request.created_at)
        }));
        
        activities.value = [...recentOrders, ...recentRequests]
            .sort((a, b) => new Date(b.time) - new Date(a.time))
            .slice(0, 5);
            
    } catch (error) {
        console.error('Error fetching stats:', error);
    } finally {
        loading.value = false;
    }
};

const formatTimeAgo = (dateString) => {
    if (!dateString) return 'Recently';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getOrderStatusText = (status) => {
    const statusStr = typeof status === 'string' ? status : (status?.label || '');
    if (statusStr.toLowerCase().includes('delivered') || statusStr.toLowerCase().includes('completed')) {
        return 'delivered successfully';
    }
    if (statusStr.toLowerCase().includes('shipped')) {
        return 'shipped';
    }
    return `status: ${statusStr}`;
};

const getRequestStatusText = (status) => {
    if (status === 'approved') return 'was approved';
    if (status === 'rejected') return 'was rejected';
    if (status === 'paid') return 'payment completed';
    return `status: ${status}`;
};

const getActivityIcon = (type) => {
    if (type === 'order') return ShoppingBag;
    if (type === 'request') return Package;
    return CheckCircle;
};

onMounted(() => {
    fetchStats();
});
</script>
