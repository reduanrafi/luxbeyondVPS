<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-serif font-bold text-white tracking-wide">Edit Order</h2>
                <p class="text-zinc-400 mt-1" v-if="order">Order #{{ order.order_number }}</p>
            </div>
            <button @click="$router.back()" 
                class="flex items-center gap-2 px-4 py-2 bg-white/5 text-zinc-300 rounded-lg hover:bg-white/10 transition-colors">
                <ArrowLeft class="w-4 h-4" />
                Back
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
            <div class="animate-spin w-8 h-8 border-2 border-primary border-t-transparent rounded-full"></div>
        </div>

        <form v-else-if="order" @submit.prevent="updateOrder" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Shipping Details -->
                    <div class="bg-[#111111] p-6 rounded-xl border border-white/5 space-y-4">
                        <h3 class="text-lg font-bold text-white mb-4">Shipping Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Name</label>
                                <input v-model="form.shipping_name" type="text" 
                                    class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Phone</label>
                                <input v-model="form.shipping_phone" type="text" 
                                    class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Email</label>
                                <input v-model="form.shipping_email" type="email" 
                                    class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Address</label>
                                <textarea v-model="form.shipping_address" rows="3" 
                                    class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-[#111111] p-6 rounded-xl border border-white/5 space-y-4">
                        <h3 class="text-lg font-bold text-white mb-4">Notes</h3>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Admin Notes</label>
                            <textarea v-model="form.notes" rows="3" 
                                class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary"
                                placeholder="Internal notes about this order..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status -->
                    <div class="bg-[#111111] p-6 rounded-xl border border-white/5 space-y-4">
                        <h3 class="text-lg font-bold text-white mb-4">Status & Payment</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Order Status</label>
                            <select v-model="form.status_id" 
                                class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                                <option v-for="status in orderStatuses" :key="status.id" :value="status.id">
                                    {{ status.label }}
                                </option>
                            </select>
                            
                            <div class="mt-2">
                                <label class="block text-xs font-medium text-zinc-500 mb-1">Status Note (Optional)</label>
                                <input v-model="form.status_note" type="text" placeholder="Reason for status change"
                                    class="w-full px-3 py-1.5 text-sm bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Payment Status</label>
                            <select v-model="form.payment_status" 
                                class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Event</label>
                             <select v-model="form.event_id" 
                                class="w-full px-4 py-2 bg-black/20 border border-white/10 rounded-lg text-white focus:outline-none focus:border-primary">
                                <option :value="null">No Event</option>
                                <option v-for="event in events" :key="event.id" :value="event.id">
                                    {{ event.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3">
                        <button type="submit" :disabled="saving"
                            class="w-full px-4 py-3 bg-primary text-black font-bold uppercase tracking-wider rounded-lg hover:bg-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ saving ? 'Saving...' : 'Update Order' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft } from 'lucide-vue-next';
import axios from '../../axios';

const route = useRoute();
const router = useRouter();
const orderId = route.params.id;

const loading = ref(true);
const saving = ref(false);
const order = ref(null);
const orderStatuses = ref([]);
const events = ref([]);

const form = ref({
    shipping_name: '',
    shipping_phone: '',
    shipping_email: '',
    shipping_address: '',
    notes: '',
    status_id: '',
    status_note: '',
    payment_status: '',
    event_id: null
});

const fetchData = async () => {
    loading.value = true;
    try {
        const [orderRes, statusesRes, eventsRes] = await Promise.all([
            axios.get(`/admin/orders/${orderId}`),
            axios.get('/order-statuses'),
            axios.get('/admin/events')
        ]);

        order.value = orderRes.data;
        orderStatuses.value = statusesRes.data;
        events.value = eventsRes.data.data || eventsRes.data || [];

        // Populate form
        form.value = {
            shipping_name: order.value.shipping_name,
            shipping_phone: order.value.shipping_phone,
            shipping_email: order.value.shipping_email,
            shipping_address: order.value.shipping_address,
            notes: order.value.notes,
            status_id: order.value.status_id,
            payment_status: order.value.payment_status,
            event_id: order.value.event_id,
            status_note: ''
        };

    } catch (err) {
        console.error('Error fetching data:', err);
        alert('Failed to load order data');
    } finally {
        loading.value = false;
    }
};

const updateOrder = async () => {
    saving.value = true;
    try {
        await axios.put(`/admin/orders/${orderId}`, form.value);
        alert('Order updated successfully');
        router.push(`/admin/orders/${orderId}`);
    } catch (err) {
        console.error('Error updating order:', err);
        alert(err.response?.data?.message || 'Failed to update order');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>
