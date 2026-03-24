<template>
    <div class="bg-surface">
        
        <!-- Categories List -->
        <div class="px-4 pb-4">
            <div v-if="loading" class="space-y-0">
                <div v-for="n in 8" :key="n" class="h-16 bg-black/90 rounded-lg animate-pulse mb-1"></div>
            </div>
            
            <div v-else-if="categories.length > 0" class="space-y-0">
                <SidebarItem
                    v-for="(category, index) in categories"
                    :key="category.id"
                    :category="category"
                    :is-last="index === categories.length - 1"
                />
            </div>
            
            <div v-else class="text-center py-8 text-gray-500 text-sm">
                No categories available
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import SidebarItem from './SidebarItem.vue';
import { useProductStore } from '@/stores/product';

const productStore = useProductStore();

const categories = computed(() => productStore.categories);
const loading = computed(() => productStore.loadingCategories);

onMounted(() => {
    productStore.fetchCategories();
});
</script>