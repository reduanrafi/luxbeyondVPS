<template>
    <section id="products" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl mb-4">Featured Products</h2>
                <p class="text-xl text-slate-500 max-w-2xl mx-auto">Handpicked items ready for immediate delivery,
                    curated just for you.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <Card v-for="product in products" :key="product.id" bodyClass="p-0" class="group h-full flex flex-col">
                    <template #header>
                        <div
                            class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-200 relative group-hover:opacity-90 transition-opacity">
                            <img :src="product.image" :alt="product.name"
                                class="w-full h-64 object-cover object-center group-hover:scale-105 transition-transform duration-500">
                            <span v-if="product.discount"
                                class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">-{{
                                    product.discount }}%</span>

                            <!-- Wishlist Button Overlay -->
                            <button @click.prevent="wishlistStore.toggleItem(product)"
                                class="absolute top-3 right-3 p-2 bg-white/90 backdrop-blur-sm rounded-full hover:bg-white transition-all duration-200 shadow-sm group-hover:scale-110"
                                :class="wishlistStore.isInWishlist(product.id) ? 'text-secondary' : 'text-slate-400 hover:text-secondary'">
                                <Heart class="w-5 h-5"
                                    :fill="wishlistStore.isInWishlist(product.id) ? 'currentColor' : 'none'" />
                            </button>
                        </div>
                    </template>
                    <div class="p-5 flex-1 flex flex-col">
                        <router-link :to="`/product/${product.id}`">
                            <h3
                                class="text-lg font-bold text-slate-900 mb-1 line-clamp-1 hover:text-primary transition-colors">
                                {{ product.name }}</h3>
                        </router-link>
                        <div class="flex items-baseline gap-2 mb-4">
                            <span class="text-xl font-bold text-primary">৳{{ product.price }}</span>
                            <span v-if="product.oldPrice" class="text-sm text-slate-400 line-through">৳{{
                                product.oldPrice }}</span>
                        </div>
                        <div class="mt-auto">
                            <Button variant="primary" block @click.prevent="cartStore.addItem(product)"
                                class="flex items-center justify-center gap-2 group-hover:bg-primary-hover transition-all">
                                <ShoppingCart class="w-4 h-4" />
                                Add to Cart
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>
            <div class="text-center mt-16">
                <router-link to="/shop">
                    <Button variant="outline" size="lg">View All Products</Button>
                </router-link>
            </div>
        </div>
    </section>
</template>

<script setup>
import Card from './ui/Card.vue';
import Button from './ui/Button.vue';
import { ShoppingCart, Heart } from 'lucide-vue-next';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';

const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const products = [
    { id: 1, name: 'Wireless Noise Cancelling Headphones', price: '12,500', oldPrice: '15,000', discount: 15, image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 2, name: 'Smart Fitness Watch Series 5', price: '4,200', oldPrice: '5,500', discount: 20, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 3, name: 'Premium Leather Backpack', price: '8,500', image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
    { id: 4, name: 'Ergonomic Office Chair', price: '18,000', image: 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60' },
];
</script>
