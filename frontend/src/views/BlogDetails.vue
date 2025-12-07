<template>
    <div class="bg-surface min-h-screen pt-20 pb-12">
        <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <span class="text-primary font-semibold">{{ blog.category }}</span>
                <h1 class="text-4xl sm:text-5xl font-bold text-white mt-2 mb-4">{{ blog.title }}</h1>
                <div class="flex items-center text-gray-500">
                    <span>By {{ blog.author }}</span>
                    <span class="mx-2">•</span>
                    <span>{{ blog.date }}</span>
                </div>
            </div>

            <div class="rounded-xl overflow-hidden mb-10 h-[400px]">
                <img :src="blog.image" :alt="blog.title" class="w-full h-full object-cover">
            </div>

            <div class="prose prose-lg max-w-none text-slate-300">
                <p class="lead text-xl mb-6 font-medium">{{ blog.excerpt }}</p>
                <div v-html="blog.content"></div>
            </div>
        </article>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// Mock data fetch based on ID
const blog = ref({
    id: 1,
    title: 'Top 10 Gadgets to Import in 2024',
    category: 'Technology',
    author: 'Admin',
    date: 'Nov 18, 2024',
    excerpt: 'Discover the hottest tech gadgets that are trending globally and how you can get them easily. From smart home devices to the latest wearables, we cover it all.',
    image: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
    content: `
        <p class="mb-4">Technology evolves rapidly, and getting your hands on the latest gadgets can be a challenge if they aren't available in your local market. That's where we come in. We've curated a list of the top 10 gadgets that are making waves internationally.</p>
        <h2 class="text-2xl font-bold mb-4 mt-8">1. Smart Home Hubs</h2>
        <p class="mb-4">The center of any modern home. The latest hubs from Google and Amazon offer seamless integration with thousands of devices.</p>
        <h2 class="text-2xl font-bold mb-4 mt-8">2. Next-Gen Wearables</h2>
        <p class="mb-4">From fitness trackers to smart glasses, wearable tech is becoming more integrated into our daily lives.</p>
        <p class="mb-4">...</p>
        <p class="mt-8">Ready to upgrade your tech game? Request these products today through our platform and get them delivered to your doorstep!</p>
    `
});

onMounted(() => {
    // In real app, fetch blog by route.params.id
    console.log('Blog ID:', route.params.id);
});
</script>
