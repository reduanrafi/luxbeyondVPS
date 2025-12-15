<template>
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="stat in stats" :key="stat.label"
                class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6 hover:shadow-lg transition-shadow hover:border-primary/20 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-400">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ stat.value }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                        :class="stat.bgColor">
                        <component :is="stat.icon" class="w-6 h-6" :class="stat.iconColor" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Revenue & Order Types -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Chart (Line) -->
            <div class="lg:col-span-2 bg-[#111111] rounded-xl shadow-md border border-white/5 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Revenue History (Last 6 Months)</h3>
                <div class="h-64">
                    <Line v-if="revenueChartData" :data="revenueChartData" :options="chartOptions" />
                    <div v-else class="h-full flex items-center justify-center text-zinc-500">No data available</div>
                </div>
            </div>

            <!-- Order Type Distribution (Doughnut) -->
            <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Request vs Shop</h3>
                <div class="h-64 flex items-center justify-center">
                    <Doughnut v-if="orderTypeChartData" :data="orderTypeChartData" :options="doughnutOptions" />
                     <div v-else class="text-zinc-500">No data available</div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Success Ratio & Top Products -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
             <!-- Success Ratio (Pie) -->
             <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Order Success Ratio</h3>
                <div class="h-64 flex items-center justify-center">
                    <Pie v-if="statusChartData" :data="statusChartData" :options="doughnutOptions" />
                    <div v-else class="text-zinc-500">No data available</div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="lg:col-span-2 bg-[#111111] rounded-xl shadow-md border border-white/5 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Top Selling Products</h3>
                <div class="space-y-4">
                    <div v-if="topProducts.length > 0">
                         <div v-for="product in topProducts" :key="product.id"
                            class="flex items-center justify-between group py-2 border-b border-white/5 last:border-0 hover:bg-white/5 px-2 rounded">
                            <div class="flex items-center gap-3">
                                <img :src="product.image || 'https://via.placeholder.com/50'" :alt="product.name"
                                    class="w-10 h-10 rounded-lg object-contain ring-1 ring-white/10">
                                <div>
                                    <p class="font-semibold text-white text-sm group-hover:text-primary transition-colors">
                                        {{ product.name }}</p>
                                    <p class="text-xs text-zinc-500">{{ product.sales }} sales</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-primary">৳{{ parseFloat(product.revenue).toLocaleString() }}</span>
                        </div>
                    </div>
                   <div v-else class="text-zinc-500 text-center py-10">No top products found</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { DollarSign, ShoppingBag, Package, Users } from 'lucide-vue-next';
import axios from '../../axios';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  ArcElement
} from 'chart.js';
import { Line, Doughnut, Pie } from 'vue-chartjs';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  ArcElement
);

const stats = ref([
    { label: 'Total Revenue', value: '৳0', icon: DollarSign, bgColor: 'bg-green-500/10', iconColor: 'text-green-500' },
    { label: 'Total Orders (Shop)', value: '0', icon: ShoppingBag, bgColor: 'bg-blue-500/10', iconColor: 'text-blue-500' },
    { label: 'Product Requests', value: '0', icon: Package, bgColor: 'bg-purple-500/10', iconColor: 'text-purple-500' },
    { label: 'Active Customers', value: '0', icon: Users, bgColor: 'bg-orange-500/10', iconColor: 'text-orange-500' },
]);

const topProducts = ref([]);
const revenueChartData = ref(null);
const statusChartData = ref(null);
const orderTypeChartData = ref(null);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    borderColor: '#D4AF37', // Primary color
    color: '#fff',
    scales: {
        y: {
            grid: { color: 'rgba(255, 255, 255, 0.1)' },
            ticks: { color: '#9ca3af' }
        },
        x: {
            grid: { color: 'rgba(255, 255, 255, 0.1)' },
            ticks: { color: '#9ca3af' }
        }
    },
    plugins: {
        legend: { labels: { color: '#fff' } }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { 
            position: 'right',
            labels: { color: '#fff' } 
        }
    }
};

const fetchDashboardData = async () => {
    try {
        // 1. Stats
        const statsRes = await axios.get('/admin/dashboard/stats');
        stats.value[0].value = `৳${parseFloat(statsRes.data.revenue).toLocaleString()}`;
        stats.value[1].value = statsRes.data.orders.toLocaleString();
        stats.value[2].value = statsRes.data.requests.toLocaleString();
        stats.value[3].value = statsRes.data.customers.toLocaleString();

        // 2. Charts
        const chartsRes = await axios.get('/admin/dashboard/charts');
        
        // Revenue Line Chart
        revenueChartData.value = {
            labels: chartsRes.data.revenue_history.labels,
            datasets: [{
                label: 'Monthly Revenue',
                data: chartsRes.data.revenue_history.data,
                fill: true,
                borderColor: '#D4AF37',
                backgroundColor: 'rgba(212, 175, 55, 0.1)',
                tension: 0.4
            }]
        };

        // Success Ratio Pie Chart
        const statusDist = chartsRes.data.order_status_distribution;
        statusChartData.value = {
            labels: ['Completed', 'Processing/Active', 'Cancelled'],
            datasets: [{
                data: [statusDist.completed, statusDist.processing, statusDist.cancelled],
                backgroundColor: ['#22c55e', '#3b82f6', '#ef4444'],
                borderWidth: 0
            }]
        };

        // Order Type Doughnut Chart
        const typeDist = chartsRes.data.type_distribution;
        orderTypeChartData.value = {
            labels: ['Shop Orders', 'Product Requests'],
            datasets: [{
                data: [typeDist.shop, typeDist.request],
                backgroundColor: ['#eab308', '#a855f7'], // Yellow, Purple
                borderWidth: 0
            }]
        };

        // 3. Top Products
        const topProdRes = await axios.get('/admin/dashboard/top-products');
        topProducts.value = topProdRes.data || [];

    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    }
};

onMounted(() => {
    fetchDashboardData();
});
</script>
