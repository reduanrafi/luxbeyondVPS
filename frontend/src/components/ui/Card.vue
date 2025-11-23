<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
        <slot name="header"></slot>
        <div :class="[bodyClass ? bodyClass : 'p-6']">
            <slot></slot>
        </div>
        <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    bodyClass: {
        type: String,
        default: ''
    }
});
</script>
