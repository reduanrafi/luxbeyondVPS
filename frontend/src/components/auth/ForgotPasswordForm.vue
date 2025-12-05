<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Reset Password</h2>
            <p class="text-sm text-gray-600">
                Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>

        <form class="space-y-5" @submit.prevent="handleReset">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email Address
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    v-model="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-gray-900 placeholder-gray-400 transition-all"
                    placeholder="Enter your email"
                />
            </div>

            <!-- Success/Error Message -->
            <p v-if="message" :class="message.includes('receive') ? 'text-primary' : 'text-red-600'" class="text-sm bg-primary/10 p-3 rounded-lg">
                {{ message }}
            </p>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="loading"
                class="w-full bg-primary hover:bg-primary/80 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <span v-if="loading" class="flex items-center gap-2">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                    Sending...
                </span>
                <span v-else>Send Reset Link</span>
            </button>

            <!-- Back to Login -->
            <div class="text-center pt-4">
                <button
                    type="button"
                    @click="modalStore.switchModal('login')"
                    class="text-sm text-primary hover:text-primary/80 font-semibold"
                >
                    ← Back to Login
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useModalStore } from '../../stores/modal';
import axios from '../../axios';

const email = ref('');
const message = ref('');
const loading = ref(false);
const modalStore = useModalStore();

const handleReset = async () => {
    loading.value = true;
    message.value = '';
    
    try {
        // TODO: Replace with actual API endpoint when backend is ready
        await axios.post('/auth/forgot-password', { email: email.value });
        message.value = `If an account exists for ${email.value}, you will receive a password reset link shortly.`;
        email.value = '';
    } catch (error) {
        message.value = error.response?.data?.message || 'Failed to send reset link. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>
