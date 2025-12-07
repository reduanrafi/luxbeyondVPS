<template>
    <div class="bg-surface border border-white/[0.05] hover:border-primary/50 transition-all duration-300 group">
        <slot name="header"></slot>
        <div :class="[bodyClass ? bodyClass : 'p-6']">
            <slot></slot>
        </div>
        <div v-if="$slots.footer" class="px-6 py-4 bg-white/[0.02] border-t border-white/[0.05]">
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
