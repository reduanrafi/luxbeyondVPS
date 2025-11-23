<template>
    <div class="bg-white min-h-screen pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Our Blog</h1>
                <p class="text-xl text-gray-600">Latest news, tips, and insights from the world of global shopping.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="blog in blogs" :key="blog.id" class="group cursor-pointer">
                    <router-link :to="`/blog/${blog.id}`">
                        <div class="rounded-xl overflow-hidden shadow-lg mb-4 h-64">
                            <img :src="blog.image" :alt="blog.title" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        </div>
                        <div class="px-2">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <span class="text-primary font-semibold mr-2">{{ blog.category }}</span>
                                <span>• {{ blog.date }}</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mt-2 group-hover:text-primary transition mb-3">{{ blog.title }}</h3>
                            <p class="text-gray-600 line-clamp-3">{{ blog.excerpt }}</p>
                            <span class="inline-block mt-4 text-primary font-semibold hover:underline">Read Article &rarr;</span>
                        </div>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const blogs = [
    { id: 1, title: 'Top 10 Gadgets to Import in 2024', category: 'Technology', date: 'Nov 18, 2024', excerpt: 'Discover the hottest tech gadgets that are trending globally and how you can get them easily. From smart home devices to the latest wearables, we cover it all.', image: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 2, title: 'Understanding Customs & Shipping Duties', category: 'Guide', date: 'Nov 15, 2024', excerpt: 'A comprehensive guide to understanding how shipping charges and customs duties are calculated. Avoid surprises and plan your purchases better.', image: 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 3, title: 'Why Authenticity Matters', category: 'Lifestyle', date: 'Nov 10, 2024', excerpt: 'We ensure every product you request is 100% authentic and sourced directly from official stores. Learn about our verification process.', image: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 4, title: 'Best Beauty Products from Sephora US', category: 'Beauty', date: 'Nov 05, 2024', excerpt: 'Get your hands on the exclusive beauty products that are not available locally. Here are our top picks from Sephora US.', image: 'https://images.unsplash.com/photo-1596462502278-27bfdd403348?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 5, title: 'How to Save Money on International Shipping', category: 'Tips', date: 'Oct 28, 2024', excerpt: 'Shipping costs can add up. Here are some practical tips to consolidate your packages and save big on international shipping fees.', image: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
];
</script>
