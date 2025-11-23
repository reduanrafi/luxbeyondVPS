<template>
    <div>
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Orders -->
            <div class="relative bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-2xl shadow-xl overflow-hidden group hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">Total Orders</p>
                        <p class="text-4xl font-extrabold text-white">12</p>
                        <p class="text-blue-200 text-xs mt-2">+2 this month</p>
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
                        <p class="text-4xl font-extrabold text-white">3</p>
                        <p class="text-purple-200 text-xs mt-2">Pending approval</p>
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
                        <p class="text-4xl font-extrabold text-white">৳45,200</p>
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
                <button class="text-sm font-medium text-primary hover:text-primary-hover transition-colors">View All</button>
            </div>
            <div class="space-y-4">
                <div v-for="activity in activities" :key="activity.id" 
                    class="flex items-start gap-4 p-4 rounded-xl hover:bg-gray-50 transition-colors group">
                    <div class="mt-0.5 p-3 rounded-xl transition-all group-hover:scale-110"
                        :class="getActivityBgClass(activity.icon)">
                        <component :is="activity.icon" class="w-5 h-5" 
                            :class="getActivityIconClass(activity.icon)" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-900 group-hover:text-primary transition-colors">{{ activity.title }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ activity.time }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ShoppingBag, Package, DollarSign, CheckCircle, Truck, Tag } from 'lucide-vue-next';

const activities = [
    { id: 1, title: 'Order #1234 delivered successfully', time: '2 hours ago', icon: CheckCircle },
    { id: 2, title: 'Your request for "Sony Headphones" was approved', time: '5 hours ago', icon: CheckCircle },
    { id: 3, title: 'Order #1235 shipped', time: '1 day ago', icon: Truck },
    { id: 4, title: 'New coupon available: 20% OFF', time: '2 days ago', icon: Tag },
];

const getActivityBgClass = (icon) => {
    if (icon === CheckCircle) return 'bg-green-100';
    if (icon === Truck) return 'bg-blue-100';
    if (icon === Tag) return 'bg-orange-100';
    return 'bg-gray-100';
};

const getActivityIconClass = (icon) => {
    if (icon === CheckCircle) return 'text-green-600';
    if (icon === Truck) return 'text-blue-600';
    if (icon === Tag) return 'text-orange-600';
    return 'text-gray-600';
};
</script>
