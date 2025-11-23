<template>
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl mb-4">What Our Customers Say</h2>
                <p class="text-xl text-slate-500 max-w-2xl mx-auto">Trusted by thousands of happy shoppers.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <Card v-for="review in reviews" :key="review.id" class="h-full">
                    <div class="flex items-center mb-6">
                        <img :src="review.avatar" :alt="review.name" class="w-14 h-14 rounded-full mr-4 border-2 border-white shadow-sm">
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">{{ review.name }}</h4>
                            <div class="flex text-yellow-400 text-sm">
                                <span v-for="i in 5" :key="i">★</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-slate-600 italic leading-relaxed">"{{ review.comment }}"</p>
                </Card>
            </div>
        </div>
    </section>
</template>

<script setup>
import Card from './ui/Card.vue';

const reviews = [
    { id: 1, name: 'Sarah Ahmed', comment: 'Incredible service! I got my makeup products from Sephora US within 2 weeks. Highly recommended.', avatar: 'https://randomuser.me/api/portraits/women/44.jpg' },
    { id: 2, name: 'Rahim Uddin', comment: 'The best way to shop from Amazon and AliExpress without any hassle. The custom request feature is a game changer.', avatar: 'https://randomuser.me/api/portraits/men/32.jpg' },
    { id: 3, name: 'Nusrat Jahan', comment: 'Authentic products and transparent pricing. I love how they handle all the customs and shipping for me.', avatar: 'https://randomuser.me/api/portraits/women/68.jpg' },
];
</script>
