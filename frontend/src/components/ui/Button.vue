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
    const baseClasses = 'inline-flex items-center justify-center rounded-full font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    const variants = {
        primary: 'bg-primary text-slate-900 hover:bg-primary-hover hover:rounded-full focus:ring-primary',
        secondary: 'bg-secondary text-slate-900 hover:bg-blue-600 hover:rounded-full focus:ring-secondary',
        outline: 'border border-white/20 bg-transparent text-slate-300 hover:bg-slate-800 hover:rounded-full focus:ring-gray-200',
        ghost: 'bg-transparent text-slate-300 hover:bg-slate-800 hover:rounded-full focus:ring-gray-200',
        danger: 'bg-red-600 text-white hover:bg-red-700 hover:rounded-full focus:ring-red-500'
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
