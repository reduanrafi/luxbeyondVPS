<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-white tracking-tight">Roles & Permissions</h1>
            <button @click="openModal()"
                class="flex items-center gap-2 px-6 py-2.5 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-all">
                <Plus class="w-5 h-5" />
                Add Role
            </button>
        </div>

        <!-- Roles List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="role in roles" :key="role.id" 
                class="bg-[#111111] rounded-xl shadow-md border border-white/5 p-6 hover:shadow-lg transition-all hover:border-primary/20 group">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-white group-hover:text-primary transition-colors">{{ role.name }}</h3>
                    <div class="flex gap-2">
                        <button @click="openModal(role)" 
                            class="p-2 text-zinc-400 hover:text-white hover:bg-white/10 rounded-lg transition-all">
                            <Edit class="w-4 h-4" />
                        </button>
                        <button v-if="role.name != 'Super Admin'" @click="deleteRole(role.id)"
                            class="p-2 text-zinc-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-all">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <p class="text-sm font-medium text-zinc-400">Permissions:</p>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="permission in role.permissions" :key="permission.id"
                            class="px-2 py-1 bg-white/5 text-zinc-300 text-xs rounded border border-white/10">
                            {{ permission.name }}
                        </span>
                        <span v-if="role.permissions.length === 0" class="text-xs text-zinc-600 italic">
                            No permissions assigned
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
            <div class="bg-[#111111] border border-white/10 rounded-2xl w-full max-w-2xl p-6 shadow-2xl overflow-y-auto max-h-[90vh]">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit Role' : 'Add Role' }}</h2>
                    <button @click="closeModal" class="text-zinc-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="saveRole" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Role Name</label>
                        <input v-model="form.name" type="text" required
                            class="w-full px-4 py-2 bg-zinc-900 border border-white/10 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-3">Permissions</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <label v-for="permission in allPermissions" :key="permission.id" 
                                class="flex items-center gap-2 p-3 bg-zinc-900 rounded-lg border border-white/5 cursor-pointer hover:border-primary/30 transition-colors">
                                <input type="checkbox" :value="permission.name" v-model="form.permissions"
                                    class="rounded bg-zinc-800 border-white/20 text-primary focus:ring-primary/50">
                                <span class="text-sm text-white">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/5">
                        <button type="button" @click="closeModal"
                            class="px-4 py-2 text-zinc-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-primary text-black font-medium rounded-lg hover:bg-primary/90 transition-colors">
                            {{ isEditing ? 'Update Role' : 'Create Role' }}
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
const roles = ref([]);
const allPermissions = ref([]);
const showModal = ref(false);
const isEditing = ref(false);

const form = ref({
    id: null,
    name: '',
    permissions: []
});

const fetchPermissions = async () => {
    try {
        const response = await axios.get('/admin/permissions');
        allPermissions.value = response.data;
    } catch (error) {
        console.error('Error fetching permissions:', error);
    }
};

const fetchRoles = async () => {
    try {
        const response = await axios.get('/admin/roles');
        roles.value = response.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const openModal = (role = null) => {
    isEditing.value = !!role;
    if (role) {
        form.value = {
            id: role.id,
            name: role.name,
            permissions: role.permissions.map(p => p.name)
        };
    } else {
        form.value = {
            id: null,
            name: '',
            permissions: []
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = { id: null, name: '', permissions: [] };
};

const saveRole = async () => {
    try {
        if (isEditing.value) {
            await axios.put(`/admin/roles/${form.value.id}`, form.value);
            toast.success('Role updated successfully');
        } else {
            await axios.post('/admin/roles', form.value);
            toast.success('Role created successfully');
        }
        closeModal();
        fetchRoles();
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || 'Failed to save role');
    }
};

const deleteRole = async (id) => {
    if (!confirm('Are you sure you want to delete this role?')) return;
    
    try {
        await axios.delete(`/admin/roles/${id}`);
        toast.success('Role deleted successfully');
        fetchRoles();
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || 'Failed to delete role');
    }
};

onMounted(() => {
    fetchRoles();
    fetchPermissions();
});
</script>
