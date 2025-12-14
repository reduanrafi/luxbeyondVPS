<template>
    <div class="min-h-screen bg-black pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Our Blog</h1>
                <p class="text-zinc-400 text-lg max-w-2xl mx-auto">
                    Stay updated with the latest news, trends, and stories from Luxbeyond.
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="n in 6" :key="n" class="bg-zinc-900 rounded-2xl overflow-hidden animate-pulse">
                    <div class="h-48 bg-zinc-800"></div>
                    <div class="p-6 space-y-4">
                        <div class="h-4 bg-zinc-800 rounded w-3/4"></div>
                        <div class="h-4 bg-zinc-800 rounded w-1/2"></div>
                    </div>
                </div>
            </div>

            <!-- Blog Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <router-link v-for="post in posts" :key="post.id" 
                    :to="`/blogs/${post.slug}`"
                    class="group bg-zinc-900 rounded-2xl overflow-hidden border border-white/5 hover:border-amber-500/50 transition-all hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/10">
                    
                    <!-- Image -->
                    <div class="relative h-56 overflow-hidden">
                        <img :src="post.image_url || '/assets/blog-placeholder.jpg'" 
                            :alt="post.title"
                            class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 bg-black/60 backdrop-blur-md text-white text-xs font-semibold rounded-full border border-white/10">
                                {{ formatDate(post.published_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:text-amber-500 transition-colors">
                            {{ post.title }}
                        </h2>
                        <p class="text-zinc-400 text-sm line-clamp-3 mb-4">
                            {{ post.excerpt || stripHtml(post.content) }}
                        </p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-white/5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold text-xs">
                                    {{ getInitials(post.author?.name) }}
                                </div>
                                <span class="text-sm text-zinc-300">{{ post.author?.name || 'Admin' }}</span>
                            </div>
                            <span class="text-xs text-amber-500 font-medium flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                Read More <ArrowRight class="w-4 h-4" />
                            </span>
                        </div>
                    </div>
                </router-link>
            </div>

            <!-- Empty State -->
            <div v-if="!loading && posts.length === 0" class="text-center py-20">
                <div class="bg-zinc-900/50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                    <FileText class="w-10 h-10 text-zinc-600" />
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No posts found</h3>
                <p class="text-zinc-500">Check back later for new articles.</p>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="mt-16 flex justify-center gap-2">
                <button @click="fetchPosts(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-2 bg-zinc-900 border border-white/10 rounded-lg text-white hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Previous
                </button>
                <button @click="fetchPosts(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ArrowRight, FileText } from 'lucide-vue-next';
import axios from '../axios';

const posts = ref([]);
const loading = ref(true);
const pagination = ref({
    current_page: 1,
    last_page: 1
});

const fetchPosts = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/blogs', { params: { page } });
        posts.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page
        };
    } catch (error) {
        console.error('Error fetching posts:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getInitials = (name) => {
    if (!name) return 'A';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const stripHtml = (html) => {
    const tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
};

onMounted(() => {
    fetchPosts();
});
</script>
