import { defineStore } from 'pinia';
import axios from '../axios';

export const useProductStore = defineStore('product', {
    state: () => ({
        featuredProducts: [],
        categories: [],
        brands: [],
        loadingFeatured: false,
        loadingCategories: false,
        loadingBrands: false,
        lastFetchedFeatured: null,
        lastFetchedCategories: null,
        lastFetchedBrands: null,
        cacheTimeout: 5 * 60 * 1000, // 5 minutes cache
    }),

    actions: {
        async fetchFeatured(force = false) {
            const now = Date.now();
            if (!force && this.featuredProducts.length > 0 && this.lastFetchedFeatured && (now - this.lastFetchedFeatured < this.cacheTimeout)) {
                return;
            }

            this.loadingFeatured = true;
            try {
                const response = await axios.get('/products', {
                    params: {
                        featured: true,
                        per_page: 8
                    }
                });

                if (response.data.data) {
                    this.featuredProducts = Array.isArray(response.data.data)
                        ? response.data.data
                        : response.data.data.data || [];
                } else {
                    this.featuredProducts = Array.isArray(response.data) ? response.data : [];
                }
                this.lastFetchedFeatured = Date.now();
            } catch (error) {
                console.error('Error fetching featured products:', error);
                // Keep old data if fetch fails
            } finally {
                this.loadingFeatured = false;
            }
        },

        async fetchCategories(force = false) {
            const now = Date.now();
            if (!force && this.categories.length > 0 && this.lastFetchedCategories && (now - this.lastFetchedCategories < this.cacheTimeout)) {
                return;
            }

            this.loadingCategories = true;
            try {
                const response = await axios.get('/categories');
                this.categories = response.data.data || response.data || [];
                this.lastFetchedCategories = Date.now();
            } catch (error) {
                console.error('Error fetching categories:', error);
            } finally {
                this.loadingCategories = false;
            }
        },

        async fetchBrands(force = false) {
            const now = Date.now();
            if (!force && this.brands.length > 0 && this.lastFetchedBrands && (now - this.lastFetchedBrands < this.cacheTimeout)) {
                return;
            }

            this.loadingBrands = true;
            try {
                const response = await axios.get('/brands', { params: { all: true } });
                this.brands = response.data.data || response.data || [];
                this.lastFetchedBrands = Date.now();
            } catch (error) {
                console.error('Error fetching brands:', error);
            } finally {
                this.loadingBrands = false;
            }
        }
    }
});
