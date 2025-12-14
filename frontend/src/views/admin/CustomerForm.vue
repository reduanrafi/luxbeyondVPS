<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="bg-[#111111] rounded-xl shadow-xl border border-white/10 w-full max-w-2xl mx-4 transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/5">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit Customer' : 'Add New Customer' }}</h2>
                    <p class="text-sm text-zinc-400 mt-1">{{ isEditing ? 'Update customer details' : 'Create a new customer account' }}</p>
                </div>
                <button @click="$emit('close')" class="text-zinc-400 hover:text-white transition-colors">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Form -->
            <div class="p-6">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-2">Full Name</label>
                        <input type="text" v-model="form.name" required
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': errors.name }"
                            placeholder="Enter full name"
                            @input="errors.name = null">
                        <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name[0] }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-2">Email Address</label>
                        <input type="email" v-model="form.email" required
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': errors.email }"
                            placeholder="Enter email address"
                            @input="errors.email = null">
                        <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-2">Phone Number</label>
                        <input type="tel" v-model="form.phone"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': errors.phone }"
                            placeholder="Enter phone number (optional)"
                            @input="errors.phone = null">
                        <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-2">
                            {{ isEditing ? 'Password (leave blank to keep current)' : 'Password' }}
                        </label>
                        <input type="password" v-model="form.password" :required="!isEditing"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': errors.password }"
                            placeholder="Enter password"
                            @input="errors.password = null">
                        <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 gap-3">
                        <button type="button" @click="$emit('close')"
                            class="px-4 py-2 border border-white/10 rounded-lg text-zinc-400 hover:text-white hover:bg-white/5 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="loading"
                            class="px-6 py-2 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <span v-if="loading" class="w-4 h-4 border-2 border-black/30 border-t-black rounded-full animate-spin"></span>
                            {{ isEditing ? 'Update Customer' : 'Create Customer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { X } from 'lucide-vue-next';
import axios from '../../axios';
import { useToast } from 'vue-toastification';

const props = defineProps({
    customer: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'refresh']);

const toast = useToast();
const loading = ref(false);
const errors = ref({});

const isEditing = computed(() => !!props.customer);

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: ''
});

// Initialize form when customer prop changes or on mount
watch(() => props.customer, (newVal) => {
    errors.value = {}; // Clear errors on customer change
    if (newVal) {
        form.value = {
            name: newVal.name,
            email: newVal.email,
            phone: newVal.phone,
            password: ''
        };
    } else {
        form.value = {
            name: '',
            email: '',
            phone: '',
            password: ''
        };
    }
}, { immediate: true });

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {}; // Clear previous errors
    
    try {
        if (isEditing.value) {
            await axios.put(`/admin/customers/${props.customer.id}`, form.value);
            toast.success('Customer updated successfully');
        } else {
            await axios.post('/admin/customers', form.value);
            toast.success('Customer created successfully');
        }
        emit('refresh');
        emit('close');
    } catch (error) {
        if (error.response?.data?.errors) {
            // Validation errors - show in form
            errors.value = error.response.data.errors;
        } else {
            // System/Fatal errors - show in toast
            console.error(error);
            toast.error(error.response?.data?.message || 'An error occurred');
        }
    } finally {
        loading.value = false;
    }
};
</script>
