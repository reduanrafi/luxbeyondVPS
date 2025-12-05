import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    { path: '/', component: () => import('../views/LandingPage.vue') },
    // Auth routes redirect to home and open modals (handled by router guard)
    { path: '/login', redirect: '/' },
    { path: '/register', redirect: '/' },
    { path: '/forgot-password', redirect: '/' },

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
                path: 'orders/:orderNumber',
                component: () => import('../views/dashboard/OrderDetails.vue'),
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
                path: 'brands',
                component: () => import('../views/admin/BrandsManagement.vue'),
            },
            {
                path: 'charges',
                component: () => import('../views/admin/ChargesManagement.vue'),
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
                path: 'order-statuses',
                component: () => import('../views/admin/OrderStatusManagement.vue'),
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
                path: 'requests/:id/edit',
                component: () => import('../views/admin/EditProductRequest.vue'),
            },
            {
                path: 'orders/:id',
                component: () => import('../views/admin/AdminOrderDetails.vue'),
            },
            {
                path: 'requests/:id',
                component: () => import('../views/admin/AdminRequestDetails.vue'),
            },
            {
                path: 'analytics',
                component: () => import('../views/admin/Analytics.vue'),
            },
            {
                path: 'settings',
                component: () => import('../views/admin/AdminSettings.vue'),
            },
            {
                path: 'events',
                component: () => import('../views/admin/EventsManagement.vue'),
            },
        ]
    },

    {
        path: '/request-product',
        component: () => import('../views/RequestProduct.vue'),
        meta: { requiresAuth: true }
    },


    {
        path: '/shop',
        component: () => import('../views/Shop.vue'),
    },
    {
        path: '/products/:slug',
        component: () => import('../views/ProductDetails.vue'),
    },
    {
        path: '/checkout',
        component: () => import('../views/Checkout.vue'),
        meta: { requiresAuth: true },
    },

    // Public Pages
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

    // Handle direct navigation to auth routes - open modals instead
    if ((to.path === '/login' || to.path === '/register' || to.path === '/forgot-password')) {
        if (authStore.isAuthenticated) {
            // If already authenticated, redirect to dashboard
            next('/dashboard');
        } else {
            // Import modal store and open appropriate modal
            const { useModalStore } = await import('../stores/modal');
            const modalStore = useModalStore();
            const modalName = to.path === '/login' ? 'login' : to.path === '/register' ? 'register' : 'forgot-password';
            modalStore.openModal(modalName);
            next('/'); // Redirect to home
        }
        return;
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        // Import modal store dynamically
        const { useModalStore } = await import('../stores/modal');
        const modalStore = useModalStore();
        
        // Store the intended destination
        const redirectPath = to.fullPath;
        // Open login modal instead of redirecting
        modalStore.openModal('login', { redirect: redirectPath });
        next(false); // Prevent navigation, stay on current page
    } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
        // Redirect non-admin users trying to access admin routes
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
