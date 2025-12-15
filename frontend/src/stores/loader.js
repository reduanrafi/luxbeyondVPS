import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoaderStore = defineStore('loader', () => {
    const isLoading = ref(false);
    const message = ref('');

    const startLoading = (msg = '') => {
        isLoading.value = true;
        message.value = msg;
    };

    const stopLoading = () => {
        isLoading.value = false;
        message.value = '';
    };

    return {
        isLoading,
        message,
        startLoading,
        stopLoading
    };
});
