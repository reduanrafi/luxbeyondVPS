<template>
    <section class="relative w-full overflow-hidden group">
        <!-- Slides -->
        <div class="relative w-full h-auto">
            <div v-for="(slide, index) in slides" :key="index"
                class="transition-opacity duration-700 ease-in-out w-full"
                :class="{ 'opacity-100 relative': currentSlide === index, 'opacity-0 absolute top-0 left-0': currentSlide !== index }">

                <!-- Desktop Banner -->
                <img :src="slide.desktop" :alt="slide.alt" class="hidden lg:block w-full h-auto object-cover">

                <!-- Mobile Banner -->
                <img :src="slide.mobile" :alt="slide.alt" class="block lg:hidden w-full h-auto object-cover">
            </div>
        </div>

        <!-- Navigation Arrows -->
        <button @click="prevSlide"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/50 hover:bg-white text-slate-800 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 z-10">
            <ChevronLeft class="w-6 h-6" />
        </button>
        <button @click="nextSlide"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/50 hover:bg-white text-slate-800 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 z-10">
            <ChevronRight class="w-6 h-6" />
        </button>

        <!-- Dots -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
            <button v-for="(slide, index) in slides" :key="index" @click="goToSlide(index)"
                class="w-3 h-3 rounded-full transition-all duration-300"
                :class="currentSlide === index ? 'bg-primary scale-110' : 'bg-white/60 hover:bg-white'">
            </button>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const currentSlide = ref(0);
const autoplayInterval = ref(null);

const slides = [
    {
        desktop: '/assets/large-image.png',
        mobile: '/assets/luxbeyond-mobile.png',
        alt: 'Global Shopping Simplified'
    },
    {
        desktop: '/assets/hero-2.png', // Placeholder for second slide
        mobile: '/assets/hero-2-mobile.png',
        alt: 'Shop from Anywhere'
    }
];

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.length;
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

const startAutoplay = () => {
    autoplayInterval.value = setInterval(nextSlide, 5000); // Change slide every 5 seconds
};

const stopAutoplay = () => {
    if (autoplayInterval.value) {
        clearInterval(autoplayInterval.value);
    }
};

onMounted(() => {
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>
