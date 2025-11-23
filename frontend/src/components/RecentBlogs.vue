<template>
    <section id="blogs" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl mb-4">Latest News</h2>
                <p class="text-xl text-slate-500 max-w-2xl mx-auto">Insights, trends, and updates from our team.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <Card v-for="blog in blogs" :key="blog.id" bodyClass="p-0" class="group h-full flex flex-col cursor-pointer hover:-translate-y-1 transition-transform duration-300">
                    <template #header>
                        <div class="overflow-hidden h-48 relative">
                            <img :src="blog.image" :alt="blog.title" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/90 backdrop-blur-sm text-primary text-xs font-bold px-3 py-1 rounded-full shadow-sm">{{ blog.category }}</span>
                            </div>
                        </div>
                    </template>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary transition-colors">{{ blog.title }}</h3>
                        <p class="text-slate-500 text-sm line-clamp-3 mb-6 flex-1">{{ blog.excerpt }}</p>
                        <div class="mt-auto">
                            <router-link :to="`/blog/${blog.id}`" class="inline-flex items-center text-sm font-semibold text-primary hover:text-blue-700 transition-colors">
                                Read More 
                                <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
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
import Card from './ui/Card.vue';

const blogs = [
    { id: 1, title: 'Top 10 Gadgets to Import in 2024', category: 'Technology', excerpt: 'Discover the hottest tech gadgets that are trending globally and how you can get them easily.', image: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 2, title: 'Understanding Customs & Shipping Duties', category: 'Guide', excerpt: 'A comprehensive guide to understanding how shipping charges and customs duties are calculated.', image: 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 3, title: 'Why Authenticity Matters', category: 'Lifestyle', excerpt: 'We ensure every product you request is 100% authentic and sourced directly from official stores.', image: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
];
</script>
