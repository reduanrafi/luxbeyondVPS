<template>
    <section id="blogs" class="py-24 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl mb-4">Latest News</h2>
                <p class="text-xl text-slate-500 max-w-2xl mx-auto">Insights, trends, and updates from our team.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <Card v-for="blog in blogs" :key="blog.id" bodyClass="p-0"
                    class="group h-full flex flex-col cursor-pointer hover:-translate-y-1 transition-transform duration-300">
                    <template #header>
                        <div class="overflow-hidden h-48 relative">
                            <img :src="blog.image_url || '/assets/blog-placeholder.jpg'" :alt="blog.title"
                                class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                            <div class="absolute top-0 right-0">
                                <span
                                    class="bg-primary text-slate-900 text-[10px] font-bold px-3 py-1 uppercase tracking-wider">
                                    {{ blog.category || 'News' }}
                                </span>
                            </div>
                        </div>
                    </template>
                    <div class="p-6 flex-1 flex flex-col text-left">
                        <h3
                            class="text-xl font-serif text-white uppercase tracking-wider mb-3 group-hover:text-primary transition-colors">
                            {{ blog.title }}</h3>
                        <p class="text-slate-400 text-sm line-clamp-3 mb-6 flex-1" v-html="blog.excerpt || blog.content?.substring(0, 100) + '...'"></p>
                        <div class="mt-auto">
                            <router-link :to="`/blogs/${blog.slug}`"
                                class="flex items-center gap-2 text-sm text-white font-bold uppercase tracking-widest hover:text-primary transition-colors group/btn">
                                Read More
                                <svg class="ml-1 w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </router-link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Card from './ui/Card.vue';
import axios from '../axios';

const blogs = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/blogs/recent');
        blogs.value = response.data;
    } catch (error) {
        console.error('Error fetching recent blogs:', error);
    }
});
</script>
