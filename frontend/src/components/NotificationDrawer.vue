<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[60] flex justify-end pointer-events-none">
            <!-- Backdrop -->
            <Transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isOpen" class="absolute inset-0 bg-gray-500/45 pointer-events-auto"
                    @click="$emit('close')">
                </div>
            </Transition>

            <!-- Panel -->
            <Transition enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                enter-from-class="translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                <div v-if="isOpen"
                    class="w-screen max-w-md bg-white shadow-2xl flex flex-col pointer-events-auto h-full relative z-10">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-purple-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2"
                                    id="notification-title">
                                    <Bell class="w-6 h-6 text-primary" />
                                    Notifications
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">{{ unreadCount }} unread</p>
                            </div>
                            <button @click="$emit('close')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <X class="w-6 h-6 text-gray-600" />
                            </button>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-for="notification in notifications" :key="notification.id"
                            class="p-4 rounded-xl border transition-all hover:shadow-md cursor-pointer"
                            :class="notification.read ? 'bg-white border-gray-200' : 'bg-blue-50 border-blue-200'">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                        :class="getIconBg(notification.type)">
                                        <component :is="getIcon(notification.type)" class="w-5 h-5"
                                            :class="getIconColor(notification.type)" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-900 text-sm"
                                        :class="{ 'text-primary': !notification.read }">
                                        {{ notification.title }}
                                    </p>
                                    <p class="text-slate-600 text-sm mt-1">{{ notification.message }}</p>
                                    <p class="text-slate-400 text-xs mt-2">{{ notification.time }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="notifications.length === 0" class="text-center py-12">
                            <Bell class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                            <p class="text-slate-500 font-medium">No notifications yet</p>
                            <p class="text-slate-400 text-sm mt-1">We'll notify you when something happens</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        <button @click="markAllAsRead"
                            class="w-full py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-hover transition-colors">
                            Mark All as Read
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Bell, X, ShoppingBag, Package, Tag, CheckCircle } from 'lucide-vue-next';

defineProps({
    isOpen: Boolean
});

defineEmits(['close']);

// Mock notifications data (should come from a notifications store/API)
const notifications = ref([
    {
        id: 1,
        title: 'Order Shipped',
        message: 'Your order #ORD-7828 has been shipped and is on its way!',
        time: '2 hours ago',
        type: 'order',
        read: false
    },
    {
        id: 2,
        title: 'Request Approved',
        message: 'Your request for "Sony Headphones" has been approved.',
        time: '5 hours ago',
        type: 'request',
        read: false
    },
    {
        id: 3,
        title: 'Flash Sale Alert',
        message: 'Don\'t miss out on our 24-hour flash sale starting tomorrow!',
        time: '1 day ago',
        type: 'promo',
        read: false
    },
    {
        id: 4,
        title: 'Order Delivered',
        message: 'Order #ORD-7827 has been delivered. Enjoy your purchase!',
        time: '3 days ago',
        type: 'order',
        read: true
    }
]);

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

const getIcon = (type) => {
    switch (type) {
        case 'order': return ShoppingBag;
        case 'request': return Package;
        case 'promo': return Tag;
        default: return CheckCircle;
    }
};

const getIconBg = (type) => {
    switch (type) {
        case 'order': return 'bg-blue-100';
        case 'request': return 'bg-purple-100';
        case 'promo': return 'bg-orange-100';
        default: return 'bg-green-100';
    }
};

const getIconColor = (type) => {
    switch (type) {
        case 'order': return 'text-blue-600';
        case 'request': return 'text-purple-600';
        case 'promo': return 'text-orange-600';
        default: return 'text-green-600';
    }
};

const markAllAsRead = () => {
    notifications.value.forEach(n => n.read = true);
};
</script>
