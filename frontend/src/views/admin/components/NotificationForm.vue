<template>
    <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Type *</label>
                <select v-model="form.event_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="order_placed">Order Placed</option>
                    <option value="order_status_changed">Order Status Changed</option>
                    <option value="payment_received">Payment Received</option>
                    <option value="payment_failed">Payment Failed</option>
                    <option value="shipment_created">Shipment Created</option>
                    <option value="shipment_delivered">Shipment Delivered</option>
                    <option value="product_low_stock">Product Low Stock</option>
                    <option value="new_customer_registered">New Customer Registered</option>
                    <option value="customer_review_submitted">Customer Review Submitted</option>
                    <option value="coupon_created">Coupon Created</option>
                    <option value="refund_requested">Refund Requested</option>
                    <option value="custom">Custom Event</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Channel *</label>
                <select v-model="form.channel" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="push">Push Notification</option>
                    <option value="in_app">In-App Notification</option>
                    <option value="webhook">Webhook</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Type *</label>
                <select v-model="form.recipient_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                    <option value="both">Both</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select v-model="form.priority"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
        </div>

        <div v-if="form.channel === 'email'" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input v-model="form.subject" type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="Email subject">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                <input v-model="form.template_name" type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="email.order_placed">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Message Template</label>
            <textarea v-model="form.message_template" rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Hello {{customer_name}}, Your order #{{order_number}} has been placed successfully. Total: {{order_total}}"></textarea>
            <p class="text-xs text-gray-500 mt-1">Use variables like {{customer_name}}, {{order_number}}, {{order_total}}, etc.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delay (minutes)</label>
                <input v-model.number="form.delay_minutes" type="number" min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="0">
                <p class="text-xs text-gray-500 mt-1">Delay before sending notification</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input v-model.number="form.sort_order" type="number" min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                    placeholder="0">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Description of this notification rule..."></textarea>
        </div>

        <div class="space-y-2">
            <label class="flex items-center">
                <input v-model="form.enabled" type="checkbox"
                    class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-gray-700">Enabled</span>
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="$emit('cancel')"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="submit" :disabled="saving"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Save' }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from '../../../axios';

const props = defineProps({
    setting: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['save', 'cancel']);

const saving = ref(false);

const form = ref({
    event_type: 'order_placed',
    channel: 'email',
    recipient_type: 'customer',
    enabled: true,
    template_name: '',
    subject: '',
    message_template: '',
    variables: null,
    conditions: null,
    recipients: null,
    delay_minutes: 0,
    priority: 'normal',
    description: '',
    sort_order: 0,
});

watch(() => props.setting, (newSetting) => {
    if (newSetting) {
        form.value = { ...newSetting };
    } else {
        form.value = {
            event_type: 'order_placed',
            channel: 'email',
            recipient_type: 'customer',
            enabled: true,
            template_name: '',
            subject: '',
            message_template: '',
            variables: null,
            conditions: null,
            recipients: null,
            delay_minutes: 0,
            priority: 'normal',
            description: '',
            sort_order: 0,
        };
    }
}, { immediate: true });

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.setting) {
            await axios.put(`/admin/settings/notifications/${props.setting.id}`, form.value);
        } else {
            await axios.post('/admin/settings/notifications', form.value);
        }
        emit('save');
    } catch (error) {
        console.error('Error saving notification setting:', error);
        alert(error.response?.data?.message || 'Error saving notification setting');
    } finally {
        saving.value = false;
    }
};
</script>

