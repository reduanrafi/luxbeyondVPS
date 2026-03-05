<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-3xl font-bold text-white mb-2">Login</h2>
            <p class="text-sm text-gray-600">
                Login to make an order, access your orders, special offers, and more!
            </p>
        </div>

        <form class="space-y-5" @submit.prevent="handleLogin">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Email Address
                </label>
                <input id="email" name="email" type="email" autocomplete="email" required v-model="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 transition-all"
                    placeholder="Enter your email" />
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password" required v-model="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 pr-10 transition-all"
                        placeholder="Enter your password" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-primary">
                        <Eye v-if="!showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Forgot Password Link -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox"
                        class="h-4 w-4 text-primary focus:ring-primary/20 border-gray-300 rounded accent-primary" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <button type="button" @click="modalStore.switchModal('forgot-password')"
                    class="text-sm text-primary hover:text-primary/80 font-medium">
                    Forgot password?
                </button>
            </div>

            <!-- Error Message -->
            <p v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">
                {{ error }}
            </p>

            <!-- Submit Button -->
            <button type="submit" :disabled="loading"
                class="w-full bg-primary hover:bg-primary/80 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span v-if="loading" class="flex items-center gap-2">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                    Signing in...
                </span>
                <span v-else>Login</span>
            </button>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-surface text-gray-500">or</span>
                </div>
            </div>

            <!-- Social Login -->
            <div class="flex gap-3 justify-center">
                <button type="button"
                    class="w-12 h-12 rounded-full border-2 border-gray-300 hover:border-gray-400 flex items-center justify-center transition-colors"
                    title="Login with Google">
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                </button>
                <button type="button"
                    class="w-12 h-12 rounded-full border-2 border-gray-300 hover:border-gray-400 flex items-center justify-center transition-colors"
                    title="Login with LinkedIn">
                    <svg class="w-6 h-6" fill="#0077B5" viewBox="0 0 24 24">
                        <path
                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                    </svg>
                </button>
            </div>

            <!-- Register Link -->
            <div class="text-center pt-4">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <button type="button" @click="modalStore.switchModal('register')"
                        class="text-primary hover:text-primary/80 font-semibold">
                        Register
                    </button>
                </p>
            </div>

            <!-- Terms -->
            <p class="text-xs text-gray-500 text-center">
                By continuing you agree to
                <a href="#" class="text-primary hover:underline">Terms & Conditions</a>,
                <a href="#" class="text-primary hover:underline">Privacy Policy</a> &
                <a href="#" class="text-primary hover:underline">Refund-Return Policy</a>
            </p>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useModalStore } from '../../stores/modal';
import { useRouter } from 'vue-router';
import { Eye, EyeOff } from 'lucide-vue-next';
import { trackSignIn } from '../../utils/analytics';

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);
const showPassword = ref(false);
const authStore = useAuthStore();
const modalStore = useModalStore();
const router = useRouter();

const handleLogin = async () => {
    loading.value = true;
    error.value = '';
    try {
        await authStore.login({ email: email.value, password: password.value });
        trackSignIn('email');
        modalStore.closeModal();
        const redirectTo = modalStore.props?.redirect || '/dashboard';
        router.push(redirectTo);
    } catch (e) {
        error.value = e.response?.data?.message || 'Invalid credentials. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>
