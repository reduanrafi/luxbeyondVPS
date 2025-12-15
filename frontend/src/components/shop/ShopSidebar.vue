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
import { ref, onMounted } from 'vue';
import { Zap, ChevronRight } from 'lucide-vue-next';
import axios from '../../axios';
import SidebarItem from './SidebarItem.vue';

const categories = ref([]);
const loading = ref(true);

onMounted(() => {
    fetchCategories();
});

const fetchCategories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/categories');
        // Handle both paginated and non-paginated responses
        if (response.data.data) {
            categories.value = Array.isArray(response.data.data)
                ? response.data.data
                : response.data.data.data || [];
        } else {
            categories.value = Array.isArray(response.data) ? response.data : [];
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
        categories.value = [];
    } finally {
        loading.value = false;
    }
};
</script>