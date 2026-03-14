<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-3xl font-bold text-white mb-2">Create Account</h2>
            <p class="text-sm text-gray-600">
                Register to make an order, access your orders, special offers, and more!
            </p>
        </div>

        <form class="space-y-5" @submit.prevent="handleRegister">
            <!-- Name Input -->
            <div>
                <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Full Name
                </label>
                <input id="name" name="name" type="text" required v-model="name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 transition-all"
                    :class="{ 'border-red-500': authStore.errors.name }" placeholder="Enter your full name" />
                <p v-if="authStore.errors.name" class="text-red-500 text-xs mt-1">
                    {{ authStore.errors.name[0] }}
                </p>
            </div>

            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Email Address
                </label>
                <input id="email" name="email" type="email" autocomplete="email" required v-model="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 transition-all"
                    :class="{ 'border-red-500': authStore.errors.email }" placeholder="Enter your email" />
                <p v-if="authStore.errors.email" class="text-red-500 text-xs mt-1">
                    {{ authStore.errors.email[0] }}
                </p>
            </div>

            <!-- Phone Input -->
            <div>
                <label for="phone" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Phone Number
                </label>
                <input id="phone" name="phone" type="text" autocomplete="phone" required v-model="phone"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 transition-all"
                    :class="{ 'border-red-500': authStore.errors.phone }" placeholder="Enter your phone number" />
                <p v-if="authStore.errors.phone" class="text-red-500 text-xs mt-1">
                    {{ authStore.errors.phone[0] }}
                </p>
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password" required v-model="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 pr-10 transition-all"
                        :class="{ 'border-red-500': authStore.errors.password }" placeholder="Enter your password" />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <Eye v-if="!showPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
                <p v-if="authStore.errors.password" class="text-red-500 text-xs mt-1">
                    {{ authStore.errors.password[0] }}
                </p>
            </div>

            <!-- Confirm Password Input -->
            <div>
                <label for="password-confirm" class="block text-sm font-medium text-slate-300 mb-1.5">
                    Confirm Password
                </label>
                <div class="relative">
                    <input id="password-confirm" name="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password" required
                        v-model="password_confirmation"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-white placeholder-gray-400 pr-10 transition-all"
                        placeholder="Confirm your password" />
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                        <EyeOff v-else class="h-5 w-5" />
                    </button>
                </div>
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
                    Creating account...
                </span>
                <span v-else>Create Account</span>
            </button>

            <!-- Login Link -->
            <div class="text-center pt-4">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <button type="button" @click="modalStore.switchModal('login')"
                        class="text-primary hover:text-primary/80 font-semibold">
                        Login
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
import { trackSignUp, trackTravelerSignUp } from '../../utils/analytics';

const name = ref('');
const email = ref('');
const phone = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const authStore = useAuthStore();
const modalStore = useModalStore();
const router = useRouter();

const handleRegister = async () => {
    if (password.value != password_confirmation.value) {
        error.value = 'Passwords do not match';
        return;
    }
    if (!phone.value) {
        error.value = 'Phone number is required';
        return;
    }
    loading.value = true;
    error.value = '';

    try {
        await authStore.register({
            name: name.value,
            email: email.value,
            phone: phone.value,
            password: password.value,
            password_confirmation: password_confirmation.value,
            role: 'Customer'
        });
        trackSignUp('email');
        if (name.value.toLowerCase().includes('traveler') || modalStore.props?.role === 'Traveller') {
            trackTravelerSignUp();
        }
        modalStore.closeModal();
        const redirectTo = modalStore.props?.redirect || '/dashboard';
        router.push(redirectTo);
    } catch (e) {
        if (!authStore.errors || Object.keys(authStore.errors).length === 0 || Array.isArray(authStore.errors)) {
            error.value = 'Registration failed. Please check your inputs.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
