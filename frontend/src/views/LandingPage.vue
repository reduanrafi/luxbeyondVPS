<template>
    <div class="font-sans text-gray-900 antialiased">
        <main>
            <HeroSection />
            <FeaturedProducts />
            <RecentBlogs />
            <UserReviews />
        </main>
    </div>
</template>

<script setup>
import HeroSection from '../components/HeroSection.vue';
import FeaturedProducts from '../components/FeaturedProducts.vue';
import RecentBlogs from '../components/RecentBlogs.vue';
import UserReviews from '../components/UserReviews.vue';
</script>
