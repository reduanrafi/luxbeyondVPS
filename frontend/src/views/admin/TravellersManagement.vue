<template>
    <div class="space-y-6">
        <!-- Header -->
        <h2 class="text-2xl font-bold text-white">Traveller Management</h2>

        <!-- Filters -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="flex gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search travellers..."
                    class="flex-1 px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                <select v-model="filters.status" @change="fetchTravellers(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <!-- Travellers Table -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Passport</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="loading">
                            <td colspan="5" class="px-6 py-12 text-center text-zinc-500">Loading...</td>
                        </tr>
                        <tr v-else-if="travellers.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-zinc-500">No travellers found</td>
                        </tr>
                        <tr v-for="user in travellers" :key="user.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-500 font-bold">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ user.name }}</div>
                                        <div class="text-sm text-zinc-500">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-300">
                                {{ user.traveller_profile?.passport_number || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-300">
                                {{ user.traveller_profile?.phone_number || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full border"
                                    :class="getStatusColor(user.traveller_profile?.verification_status)">
                                    {{ user.traveller_profile?.verification_status || 'Unregistered' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="openModal(user)" class="text-amber-500 hover:text-amber-400">
                                    Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <!-- Simple pagination controls -->
                <div class="flex gap-2">
                     <button @click="fetchTravellers(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-white/10 rounded hover:bg-white/5 disabled:opacity-50 text-white">Previous</button>
                     <button @click="fetchTravellers(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-white/10 rounded hover:bg-white/5 disabled:opacity-50 text-white">Next</button>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div class="bg-zinc-900 border border-white/10 rounded-2xl w-full max-w-2xl p-6 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Traveller Details</h3>
                    <button @click="selectedUser = null" class="text-zinc-400 hover:text-white"><X class="w-5 h-5"/></button>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-zinc-500">Name</label>
                            <p class="text-white">{{ selectedUser.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500">Email</label>
                            <p class="text-white">{{ selectedUser.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500">Passport Number</label>
                            <p class="text-white">{{ selectedUser.traveller_profile?.passport_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-500">Phone</label>
                            <p class="text-white">{{ selectedUser.traveller_profile?.phone_number || 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-500 mb-2">Documents</label>
                        <div v-if="selectedUser.traveller_profile?.documents && selectedUser.traveller_profile.documents.length" class="grid grid-cols-2 gap-4">
                            <a v-for="(doc, idx) in selectedUser.traveller_profile.documents" :key="idx" 
                               :href="`/storage/${doc}`" target="_blank"
                               class="block p-3 bg-white/5 rounded-lg border border-white/10 hover:border-amber-500/50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <FileText class="w-4 h-4 text-amber-500"/>
                                    <span class="text-sm text-zinc-300 truncate">Document {{ idx + 1 }}</span>
                                </div>
                            </a>
                        </div>
                        <p v-else class="text-sm text-zinc-500">No documents uploaded</p>
                    </div>

                    <div class="pt-6 border-t border-white/10 flex justify-end gap-3">
                        <button v-if="selectedUser.traveller_profile?.verification_status !== 'rejected'"
                            @click="updateStatus('rejected')"
                            class="px-4 py-2 border border-red-500/50 text-red-500 rounded-lg hover:bg-red-500/10 transition-colors">
                            Reject
                        </button>
                        <button v-if="selectedUser.traveller_profile?.verification_status !== 'approved'"
                            @click="updateStatus('approved')"
                            class="px-4 py-2 bg-emerald-500 text-black font-bold rounded-lg hover:bg-emerald-400 transition-colors">
                            Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { X, FileText } from 'lucide-vue-next';
import axios from '../../axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const travellers = ref([]);
const loading = ref(true);
const selectedUser = ref(null);
const filters = ref({ search: '', status: '' });
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const fetchTravellers = async (page = 1) => {
    loading.value = true;
    try {
        const params = { page, ...filters.value };
        const response = await axios.get('/admin/travellers', { params });
        travellers.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total
        };
    } catch (error) {
        console.error('Error fetching travellers:', error);
        toast.error('Failed to load travellers');
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    setTimeout(() => { fetchTravellers(1); }, 500);
};

const openModal = (user) => {
    selectedUser.value = JSON.parse(JSON.stringify(user)); // Deep copy
};

const updateStatus = async (status) => {
    try {
        await axios.post(`/admin/travellers/${selectedUser.value.id}/status`, { status });
        toast.success(`Traveller ${status} successfully`);
        selectedUser.value = null;
        fetchTravellers(pagination.value.current_page);
    } catch (error) {
        toast.error('Failed to update status');
    }
};

const getStatusColor = (status) => {
    switch(status) {
        case 'approved': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'rejected': return 'bg-red-500/10 text-red-400 border-red-500/20';
        default: return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
    }
};

onMounted(() => {
    fetchTravellers();
});
</script>
