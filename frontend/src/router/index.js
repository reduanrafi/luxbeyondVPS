import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useLoaderStore } from '../stores/loader';

const routes = [
    { path: '/', meta: { title: 'Home' }, component: () => import('../views/LandingPage.vue') },
    // Auth routes redirect to home and open modals (handled by router guard)
    { path: '/login', redirect: '/dashboard' },
    { path: '/register', redirect: '/dashboard' },
    { path: '/forgot-password', redirect: '/dashboard' },

    // Customer Dashboard
    {
        path: '/dashboard',
        component: () => import('../views/dashboard/DashboardLayout.vue'),
        meta: { requiresAuth: true},
        children: [
            {
                path: '',
                component: () => import('../views/dashboard/Overview.vue'), 
                meta: { title: 'My Dashboard' },
            },
            {
                path: '/traveller-dashboard',
                component: () => import('../views/TravellerDashboard.vue'),
                meta: { title: 'My Trips' },
            },
            {
                path: 'requests',
                component: () => import('../views/dashboard/MyRequests.vue'),
                meta: { title: 'My Requests' },
            },
            {
                path: 'requests/:id',
                component: () => import('../views/dashboard/RequestDetails.vue'),
                meta: { title: 'Request Details' },
            },
            {
                path: 'orders',
                component: () => import('../views/dashboard/MyOrders.vue'),
                meta: { title: 'My Orders' },
            },
            {
                path: 'orders/:orderNumber',
                component: () => import('../views/dashboard/OrderDetails.vue'),
                meta: { title: 'Order Details' },
            },
            {
                path: 'notifications',
                component: () => import('../views/dashboard/Notifications.vue'),
                meta: { title: 'Notifications' },
            },
            {
                path: 'coupons',
                component: () => import('../views/dashboard/Coupons.vue'),
                meta: { title: 'My Coupons' },
            },
            {
                path: 'settings',
                component: () => import('../views/dashboard/Settings.vue'),
                meta: { title: 'Settings' },
            },
            {
                path: '/traveller-dashboard',
                component: () => import('../views/TravellerDashboard.vue'),
                meta: { title: 'My Trips' },
            },
        ]
    },

    {
        path: '/blogs',
        name: 'blogs',
        component: () => import('../views/BlogList.vue'),
        meta: { title: 'Blog' }
    },
    {
        path: '/blogs/:slug',
        name: 'blog-details',
        component: () => import('../views/BlogDetails.vue'),
        meta: { title: 'Blog Details' }
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
                meta: { title: 'Admin Dashboard' },
            },
            {
                path: 'products',
                component: () => import('../views/admin/ProductsManagement.vue'),
                meta: { title: 'Products' },
            },
            {
                path: 'products/create',
                component: () => import('../views/admin/ProductForm.vue'),
                meta: { title: 'Create Product' },
            },
            {
                path: 'products/:id/edit',
                component: () => import('../views/admin/ProductForm.vue'),
                meta: { title: 'Edit Product' },
            },
            {
                path: 'categories',
                component: () => import('../views/admin/CategoriesManagement.vue'),
                meta: { title: 'Categories' },
            },
            {
                path: 'brands',
                component: () => import('../views/admin/BrandsManagement.vue'),
                meta: { title: 'Brands' },
            },
            {
                path: 'charges',
                component: () => import('../views/admin/ChargesManagement.vue'),
                meta: { title: 'Charges' },
            },
            {
                path: 'coupons',
                component: () => import('../views/admin/CouponsManagement.vue'),
                meta: { title: 'Coupons' },
            },
            {
                path: 'orders',
                component: () => import('../views/admin/OrdersManagement.vue'),
                meta: { title: 'Orders' },
            },
            {
                path: 'order-statuses',
                component: () => import('../views/admin/OrderStatusManagement.vue'),
                meta: { title: 'Order Statuses' },
            },
            {
                path: 'customers',
                component: () => import('../views/admin/CustomersManagement.vue'),
                meta: { title: 'Customers' },
            },
            {
                path: 'customers/:id',
                component: () => import('../views/admin/CustomerDetails.vue'),
                meta: { title: 'Customer Details' },
            },
            {
                path: 'requests',
                component: () => import('../views/AdminRequestDashboard.vue'),
                meta: { title: 'Requests' },
            },
            {
                path: 'requests/:id/edit',
                component: () => import('../views/admin/EditProductRequest.vue'),
                meta: { title: 'Edit Request' },
            },
            {
                path: 'orders/:id',
                component: () => import('../views/admin/AdminOrderDetails.vue'),
                meta: { title: 'Order Details' },
            },
            {
                path: 'requests/:id',
                component: () => import('../views/admin/AdminRequestDetails.vue'),
                            meta: { title: 'Request Details' },
            },
            {
                path: 'analytics',
                component: () => import('../views/admin/Analytics.vue'),
                meta: { title: 'Analytics' },
            },
            {
                path: 'settings',
                component: () => import('../views/admin/AdminSettings.vue'),
            },
            {
                path: 'blogs',
                name: 'admin-blogs',
                component: () => import('../views/admin/BlogsManagement.vue'),
                meta: { title: 'Blog Management' }
            },
            {
                path: 'events',
                component: () => import('../views/admin/EventsManagement.vue'),
                meta: { title: 'Events Management' },
            },
            {
                path: 'users',
                component: () => import('../views/admin/UsersManagement.vue'),
                meta: { title: 'Users Management' },
            },
            {
                path: 'roles',
                component: () => import('../views/admin/RolesManagement.vue'),
                meta: { title: 'Roles Management' },
            },
            {
                path: 'travellers',
                component: () => import('../views/admin/TravellersManagement.vue'),
                meta: { title: 'Travellers Management' },
            },
            {
                path: 'travellers/:id',
                component: () => import('../views/admin/TravellerDetails.vue'),
                meta: { title: 'Traveller Details' },
            },
        ]
    },

    {
        path: '/request-product',
        component: () => import('../views/RequestProduct.vue'),
        meta: { requiresAuth: true, title: 'Request Product', description: 'Request a product from the admin luxbeyond' },
    },


    {
        path: '/shop',
        component: () => import('../views/Shop.vue'),
        meta: { title: 'Shop', description: 'Shop luxbeyond' },
    },
    {
        path: '/shop/:slug',
        component: () => import('../views/ProductDetails.vue'),
        meta: { title: 'Product Details', description: 'Product details luxbeyond' },
    },
    {
        path: '/checkout',
        component: () => import('../views/Checkout.vue'),
        meta: { requiresAuth: true, title: 'Checkout', description: 'Checkout luxbeyond' },
    },

    // Public Pages
    { path: '/travellers', component: () => import('../views/TravellerLanding.vue'), meta: { title: 'Travellers', description: 'Travellers luxbeyond' } },
    { path: '/track-order', component: () => import('../views/OrderTracking.vue'), meta: { title: 'Track Order', description: 'Track order luxbeyond' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const loaderStore = useLoaderStore();
    loaderStore.startLoading();

    const authStore = useAuthStore();
    
    // Update document title
    document.title = `${to.meta.title} - Luxbeyond` || 'Luxbeyond';
    document.description = to.meta.description || 'Luxbeyond';

    if (!authStore.user && authStore.token) {
        await authStore.fetchUser();
    }

    // Handle direct navigation to auth routes - open modals instead
    if ((to.path === '/login' || to.path === '/register' || to.path === '/forgot-password')) {
        if (authStore.isAuthenticated) {
            next('/dashboard');
        } else {
            const { useModalStore } = await import('../stores/modal');
            const modalStore = useModalStore();
            const modalName = to.path === '/login' ? 'login' : to.path === '/register' ? 'register' : 'forgot-password';
            modalStore.openModal(modalName);
            next('/'); 
        }
        return;
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        const { useModalStore } = await import('../stores/modal');
        const modalStore = useModalStore();
        modalStore.openModal('login', { redirect: to.fullPath });
        next(false);
    } else if (to.meta.requiresAdmin && !authStore.user?.roles?.some(role => role.name === 'Admin' || role.name === 'Super Admin')) {
        next('/');
    } else {
        next();
    }
});

router.afterEach(() => {
    const loaderStore = useLoaderStore();
    setTimeout(() => {
        loaderStore.stopLoading();
    }, 500);
});

export default router;
