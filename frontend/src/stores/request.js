import { defineStore } from 'pinia';
import axios from '../axios';

export const useRequestStore = defineStore('request', {
    state: () => ({
        requests: [],
        currentRequest: null,
        loading: false,
        error: null,
    }),
    actions: {
        async fetchRequests() {
            this.loading = true;
            try {
                const response = await axios.get('/requests');
                this.requests = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch requests';
            } finally {
                this.loading = false;
            }
        },
        async createRequest(requestData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post('/requests', requestData);
                this.requests.unshift(response.data);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create request';
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
