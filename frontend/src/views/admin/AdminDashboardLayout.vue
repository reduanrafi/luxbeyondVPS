<template>
    <div class="min-h-screen bg-[#0a0a0a] text-white">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#111111] border-r border-white/5 transition-transform duration-300"
            :class="{ '-translate-x-full': !isSidebarOpen, 'translate-x-0': isSidebarOpen, 'lg:translate-x-0': true }">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="h-20 flex items-center justify-between px-6 border-b border-white/5">
                    <router-link to="/" class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <span class="text-black font-bold text-sm">L</span>
                        </div>
                        <span class="text-xl font-bold text-white">Lux</span>
                    </router-link>
                    <button class="lg:hidden text-zinc-400 hover:text-white" @click="isSidebarOpen = false">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto custom-scrollbar">
                    <div v-for="(group, index) in navigationGroups" :key="index">
                        <p v-if="group.title"
                            class="px-3 mb-2 text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            {{ group.title }}
                        </p>
                        <div class="space-y-1">
                            <router-link v-for="item in group.items" :key="item.name" :to="item.href"
                                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-all relative group border border-transparent"
                                :class="isActive(item.href)
                                    ? 'text-primary bg-primary/10 border-primary/20'
                                    : 'text-zinc-400 hover:text-white hover:bg-white/5'"
                                @click="isSidebarOpen = false">
                                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                                <span class="flex-1">{{ item.name }}</span>
                                <span v-if="item.badge"
                                    class="h-5 px-2 min-w-[20px] flex items-center justify-center bg-primary text-black text-[10px] font-bold rounded-full">
                                    {{ item.badge }}
                                </span>
                            </router-link>
                        </div>
                    </div>
                </nav>

                <!-- Logout -->
                <div class="p-4 border-t border-white/5">
                    <router-link to="/"
                        class="text-zinc-400 hover:text-white flex items-center gap-3 px-3 py-2.5 text-sm font-medium hover:bg-white/5 rounded-lg mb-2">
                        <Earth class="w-5 h-5" />Goto Website
                    </router-link>
                    <button @click="handleLogout"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-zinc-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg w-full transition-colors">
                        <LogOut class="w-5 h-5" />
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div v-if="isSidebarOpen" class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="isSidebarOpen = false"></div>

        <!-- Main Content -->
        <div class="lg:ml-64">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-[#111111]/80 backdrop-blur-md border-b border-white/5">
                <div class="px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                    <!-- Left: Menu + Search -->
                    <div class="flex items-center gap-4 flex-1 max-w-2xl">
                        <button @click="isSidebarOpen = true"
                            class="lg:hidden p-2 -ml-2 text-zinc-400 hover:text-white">
                            <Menu class="w-6 h-6" />
                        </button>
                        <div class="relative w-full max-w-md hidden md:block">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" />
                            <input type="text" placeholder="Search data..."
                                class="w-full pl-10 pr-4 py-2.5 bg-white/5 border border-white/5 rounded-lg text-sm text-white focus:ring-1 focus:ring-primary focus:border-primary placeholder:text-zinc-600 transition-all">
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-4">
                        <div
                            class="hidden md:flex items-center gap-2 text-sm font-medium text-zinc-400 bg-white/5 border border-white/5 px-3 py-2 rounded-lg">
                            <Calendar class="w-4 h-4 text-zinc-500" />
                            <span>{{ currentDate }}</span>
                        </div>
                        <div
                            class="hidden md:flex items-center gap-2 text-sm font-medium text-zinc-400 bg-white/5 border border-white/5 px-3 py-2 rounded-lg w-[110px]">
                            <Timer class="w-4 h-4 text-zinc-500" />
                            <span>{{ currentTime }}</span>
                        </div>

                        <button class="relative p-2 text-zinc-400 hover:text-white transition-colors">
                            <MessageSquare class="w-5 h-5" />
                        </button>

                        <div class="relative">
                            <button @click="toggleNotifications"
                                class="relative p-2 text-zinc-400 hover:text-white transition-colors">
                                <Bell class="w-5 h-5" />
                                <span v-if="unreadCount > 0"
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold border-2 border-[#111111]">
                                    {{ unreadCount }}
                                </span>
                            </button>

                            <!-- Notification Dropdown -->
                            <div v-if="showNotifications"
                                class="absolute right-0 top-full mt-2 w-80 bg-[#111111] border border-white/10 rounded-xl shadow-xl overflow-hidden z-50 origin-top-right">
                                <div class="p-4 border-b border-white/5 flex justify-between items-center">
                                    <h3 class="font-semibold text-white">Notifications</h3>
                                    <button v-if="unreadCount > 0" @click="notificationStore.markAllAsRead"
                                        class="text-xs text-primary hover:underline">
                                        Mark all read
                                    </button>
                                </div>
                                <div class="max-h-[400px] overflow-y-auto">
                                    <div v-if="notifications.length === 0"
                                        class="p-8 text-center text-zinc-500 text-sm">
                                        No notifications
                                    </div>
                                    <div v-else>
                                        <div v-for="notification in notifications" :key="notification.id"
                                            class="p-4 border-b border-white/5 hover:bg-white/5 transition-colors cursor-pointer"
                                            :class="{ 'bg-white/[0.02]': !notification.read_at }"
                                            @click="handleNotificationClick(notification)">
                                            <p class="text-sm text-white mb-1">{{ notification.data.message }}</p>
                                            <p class="text-xs text-zinc-500">{{ new
                                                Date(notification.created_at).toLocaleString() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-t border-white/5 text-center">
                                    <router-link to="/admin/notifications"
                                        class="text-xs text-zinc-400 hover:text-white">
                                        View all notifications
                                    </router-link>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pl-4 border-l border-white/10">
                            <img :src="`https://ui-avatars.com/api/?name=${user?.name || 'Admin'}&background=D4AF37&color=000`"
                                alt="Profile" class="w-10 h-10 rounded-full ring-2 ring-primary/20">
                            <div class="hidden lg:block text-left">
                                <p class="text-sm font-bold text-white leading-tight">{{ user?.name || 'Admin' }}</p>
                                <p class="text-xs text-primary">Admin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6 lg:p-8">
                <router-view />
            </main>
        </div>

        <!-- Backdrop for notifications -->
        <div v-if="showNotifications" @click="showNotifications = false" class="fixed inset-0 z-20 cursor-default">
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

import { useAuthStore } from '../../stores/auth';
import { useNotificationStore } from '../../stores/notification';
import axios from '../../axios';
import { useRouter, useRoute } from 'vue-router';
import {
    LayoutDashboard,
    ShoppingBag,
    Package,
    Users,
    BarChart3,
    Settings,
    LogOut,
    Menu,
    Search,
    Bell,
    MessageSquare,
    Calendar,
    FileText,
    Tag,
    HelpCircle,
    ChevronLeft,
    Component,
    Earth,
    Ticket,
    Crown,
    DollarSign,
    Timer,
    Shield
} from 'lucide-vue-next';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const router = useRouter();
const route = useRoute();

const isSidebarOpen = ref(false);
const showNotifications = ref(false);
const pendingCounts = ref({ orders: 0, requests: 0 });

const navigationGroups = computed(() => [
    {
        items: [
            { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
            { name: 'Customer', href: '/admin/customers', icon: Users },
            { name: 'All Order', href: '/admin/orders', icon: Package, badge: pendingCounts.value.orders > 0 ? pendingCounts.value.orders : null },
            { name: 'Order Statuses', href: '/admin/order-statuses', icon: Tag },
        ]
    },
    {
        title: 'Event & Offers',
        items: [
            { name: 'Requests', href: '/admin/requests', icon: ShoppingBag, badge: pendingCounts.value.requests > 0 ? pendingCounts.value.requests : null },
            { name: 'Events', href: '/admin/events', icon: Calendar },
        ]
    },
    {
        title: 'Hub Management',
        items: [
            { name: 'Brands', href: '/admin/brands', icon: Crown },
            { name: 'Categories', href: '/admin/categories', icon: Tag },
            { name: 'Products', href: '/admin/products', icon: Package },
            { name: 'Coupons', href: '/admin/coupons', icon: Ticket },
            { name: 'Charges', href: '/admin/charges', icon: DollarSign },
        ]
    },
    {
        title: 'Analytics & Reports',
        items: [
            { name: 'Analytics', href: '/admin/analytics', icon: BarChart3 },
            { name: 'Reports', href: '/admin/reports', icon: FileText },
        ]
    },
    {
        title: 'Users Management',
        items: [
            { name: 'All Users', href: '/admin/users', icon: Users },
            { name: 'Roles & Permissions', href: '/admin/roles', icon: Shield },
            { name: 'Blogs', href: '/admin/blogs', icon: FileText },
            { name: 'Travellers', href: '/admin/travellers', icon: Users },
        ]
    },
    {
        title: 'Account',
        items: [
            { name: 'Settings', href: '/admin/settings', icon: Settings },
            { name: 'Help & Support', href: '/admin/support', icon: HelpCircle },
        ]
    }
]);

const user = computed(() => authStore.user);
const unreadCount = computed(() => notificationStore.unreadCount);
const notifications = computed(() => notificationStore.notifications);

onMounted(() => {
    if (authStore.isAuthenticated) {
        notificationStore.fetchNotifications();
    }
});

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
        notificationStore.fetchNotifications();
    }
};

const handleNotificationClick = (notification) => {
    notificationStore.markAsRead(notification.id);
    showNotifications.value = false;
    // Navigate based on notification type if needed
};

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: '2-digit'
    });
});

const currentTime = ref("");

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString();
};

onMounted(() => {
    updateTime();
    const interval = setInterval(updateTime, 1000);

    onUnmounted(() => clearInterval(interval));
});

const isActive = (path) => {
    if (path === '/admin') {
        return route.path === '/admin';
    }
    return route.path.startsWith(path);
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>
