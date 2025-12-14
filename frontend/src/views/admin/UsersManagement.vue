<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-white tracking-tight">Users Management</h1>
            <button @click="openModal()"
                class="flex items-center gap-2 px-6 py-2.5 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-all">
                <Plus class="w-5 h-5" />
                Add User
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-[#111111] rounded-xl shadow-md border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5 bg-white/5">
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Name</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Email</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Roles</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-zinc-400">Date Created</th>
                            <th class="text-right py-4 px-6 text-sm font-semibold text-zinc-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-white/5 transition-colors group">
                            <td class="py-4 px-6 text-white font-medium">{{ user.name }}</td>
                            <td class="py-4 px-6 text-zinc-400">{{ user.email }}</td>
                            <td class="py-4 px-6">
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="role in user.roles" :key="role.id"
                                        class="px-2 py-1 bg-primary/10 text-primary text-xs rounded-full border border-primary/20">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-zinc-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <button @click="openModal(user)"
                                    class="p-2 text-zinc-400 hover:text-white hover:bg-white/10 rounded-lg transition-all">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deleteUser(user.id)"
                                    class="p-2 text-zinc-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-all">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="5" class="py-8 text-center text-zinc-500">No users found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
             <!-- Pagination -->
            <div v-if="pagination.total > pagination.per_page" class="border-t border-white/5 p-4 flex justify-between items-center">
                <span class="text-sm text-zinc-400">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                </span>
                 <div class="flex gap-2">
                    <button 
                        @click="fetchUsers(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-4 py-2 border border-white/10 rounded text-sm text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-white/5"
                    >
                        Previous
                    </button>
                    <button 
                        @click="fetchUsers(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-4 py-2 border border-white/10 rounded text-sm text-white disabled:opacity-50 disabled:cursor-not-allowed hover:bg-white/5"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- User Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-[#111111] border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit User' : 'Add User' }}</h2>
                    <button @click="closeModal" class="text-zinc-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Name</label>
                        <input v-model="form.name" type="text" required
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Email</label>
                        <input v-model="form.email" type="email" required
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">
                            Password <span v-if="isEditing" class="text-xs text-zinc-500">(Leave blank to keep current)</span>
                        </label>
                        <input v-model="form.password" type="password" :required="!isEditing"
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Roles</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto bg-zinc-900 p-3 rounded-lg border border-white/10">
                            <label v-for="role in allRoles" :key="role.id" class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" :value="role.name" v-model="form.roles"
                                    class="rounded bg-zinc-800 border-white/20 text-primary focus:ring-primary/50">
                                <span class="text-sm text-white">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModal"
                            class="px-4 py-2 text-zinc-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-colors">
                            {{ isEditing ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Edit, Trash2, X } from 'lucide-vue-next';
import axios from '../../axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const users = ref([]);
const allRoles = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const pagination = ref({});

const form = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    roles: []
});

const fetchRoles = async () => {
    try {
        const response = await axios.get('/admin/roles');
        allRoles.value = response.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const fetchUsers = async (page = 1) => {
    try {
        const response = await axios.get(`/admin/users?page=${page}`);
        users.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            per_page: response.data.per_page,
        };
    } catch (error) {
        console.error('Error fetching users:', error);
        toast.error('Failed to load users');
    }
};

const openModal = (user = null) => {
    isEditing.value = !!user;
    if (user) {
        form.value = {
            id: user.id,
            name: user.name,
            email: user.email,
            password: '',
            roles: user.roles.map(r => r.name)
        };
    } else {
        form.value = {
            id: null,
            name: '',
            email: '',
            password: '',
            roles: []
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = { id: null, name: '', email: '', password: '', roles: [] };
};

const saveUser = async () => {
    try {
        if (isEditing.value) {
            await axios.put(`/admin/users/${form.value.id}`, form.value);
            toast.success('User updated successfully');
        } else {
            await axios.post('/admin/users', form.value);
            toast.success('User created successfully');
        }
        closeModal();
        fetchUsers(pagination.value.current_page);
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || 'Failed to save user');
    }
};

const deleteUser = async (id) => {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    try {
        await axios.delete(`/admin/users/${id}`);
        toast.success('User deleted successfully');
        fetchUsers(pagination.value.current_page);
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || 'Failed to delete user');
    }
};

onMounted(() => {
    fetchUsers();
    fetchRoles();
});
</script>
