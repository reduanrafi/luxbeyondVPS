<template>
    <section id="products" class="py-24 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary/10 text-primary rounded-full text-sm font-semibold mb-4">
                    <Star class="w-4 h-4 fill-current" />
                    Featured Collection
                </div>
                <h2 class="text-4xl font-bold text-primary sm:text-5xl mb-4">
                    Handpicked for You
                </h2>
                <p class="text-lg text-slate-400 max-w-2xl mx-auto">
                    Discover our curated selection of premium products, ready for immediate delivery
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="n in 4" :key="n" class="animate-pulse">
                    <div class="bg-white/5 rounded-2xl h-80 mb-4"></div>
                    <div class="h-4 bg-white/5 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-white/5 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Products Grid -->
            <div v-else-if="products.length > 0"
                class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4  2xl:grid-cols-6 gap-6">
                <ProductCard v-for="product in products" :key="product.id" :product="product" view-mode="grid"
                    :show-stock="true" :show-category="true" :show-brand="true" :show-quick-view="true"
                    :show-wishlist="true" />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <Package class="w-10 h-10 text-gray-400" />
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Featured Products</h3>
                <p class="text-gray-500 mb-6">Check back soon for our featured collection</p>
                <router-link to="/shop">
                    <Button variant="primary" size="lg">Browse All Products</Button>
                </router-link>
            </div>

            <!-- View All Button -->
            <div v-if="products.length > 0" class="text-center mt-12">
                <router-link to="/shop">
                    <Button variant="outline" size="lg" class="px-8">
                        View All Products
                        <ArrowRight class="w-5 h-5 ml-2 inline" />
                    </Button>
                </router-link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Button from './ui/Button.vue';
import ProductCard from './ProductCard.vue';
import { Star, Package, ArrowRight } from 'lucide-vue-next';
import axios from '../axios';

const products = ref([]);
const loading = ref(true);

const fetchFeaturedProducts = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/products', {
            params: {
                featured: true,
                per_page: 8
            }
        });

        // Handle paginated response
        if (response.data.data) {
            products.value = Array.isArray(response.data.data)
                ? response.data.data
                : response.data.data.data || [];
        } else {
            products.value = Array.isArray(response.data) ? response.data : [];
        }
    } catch (error) {
        console.error('Error fetching featured products:', error);
        products.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchFeaturedProducts();
});
</script>
