<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-900"></h2>
            <router-link to="/request-product"
                class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-hover transition-colors">
                New Request
            </router-link>
        </div>
        <RequestList />
    </div>
</template>

<script setup>
import RequestList from '../../components/RequestList.vue';
</script>
