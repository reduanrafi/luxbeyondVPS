import { defineStore } from 'pinia';
import axios from '../axios';

export const useCmsStore = defineStore('cms', {
    state: () => ({
        pages: {},              // { [key]: pageObject }
        loading: {},            // { [key]: boolean }
        lastFetched: {},        // { [key]: timestamp }
        cacheTimeout: 5 * 60 * 1000, // 5 minutes
    }),

    actions: {
        async fetchPage(key, force = false) {
            const now = Date.now();
            if (
                !force &&
                this.pages[key] &&
                this.lastFetched[key] &&
                now - this.lastFetched[key] < this.cacheTimeout
            ) {
                return this.pages[key];
            }

            this.loading[key] = true;
            try {
                const response = await axios.get(`/pages/${key}`);
                this.pages[key] = response.data;
                this.lastFetched[key] = Date.now();
                return response.data;
            } catch (error) {
                if (error.response?.status === 404) {
                    this.pages[key] = null;
                }
                throw error;
            } finally {
                this.loading[key] = false;
            }
        },

        isLoading(key) {
            return this.loading[key] ?? false;
        },

        getPage(key) {
            return this.pages[key] ?? null;
        },
    },
});
