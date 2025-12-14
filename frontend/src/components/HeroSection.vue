<template>
    <section class="relative w-full overflow-hidden group">
        <swiper :modules="modules" :slides-per-view="1" :space-between="0" :effect="'fade'" :loop="true" :autoplay="{
            delay: 5000,
            disableOnInteraction: false,
        }" :pagination="{
                clickable: true,
                dynamicBullets: true,
            }" :navigation="{
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            }" class="w-full h-auto">
            <swiper-slide v-for="(slide, index) in slides" :key="index">
                <!-- Desktop Banner -->
                <img :src="slide.desktop" :alt="slide.alt" class="hidden lg:block w-full h-auto object-cover">
                <!-- Mobile Banner -->
                <img :src="slide.mobile" :alt="slide.alt" class="block lg:hidden w-full h-auto object-cover">
            </swiper-slide>

            <!-- Custom Navigation Arrows -->
            <div
                class="swiper-button-prev absolute left-4 top-1/2 -translate-y-1/2 !text-white !w-10 !h-10 rounded-full bg-black/20 hover:bg-primary/80 transition-all after:!content-[''] z-10 opacity-0 group-hover:opacity-100 flex items-center justify-center">
                <ChevronLeft class="w-6 h-6" />
            </div>
            <div
                class="swiper-button-next absolute right-4 top-1/2 -translate-y-1/2 !text-white !w-10 !h-10 rounded-full bg-black/20 hover:bg-primary/80 transition-all after:!content-[''] z-10 opacity-0 group-hover:opacity-100 flex items-center justify-center">
                <ChevronRight class="w-6 h-6" />
            </div>
        </swiper>
    </section>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination, Navigation, EffectFade } from 'swiper/modules';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';

const modules = [Autoplay, Pagination, Navigation, EffectFade];

const slides = [
    {
        desktop: '/assets/large-image.png',
        mobile: '/assets/luxbeyond-mobile.png',
        alt: 'Global Shopping Simplified'
    },
    {
        desktop: '/assets/hero-2.png',
        mobile: '/assets/hero-2-mobile.png',
        alt: 'Shop from Anywhere'
    }
];
</script>

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
