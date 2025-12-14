<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-white">Notifications</h2>
            <button @click="markAllAsRead"
                class="text-xs font-bold uppercase tracking-widest text-primary hover:text-white transition-colors">Mark
                all as read</button>
        </div>

        <div v-if="loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
        <div v-else-if="notifications.length === 0" class="text-center py-8 text-slate-500">
            No notifications
        </div>
        <div v-else class="space-y-4">
            <div v-for="notification in notifications" :key="notification.id" 
                class="bg-surface p-4 border border-white/5 flex gap-4 transition-all hover:bg-white/5 group"
                :class="{ 'border-l-2 border-l-primary': !notification.read_at, 'border-l-transparent': notification.read_at }">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-slate-900">
                        <component :is="getIcon(notification.data?.type || notification.type)" class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <h3 class="font-bold text-sm" :class="!notification.read_at ? 'text-white' : 'text-slate-400'">
                            {{ notification.data?.title }}</h3>
                        <span class="text-[10px] text-slate-500 uppercase tracking-wide">{{
                            formatDate(notification.created_at) }}</span>
                    </div>
                    <p class="mt-1 text-sm leading-relaxed"
                        :class="!notification.read_at ? 'text-slate-300' : 'text-slate-500'">{{
                            notification.data?.message.length > 100 ? notification.data?.message.slice(0, 100) + '...' :
                                notification.data?.message }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ShoppingBag, Package, Tag, Bell } from 'lucide-vue-next';
import axios from '../../axios';
import { formatDistanceToNow } from 'date-fns';

const notifications = ref([]);
const loading = ref(true);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-read');
        // Update local state
        notifications.value.forEach(n => n.read_at = new Date());
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
};

const formatDate = (date) => {
    if (!date) return '';
    try {
        return formatDistanceToNow(new Date(date), { addSuffix: true });
    } catch (e) {
        return date;
    }
};

const getIcon = (type) => {
    // Map database notification types to icons
    if (type?.includes('order')) return ShoppingBag;
    if (type?.includes('product_request')) return Package;
    if (type?.includes('promo')) return Tag;
    return Bell;
};

onMounted(() => {
    fetchNotifications();
});
</script>
