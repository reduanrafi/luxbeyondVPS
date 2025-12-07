<template>
    <aside class="w-80 flex-shrink-0 hidden lg:block bg-surface border-r border-white/10">
        <div class="fixed w-80">
            <!-- Header -->
            <div class="border-b border-white/10 px-4 py-3 text-white">
                <h2 class="font-bold text-lg flex items-center gap-2">
                    <Layers class="w-5 h-5" />
                    Shop By Category
                </h2>
            </div>

            <div class="p-2 max-h-[calc(100vh-120px)] overflow-y-auto">
                <!-- All Categories button -->
                <button @click="selectCategory(null)"
                    :class="selectedCategory.value === null ? 'bg-primary/10 text-primary' : 'hover:bg-gray-50'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 transition-all text-left font-medium border border-white/10 mb-2 rounded-lg">
                    All Categories
                </button>

                <!-- Categories List -->
                <CategoryItem v-for="category in categories" :key="category.id" :category="category"
                    :selected-category="selectedCategory.value" @select-category="selectCategory" />
            </div>
        </div>
    </aside>
</template>

<script>
import { ref, onMounted } from 'vue';
import CategoryItem from './CategoryItem.vue';
import axios from 'axios';


const categories = ref([]);
const selectedCategory = ref(null);

onMounted(() => {
    fetchCategories();
});

const fetchCategories = async () => {
    try {
        const response = await axios.get('/categories');
        console.log(response.data);
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const selectCategory = (id) => {
    selectedCategory.value = id;
};

</script>
