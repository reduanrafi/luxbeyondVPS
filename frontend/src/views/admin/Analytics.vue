<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-2xl font-bold text-white">Analytics & Reports</h2>
            <p class="text-sm text-zinc-400 mt-1">View detailed business insights</p>
        </div>

        <!-- Date Range -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="flex items-center gap-4">
                <label class="text-sm font-bold text-zinc-300">Date Range:</label>
                <input type="date"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 dark-calendar">
                <span class="text-zinc-500">to</span>
                <input type="date"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 dark-calendar">
                <button
                    class="px-6 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-all shadow-lg shadow-primary-500/20">
                    Apply
                </button>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-6">
            <h3 class="text-lg font-bold text-white mb-6">Revenue Trend</h3>
            <div class="h-80 flex items-end justify-between gap-2 border-b border-white/5 pb-2">
                <div v-for="(value, index) in revenueData" :key="index"
                    class="flex-1 bg-gradient-to-t from-primary/50 to-primary rounded-t-lg relative group cursor-pointer hover:from-primary/60 hover:to-primary transition-all shadow-[0_0_15px_rgba(245,158,11,0.2)]"
                    :style="{ height: value + '%' }">
                    <div
                        class="absolute -top-10 left-1/2 -translate-x-1/2 bg-white text-black font-bold text-xs px-2 py-1.5 rounded shadow-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 before:content-[''] before:absolute before:top-full before:left-1/2 before:-translate-x-1/2 before:border-4 before:border-transparent before:border-t-white">
                        ৳{{ (value * 2000).toLocaleString() }}
                    </div>
                </div>
            </div>
            <div class="flex justify-between mt-4 text-xs font-medium text-zinc-500">
                <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
                <span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-6">
                <h3 class="text-lg font-bold text-white mb-6">Top Categories</h3>
                <div class="space-y-6">
                    <div v-for="category in topCategories" :key="category.name">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-zinc-300">{{ category.name }}</span>
                            <span class="text-sm font-bold text-primary-500">{{ category.percentage }}%</span>
                        </div>
                        <div class="w-full bg-white/5 rounded-full h-2 overflow-hidden border border-white/5">
                            <div class="bg-primary h-2 rounded-full transition-all shadow-[0_0_10px_rgba(245,158,11,0.5)]"
                                :style="{ width: category.percentage + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-6">
                <h3 class="text-lg font-bold text-white mb-6">Customer Insights</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-5 bg-white/5 rounded-xl border border-white/10 hover:border-blue-500/30 transition-colors group">
                        <div>
                            <p class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">Average Order Value</p>
                            <p class="text-2xl font-bold text-white mt-1">৳4,567</p>
                        </div>
                        <div class="p-3 bg-blue-500/10 rounded-lg group-hover:bg-blue-500/20 transition-colors">
                            <TrendingUp class="w-6 h-6 text-blue-400" />
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-5 bg-white/5 rounded-xl border border-white/10 hover:border-emerald-500/30 transition-colors group">
                        <div>
                            <p class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">Repeat Customer Rate</p>
                            <p class="text-2xl font-bold text-white mt-1">67%</p>
                        </div>
                        <div class="p-3 bg-emerald-500/10 rounded-lg group-hover:bg-emerald-500/20 transition-colors">
                            <Users class="w-6 h-6 text-emerald-400" />
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-5 bg-white/5 rounded-xl border border-white/10 hover:border-purple-500/30 transition-colors group">
                        <div>
                            <p class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">Customer Lifetime Value</p>
                            <p class="text-2xl font-bold text-white mt-1">৳23,456</p>
                        </div>
                        <div class="p-3 bg-purple-500/10 rounded-lg group-hover:bg-purple-500/20 transition-colors">
                            <DollarSign class="w-6 h-6 text-purple-400" />
                        </div>
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

<style>
.dark-calendar {
    color-scheme: dark;
}
</style>
