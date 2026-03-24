<script setup>
import { onMounted, ref, computed } from 'vue';
import { useCmsStore } from '@/stores/cms';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination, Navigation, EffectFade } from 'swiper/modules';
import { ChevronLeft, ChevronRight, ArrowRight } from 'lucide-vue-next';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';

const cmsStore = useCmsStore();
const modules = [Autoplay, Pagination, Navigation, EffectFade];
const loading = ref(true);

const banner = computed(() => cmsStore.getPage('hero-banner'));
const slides = computed(() => banner.value?.content?.slides || []);

onMounted(async () => {
    loading.value = true;
    try {
        await cmsStore.fetchPage('hero-banner');
    } catch (error) {
        console.error('Error fetching hero banner:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <section class="relative w-full overflow-hidden group min-h-[400px] bg-zinc-900">
        <!-- Skeleton Loading -->
        <div v-if="loading" class="w-full h-[400px] lg:h-[600px] animate-pulse bg-zinc-800 flex items-center justify-center">
             <div class="space-y-4 text-center">
                 <div class="h-8 w-64 bg-zinc-700 mx-auto rounded"></div>
                 <div class="h-4 w-48 bg-zinc-700 mx-auto rounded"></div>
             </div>
        </div>
        <section v-else>
            <swiper v-if="banner?.is_active" :modules="modules" :slides-per-view="1" :space-between="0" :effect="'fade'" :loop="slides.length > 1" :autoplay="{
                delay: 6000,
                disableOnInteraction: false,
            }" :pagination="{
                    clickable: true,
                    dynamicBullets: true,
                }" :navigation="{
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                }" class="w-full h-[400px] lg:h-[600px]">
                
                <swiper-slide v-for="(slide, index) in slides" :key="index" class="h-full">
                    <div class="relative w-full h-full overflow-hidden bg-zinc-900">
                        <!-- Desktop Banner -->
                        <img :src="slide.desktop" :alt="slide.alt" 
                            class="hidden lg:block w-full h-full object-cover">
                        <!-- Mobile Banner -->
                        <img :src="slide.mobile || slide.desktop" :alt="slide.alt" 
                            class="block lg:hidden w-full h-full object-cover">
                        
                        <!-- Overlay Content -->
                        <div v-if="slide.heading || slide.subheading" class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent flex items-center">
                            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                                <div class="max-w-2xl space-y-6">
                                    <h1 v-if="slide.heading" class="text-4xl md:text-6xl font-bold text-white leading-tight drop-shadow-lg">
                                        {{ slide.heading }}
                                    </h1>
                                    <p v-if="slide.subheading" class="text-lg md:text-xl text-zinc-200 drop-shadow-md">
                                        {{ slide.subheading }}
                                    </p>
                                    <div v-if="slide.cta_text" class="pt-4">
                                        <router-link :to="slide.cta_link || '/shop'" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-black font-bold rounded-full hover:opacity-90 transition-all hover:gap-4 scale-100 active:scale-95">
                                            {{ slide.cta_text }}
                                            <ArrowRight class="w-5 h-5" />
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
    
                <!-- Custom Navigation Arrows -->
                <div v-if="slides.length > 1"
                    class="swiper-button-prev absolute left-4 top-1/2 -translate-y-1/2 !text-white !w-12 !h-12 rounded-full bg-black/30 backdrop-blur-md border border-white/10 hover:bg-primary/80 transition-all after:!content-[''] z-10 opacity-0 group-hover:opacity-100 flex items-center justify-center">
                    <ChevronLeft class="w-6 h-6" />
                </div>
                <div v-if="slides.length > 1"
                    class="swiper-button-next absolute right-4 top-1/2 -translate-y-1/2 !text-white !w-12 !h-12 rounded-full bg-black/30 backdrop-blur-md border border-white/10 hover:bg-primary/80 transition-all after:!content-[''] z-10 opacity-0 group-hover:opacity-100 flex items-center justify-center">
                    <ChevronRight class="w-6 h-6" />
                </div>
            </swiper>
        </section>

    </section>
</template>

<style>
/* Custom Pagination Styles */
.swiper-pagination-bullet {
    background: rgba(255, 255, 255, 0.5);
    opacity: 1;
    transition: all 0.3s ease;
}

.swiper-pagination-bullet-active {
    background: #C8AE7D;
    /* Using Primary Color */
    width: 20px;
    border-radius: 4px;
}
</style>
