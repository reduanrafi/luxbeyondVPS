<template>
    <button :class="computedClasses" :disabled="disabled">
        <slot></slot>
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'outline', 'ghost', 'danger'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    disabled: {
        type: Boolean,
        default: false
    },
    block: {
        type: Boolean,
        default: false
    }
});

const computedClasses = computed(() => {
    const baseClasses = 'inline-flex items-center justify-center rounded-full font-medium transition-all text-sm duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#09090b] disabled:opacity-50 disabled:cursor-not-allowed';
    
    const variants = {
        primary: 'bg-primary text-black font-semibold hover:bg-primary/100 focus:ring-primary shadow-[0_0_15px_rgba(200,174,125,0.3)] hover:shadow-[0_0_25px_rgba(200,174,125,0.5)]',
        secondary: 'bg-white/10 text-white hover:bg-white/20 focus:ring-white/50 backdrop-blur-sm border border-white/5',
        outline: 'border border-primary/30 text-primary hover:bg-primary/10 focus:ring-primary',
        ghost: 'text-primary hover:bg-primary/10 focus:ring-primary',
        danger: 'bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500 hover:text-white focus:ring-red-500'
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-base',
        lg: 'px-6 py-3 text-lg'
    };

    return [
        baseClasses,
        variants[props.variant],
        sizes[props.size],
        props.block ? 'w-full' : ''
    ].join(' ');
});
</script>
