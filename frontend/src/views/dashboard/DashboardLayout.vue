<template>
    <div class="min-h-screen bg-background">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 bg-surface border-r border-white/5 shadow-xl transform transition-transform duration-300"
            :class="{ '-translate-x-full': !isSidebarOpen, 'translate-x-0': isSidebarOpen, 'lg:translate-x-0': true }">
            <div class="h-full flex flex-col relative overflow-hidden">

                <!-- Logo -->
                <div class="relative h-20 flex items-center px-6 border-b border-white/10">
                    <router-link to="/"
                        class="text-3xl font-extrabold text-white tracking-tighter flex items-center gap-2">
                        <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-white text-xl">L</span>
                        </div>
                        <span>LUX<span class="text-accent">.</span></span>
                    </router-link>
                </div>

                <!-- Navigation -->
                <nav class="relative flex-1 overflow-y-auto py-8 px-4 space-y-2">
                    <router-link v-for="item in navigation.filter(i => !i.action)" :key="item.name" :to="item.href"
                        class="flex items-center gap-4 px-5 py-3.5 text-sm font-semibold transition-all duration-200 group rounded-lg"
                        :class="[
                            isActive(item.href)
        ? 'bg-primary text-slate-900 font-bold shadow-md'
        : 'text-slate-400 hover:bg-white/5 hover:text-white'
                        ]" @click="isSidebarOpen = false">
                        <component :is="item.icon" class="w-5 h-5 transition-transform group-hover:scale-110"
                            :class="isActive(item.href) ? 'text-slate-900' : 'text-current'" />
                        <span>{{ item.name }}</span>
                        <span v-if="item.name === 'Notifications' && unreadCount > 0"
                            class="ml-auto bg-orange-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            {{ unreadCount }}
                        </span>
                    </router-link>
                    <button v-for="item in navigation.filter(i => i.action)" :key="item.name"
                        @click="handleAction(item.action); isSidebarOpen = false"
                        class="w-full flex items-center gap-4 px-5 py-3.5 text-sm font-semibold transition-all duration-200 group text-slate-400 hover:bg-white/5 hover:text-white rounded-lg">
                        <component :is="item.icon"
                            class="w-5 h-5 transition-transform group-hover:scale-110 text-current" />
                        <span>{{ item.name }}</span>
                        <span v-if="item.action === 'cart' && cartStore.totalItems > 0"
                            class="ml-auto bg-primary text-slate-900 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            {{ cartStore.totalItems }}
                        </span>
                        <span v-if="item.action === 'wishlist' && wishlistStore.items.length > 0"
                            class="ml-auto bg-primary text-slate-900 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                            {{ wishlistStore.items.length }}
                        </span>
                    </button>
                </nav>

                <div>
                    <router-link to="/shop"
                        class="flex items-center gap-2 px-5 py-3.5 text-[12px] hover:underline font-semibold rounded-xl transition-all duration-200 group text-white/80 hover:bg-white/10 hover:text-white hover:translate-x-1 cursor-pointer z-100"
                        @click="isSidebarOpen = false">
                        <Earth class="w-4 h-4" /> Goto Website
                    </router-link>
                </div>
                <!-- User Profile (Bottom) -->
                <div class="relative p-4 border-t border-white/10 backdrop-blur-sm">
                    <div
                        class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20">
                        <div
                            class="w-12 h-12 rounded-full bg-accent flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            {{ userInitials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ user?.name }}</p>
                            <p class="text-xs text-white/70 truncate">{{ user?.email }}</p>
                        </div>
                    </div>
                    <button @click="handleLogout"
                        class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-red-500/20 backdrop-blur-sm border border-red-400/30 rounded-xl hover:bg-red-500/30 transition-all hover:scale-105 shadow-lg">
                        <LogOut class="w-4 h-4" />
                        Sign Out
                    </button>
                </div>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div v-if="isSidebarOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
            @click="isSidebarOpen = false"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden lg:ml-72">
            <!-- Desktop Header -->
            <header
                class="hidden lg:block bg-surface/80 backdrop-blur-md border-b border-white/10  shadow-sm fixed top-0 right-0 left-0 lg:left-72 z-30">
                <div class="flex items-center justify-between px-8 h-20">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ currentPageTitle }}</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Welcome back, {{ user?.name?.split(' ')[0] }}!</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <router-link to="/shop"
                            class="p-2 flex gap-2 text-slate-400  border border-primary/10 items-center rounded hover:bg-primary/10 hover:text-white transition-colors">
                            <Store class="w-5 h-5" />
                            <span class="">Shop</span>
                        </router-link>
                        <router-link to="/request-product"
                            class="flex gap-2 ites-center p-2 text-slate-400 hover:text-primary transition-colors border border-primary/10 items-center rounded">
                            <PlaneTakeoff class="w-5 h-5" />
                            <span class="">Request</span>
                        </router-link>
                        <router-link to="/dashboard/notifications"
                            class="relative p-2 text-slate-400 hover:text-primary transition-colors group">
                            <Bell class="w-6 h-6" />
                            <span v-if="unreadCount > 0"
                                class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                                {{ unreadCount }}
                            </span>
                        </router-link>
                    </div>
                </div>
            </header>

            <!-- Mobile Header -->
            <header
                class="bg-surface/90 backdrop-blur-md border-b border-white/10 shadow-sm lg:hidden fixed top-0 left-0 right-0 z-30">
                <div class="flex items-center justify-between px-4 h-16">
                    <button @click="isSidebarOpen = true"
                        class="p-2 -ml-2 text-gray-600 hover:text-primary transition-colors">
                        <Menu class="w-6 h-6" />
                    </button>
                    <span class="font-bold text-lg text-primary">{{ currentPageTitle }}</span>
                    <button class="relative p-2 text-slate-400 hover:text-primary transition-colors">
                        <Bell class="w-5 h-5" />
                        <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">{{ unreadCount }}</span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pt-20 lg:pt-28">
                <router-view></router-view>
            </main>
        </div>

        <!-- Cart Drawer -->
        <CartDrawer :isOpen="isCartOpen" @close="isCartOpen = false" />

        <!-- Wishlist Drawer -->
        <WishlistDrawer :isOpen="isWishlistOpen" @close="isWishlistOpen = false" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useCartStore } from '../../stores/cart';
