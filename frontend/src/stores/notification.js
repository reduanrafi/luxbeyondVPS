import { defineStore } from 'pinia';
import axios from '../axios';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        loading: false,
        initialized: false
    }),

    getters: {
        unreadCount: (state) => state.notifications.filter(n => !n.read_at).length,
        allNotifications: (state) => state.notifications
    },

    actions: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await axios.get('/notifications');
                this.notifications = response.data.map(n => ({
                    ...n,
                    read: !!n.read_at
                }));
                this.initialized = true;
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        async markAsRead(id) {
            try {
                await axios.post(`/notifications/${id}/read`);
                const notification = this.notifications.find(n => n.id === id);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                    notification.read = true;
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                await axios.post('/notifications/mark-all-read');
                this.notifications.forEach(n => {
                    n.read_at = new Date().toISOString();
                    n.read = true;
                });
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        },

        addNotification(notification) {
            this.notifications.unshift({
                ...notification,
                read: false,
                read_at: null
            });
        }
    }
});
