<template>
    <div class="min-h-screen bg-[#F7F8FA]">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-100 transition-transform duration-300"
            :class="{ '-translate-x-full': !isSidebarOpen, 'translate-x-0': isSidebarOpen, 'lg:translate-x-0': true }">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="h-20 flex items-center justify-between px-6">
                    <router-link to="/" class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">L</span>
                        </div>
                        <span class="text-xl font-bold text-slate-800">Lux</span>
                    </router-link>
                    <button class="lg:hidden text-gray-400 hover:text-gray-600" @click="isSidebarOpen = false">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto">
                    <div v-for="(group, index) in navigationGroups" :key="index">
                        <p v-if="group.title"
                            class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            {{ group.title }}
                        </p>
                        <div class="space-y-1">
                            <router-link v-for="item in group.items" :key="item.name" :to="item.href"
                                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors relative group"
                                :class="isActive(item.href)
                                    ? 'text-blue-600 bg-blue-50'
                                    : 'text-gray-600 hover:text-slate-800 hover:bg-gray-50'"
                                @click="isSidebarOpen = false">
                                <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                                <span class="flex-1">{{ item.name }}</span>
                                <span v-if="item.badge"
                                    class="w-5 h-5 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full">
                                    {{ item.badge }}
                                </span>
                            </router-link>
                        </div>
                    </div>
                </nav>

                <!-- Logout -->
                <div class="p-4 border-t border-gray-100">
                    <router-link to="/"
                        class="text-black flex items-center gap-3 px-3 py-2.5 text-sm font-medium hover:underline">
                        <Earth class="w-5 h-5" />Goto Website
                    </router-link>
                    <button @click="handleLogout"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg w-full transition-colors">
                        <LogOut class="w-5 h-5" />
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div v-if="isSidebarOpen" class="fixed inset-0 bg-black/20 z-40 lg:hidden" @click="isSidebarOpen = false"></div>

        <!-- Main Content -->
        <div class="lg:ml-64">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-white border-b border-gray-100">
                <div class="px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                    <!-- Left: Menu + Search -->
                    <div class="flex items-center gap-4 flex-1 max-w-2xl">
                        <button @click="isSidebarOpen = true"
                            class="lg:hidden p-2 -ml-2 text-gray-500 hover:text-gray-700">
                            <Menu class="w-6 h-6" />
                        </button>
                        <div class="relative w-full max-w-md hidden md:block">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input type="text" placeholder="Search data..."
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-blue-100 focus:bg-white placeholder:text-gray-400">
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-4">
                        <div
                            class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-600 bg-gray-50 px-3 py-2 rounded-lg">
                            <Calendar class="w-4 h-4 text-slate-400" />
                            <span>{{ currentDate }}</span>
                        </div>
                        <div
                            class="hidden md:flex items-center gap-2 text-sm font-medium text-slate-600 bg-gray-50 px-3 py-2 rounded-lg w-[110px]">
                            <Timer class="w-4 h-4 text-slate-400" />
                            <span>{{ currentTime }}</span>
                        </div>

                        <button class="relative p-2 text-gray-400 hover:text-slate-600 transition-colors">
                            <MessageSquare class="w-5 h-5" />
                        </button>

                        <button class="relative p-2 text-gray-400 hover:text-slate-600 transition-colors">
                            <Bell class="w-5 h-5" />
                            <span
                                class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                        </button>

                        <div class="flex items-center gap-3 pl-4 border-l border-gray-100">
                            <img :src="`https://ui-avatars.com/api/?name=${user?.name}&background=3B82F6&color=fff`"
                                alt="Profile" class="w-10 h-10 rounded-full">
                            <div class="hidden lg:block text-left">
                                <p class="text-sm font-bold text-slate-800 leading-tight">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500">Admin</p>
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
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
    Timer
} from 'lucide-vue-next';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isSidebarOpen = ref(false);

const navigationGroups = [
    {
        items: [
            { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
            { name: 'Customer', href: '/admin/customers', icon: Users },
            { name: 'All Order', href: '/admin/orders', icon: Package, badge: '2' },
            { name: 'Order Statuses', href: '/admin/order-statuses', icon: Tag },
        ]
    },
    {
        title: 'Event & Offers',
        items: [
            { name: 'Requests', href: '/admin/requests', icon: ShoppingBag },
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
        title: 'Account',
        items: [
            { name: 'Settings', href: '/admin/settings', icon: Settings },
            { name: 'Help & Support', href: '/admin/support', icon: HelpCircle },
        ]
    }
];

const user = computed(() => authStore.user);

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
