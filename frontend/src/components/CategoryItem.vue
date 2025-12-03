<template>
    <div class="border border-gray-200 rounded-lg mb-2 overflow-hidden">
        <button @click="toggle"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 transition-all text-left font-medium hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <component :is="getCategoryIcon(category.name)" class="w-5 h-5 flex-shrink-0" />
                <span>{{ category.name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span v-if="category.products_count" class="text-xs bg-gray-100 px-2 py-0.5 rounded-full">
                    {{ category.products_count }}
                </span>
                <svg v-if="category.childrens && category.childrens.length" :class="expanded.value ? 'rotate-90' : ''"
                    class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </button>

        <!-- Recursive children -->
        <div v-if="category.childrens && category.childrens.length && expanded.value" class="ml-6 mt-1 space-y-1">
            <CategoryItem v-for="child in category.childrens" :key="child.id" :category="child"
                :selected-category="selectedCategory" @select-category="$emit('select-category', $event)" />
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    name: 'CategoryItem',
    props: {
        category: Object,
        selectedCategory: Number
    },
    setup(props, { emit }) {
        const expanded = ref(false);

        const toggle = () => {
            expanded.value = !expanded.value;
            emit('select-category', props.category.id);
        };

        const getCategoryIcon = (name) => {
            // Return a dynamic icon component based on category name
            return 'Layers';
        };

        return { expanded, toggle, getCategoryIcon };
    }
};
</script>
