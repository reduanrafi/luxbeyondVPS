<template>
  <Header v-if="!isDashboardRoute" />
  <keep-alive :include="cachedViews">
    <router-view :key="routeKey"></router-view>
  </keep-alive>
  <Footer v-if="!isDashboardRoute" />
  <AuthModal />
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useSettingsStore } from './stores/settings';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import AuthModal from './components/AuthModal.vue';

const route = useRoute();
const settingsStore = useSettingsStore();
const isDashboardRoute = computed(() => route.path.startsWith('/dashboard') || route.path.startsWith('/traveller-dashboard'));

// Cache these pages in memory so they don't re-fetch on every navigation
const cachedViews = ['LandingPage', 'Shop', 'BlogList', 'TravellerLanding'];

// Only re-mount the page when the route truly changes (not just query changes on non-shop pages)
const routeKey = computed(() => {
  // Shop page should remount when query changes (filters), others use path only
  if (route.path.startsWith('/shop')) return route.fullPath;
  return route.path;
});

onMounted(() => {
  // Settings are already being prefetched in main.js — this is a no-op if already initialized
  settingsStore.fetchSettings();
  document.dispatchEvent(new Event('prerender-ready'))
})
</script>
