<template>
    <div class="min-h-screen flex items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-surface p-10 rounded-2xl shadow-xl border border-gray-500">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-primary">Create your account</h2>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Already have an account?
                    <router-link to="/login" class="font-medium text-secondary hover:text-accent transition-colors">
                        Sign in
                    </router-link>
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="name" class="sr-only">Full Name</label>
                        <input id="name" name="name" type="text" required v-model="name"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                            :class="{ 'border-red-500': authStore.errors.name }" placeholder="Full Name">
                        <p v-if="authStore.errors.name" class="text-red-500 text-xs mt-1">{{ authStore.errors.name[0] }}
                        </p>
                    </div>
                    <div>
                        <label for="email-address" class="sr-only">Email address</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required
                            v-model="email"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm"
                            :class="{ 'border-red-500': authStore.errors.email }" placeholder="Email address">
                        <p v-if="authStore.errors.email" class="text-red-500 text-xs mt-1">{{ authStore.errors.email[0]
                            }}</p>
                    </div>
                    <div class="relative">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password" required v-model="password"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm pr-10"
                            :class="{ 'border-red-500': authStore.errors.password }" placeholder="Password">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                        <p v-if="authStore.errors.password" class="text-red-500 text-xs mt-1">{{
                            authStore.errors.password[0] }}</p>
                    </div>
                    <div class="relative">
                        <label for="password-confirm" class="sr-only">Confirm Password</label>
                        <input id="password-confirm" name="password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password" required
                            v-model="password_confirmation"
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-white focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm pr-10"
                            placeholder="Confirm Password">
                        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="loading"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-slate-900 bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                        <span v-else>Create Account</span>
                    </button>
                </div>

                <div class="text-center mt-4">
                    <p class="text-sm text-slate-600">
                        Want to earn money while travelling?
                        <router-link to="/travellers"
                            class="font-medium text-secondary hover:text-accent transition-colors">
                            Join as a Traveller
                        </router-link>
                    </p>
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

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const authStore = useAuthStore();
const router = useRouter();

const handleRegister = async () => {
    if (password.value != password_confirmation.value) {
        error.value = "Passwords do not match";
        return;
    }
    loading.value = true;
    error.value = ''; // Clear previous generic errors

    try {
        await authStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value,
            role: 'Customer'
        });
        router.push('/dashboard');
    } catch (e) {
        // If it's not a validation error (which is handled by authStore.errors), show generic error
        if (!authStore.errors || Object.keys(authStore.errors).length === 0 || Array.isArray(authStore.errors)) {
            error.value = 'Registration failed. Please check your inputs.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
