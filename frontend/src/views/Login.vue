<template>
    <div class="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-surface p-10 rounded-2xl shadow-xl border border-gray-100">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-primary">Sign in to your account</h2>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Or
                    <router-link to="/register" class="font-medium text-secondary hover:text-accent transition-colors">
                        create a new account
                    </router-link>
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="mb-4">
                        <label for="email-address" class="sr-only">Email address</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required
                            v-model="email"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                            placeholder="Email address">
                    </div>
                    <div class="relative">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password" required v-model="password"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm pr-10"
                            placeholder="Password">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox"
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <router-link to="/forgot-password"
                            class="font-medium text-secondary hover:text-accent transition-colors">
                            Forgot your password?
                        </router-link>
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="loading"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-slate-900 bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                        <span v-else>Sign in</span>
                    </button>
                </div>
            </form>
            <p v-if="error" class="text-red-500 text-sm text-center mt-2 bg-red-50 p-2 rounded">{{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { Eye, EyeOff } from 'lucide-vue-next';

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);
const showPassword = ref(false);
const authStore = useAuthStore();
const router = useRouter();

const handleLogin = async () => {
    loading.value = true;
    try {
        await authStore.login({ email: email.value, password: password.value });
        router.push('/dashboard');
    } catch (e) {
        error.value = 'Invalid credentials';
    } finally {
        loading.value = false;
    }
};
</script>
