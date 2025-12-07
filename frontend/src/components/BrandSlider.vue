<template>
    <div v-if="brands.length > 0" class="py-12 bg-surface border-y border-white/5 overflow-hidden">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-serif text-primary uppercase tracking-[0.2em] mb-4 drop-shadow-sm">World Class
                    Brands</h2>
                <div class="flex items-center justify-center gap-4 mb-4">
                    <span class="h-px w-12 bg-gradient-to-r from-transparent to-primary/50"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-primary/80"></span>
                    <span class="h-px w-12 bg-gradient-to-l from-transparent to-primary/50"></span>
                </div>
                <p class="text-slate-400 font-light tracking-widest text-xs uppercase opacity-80">Curated excellence
                    from the world's finest houses</p>
            </div>

            <Vue3Marquee :clone="true" :duration="40" :pause-on-hover="true" class="py-4 overflow-hidden">
                <div v-for="brand in brands" :key="brand.id"
                    class="mx-12 flex items-center justify-center min-w-[150px]">
                    <!-- Image Logo with Gold Hover -->
                    <div v-if="brand.image_url" class="relative group/brand transition-all duration-300">
                        <!-- Ghost Image (Preserves Aspect Ratio) -->
                        <img :src="getImageUrl(brand.image_url)" :alt="brand.name" class="h-16 w-auto opacity-0"
                            aria-hidden="true" />

                        <!-- Color Layer (Masked by Logo) -->
                        <div class="absolute inset-0 bg-slate-500 group-hover/brand:bg-primary transition-colors duration-300"
                            :style="{
                                '-webkit-mask-image': `url(${getImageUrl(brand.image_url)})`,
                                'mask-image': `url(${getImageUrl(brand.image_url)})`,
                                '-webkit-mask-size': 'contain',
                                'mask-size': 'contain',
                                '-webkit-mask-repeat': 'no-repeat',
                                'mask-repeat': 'no-repeat',
                                '-webkit-mask-position': 'center',
                                'mask-position': 'center'
                            }">
                        </div>
                    </div>

                    <!-- Text Fallback -->
                    <span v-else
                        class="text-2xl font-serif text-slate-500 hover:text-primary transition-colors duration-300 font-bold uppercase cursor-default">
                        {{ brand.name }}
                    </span>
                </div>
            </Vue3Marquee>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Vue3Marquee } from 'vue3-marquee';
import axios from '../axios';

const brands = ref([]);

const getImageUrl = (url) => {
    if (!url) return '';
    if (url.startsWith('http')) {
        return url.replace('http://127.0.0.1:8000', '').replace('http://localhost:8000', '');
    }
    return url;
};

const fetchBrands = async () => {
    try {
        const response = await axios.get('/brands', { params: { all: true } });
        console.log(response.data);
        brands.value = response.data;
    } catch (error) {
        console.error('Error fetching brands:', error);
    }
};

onMounted(() => {
    fetchBrands();
});
</script>

<style scoped>
.animate-scroll {
    animation: scroll 30s linear infinite;
}

@keyframes scroll {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-50%);
    }
}
</style>
