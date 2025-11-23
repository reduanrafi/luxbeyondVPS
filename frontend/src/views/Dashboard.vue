<template>
    <div class="p-8 pt-24">
        <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
        <div v-if="user">
            <p class="text-lg">Welcome, <strong>{{ user.name }}</strong>!</p>
            <p class="text-gray-600">Role: {{ user.roles?.[0]?.name }}</p>
            
            <div class="mt-6 flex gap-4">
                <router-link to="/request-product" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">New Request</router-link>
                <router-link v-if="user.roles?.[0]?.name === 'Admin'" to="/admin/requests" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Admin Panel</router-link>
                <button @click="handleLogout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
            </div>

            <RequestList />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import RequestList from '../components/RequestList.vue';

const authStore = useAuthStore();
const router = useRouter();
const user = computed(() => authStore.user);

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>
