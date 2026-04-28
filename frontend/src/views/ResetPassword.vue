<template>
    <div class="min-h-screen bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <img src="/logo.png" alt="Luxbeyond" class="h-12 w-auto" />
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">Create new password</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-700">
                <form class="space-y-6" @submit.prevent="handleResetPassword">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" required v-model="form.email"
                                :readonly="route.query.email"
                                class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-700 text-white" 
                                :class="{ 'opacity-60 cursor-not-allowed': route.query.email }" />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300">New Password</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required v-model="form.password"
                                class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-700 text-white" />
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirm New Password</label>
                        <div class="mt-1">
                            <input id="password_confirmation" name="password_confirmation" type="password" required v-model="form.password_confirmation"
                                class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm bg-gray-700 text-white" />
                        </div>
                    </div>

                    <div v-if="error" class="text-red-500 text-sm p-3 bg-red-500/10 rounded-md">
                        {{ error }}
                    </div>
                    
                    <div v-if="successMsg" class="text-green-500 text-sm p-3 bg-green-500/10 rounded-md">
                        {{ successMsg }}
                        <div class="mt-2">
                            <router-link to="/" class="text-primary hover:text-primary/80 font-medium">Return home to login</router-link>
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="loading || successMsg !== ''"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="loading" class="mr-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                            </span>
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from '../axios';

const route = useRoute();
const router = useRouter();

const form = reactive({
    token: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const loading = ref(false);
const error = ref('');
const successMsg = ref('');

onMounted(() => {
    form.token = route.query.token || '';
    form.email = route.query.email || '';
    
    if (!form.token || !form.email) {
        error.value = 'Invalid or missing password reset link parameters.';
    }
});

const handleResetPassword = async () => {
    if (form.password !== form.password_confirmation) {
        error.value = 'Passwords do not match.';
        return;
    }

    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post('/auth/reset-password', form);
        successMsg.value = response.data.message || 'Password has been reset successfully.';
        form.password = '';
        form.password_confirmation = '';
        
        // Optional: Open login modal automatically after a short delay
        setTimeout(() => {
            router.push('/');
        }, 3000);
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to reset password. The link might be expired or invalid.';
    } finally {
        loading.value = false;
    }
};
</script>
