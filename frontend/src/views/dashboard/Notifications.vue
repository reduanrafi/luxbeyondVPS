<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-900">Notifications</h2>
            <button class="text-sm font-medium text-primary hover:text-primary-hover">Mark all as read</button>
        </div>

        <div class="space-y-4">
            <div v-for="notification in notifications" :key="notification.id" 
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-4 transition-all hover:shadow-md"
                :class="{ 'bg-blue-50/50 border-blue-100': !notification.read }">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                        :class="notification.type === 'order' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'">
                        <component :is="getIcon(notification.type)" class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <h3 class="font-bold text-slate-900" :class="{ 'text-primary': !notification.read }">{{ notification.title }}</h3>
                        <span class="text-xs text-slate-400">{{ notification.time }}</span>
                    </div>
                    <p class="text-slate-600 mt-1 text-sm">{{ notification.message }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ShoppingBag, Package, Tag, Bell } from 'lucide-vue-next';

const notifications = [
    { id: 1, title: 'Order Shipped', message: 'Your order #ORD-7828 has been shipped and is on its way!', time: '2 hours ago', type: 'order', read: false },
    { id: 2, title: 'Request Approved', message: 'Great news! Your request for "Sony Headphones" has been approved by our admin.', time: '5 hours ago', type: 'request', read: false },
    { id: 3, title: 'Flash Sale Alert', message: 'Don\'t miss out on our 24-hour flash sale starting tomorrow!', time: '1 day ago', type: 'promo', read: true },
    { id: 4, title: 'Order Delivered', message: 'Order #ORD-7827 has been delivered. Enjoy your purchase!', time: '3 weeks ago', type: 'order', read: true },
];

const getIcon = (type) => {
    switch (type) {
        case 'order': return ShoppingBag;
        case 'request': return Package;
        case 'promo': return Tag;
        default: return Bell;
    }
};
</script>
