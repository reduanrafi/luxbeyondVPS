<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-serif font-bold text-white tracking-wide">User Management</h2>
                <p class="text-zinc-400 mt-2 text-sm">Manage and track your platform users</p>
            </div>
            <router-link to="/admin/requests" class="px-4 py-2 bg-white/5 border border-white/10 text-white rounded-lg hover:bg-white/10 transition-all flex items-center gap-2">
                <FileText class="w-4 h-4 text-primary" />
                <span>Product Requests</span>
            </router-link>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div v-for="stat in userStats" :key="stat.label"
                class="bg-[#111111] rounded-xl border border-white/5 p-6 hover:border-primary/30 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ stat.label }}</p>
                        <p class="text-3xl font-serif font-bold text-white">{{ stat.value }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center">
                        <component :is="stat.icon" class="w-5 h-5 text-primary" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-[#111111] rounded-xl border border-white/5 p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative group">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 group-focus-within:text-primary transition-colors" />
                    <input v-model="filters.search" type="text" placeholder="Search users by name, email or phone..."
                        class="w-full pl-10 pr-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 transition-all placeholder:text-zinc-600">
                </div>

                <select v-model="filters.role"
                    class="px-4 py-2.5 bg-black/20 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/50 cursor-pointer">
                    <option value="" class="bg-[#111111]">All Roles</option>
                    <option value="Admin" class="bg-[#111111]">Admin</option>
                    <option value="Customer" class="bg-[#111111]">Customer</option>
                    <option value="Traveller" class="bg-[#111111]">Traveller</option>
                </select>

                <div></div> <!-- Spacer -->

                <button @click="resetFilters"
                    class="px-4 py-2.5 bg-white/5 border border-white/10 text-white rounded-lg hover:bg-white/10 hover:border-white/20 transition-all flex items-center justify-center gap-2">
                    <RotateCcw class="w-4 h-4" />
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-[#111111] rounded-xl border border-white/5 p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="text-zinc-400 mt-4">Loading users...</p>
        </div>

        <!-- Users Table -->
        <div v-else class="bg-[#111111] rounded-xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-black/20 border-b border-white/5">
                        <tr>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Name</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Email</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Phone</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Roles</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Joined</th>
                            <th class="text-left py-4 px-6 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="users.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <Ghost class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-lg font-medium text-zinc-400">No users found</p>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="user in users" :key="user.id" class="group hover:bg-white/[0.02] transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-xs font-bold text-zinc-300">
                                        {{ user.name?.charAt(0) || '?' }}
                                    </div>
                                    <div class="text-sm font-medium text-white">{{ user.name }}</div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm text-zinc-400">{{ user.email }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm text-primary font-medium">{{ user.phone || 'N/A' }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="role in user.roles" :key="role.id" 
                                        class="px-2 py-0.5 bg-primary/10 border border-primary/20 rounded-full text-[10px] font-bold text-primary uppercase tracking-wider">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-sm text-zinc-400">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <button @click="editUser(user)" class="p-2 bg-white/5 hover:bg-white/10 text-blue-400 rounded-lg transition-colors">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteUser(user.id)" class="p-2 bg-white/5 hover:bg-white/10 text-red-400 rounded-lg transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <span class="text-xs text-zinc-500">
                    Showing {{ users.length }} of {{ pagination.total }} users
                </span>
                <div class="flex gap-2">
                    <button @click="fetchUsers(pagination.current_page - 1)" 
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 bg-white/5 border border-white/10 rounded text-xs text-white disabled:opacity-30 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button @click="fetchUsers(pagination.current_page + 1)" 
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 bg-white/5 border border-white/10 rounded text-xs text-white disabled:opacity-30 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { User, Users, Search, RotateCcw, Ghost, Edit, Trash2, FileText, ShieldCheck } from 'lucide-vue-next';
import axios from '../axios';
import { debounce } from 'lodash';
import { useToast } from "vue-toastification";

const toast = useToast();
const loading = ref(false);
const users = ref([]);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
});

const filters = ref({
    search: '',
    role: '',
});

const userStats = computed(() => {
    return [
        { label: 'Total Users', value: pagination.value.total.toString(), icon: Users },
        { label: 'Active Today', value: '...', icon: User }, // Placeholder
        { label: 'Administrators', value: '...', icon: ShieldCheck }, // Placeholder
        { label: 'Customers', value: '...', icon: User }, // Placeholder
    ];
});

const fetchUsers = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            search: filters.value.search,
            role: filters.value.role
        };
        const response = await axios.get('/admin/users', { params });
        users.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total
        };
    } catch (error) {
        console.error('Error fetching users:', error);
        toast.error('Failed to load users');
    } finally {
        loading.value = false;
    }
};

const debouncedFetch = debounce(() => {
    fetchUsers(1);
}, 500);

watch(() => filters.value.search, () => {
    debouncedFetch();
});

watch(() => filters.value.role, () => {
    fetchUsers(1);
});

const resetFilters = () => {
    filters.value = {
        search: '',
        role: '',
    };
    fetchUsers(1);
};

const editUser = (user) => {
    // Implement edit logic or open modal
    toast.info('Edit functionality coming soon');
};

const deleteUser = async (id) => {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    try {
        await axios.delete(`/admin/users/${id}`);
        toast.success('User deleted successfully');
        fetchUsers(pagination.value.current_page);
    } catch (error) {
        toast.error(error.response?.data?.message || 'Failed to delete user');
    }
};

onMounted(() => {
    fetchUsers();
});
</script>
