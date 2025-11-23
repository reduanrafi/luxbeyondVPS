<template>
    <div class="p-8 pt-24">
        <h1 class="text-3xl font-bold mb-6">Admin Request Dashboard</h1>
        <div v-if="requestStore.loading" class="text-gray-500">Loading...</div>
        <div v-else class="overflow-x-auto bg-white rounded shadow">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="p-4">ID</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Product</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Total (BDT)</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="req in requestStore.requests" :key="req.id" class="border-b hover:bg-gray-50">
                        <td class="p-4">{{ req.id }}</td>
                        <td class="p-4">{{ req.user?.name }}</td>
                        <td class="p-4">
                            <a :href="req.url" target="_blank"
                                class="text-blue-600 hover:underline truncate block max-w-xs">{{ req.url }}</a>
                        </td>
                        <td class="p-4">{{ req.currency }} {{ req.price }} x {{ req.quantity }}</td>
                        <td class="p-4">{{ req.total_amount_bdt }}</td>
                        <td class="p-4">
                            <span :class="{
                                'bg-yellow-100 text-yellow-800': req.status === 'pending',
                                'bg-green-100 text-green-800': req.status === 'approved',
                                'bg-blue-100 text-blue-800': req.status === 'paid',
                                'bg-red-100 text-red-800': req.status === 'rejected'
                            }" class="px-2 py-1 rounded text-xs font-bold uppercase">
                                {{ req.status }}
                            </span>
                        </td>
                        <td class="p-4">
                            <button @click="openEditModal(req)"
                                class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Edit Modal -->
        <div v-if="editingRequest" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
                <h2 class="text-xl font-bold mb-4">Edit Request #{{ editingRequest.id }}</h2>
                <form @submit.prevent="updateRequest">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Status</label>
                        <select v-model="editForm.status" class="w-full p-2 border rounded">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="paid">Paid</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Admin Image URL</label>
                        <input v-model="editForm.admin_image_url" type="url" class="w-full p-2 border rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Admin Note</label>
                        <textarea v-model="editForm.admin_note" class="w-full p-2 border rounded"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editingRequest = null"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRequestStore } from '../stores/request';
import axios from '../axios';

const requestStore = useRequestStore();
const editingRequest = ref(null);
const editForm = ref({
    status: '',
    admin_image_url: '',
    admin_note: ''
});

onMounted(() => {
    requestStore.fetchRequests();
});

const openEditModal = (req) => {
    editingRequest.value = req;
    editForm.value = {
        status: req.status,
        admin_image_url: req.admin_image_url || '',
        admin_note: req.admin_note || ''
    };
};

const updateRequest = async () => {
    try {
        const response = await axios.put(`/requests/${editingRequest.value.id}`, editForm.value);
        // Update local store
        const index = requestStore.requests.findIndex(r => r.id === editingRequest.value.id);
        if (index !== -1) {
            requestStore.requests[index] = response.data;
        }
        editingRequest.value = null;
    } catch (error) {
        alert('Failed to update request');
    }
};
</script>
