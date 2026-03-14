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
