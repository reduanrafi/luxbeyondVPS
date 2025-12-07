<template>
  <div>
    <!-- Category Row -->
    <div class="flex items-center justify-between py-2 cursor-pointer group hover:bg-white/10 rounded-lg"
      :class="{ 'border-b border-white/10': !isLast }" @click="handleCategoryClick">
      <!-- Category Icon -->
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <div
          class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden border border-white/10">
          <img v-if="category.image_url" :src="category.image_url" :alt="category.name"
            class="w-full h-full object-cover" />
          <div v-else
            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary/20 to-purple-500/20">
            <span class="text-primary font-bold text-lg">
              {{ category.name.charAt(0).toUpperCase() }}
            </span>
          </div>
        </div>

        <!-- Category Name -->
        <span class="text-white font-medium text-sm group-hover:text-primary transition-colors truncate"
          :class="{ 'text-primary font-semibold': isActive }">
          {{ category.name }}
        </span>
      </div>

      <!-- Arrow Icon -->
      <ChevronRight class="w-5 h-5 text-gray-400 shrink-0 ml-2" :class="{ 'rotate-90': hasChildren && isOpen }"
        v-if="hasChildren" />
    </div>

    <!-- Nested Children (Simplified) -->
    <div v-if="hasChildren && isOpen" class="ml-4 space-y-0 border-gray-100">
      <SidebarItem v-for="(child, childIndex) in category.children" :key="child.id" :category="child"
        :is-last="childIndex === category.children.length - 1" />
    </div>
  </div>
</template>
  
  <script setup>
  import { ref, computed } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { ChevronRight } from 'lucide-vue-next';
  import SidebarItem from './SidebarItem.vue';
  
  const props = defineProps({
    category: {
      type: Object,
      required: true
    },
    isLast: {
      type: Boolean,
      default: false
    }
  });

  const route = useRoute();
  const router = useRouter();
  const isOpen = ref(false);
  
  const hasChildren = computed(() => {
    return props.category.children && props.category.children.length > 0;
  });

  const isActive = computed(() => {
    const categoryParam = route.query.category;
    return categoryParam === props.category.slug || categoryParam === props.category.name;
  });
  
  function handleCategoryClick(event) {
   
      isOpen.value = !isOpen.value;
      // Navigate to shop and set category filter without showing in URL
      router.push({ 
        path: '/shop',
        query: { category: props.category.name }
      });
  }
  </script>
  