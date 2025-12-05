<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Events Management</h2>
                <p class="text-sm text-slate-600 mt-1">Manage flash sales, promotions, and special events</p>
            </div>
            <button @click="openAddModal"
                class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary-hover transition-all shadow-md flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Create Event
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search events..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                <select v-model="filters.status" @change="fetchEvents(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="live">Live Now</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="expired">Expired</option>
                </select>
                <select v-model="filters.position" @change="fetchEvents(1)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                    <option value="">All Positions</option>
                    <option value="hero">Hero Banner</option>
                    <option value="sidebar">Sidebar</option>
                    <option value="both">Both</option>
                </select>
                <button @click="resetFilters"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-slate-600 mt-2">Loading events...</p>
        </div>

        <!-- Events Table -->
        <div v-else class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="events.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No events found
                            </td>
                        </tr>
                        <tr v-for="event in events" :key="event.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 mr-3">
                                        <img v-if="event.image_url" :src="event.image_url" :alt="event.name" class="h-full w-full object-cover">
                                        <Calendar v-else class="h-full w-full p-2 text-gray-400" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ event.name }}</div>
                                        <div class="text-sm text-gray-500">{{ event.slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="{
                                        'bg-blue-100 text-blue-700': event.position === 'hero',
                                        'bg-green-100 text-green-700': event.position === 'sidebar',
                                        'bg-purple-100 text-purple-700': event.position === 'both'
                                    }">
                                    {{ event.position === 'hero' ? 'Hero' : event.position === 'sidebar' ? 'Sidebar' : 'Both' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ event.products_count || 0 }} products
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ formatDate(event.start_date) }}</div>
                                <div class="text-sm text-gray-500">to {{ formatDate(event.end_date) }}</div>
                            </td>
                           
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ event.is_active ? 'Active' : 'Inactive' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-700': event.is_live,
                                        'bg-yellow-100 text-yellow-700': event.is_upcoming,
                                        'bg-red-100 text-red-700': event.is_expired,
                                        'bg-gray-100 text-gray-700': !event.is_active
                                    }">
                                    {{ getStatusLabel(event) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ event.priority }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="editEvent(event)"
                                        class="p-2 hover:bg-blue-50 rounded-lg transition-colors">
                                        <Edit class="w-4 h-4 text-blue-600" />
                                    </button>
                                    <button @click="deleteEvent(event.id)"
                                        class="p-2 hover:bg-red-50 rounded-lg transition-colors">
                                        <Trash2 class="w-4 h-4 text-red-600" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} events
                </p>
                <div class="flex gap-2">
                    <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                        :class="page === pagination.current_page ? 'bg-primary text-white' : 'border border-gray-300 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                        {{ page }}
                    </button>
                    <button @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Event Form Modal -->
        <EventFormModal
            v-if="showModal"
            :event="selectedEvent"
            @close="closeModal"
            @saved="handleEventSaved"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, Edit, Trash2, Calendar } from 'lucide-vue-next';
import axios from '../../axios';
import EventFormModal from './components/EventFormModal.vue';

const loading = ref(true);
const events = ref([]);
const showModal = ref(false);
const selectedEvent = ref(null);

const filters = ref({
    search: '',
    status: '',
    position: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
});

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const fetchEvents = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: pagination.value.per_page,
            ...filters.value
        };

        const response = await axios.get('/admin/events', { params });
        
        // Debug logging
        console.log('Events API Response:', response.data);
        console.log('Events data:', response.data.data);
        console.log('Events count:', response.data.data?.length || 0);
        
        events.value = response.data.data || [];
        
        if (response.data.meta) {
            pagination.value = {
                current_page: response.data.meta.current_page || response.data.current_page,
                last_page: response.data.meta.last_page || response.data.last_page,
                per_page: response.data.meta.per_page || response.data.per_page,
                total: response.data.meta.total || response.data.total,
                from: response.data.meta.from || response.data.from,
                to: response.data.meta.to || response.data.to,
            };
        }
        
        console.log('Events after assignment:', events.value);
        console.log('Events length:', events.value.length);
    } catch (error) {
        console.error('Error fetching events:', error);
        alert('Failed to load events');
    } finally {
        loading.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusLabel = (event) => {
    if (!event.is_active) return 'Inactive';
    if (event.is_live) return 'Live';
    if (event.is_upcoming) return 'Upcoming';
    if (event.is_expired) return 'Expired';
    return 'Active';
};

const openAddModal = () => {
    selectedEvent.value = null;
    showModal.value = true;
};

const editEvent = async (event) => {
    try {
        // Fetch full event with products
        const response = await axios.get(`/admin/events/${event.id}`);
        selectedEvent.value = response.data.event || response.data;
        showModal.value = true;
    } catch (error) {
        console.error('Error fetching event:', error);
        alert('Failed to load event details');
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedEvent.value = null;
};

const handleEventSaved = () => {
    closeModal();
    fetchEvents(pagination.value.current_page);
};

const deleteEvent = async (id) => {
    if (!confirm('Are you sure you want to delete this event?')) {
        return;
    }

    try {
        await axios.delete(`/admin/events/${id}`);
        alert('Event deleted successfully');
        fetchEvents(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting event:', error);
        alert('Failed to delete event');
    }
};

const handleSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        fetchEvents(1);
    }, 500);
};

const searchTimeout = ref(null);

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        position: ''
    };
    fetchEvents(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchEvents(page);
    }
};

onMounted(() => {
    fetchEvents();
});
</script>

