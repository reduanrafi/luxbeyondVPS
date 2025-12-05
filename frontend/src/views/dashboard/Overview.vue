<template>
    <div>
        <!-- Profile Summary -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white font-bold text-xl shadow-md">
                    {{ userInitials }}
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Welcome back,</p>
                    <p class="text-xl font-bold text-slate-900 leading-tight">
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
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-primary bg-primary/5 hover:bg-primary/10 transition-colors">
                    View My Orders
                    <span class="text-xs">→</span>
                </router-link>
                <router-link
                    to="/dashboard/requests"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Active Requests
                </router-link>
                <router-link
                    to="/dashboard/settings"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                    Manage Account
                </router-link>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Orders -->
            <div class="relative bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-2xl shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">Total Orders</p>
                        <p class="text-4xl font-extrabold text-white">{{ stats.totalOrders }}</p>
                        <p class="text-blue-200 text-xs mt-2">{{ stats.ordersThisMonth > 0 ? `+${stats.ordersThisMonth} this month` : 'No orders this month' }}</p>
                    </div>
                    <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <ShoppingBag class="w-8 h-8 text-white" />
                    </div>
                </div>
            </div>

            <!-- Active Requests -->
            <div class="relative bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-2xl shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium mb-1">Active Requests</p>
                        <p class="text-4xl font-extrabold text-white">{{ stats.activeRequests }}</p>
                        <p class="text-purple-200 text-xs mt-2">{{ stats.pendingRequests > 0 ? `${stats.pendingRequests} pending` : 'No pending requests' }}</p>
                    </div>
                    <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <Package class="w-8 h-8 text-white" />
                    </div>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="relative bg-gradient-to-br from-green-500 to-emerald-600 p-6 rounded-2xl shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium mb-1">Total Spent</p>
                        <p class="text-4xl font-extrabold text-white">৳{{ stats.totalSpent.toLocaleString() }}</p>
                        <p class="text-green-200 text-xs mt-2">All time</p>
                    </div>
                    <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
                        <DollarSign class="w-8 h-8 text-white" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-900">Recent Activity</h3>
                <router-link to="/dashboard/orders" class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors">View All</router-link>
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
                    class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                    <div class="mt-0.5 p-2 rounded-lg transition-all group-hover:scale-110"
                        :class="getActivityBgClass(activity.type)">
                        <component :is="getActivityIcon(activity.type)" class="w-4 h-4" 
                            :class="getActivityIconClass(activity.type)" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-slate-900 group-hover:text-primary transition-colors">{{ activity.title }}</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ activity.time }}</p>
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

const getActivityBgClass = (type) => {
    if (type === 'order') return 'bg-blue-100';
    if (type === 'request') return 'bg-purple-100';
    return 'bg-green-100';
};

const getActivityIconClass = (type) => {
    if (type === 'order') return 'text-blue-600';
    if (type === 'request') return 'text-purple-600';
    return 'text-green-600';
};

onMounted(() => {
    fetchStats();
});
</script>
