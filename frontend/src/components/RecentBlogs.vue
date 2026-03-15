<template>
    <section id="blogs" class="py-24 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white sm:text-4xl mb-4">Latest News</h2>
                <p class="text-xl text-[#00ffff] max-w-2xl mx-auto">Insights, trends, and updates from our team.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <template v-if="loading">
                    <div v-for="i in 3" :key="i"
                        class="bg-surface rounded-lg overflow-hidden animate-pulse border border-white/5">
                        <div class="h-48 bg-white/5 w-full"></div>
                        <div class="p-6">
                            <div class="h-6 bg-white/5 rounded w-3/4 mb-4"></div>
                            <div class="space-y-2 mb-6">
                                <div class="h-4 bg-white/5 rounded w-full"></div>
                                <div class="h-4 bg-white/5 rounded w-full"></div>
                                <div class="h-4 bg-white/5 rounded w-2/3"></div>
                            </div>
                            <div class="h-4 bg-white/5 rounded w-24"></div>
                        </div>
                    </div>
                </template>

                <Card v-else v-for="blog in blogs" :key="blog.id" bodyClass="p-0"
                    class="group h-full flex flex-col cursor-pointer hover:-translate-y-1 transition-transform duration-300">
                    <template #header>
                        <div class="overflow-hidden h-48 relative">
                            <img :src="blog.image_url || '/assets/blog-placeholder.jpg'" :alt="blog.title"
                                class="w-full h-full object-contain transform group-hover:scale-105 transition duration-500">
                            <div class="absolute top-0 right-0">
                                <span
                                    class="bg-[#00ffff] text-black shadow text-[10px] font-bold px-3 py-1 uppercase tracking-wider">
                                    {{ blog.category || 'News' }}
                                </span>
                            </div>
                        </div>
                    </template>
                    <div class="p-6 flex-1 flex flex-col text-left">
                        <h3
                            class="text-xl font-serif text-white uppercase tracking-wider mb-3 group-hover:text-primary transition-colors">
                            {{ blog.title }}</h3>
                        <p class="text-sm line-clamp-3 mb-6 flex-1"
                            v-html="blog.excerpt || blog.content?.substring(0, 100) + '...'">
                        </p>
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
const loading = ref(true);

onMounted(async () => {
    try {
        loading.value = true;
        const response = await axios.get('/blogs/recent');
        blogs.value = response.data;
    } catch (error) {
        console.error('Error fetching recent blogs:', error);
    } finally {
        loading.value = false;
    }
});
</script>
