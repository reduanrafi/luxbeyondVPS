<template>
    <section class="py-24 bg-background">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-serif text-primary sm:text-4xl mb-4 uppercase tracking-widest">What Our
                    Customers Say</h2>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto">Trusted by thousands of happy shoppers.</p>
            </div>

            <swiper :modules="modules" :slides-per-view="1" :space-between="32" :loop="true" :autoplay="{
                delay: 3000,
                disableOnInteraction: false,
            }" :pagination="{
                    clickable: true,
                    dynamicBullets: true,
                }" :breakpoints="{
                    '640': {
                        slidesPerView: 1,
                        spaceBetween: 20,
                    },
                    '768': {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    '1024': {
                        slidesPerView: 3,
                        spaceBetween: 32,
                    },
                }" class="w-full pb-14">
                <swiper-slide v-for="review in reviews" :key="review.id" class="h-auto">
                    <Card
                        class="h-full bg-surface border border-white/10 hover:border-primary/30 transition-colors p-8">
                        <div class="flex items-center mb-6">
                            <img :src="review.avatar" :alt="review.name"
                                class="w-14 h-14 rounded-full mr-4 border-2 border-primary/20 shadow-sm object-contain">
                            <div>
                                <h4 class="text-lg font-serif text-white uppercase tracking-wide">{{ review.name }}</h4>
                                <div class="flex text-primary text-sm gap-0.5">
                                    <span v-for="i in 5" :key="i">★</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-300 italic leading-relaxed text-lg">"{{ review.comment }}"</p>
                    </Card>
                </swiper-slide>
            </swiper>
        </div>
    </section>
</template>

<script setup>
import Card from './ui/Card.vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/pagination';

const modules = [Autoplay, Pagination];

const reviews = [
    { id: 1, name: 'Sarah Ahmed', comment: 'Incredible service! I got my makeup products from Sephora US within 2 weeks. Highly recommended.', avatar: 'https://randomuser.me/api/portraits/women/44.jpg' },
    { id: 2, name: 'Rahim Uddin', comment: 'The best way to shop from Amazon and AliExpress without any hassle. The custom request feature is a game changer.', avatar: 'https://randomuser.me/api/portraits/men/32.jpg' },
    { id: 3, name: 'Nusrat Jahan', comment: 'Authentic products and transparent pricing. I love how they handle all the customs and shipping for me.', avatar: 'https://randomuser.me/api/portraits/women/68.jpg' },
    { id: 4, name: 'Tanvir Hasan', comment: 'Super fast delivery and excellent customer support. They kept me updated throughout the process.', avatar: 'https://randomuser.me/api/portraits/men/45.jpg' },
    { id: 5, name: 'Farhana Akter', comment: 'I ordered a laptop from Best Buy via LuxBeyond. It arrived safely and much cheaper than local market price.', avatar: 'https://randomuser.me/api/portraits/women/22.jpg' },
];
</script>

<style scoped>
:deep(.swiper-pagination-bullet) {
    background: rgba(255, 255, 255, 0.2);
    opacity: 1;
    width: 10px;
    height: 10px;
    margin: 0 6px !important;
}

:deep(.swiper-pagination-bullet-active) {
    background: #C8AE7D;
    transform: scale(1.2);
}
</style>
