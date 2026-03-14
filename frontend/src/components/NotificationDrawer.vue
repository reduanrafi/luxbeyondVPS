<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[60] flex justify-end pointer-events-none">
            <!-- Backdrop -->
            <Transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isOpen" class="absolute inset-0 bg-black/80 backdrop-blur-sm pointer-events-auto"
                    @click="$emit('close')">
                </div>
            </Transition>

            <!-- Panel -->
            <Transition enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                enter-from-class="translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                <div v-if="isOpen"
                    class="w-screen max-w-md bg-surface shadow-2xl flex flex-col pointer-events-auto h-full relative z-10 border-l border-white/5">
                    <!-- Header -->
                    <div class="p-6 border-b border-white/5 bg-background">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-serif text-white tracking-wide flex items-center gap-2"
                                    id="notification-title">
                                    <Bell class="w-6 h-6 text-primary" />
                                    Notifications
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">{{ unreadCount }} unread</p>
                            </div>
                            <button @click="$emit('close')"
                                class="p-2 text-slate-400 hover:text-white transition-colors">
                                <X class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-for="notification in notifications" :key="notification.id"
                            @click="handleNotificationClick(notification)"
                            class="p-4 bg-background border transition-all hover:border-primary/30 cursor-pointer relative"
                            :class="notification.read ? 'border-white/5' : 'border-primary/20'">
                            <!-- Unread Indicator -->
                            <div v-if="!notification.read" class="absolute left-0 top-0 bottom-0 w-1 bg-primary"></div>

                            <div class="flex gap-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center border"
                                        :class="getIconBg(notification.type)">
                                        <component :is="getIcon(notification.type)" class="w-5 h-5"
                                            :class="getIconColor(notification.type)" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-serif text-sm tracking-wide"
                                        :class="notification.read ? 'text-slate-300' : 'text-white'">
                                        {{ notification.data.title || notification.type }}
                                    </p>
                                    <p class="text-slate-400 text-xs mt-1 leading-relaxed text-wrap">{{
                                        notification.data.message
                                    }}
                                    </p>
                                    <p class="text-slate-600 text-[10px] mt-2 uppercase tracking-wider">{{
                                        formatTime(notification.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="notifications.length === 0" class="text-center py-12">
                            <Bell class="w-16 h-16 text-slate-700 mx-auto mb-4" />
                            <p class="text-slate-400 font-light text-lg">No notifications yet</p>
                            <p class="text-slate-600 text-sm mt-1">We'll notify you when something happens</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t border-white/10 bg-background">
                        <button @click="markAllAsRead"
                            class="w-full py-3 bg-primary text-white font-bold uppercase tracking-widest text-sm rounded-none hover:bg-primary-hover transition-colors shadow-[0_0_20px_rgba(200,174,125,0.2)] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)]">
                            Mark All as Read
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { Bell, X, ShoppingBag, Package, Tag, CheckCircle } from 'lucide-vue-next';
import { useNotificationStore } from '../stores/notification';

defineProps({
    isOpen: Boolean
});

defineEmits(['close']);

const notificationStore = useNotificationStore();

const notifications = computed(() => notificationStore.allNotifications);
const unreadCount = computed(() => notificationStore.unreadCount);

const getIcon = (type) => {
    // Basic mapping, can be improved based on backend 'type' or 'data.type' field
    if (type?.includes('Order')) return ShoppingBag;
    if (type?.includes('Request')) return Package;
    if (type?.includes('Promo')) return Tag;
    return Bell;
};

const getIconBg = (type) => {
    if (type?.includes('Order')) return 'bg-primary/10 border-primary/20';
    if (type?.includes('Request')) return 'bg-purple-500/10 border-purple-500/20';
    if (type?.includes('Promo')) return 'bg-orange-500/10 border-orange-500/20';
    return 'bg-green-500/10 border-green-500/20';
};

const getIconColor = (type) => {
    if (type?.includes('Order')) return 'text-primary';
    if (type?.includes('Request')) return 'text-purple-400';
    if (type?.includes('Promo')) return 'text-orange-400';
    return 'text-green-400';
};

const formatTime = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
    if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    return 'Just now';
};

const markAllAsRead = async () => {
    await notificationStore.markAllAsRead();
};

const router = useRouter();
const handleNotificationClick = async (notification) => {
    await notificationStore.handleNotificationClick(notification, router);
    emit('close');
};
</script>
