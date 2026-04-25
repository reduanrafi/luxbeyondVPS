<template>
    <div class="font-sans text-white antialiased">
        <main>
            <HeroSection />
            <BrandSlider />
            <FeaturedProducts />
            <RecentBlogs />
            <UserReviews />
        </main>
    </div>
</template>

<script setup>
import { defineAsyncComponent } from 'vue';
import HeroSection from '../components/HeroSection.vue';

// Register component name for keep-alive matching
defineOptions({ name: 'LandingPage' });

// Lazy-load below-the-fold sections — they will not block first paint
const BrandSlider = defineAsyncComponent(() => import('../components/BrandSlider.vue'));
const FeaturedProducts = defineAsyncComponent(() => import('../components/FeaturedProducts.vue'));
const RecentBlogs = defineAsyncComponent(() => import('../components/RecentBlogs.vue'));
const UserReviews = defineAsyncComponent(() => import('../components/UserReviews.vue'));
</script>
