<template>
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in stats" :key="stat.label"
                class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ stat.value }}</p>
                        <p class="text-sm mt-2" :class="stat.change >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }}% from last month
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" :class="stat.bgColor">
                        <component :is="stat.icon" class="w-6 h-6" :class="stat.iconColor" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Revenue Overview</h3>
                <div class="h-64 flex items-end justify-between gap-2">
                    <div v-for="(value, index) in revenueData" :key="index"
                        class="flex-1 bg-primary/20 rounded-t-lg relative group cursor-pointer hover:bg-primary/30 transition-colors"
                        :style="{ height: value + '%' }">
                        <div
                            class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                            ৳{{ (value * 1000).toLocaleString() }}
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-4 text-xs text-slate-600">
                    <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Top Products</h3>
                <div class="space-y-4">
                    <div v-for="product in topProducts" :key="product.id" class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img :src="product.image" :alt="product.name" class="w-12 h-12 rounded-lg object-cover">
                            <div>
                                <p class="font-semibold text-slate-900 text-sm">{{ product.name }}</p>
                                <p class="text-xs text-slate-500">{{ product.sales }} sales</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-primary">৳{{ product.revenue.toLocaleString() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Recent Orders</h3>
                <router-link to="/admin/orders" class="text-sm text-primary hover:underline font-medium">
                    View All
                </router-link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Order ID</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Customer</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Date</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Total</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in recentOrders" :key="order.id"
                            class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm font-medium text-slate-900">#{{ order.id }}</td>
                            <td class="py-3 px-4 text-sm text-slate-600">{{ order.customer }}</td>
                            <td class="py-3 px-4 text-sm text-slate-600">{{ order.date }}</td>
                            <td class="py-3 px-4 text-sm font-semibold text-slate-900">৳{{ order.total.toLocaleString()
                                }}</td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="getStatusClass(order.status)">
                                    {{ order.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <router-link to="/admin/products"
                class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all hover:border-primary group">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-primary group-hover:scale-110 transition-all">
                        <Package class="w-6 h-6 text-blue-600 group-hover:text-white" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900">Add Product</p>
                        <p class="text-xs text-slate-500">Create new product</p>
                    </div>
                </div>
            </router-link>
            <router-link to="/admin/orders"
                class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all hover:border-primary group">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-primary group-hover:scale-110 transition-all">
                        <ShoppingBag class="w-6 h-6 text-green-600 group-hover:text-white" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900">Manage Orders</p>
                        <p class="text-xs text-slate-500">View all orders</p>
                    </div>
                </div>
            </router-link>
            <router-link to="/admin/customers"
                class="bg-white rounded-xl shadow-md border border-gray-200 p-6 hover:shadow-lg transition-all hover:border-primary group">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-primary group-hover:scale-110 transition-all">
                        <Users class="w-6 h-6 text-purple-600 group-hover:text-white" />
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900">View Customers</p>
                        <p class="text-xs text-slate-500">Customer management</p>
                    </div>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { DollarSign, ShoppingBag, Package, Users } from 'lucide-vue-next';

const stats = ref([
    { label: 'Total Revenue', value: '৳2,45,000', change: 12.5, icon: DollarSign, bgColor: 'bg-green-100', iconColor: 'text-green-600' },
    { label: 'Total Orders', value: '1,234', change: 8.2, icon: ShoppingBag, bgColor: 'bg-blue-100', iconColor: 'text-blue-600' },
    { label: 'Total Products', value: '456', change: 3.1, icon: Package, bgColor: 'bg-purple-100', iconColor: 'text-purple-600' },
    { label: 'Total Customers', value: '892', change: 15.3, icon: Users, bgColor: 'bg-orange-100', iconColor: 'text-orange-600' },
]);

const revenueData = ref([45, 52, 38, 65, 72, 58]);

const topProducts = ref([
    { id: 1, name: 'Wireless Headphones', sales: 234, revenue: 585000, image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=100' },
    { id: 2, name: 'Smart Watch', sales: 189, revenue: 472500, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=100' },
    { id: 3, name: 'Laptop Stand', sales: 156, revenue: 234000, image: 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=100' },
    { id: 4, name: 'USB-C Cable', sales: 298, revenue: 89400, image: 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=100' },
]);

const recentOrders = ref([
    { id: 'ORD-1234', customer: 'John Doe', date: '2024-01-15', total: 12500, status: 'Completed' },
    { id: 'ORD-1235', customer: 'Jane Smith', date: '2024-01-15', total: 8900, status: 'Processing' },
    { id: 'ORD-1236', customer: 'Mike Johnson', date: '2024-01-14', total: 15600, status: 'Shipped' },
    { id: 'ORD-1237', customer: 'Sarah Williams', date: '2024-01-14', total: 6700, status: 'Pending' },
    { id: 'ORD-1238', customer: 'Tom Brown', date: '2024-01-13', total: 23400, status: 'Completed' },
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
