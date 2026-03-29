import { defineStore } from 'pinia';
import axios from '../axios';

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        settings: {
            site_name: 'Luxbeyond',
            site_logo: null,
            site_email: 'info.luxbeyond@gmail.com',
            site_phone: '01404-458662',
            footer_description: 'Your premium gateway to global shopping. We connect you with travellers to bring the world to your doorstep with ease, security, and style.',
            contact_address: 'Flat 4B,House 33,Road 03,Sector 09,Uttara, Dhaka, Bangladesh, 1230',
            facebook_url: 'https://www.facebook.com/luxbeyond.store',
            instagram_url: 'https://www.instagram.com/luxbeyondbd',
            linkedin_url: 'https://www.linkedin.com/company/luxbeyondbd',
            whatsapp_number: '',
        },
        loading: false,
        initialized: false,
    }),

    getters: {
        logoUrl: (state) => state.settings.site_logo || '/assets/logo.png',
        contactEmail: (state) => state.settings.site_email,
        contactPhone: (state) => state.settings.site_phone,
        contactAddress: (state) => state.settings.contact_address,
        socialLinks: (state) => ({
            facebook: state.settings.facebook_url,
            instagram: state.settings.instagram_url,
            linkedin: state.settings.linkedin_url,
            whatsapp: state.settings.whatsapp_number,
        }),
    },

    actions: {
        async fetchSettings(force = false) {
            // Only fetch if not initialized or forced
            if (!force && this.initialized) {
                return;
            }

            this.loading = true;
            try {
                const response = await axios.get('/settings/general');
                this.settings = { ...this.settings, ...response.data };
                this.initialized = true;
            } catch (error) {
                console.error('Error fetching settings:', error);
            } finally {
                this.loading = false;
            }
        }
    }
});
