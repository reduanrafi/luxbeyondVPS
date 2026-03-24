<template>
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <div class="bg-surface border-b border-white/5 py-12 md:py-20 relative overflow-hidden">
            <!-- Background Gradient Overlay -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary/10 via-purple-900/10 to-secondary/10 opacity-30 pointer-events-none">
            </div>
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">How can we help you?</h1>
                <p class="text-[#00ffff] text-lg max-w-2xl mx-auto">
                    Find answers to common questions or reach out to our dedicated support team directly.
                </p>

                <!-- Search box -->
                <div class="mt-8 max-w-xl mx-auto relative group">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-zinc-400 group-focus-within:text-primary transition-colors" />
                    <input v-model="searchQuery" type="text" placeholder="Search for answers..."
                        class="w-full pl-12 pr-4 py-4 bg-zinc-900/80 border border-white/10 rounded-full text-white placeholder:text-zinc-500 focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all shadow-xl backdrop-blur-sm">
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <!-- Loading State -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-4"></div>
                <p class="text-zinc-400">Loading help center...</p>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <!-- Main Content: FAQs -->
                <div class="lg:col-span-2 space-y-10">
                    <div v-if="filteredSections.length > 0" class="space-y-10">
                        <section v-for="(section, sIndex) in filteredSections" :key="sIndex">
                            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                                <component :is="getIcon(section.icon)" class="w-6 h-6 text-primary" />
                                {{ section.title }}
                            </h2>
                            <div class="space-y-4">
                                <details v-for="(faq, index) in section.faqs" :key="index" :open="searchQuery.length > 0" class="group bg-surface rounded-2xl border border-white/5 overflow-hidden transition-all duration-300 hover:border-primary/20">
                                    <summary class="flex items-center justify-between p-5 cursor-pointer list-none font-medium text-white select-none">
                                        {{ faq.q }}
                                        <ChevronDown class="w-5 h-5 text-zinc-400 group-open:rotate-180 transition-transform duration-300" />
                                    </summary>
                                    <div class="px-5 pb-5 text-zinc-400 text-sm leading-relaxed" v-html="faq.a"></div>
                                </details>
                            </div>
                        </section>
                    </div>

                    <!-- No Results -->
                    <div v-else class="text-center py-20 bg-surface rounded-3xl border border-white/5">
                        <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6">
                            <Search class="w-10 h-10 text-zinc-600" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">No results found</h3>
                        <p class="text-zinc-400 max-w-sm mx-auto">
                            We couldn't find any answers for "{{ searchQuery }}". Try different keywords or contact our support team.
                        </p>
                        <button @click="searchQuery = ''" class="mt-6 text-primary font-medium hover:underline">
                            Clear search
                        </button>
                    </div>
                </div>

                <!-- Sidebar: Contact Support -->
                <div class="space-y-6">
                    <!-- Contact Card -->
                    <div class="bg-surface rounded-2xl border border-primary/20 p-6 shadow-[0_0_30px_rgba(212,175,55,0.05)] relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
                        
                        <h3 class="text-xl font-bold text-white mb-2">Still need help?</h3>
                        <p class="text-zinc-400 text-sm mb-6">Our dedicated support team is ready to assist you.</p>
                        
                        <div class="space-y-4 mb-6" v-if="page?.content?.contact">
                            <a v-if="page.content.contact.email" :href="`mailto:${page.content.contact.email}`" class="flex items-center gap-3 text-[#00ffff] hover:text-white transition-colors group/link">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center group-hover/link:bg-primary/20 group-hover/link:text-primary transition-colors">
                                    <Mail class="w-5 h-5" />
                                </div>
                                <span class="text-sm font-medium">{{ page.content.contact.email }}</span>
                            </a>
                            <a v-if="page.content.contact.phone" :href="`tel:${page.content.contact.phone.replace(/[^0-9+]/g, '')}`" class="flex items-center gap-3 text-[#00ffff] hover:text-white transition-colors group/link">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center group-hover/link:bg-primary/20 group-hover/link:text-primary transition-colors">
                                    <Phone class="w-5 h-5" />
                                </div>
                                <span class="text-sm font-medium">{{ page.content.contact.phone }}</span>
                            </a>
                            <a v-if="page.content.contact.facebook" :href="page.content.contact.facebook" target="_blank" class="flex items-center gap-3 text-[#00ffff] hover:text-white transition-colors group/link">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center group-hover/link:bg-primary/20 group-hover/link:text-primary transition-colors">
                                    <MessageCircle class="w-5 h-5" />
                                </div>
                                <span class="text-sm font-medium">Message on Facebook</span>
                            </a>
                        </div>
                        
                        <router-link to="/request-product" class="block w-full py-3 px-4 bg-primary text-black font-bold text-center rounded-xl hover:opacity-90 transition-opacity">
                            Request a Product Instead
                        </router-link>
                    </div>

                    <!-- Policy Links -->
                    <div class="bg-surface rounded-2xl border border-white/5 p-6">
                        <h3 class="text-lg font-bold text-white mb-4">Important Policies</h3>
                        <ul class="space-y-3">
                            <li>
                                <router-link to="/refund-policy" class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors text-sm text-zinc-300 hover:text-white">
                                    <div class="flex items-center gap-3">
                                        <ShieldCheck class="w-4 h-4 text-primary" />
                                        Refund Policy
                                    </div>
                                    <ArrowRight class="w-4 h-4 text-zinc-500" />
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/privacy-policy" class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors text-sm text-zinc-300 hover:text-white">
                                    <div class="flex items-center gap-3">
                                        <Lock class="w-4 h-4 text-primary" />
                                        Privacy Policy
                                    </div>
                                    <ArrowRight class="w-4 h-4 text-zinc-500" />
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/terms-of-service" class="flex items-center justify-between p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors text-sm text-zinc-300 hover:text-white">
                                    <div class="flex items-center gap-3">
                                        <FileText class="w-4 h-4 text-primary" />
                                        Terms of Service
                                    </div>
                                    <ArrowRight class="w-4 h-4 text-zinc-500" />
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useCmsStore } from '@/stores/cms';
import { 
    Search, 
    ChevronDown, 
    Info, 
    Package, 
    CreditCard, 
    Mail, 
    Phone,
    MessageCircle, 
    ShieldCheck, 
    Lock, 
    FileText, 
    ArrowRight 
} from 'lucide-vue-next';

const cmsStore = useCmsStore();
const page = ref(null);
const loading = ref(true);
const searchQuery = ref('');

const loadPage = async () => {
    loading.value = true;
    try {
        await cmsStore.fetchPage('help-center');
        page.value = cmsStore.getPage('help-center');
    } catch (error) {
        console.error('Error loading help center:', error);
    } finally {
        loading.value = false;
    }
};

const filteredSections = computed(() => {
    if (!page.value?.content?.sections) return [];
    if (!searchQuery.value) return page.value.content.sections;

    const query = searchQuery.value.toLowerCase();
    
    return page.value.content.sections.map(section => {
        const filteredFaqs = section.faqs.filter(faq => 
            faq.q.toLowerCase().includes(query) || 
            faq.a.toLowerCase().includes(query)
        );
        
        return { ...section, faqs: filteredFaqs };
    }).filter(section => section.faqs.length > 0);
});

const getIcon = (iconName) => {
    const icons = { Info, Package, CreditCard };
    return icons[iconName] || Info;
};

onMounted(loadPage);
</script>

<style scoped>
details > summary {
  list-style: none;
}
details > summary::-webkit-details-marker {
  display: none;
}
</style>
