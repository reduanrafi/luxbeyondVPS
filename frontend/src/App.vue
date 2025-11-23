<template>
  <Header v-if="!isDashboardRoute" />
  <router-view></router-view>
  <Footer v-if="!isDashboardRoute" />
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';

const route = useRoute();
const isDashboardRoute = computed(() => route.path.startsWith('/dashboard') || route.path.startsWith('/admin'));
</script>
