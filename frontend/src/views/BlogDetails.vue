<template>
    <div class="min-h-screen bg-black pt-20 pb-12">
        <!-- Loading State -->
        <div v-if="loading" class="max-w-4xl mx-auto px-4">
            <div class="h-8 bg-zinc-900 rounded w-3/4 mb-4 animate-pulse"></div>
            <div class="h-96 bg-zinc-900 rounded-2xl mb-8 animate-pulse"></div>
            <div class="space-y-4 animate-pulse">
                <div class="h-4 bg-zinc-900 rounded w-full"></div>
                <div class="h-4 bg-zinc-900 rounded w-full"></div>
                <div class="h-4 bg-zinc-900 rounded w-2/3"></div>
            </div>
        </div>

        <div v-else-if="post" class="max-w-4xl mx-auto px-4">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-sm text-zinc-500 mb-6">
                <router-link to="/" class="hover:text-primary-500 transition-colors">Home</router-link>
                <span>/</span>
                <router-link to="/blogs" class="hover:text-primary-500 transition-colors">Blog</router-link>
                <span>/</span>
                <span class="text-zinc-300 line-clamp-1">{{ post.title }}</span>
            </div>

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl md:text-5xl font-bold text-white mb-6 leading-tight">{{ post.title }}</h1>
                <div class="flex flex-wrap items-center gap-6 text-sm text-zinc-400">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center text-primary-500 font-bold">
                            {{ getInitials(post.author?.name) }}
                        </div>
                        <span class="text-white">{{ post.author?.name || 'Admin' }}</span>
                    </div>
                    <span>{{ formatDate(post.published_at) }}</span>
                    <span>{{ post.views }} views</span>
                </div>
            </div>

            <!-- Featured Image -->
            <div
                class="relative w-full aspect-video rounded-2xl overflow-hidden mb-12 border border-white/10 shadow-2xl">
                <img :src="post.image_url || '/assets/blog-placeholder.jpg'" :alt="post.title"
                    class="w-full h-full object-contain">
            </div>

            <!-- Content -->
            <div
                class="prose prose-invert prose-lg max-w-none prose-headings:font-serif prose-headings:text-primary prose-a:text-primary hover:prose-a:text-white mb-12">
                <div v-html="renderMarkdown(post.content)"></div>
            </div>

            <!-- Tags -->
            <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-2 mb-12">
                <span v-for="tag in post.tags" :key="tag"
                    class="px-3 py-1 bg-zinc-900 border border-white/10 rounded-full text-sm text-zinc-400">
                    #{{ tag }}
                </span>
            </div>

            <!-- Share (Simple implementation) -->
            <div class="border-t border-white/10 pt-8 mt-8">
                <h3 class="text-lg font-bold text-white mb-4">Share this article</h3>
                <div class="flex gap-4">
                    <button @click="copyLink"
                        class="px-4 py-2 bg-white/5 rounded-lg text-white hover:bg-white/10 transition-colors flex items-center gap-2">
                        <Link class="w-4 h-4" />
                        Copy Link
                    </button>
                    <!-- Add social share buttons here if needed -->
                </div>
            </div>
        </div>

        <div v-else class="text-center py-20">
            <h2 class="text-2xl font-bold text-white mb-4">Post not found</h2>
            <router-link to="/blogs" class="text-primary-500 hover:underline">
                Back to Blog
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { Link } from 'lucide-vue-next';
import axios from '../axios';
import { marked } from 'marked';
import { useToast } from "vue-toastification";

const toast = useToast();
// Assuming marked is installed, if not we will use a simple whitespace converter or install it. 
// For now, I'll use a simple computed for v-html if markdown is complex, 
// but since the admin saves generic text/html, direct injection works if sanitized.
// Since this is an internal admin creating content, we trust it slightly more, but let's assume raw HTML for now.

const route = useRoute();
const post = ref(null);
const loading = ref(true);

const fetchPost = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/blogs/${route.params.slug}`);
        post.value = response.data;
    } catch (error) {
        console.error('Error fetching post:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getInitials = (name) => {
    if (!name) return 'A';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const renderMarkdown = (text) => {
    if (!text) return '';
    return marked.parse(text);
};

const copyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    toast.success('Link copied to clipboard!');
};

onMounted(() => {
    fetchPost();
});
</script>
