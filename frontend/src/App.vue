<template>
  <Header v-if="!isDashboardRoute" />
  <router-view></router-view>
  <Footer v-if="!isDashboardRoute" />
  <AuthModal />
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import AuthModal from './components/AuthModal.vue';

const route = useRoute();
const isDashboardRoute = computed(() => route.path.startsWith('/dashboard') || route.path.startsWith('/admin') || route.path.startsWith('/traveller-dashboard'));
</script>
