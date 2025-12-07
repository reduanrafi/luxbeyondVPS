<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Analytics & Reports</h2>
            <p class="text-sm text-slate-600 mt-1">View detailed business insights</p>
        </div>

        <!-- Date Range -->
        <div class="bg-surface rounded-xl shadow-md border border-white/10 p-4">
            <div class="flex items-center gap-4">
                <label class="text-sm font-semibold text-slate-700">Date Range:</label>
                <input type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <span class="text-slate-600">to</span>
                <input type="date"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <button
                    class="px-4 py-2 bg-primary text-slate-900 rounded-lg hover:bg-primary-hover transition-colors">Apply</button>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-surface rounded-xl shadow-md border border-white/10 p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Revenue Trend</h3>
            <div class="h-80 flex items-end justify-between gap-2">
                <div v-for="(value, index) in revenueData" :key="index"
                    class="flex-1 bg-gradient-to-t from-primary to-primary/50 rounded-t-lg relative group cursor-pointer hover:from-primary-hover hover:to-primary/60 transition-colors"
                    :style="{ height: value + '%' }">
                    <div
                        class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                        ৳{{ (value * 2000).toLocaleString() }}
                    </div>
                </div>
            </div>
            <div class="flex justify-between mt-4 text-xs text-slate-600">
                <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
                <span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-surface rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Top Categories</h3>
                <div class="space-y-4">
                    <div v-for="category in topCategories" :key="category.name">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-slate-700">{{ category.name }}</span>
                            <span class="text-sm font-bold text-slate-900">{{ category.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all"
                                :style="{ width: category.percentage + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-surface rounded-xl shadow-md border border-white/10 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Customer Insights</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm text-slate-600">Average Order Value</p>
                            <p class="text-2xl font-bold text-slate-900 mt-1">৳4,567</p>
                        </div>
                        <TrendingUp class="w-8 h-8 text-blue-600" />
                    </div>
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm text-slate-600">Repeat Customer Rate</p>
                            <p class="text-2xl font-bold text-slate-900 mt-1">67%</p>
                        </div>
                        <Users class="w-8 h-8 text-green-600" />
                    </div>
                    <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                        <div>
                            <p class="text-sm text-slate-600">Customer Lifetime Value</p>
                            <p class="text-2xl font-bold text-slate-900 mt-1">৳23,456</p>
                        </div>
                        <DollarSign class="w-8 h-8 text-purple-600" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { TrendingUp, Users, DollarSign } from 'lucide-vue-next';

const revenueData = ref([45, 52, 38, 65, 72, 58, 68, 75, 82, 78, 85, 90]);

const topCategories = ref([
    { name: 'Electronics', percentage: 85 },
    { name: 'Clothing', percentage: 65 },
    { name: 'Home & Garden', percentage: 45 },
    { name: 'Sports', percentage: 30 },
    { name: 'Books', percentage: 20 },
]);
</script>
