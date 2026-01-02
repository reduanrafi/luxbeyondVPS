<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Travellers Management</h2>
                <p class="text-sm text-zinc-400 mt-1">View and manage traveller accounts</p>
            </div>
            <!-- <button class="px-4 py-2 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                <Plus class="w-4 h-4" />
                Add Traveller
            </button> -->
        </div>

        <!-- Stats -->
        <!-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="stat in travellerStats" :key="stat.label"
                class="bg-[#111111] rounded-lg shadow-md border border-white/5 p-4 hover:border-primary/20 transition-colors">
                <p class="text-sm text-zinc-400">{{ stat.label }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ stat.value }}</p>
            </div>
        </div> -->

        <!-- Search -->
        <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-4">
            <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search travellers by name or email..."
                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
        </div>

        <!-- Loading State -->
        <div v-if="loading"
            class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-8 text-center text-zinc-400">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2">Loading travellers...</p>
        </div>

        <!-- Travellers Table -->
        <div v-else class="bg-[#111111] rounded-xl shadow-md border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Traveller</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Email</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Title</th>
                            <!-- <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Trips</th> -->
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Joined</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="travellers.length === 0">
                            <td colspan="5" class="py-8 text-center text-zinc-500">No travellers found</td>
                        </tr>
                        <tr v-for="traveller in travellers" :key="traveller.id"
                            class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-black font-bold">
                                        {{ traveller.name?.charAt(0).toUpperCase() || '?' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white text-sm">{{ traveller.name }}</p>
                                        <p class="text-xs text-zinc-500">ID: {{ traveller.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ traveller.email }}</td>
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ traveller.title || 'N/A' }}</td>
                            <!-- <td class="py-4 px-6 text-sm text-zinc-400">{{ traveller.trips_count || 0 }}</td> -->
                            <td class="py-4 px-6 text-sm text-zinc-400">{{ new Date(traveller.created_at).toLocaleDateString() }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/travellers/${traveller.id}`"
                                        class="p-2 hover:bg-blue-500/10 rounded-lg transition-colors group">
                                        <Eye class="w-4 h-4 text-blue-500 group-hover:text-blue-400" />
                                    </router-link>
                                    <!-- <button class="p-2 hover:bg-yellow-500/10 rounded-lg transition-colors group">
                                        <Edit class="w-4 h-4 text-yellow-500 group-hover:text-yellow-400" />
                                    </button> -->
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="flex items-center justify-between">
            <p class="text-sm text-zinc-500">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} travellers
            </p>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-sm font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                    Previous
                </button>
                <button v-for="page in visiblePages" :key="page" @click="changePage(page)"
                    :class="page === pagination.current_page ? 'bg-primary text-slate-900 border-primary' : 'border border-white/10 text-zinc-400 hover:bg-white/5 hover:text-white'"
                    class="px-4 py-2 rounded-lg font-medium text-sm transition-colors border">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors text-sm font-medium text-zinc-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Eye, Plus, Edit } from 'lucide-vue-next';
import axios from '../../axios';

const loading = ref(true);
const travellers = ref([]);
const searchQuery = ref('');
const statsData = ref({});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
});

// const travellerStats = computed(() => [
//     { label: 'Total Travellers', value: statsData.value.total_travellers || 0 },
    
// ]);

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    return pages;
});

const fetchTravellers = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/travellers', {
            params: {
                page,
                search: searchQuery.value,
                per_page: 15
            }
        });

        const data = response.data.data || response.data;
        const meta = response.data; // pagination info usually on top level if using standard params or wrapping

        travellers.value = data;
        
        // Handle pagination structure if it exists
        if (response.data.per_page) {
             pagination.value = {
                current_page: response.data.current_page || 1,
                last_page: response.data.last_page || 1,
                per_page: response.data.per_page || 15,
                total: response.data.total || 0,
                from: response.data.from || 0,
                to: response.data.to || 0
            };
        }
       
    } catch (error) {
        console.error('Error fetching travellers:', error);
        // travellers.value = []; // Keep previous data or clear?
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    fetchTravellers(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchTravellers(page);
    }
};

onMounted(() => {
    fetchTravellers();
});
</script>
