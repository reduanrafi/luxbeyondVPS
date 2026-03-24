<template>
    <div class="min-h-screen bg-background">
        <!-- Header placeholder for page titles -->
        <div class="bg-surface border-b border-white/5 py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="loading" class="h-8 w-64 bg-white/10 animate-pulse rounded" />
                <h1 v-else class="text-3xl font-bold text-white">
                    {{ page?.content?.heading || page?.title || '' }}
                </h1>
            </div>
        </div>

        <!-- Page Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Loading Skeleton -->
            <div v-if="loading" class="space-y-4 animate-pulse">
                <div class="h-4 bg-white/10 rounded w-full" />
                <div class="h-4 bg-white/10 rounded w-5/6" />
                <div class="h-4 bg-white/10 rounded w-4/6" />
                <div class="h-4 bg-white/10 rounded w-full mt-8" />
                <div class="h-4 bg-white/10 rounded w-3/4" />
            </div>

            <!-- Not Found -->
            <div v-else-if="notFound" class="text-center py-24">
                <p class="text-6xl mb-4">📄</p>
                <h2 class="text-2xl font-bold text-white mb-3">Page Not Found</h2>
                <p class="text-zinc-400 mb-8">This page doesn't exist or has been removed.</p>
                <router-link to="/"
                    class="px-6 py-3 bg-primary text-black font-bold rounded-full hover:opacity-90 transition-opacity">
                    Go Home
                </router-link>
            </div>

            <!-- Rendered Content -->
            <article v-else
                class="prose prose-invert prose-headings:font-bold prose-headings:text-white prose-p:text-zinc-300 prose-li:text-zinc-300 prose-strong:text-white max-w-none"
                v-html="page?.content?.body" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useCmsStore } from '@/stores/cms';

const props = defineProps({
    slug: { type: String, default: null }
});

const route = useRoute();
const cmsStore = useCmsStore();

const page = ref(null);
const loading = ref(true);
const notFound = ref(false);

const loadPage = async () => {
    const key = props.slug || route.params.slug;
    if (!key) return;

    loading.value = true;
    notFound.value = false;

    try {
        await cmsStore.fetchPage(key);
        page.value = cmsStore.getPage(key);
        if (!page.value) {
            notFound.value = true;
        } else {
            // Update document meta
            if (page.value.content?.meta_title) {
                document.title = page.value.content.meta_title;
            }
            const metaDesc = document.querySelector('meta[name="description"]');
            if (metaDesc && page.value.content?.meta_description) {
                metaDesc.setAttribute('content', page.value.content.meta_description);
            }
        }
    } catch (e) {
        notFound.value = true;
    } finally {
        loading.value = false;
    }
};

onMounted(loadPage);
watch(() => props.slug, loadPage);
watch(() => route.params.slug, loadPage);
</script>
