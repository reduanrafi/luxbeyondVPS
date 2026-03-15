<template>
    <div class="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-surface p-10 rounded-2xl shadow-xl border border-gray-500">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-primary">Reset your password</h2>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleReset">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email-address" class="sr-only">Email address</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required
                            v-model="email"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                            placeholder="Email address">
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="loading"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                        <span v-else>Send Reset Link</span>
                    </button>
                </div>

                <div class="text-center">
                    <router-link to="/login" class="font-medium text-secondary hover:text-accent transition-colors">
                        Back to Login
                    </router-link>
                </div>
            </form>
            <p v-if="message" class="text-green-600 text-sm text-center mt-2 bg-green-50 p-2 rounded">{{ message }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const email = ref('');
const message = ref('');
const loading = ref(false);

const handleReset = async () => {
    loading.value = true;
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));

    // Mock reset logic
    message.value = `If an account exists for ${email.value}, you will receive a password reset link shortly.`;
    email.value = '';
    loading.value = false;
};
</script>
