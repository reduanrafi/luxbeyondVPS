<template>
    <label
        class="flex items-center justify-between cursor-pointer group p-4 border border-white/5 bg-white/5 hover:border-primary/20 transition-all">
        <span class="flex flex-col">
            <span class="text-sm font-bold text-slate-300 group-hover:text-white transition-colors">{{ label }}</span>
            <span class="text-[10px] text-slate-500 mt-1">{{ description }}</span>
        </span>
        <div class="relative">
            <input type="checkbox" :checked="modelValue" @change="$emit('update:modelValue', $event.target.checked)"
                class="sr-only peer">
            <div
                class="w-11 h-6 bg-slate-700/50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
            </div>
        </div>
    </label>
</template>

<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    }
});

defineEmits(['update:modelValue']);
</script>