import { useWishlistStore } from '../../stores/wishlist';
import { useNotificationStore } from '../../stores/notification';
import { useRouter, useRoute } from 'vue-router';
import {
    LayoutDashboard,
    ShoppingBag,
    Package,
    Bell,
    Ticket,
    Settings,
    LogOut,
    Menu,
    Earth,
    ShoppingCart,
    Heart,
    Store,
    PlaneTakeoff
} from 'lucide-vue-next';
import CartDrawer from '../../components/CartDrawer.vue';
import WishlistDrawer from '../../components/WishlistDrawer.vue';

const authStore = useAuthStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();
const notificationStore = useNotificationStore();
const router = useRouter();
const route = useRoute();

const isSidebarOpen = ref(false);
const isCartOpen = ref(false);
const isWishlistOpen = ref(false);
const user = computed(() => authStore.user);
const unreadCount = computed(() => notificationStore.unreadCount);

onMounted(() => {
    if (authStore.isAuthenticated) {
        notificationStore.fetchNotifications();
    }
});

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const navigation = [
    { name: 'Overview', href: '/dashboard', icon: LayoutDashboard },
    { name: 'My Requests', href: '/dashboard/requests', icon: PlaneTakeoff },
    { name: 'My Orders', href: '/dashboard/orders', icon: ShoppingBag },
    { name: 'Cart', href: '#', icon: ShoppingCart, action: 'cart' },
    { name: 'Wishlist', href: '#', icon: Heart, action: 'wishlist' },
    { name: 'Notifications', href: '/dashboard/notifications', icon: Bell },
    { name: 'Coupons', href: '/dashboard/coupons', icon: Ticket },
    { name: 'Settings', href: '/dashboard/settings', icon: Settings },
];

const currentPageTitle = computed(() => {
    const item = navigation.find(nav => isActive(nav.href));
    return item ? item.name : 'Dashboard';
});

const isActive = (path) => {
    if (path === '/dashboard' && route.path === '/dashboard') return true;
    if (path !== '/dashboard' && route.path.startsWith(path)) return true;
    return false;
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};

const handleAction = (action) => {
    if (action === 'cart') {
        isCartOpen.value = true;
    } else if (action === 'wishlist') {
        isWishlistOpen.value = true;
    }
};
</script>
