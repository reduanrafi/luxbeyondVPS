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
import HeroSection from '../components/HeroSection.vue';
import BrandSlider from '../components/BrandSlider.vue';
import FeaturedProducts from '../components/FeaturedProducts.vue';
import RecentBlogs from '../components/RecentBlogs.vue';
import UserReviews from '../components/UserReviews.vue';
</script>
