<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Customer Profile</h2>
                <p class="text-sm text-zinc-400 mt-1">View customer information and order history</p>
            </div>
            <div class="flex gap-3">
                <router-link to="/admin/customers"
                    class="px-4 py-2 border border-white/10 rounded-lg text-sm font-medium text-zinc-400 hover:text-white hover:bg-white/5 transition-colors">
                    Back to List
                </router-link>
                <button @click="showModal = true"
                    class="px-4 py-2 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-colors">
                    Edit Customer
                </button>
            </div>
        </div>

        <!-- Edit Customer Modal -->
        <CustomerForm v-if="showModal" :customer="customer" @close="showModal = false" @refresh="handleRefresh" />

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-zinc-400">Loading profile...</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Profile Card -->
            <div class="space-y-6">
                <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6 text-center">
                    <div
                        class="w-24 h-24 mx-auto rounded-full bg-primary flex items-center justify-center text-4xl font-bold text-black mb-4">
                        {{ customer.name?.charAt(0).toUpperCase() }}
                    </div>
                    <h3 class="text-xl font-bold text-white">{{ customer.name }}</h3>
                    <p class="text-zinc-400 text-sm">{{ customer.email }}</p>
                    <p class="text-zinc-500 text-xs mt-1">Joined {{ customer.joined }}</p>

                    <div class="mt-6 pt-6 border-t border-white/5 grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-white">{{ customer.orders_count }}</p>
                            <p class="text-xs text-zinc-500 uppercase tracking-wider">Orders</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-primary">৳{{ customer.total_spent?.toLocaleString() }}</p>
                            <p class="text-xs text-zinc-500 uppercase tracking-wider">Total Spent</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6">
                    <h4 class="font-bold text-white mb-4">Contact Information</h4>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-zinc-500 uppercase">Email</p>
                            <p class="text-sm text-zinc-300">{{ customer.email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 uppercase">Phone</p>
                            <p class="text-sm text-zinc-300">{{ customer.phone || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 uppercase">Status</p>
                            <span class="inline-block mt-1 px-2 py-1 rounded text-xs font-semibold"
                                :class="customer.is_active ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500'">
                                {{ customer.is_active ? 'Active' : 'Blocked' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recent Orders -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6">
                    <h4 class="font-bold text-white mb-4">Recent Orders</h4>
                    
                    <div v-if="customer.recent_orders?.length === 0" class="text-center py-8 text-zinc-500">
                        No orders found.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-white/5 border-b border-white/5">
                                <tr>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-zinc-400 uppercase">Order ID</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-zinc-400 uppercase">Date</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-zinc-400 uppercase">Total</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-zinc-400 uppercase">Status</th>
                                    <th class="text-right py-3 px-4 text-xs font-semibold text-zinc-400 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in customer.recent_orders" :key="order.id"
                                    class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-4 text-sm font-medium text-white">#{{ order.order_number || order.id }}</td>
                                    <td class="py-3 px-4 text-sm text-zinc-400">
                                        {{ new Date(order.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="py-3 px-4 text-sm font-semibold text-white">
                                        ৳{{ parseFloat(order.total).toLocaleString() }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-xs font-medium border border-transparent"
                                            :class="getStatusClass(order.status)">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <router-link :to="`/admin/orders/${order.order_number || order.id}`"
                                            class="text-primary hover:underline text-sm font-medium">
                                            View
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from '../../axios';
import { useToast } from 'vue-toastification';
import CustomerForm from './CustomerForm.vue';

const route = useRoute();
const toast = useToast();
const loading = ref(true);
const customer = ref({});
const showModal = ref(false);

const fetchCustomer = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/admin/customers/${route.params.id}`);
        customer.value = response.data;
    } catch (error) {
        toast.error('Failed to load customer details');
    } finally {
        loading.value = false;
    }
};

const getStatusClass = (status) => {
    const classes = {
        'Completed': 'bg-green-500/10 text-green-500 border-green-500/20',
        'Processing': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'Shipped': 'bg-purple-500/10 text-purple-500 border-purple-500/20',
        'Pending': 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        'Cancelled': 'bg-red-500/10 text-red-500 border-red-500/20'
    };
    return classes[status] || 'bg-white/5 text-zinc-400 border-white/10';
};

const handleRefresh = () => {
    fetchCustomer();
};

onMounted(() => {
    fetchCustomer();
});
</script>
