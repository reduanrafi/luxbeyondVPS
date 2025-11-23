import { defineStore } from 'pinia';
import axios from '../axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        errors: [],
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.roles?.some(role => role.name === 'Admin'),
    },
    actions: {
        async register(userData) {
            this.errors = [];
            try {
                const response = await axios.post('/auth/register', userData);
                this.token = response.data.access_token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
            } catch (error) {
                this.errors = error.response?.data?.errors || ['An error occurred'];
                throw error;
            }
        },
        async login(credentials) {
            this.errors = [];
            try {
                const response = await axios.post('/auth/login', credentials);
                this.token = response.data.access_token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
            } catch (error) {
                this.errors = error.response?.data?.errors || ['Invalid credentials'];
                throw error;
            }
        },
        async logout() {
            try {
                await axios.post('/auth/logout');
            } catch (error) {
                console.error('Logout failed', error);
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
            }
        },
        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await axios.get('/user');
                this.user = response.data;
            } catch (error) {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
            }
        }
    }
});
