<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">Notifications</h2>
            <button class="text-xs font-bold uppercase tracking-widest text-primary hover:text-white transition-colors">Mark all as read</button>
        </div>

        <div class="space-y-4">
            <div v-for="notification in notifications" :key="notification.id" 
                class="bg-surface p-4 border border-white/5 flex gap-4 transition-all hover:bg-white/5 group"
                :class="{ 'border-l-2 border-l-primary': !notification.read, 'border-l-transparent': notification.read }">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-slate-900">
                        <component :is="getIcon(notification.type)" class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <h3 class="font-bold text-sm" :class="!notification.read ? 'text-white' : 'text-slate-400'">{{ notification.title }}</h3>
                        <span class="text-[10px] text-slate-500 uppercase tracking-wide">{{ notification.time }}</span>
                    </div>
                    <p class="mt-1 text-sm leading-relaxed" :class="!notification.read ? 'text-slate-300' : 'text-slate-500'">{{ notification.message }}</p>
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
