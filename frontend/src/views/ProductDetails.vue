<template>
    <div class="bg-gray-50 min-h-screen pt-20 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-slate-600">
                    <li><router-link to="/" class="hover:text-primary">Home</router-link></li>
                    <li>/</li>
                    <li><router-link to="/shop" class="hover:text-primary">Shop</router-link></li>
                    <li>/</li>
                    <li class="text-slate-900 font-medium">{{ product.name }}</li>
                </ol>
            </nav>

            <!-- Product Main Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- Product Images -->
                <div class="space-y-4">
                    <div
                        class="rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg h-96 lg:h-[500px]">
                        <img :src="selectedImage" :alt="product.name" class="w-full h-full object-cover">
                    </div>
                    <div class="grid grid-cols-4 gap-3">
                        <button v-for="(img, index) in product.images" :key="index" @click="selectedImage = img"
                            class="rounded-lg overflow-hidden border-2 transition-all"
                            :class="selectedImage === img ? 'border-primary shadow-md' : 'border-gray-200 hover:border-gray-300'">
                            <img :src="img" :alt="`${product.name} ${index + 1}`" class="w-full h-20 object-cover">
                        </button>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                    <h1 class="text-3xl font-bold text-slate-900 mb-4">{{ product.name }}</h1>

                    <!-- Rating -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center">
                            <Star v-for="i in 5" :key="i" class="w-5 h-5"
                                :class="i <= product.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" />
                        </div>
                        <span class="text-sm text-slate-600">({{ product.reviews }} reviews)</span>
                    </div>

                    <!-- Price -->
                    <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-200">
                        <span class="text-4xl font-bold text-primary">৳{{ product.price }}</span>
                        <span v-if="product.oldPrice" class="text-xl text-gray-400 line-through">৳{{ product.oldPrice
                            }}</span>
                        <span v-if="product.discount"
                            class="px-3 py-1 bg-red-100 text-red-700 text-sm font-bold rounded-full">
                            -{{ product.discount }}%
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-slate-600 mb-6 leading-relaxed">{{ product.description }}</p>

                    <!-- Stock Status -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm font-semibold text-green-700">In Stock</span>
                        </div>
                        <p class="text-xs text-slate-500">Ships within 24-48 hours</p>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Quantity</label>
                            <div class="flex items-center gap-3">
                                <button @click="decrementQty"
                                    class="w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center justify-center">
                                    <Minus class="w-4 h-4" />
                                </button>
                                <input type="number" v-model="quantity" min="1"
                                    class="w-20 text-center border border-gray-300 rounded-lg py-2 focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <button @click="incrementQty"
                                    class="w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center justify-center">
                                    <Plus class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click="addToCart"
                                class="flex-1 bg-primary text-white font-semibold py-4 rounded-lg hover:bg-primary-hover transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <ShoppingCart class="w-5 h-5" />
                                Add to Cart
                            </button>
                            <button @click="toggleWishlist"
                                class="w-14 h-14 rounded-lg border-2 transition-all flex items-center justify-center"
                                :class="isInWishlist ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:border-red-500'">
                                <Heart class="w-6 h-6"
                                    :class="isInWishlist ? 'text-red-500 fill-red-500' : 'text-gray-400'" />
                            </button>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-3">Key Features</h3>
                        <ul class="space-y-2">
                            <li v-for="feature in product.features" :key="feature"
                                class="flex items-start gap-2 text-sm text-slate-600">
                                <CheckCircle class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                                {{ feature }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-12">
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex gap-8">
                        <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                            class="pb-4 text-sm font-semibold transition-all relative"
                            :class="activeTab === tab ? 'text-primary' : 'text-slate-600 hover:text-slate-900'">
                            {{ tab }}
                            <div v-if="activeTab === tab" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary">
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div v-if="activeTab === 'Description'" class="prose max-w-none">
                    <p class="text-slate-600 leading-relaxed">{{ product.fullDescription }}</p>
                </div>

                <div v-if="activeTab === 'Specifications'">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="(value, key) in product.specs" :key="key"
                            class="flex py-3 border-b border-gray-100">
                            <dt class="font-semibold text-slate-700 w-1/2">{{ key }}</dt>
                            <dd class="text-slate-600 w-1/2">{{ value }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="activeTab === 'Reviews'">
                    <div class="space-y-6">
                        <div v-for="review in productReviews" :key="review.id"
                            class="border-b border-gray-100 pb-6 last:border-0">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="font-semibold text-slate-900">{{ review.name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="flex">
                                            <Star v-for="i in 5" :key="i" class="w-4 h-4"
                                                :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" />
                                        </div>
                                        <span class="text-xs text-slate-500">{{ review.date }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-slate-600 text-sm">{{ review.comment }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div>
                <h2 class="text-2xl font-bold text-slate-900 mb-6">You May Also Like</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="item in relatedProducts" :key="item.id"
                        class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-all cursor-pointer">
                        <div class="aspect-square bg-gray-100">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900 text-sm mb-2 line-clamp-2">{{ item.name }}</h3>
                            <p class="text-primary font-bold">৳{{ item.price }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import { Star, ShoppingCart, Heart, CheckCircle, Minus, Plus } from 'lucide-vue-next';

const route = useRoute();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const quantity = ref(1);
const activeTab = ref('Description');
const tabs = ['Description', 'Specifications', 'Reviews'];
const isInWishlist = ref(false);

// Mock data
const product = ref({
    id: 1,
    name: 'Wireless Noise Cancelling Headphones',
    price: '12,500',
    oldPrice: '15,000',
    discount: 20,
    rating: 4.5,
    reviews: 128,
    description: 'Experience world-class silence and exceptional sound with our premium noise cancelling headphones.',
    fullDescription: 'Experience world-class silence and exceptional sound with our premium noise cancelling headphones. Designed for comfort and performance, these headphones are perfect for travel, work, or just relaxing. With advanced active noise cancellation technology, you can immerse yourself in your music without distractions.',
    features: [
        'Active Noise Cancellation',
        '30-hour battery life',
        'Touch controls',
        'Voice assistant support',
        'Foldable design',
        'Premium carrying case included'
    ],
    specs: {
        'Brand': 'Premium Audio',
        'Model': 'WH-1000XM4',
        'Color': 'Black',
        'Connectivity': 'Bluetooth 5.0',
        'Battery Life': '30 hours',
        'Weight': '254g'
    },
    images: [
        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
        'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=500',
        'https://images.unsplash.com/photo-1545127398-14699f92334b?w=500',
        'https://images.unsplash.com/photo-1524678606370-a47ad25cb82a?w=500'
    ]
});

const selectedImage = ref(product.value.images[0]);

const productReviews = ref([
    { id: 1, name: 'John Doe', rating: 5, date: '2 days ago', comment: 'Excellent sound quality and noise cancellation. Worth every penny!' },
    { id: 2, name: 'Jane Smith', rating: 4, date: '1 week ago', comment: 'Very comfortable for long use. Battery life is impressive.' },
    { id: 3, name: 'Mike Johnson', rating: 5, date: '2 weeks ago', comment: 'Best headphones I\'ve ever owned. Highly recommended!' }
]);

const relatedProducts = ref([
    { id: 2, name: 'Wireless Earbuds Pro', price: '8,500', image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=300' },
    { id: 3, name: 'Smart Watch Series 5', price: '15,000', image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300' },
    { id: 4, name: 'Portable Speaker', price: '6,500', image: 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=300' },
    { id: 5, name: 'USB-C Cable', price: '1,200', image: 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=300' }
]);

const incrementQty = () => quantity.value++;
const decrementQty = () => { if (quantity.value > 1) quantity.value--; };

const addToCart = () => {
    cartStore.addItem({
        id: product.value.id,
        name: product.value.name,
        price: parseFloat(product.value.price.replace(/,/g, '')),
        image: product.value.images[0],
        quantity: quantity.value
    });
};

const toggleWishlist = () => {
    isInWishlist.value = !isInWishlist.value;
    if (isInWishlist.value) {
        wishlistStore.addItem({
            id: product.value.id,
            name: product.value.name,
            price: parseFloat(product.value.price.replace(/,/g, '')),
            image: product.value.images[0]
        });
    } else {
        wishlistStore.removeItem(product.value.id);
    }
};

onMounted(() => {
    console.log('Product ID:', route.params.id);
    // Check if product is in wishlist
    isInWishlist.value = wishlistStore.items.some(item => item.id === product.value.id);
});
</script>
