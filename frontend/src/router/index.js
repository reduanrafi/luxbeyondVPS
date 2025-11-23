import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';

const routes = [
    { path: '/', component: () => import('../views/LandingPage.vue') },
    { path: '/login', component: Login },
    { path: '/register', component: Register },
    { path: '/forgot-password', component: () => import('../views/ForgotPassword.vue') },

    // Customer Dashboard
    {
        path: '/dashboard',
        component: () => import('../views/dashboard/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('../views/dashboard/Overview.vue'),
            },
            {
                path: 'requests',
                component: () => import('../views/dashboard/MyRequests.vue'),
            },
            {
                path: 'orders',
                component: () => import('../views/dashboard/MyOrders.vue'),
            },
            {
                path: 'notifications',
                component: () => import('../views/dashboard/Notifications.vue'),
            },
            {
                path: 'coupons',
                component: () => import('../views/dashboard/Coupons.vue'),
            },
            {
                path: 'settings',
                component: () => import('../views/dashboard/Settings.vue'),
            },
        ]
    },

    // Admin Dashboard
    {
        path: '/admin',
        component: () => import('../views/admin/AdminDashboardLayout.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            {
                path: '',
                component: () => import('../views/admin/AdminOverview.vue'),
            },
            {
                path: 'products',
                component: () => import('../views/admin/ProductsManagement.vue'),
            },
            {
                path: 'products/create',
                component: () => import('../views/admin/ProductForm.vue'),
            },
            {
                path: 'products/:id/edit',
                component: () => import('../views/admin/ProductForm.vue'),
            },
            {
                path: 'categories',
                component: () => import('../views/admin/CategoriesManagement.vue'),
            },
            {
                path: 'coupons',
                component: () => import('../views/admin/CouponsManagement.vue'),
            },
            {
                path: 'orders',
                component: () => import('../views/admin/OrdersManagement.vue'),
            },
            {
                path: 'customers',
                component: () => import('../views/admin/CustomersManagement.vue'),
            },
            {
                path: 'requests',
                component: () => import('../views/AdminRequestDashboard.vue'),
            },
            {
                path: 'analytics',
                component: () => import('../views/admin/Analytics.vue'),
            },
            {
                path: 'settings',
                component: () => import('../views/admin/AdminSettings.vue'),
            },
        ]
    },

    {
        path: '/request-product',
        component: () => import('../views/RequestProduct.vue'),
        meta: { requiresAuth: true }
    },

    // Public Pages
    { path: '/shop', component: () => import('../views/Shop.vue') },
    { path: '/product/:id', component: () => import('../views/ProductDetails.vue') },
    { path: '/blogs', component: () => import('../views/BlogList.vue') },
    { path: '/blog/:id', component: () => import('../views/BlogDetails.vue') },
    { path: '/travellers', component: () => import('../views/TravellerLanding.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    if (!authStore.user && authStore.token) {
        await authStore.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
    } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
        // Redirect non-admin users trying to access admin routes
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
