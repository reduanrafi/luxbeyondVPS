<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Blog Management</h2>
                <p class="text-sm text-zinc-400 mt-1">Create and manage blog posts/articles</p>
            </div>
            <button @click="openModal()"
                class="px-4 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2">
                <Plus class="w-5 h-5" />
                Create Post
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-4">
            <div class="flex gap-4">
                <input type="text" v-model="filters.search" @input="handleSearch" placeholder="Search posts..."
                    class="flex-1 px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                <select v-model="filters.status" @change="fetchPosts(1)"
                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/50">
                    <option value="">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Post</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Views</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-zinc-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-zinc-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="loading">
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">Loading...</td>
                        </tr>
                        <tr v-else-if="posts.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">No posts found</td>
                        </tr>
                        <tr v-for="post in posts" :key="post.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg overflow-hidden bg-white/5 border border-white/10 flex-shrink-0 mr-3">
                                        <img v-if="post.image_url" :src="post.image_url" class="h-full w-full object-cover">
                                        <div v-else class="h-full w-full flex items-center justify-center">
                                            <FileText class="w-5 h-5 text-zinc-600" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white line-clamp-1">{{ post.title }}</div>
                                        <div class="text-xs text-zinc-500 line-clamp-1">{{ post.excerpt || 'No excerpt' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-300">
                                {{ post.author?.name || 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full border"
                                    :class="post.is_published ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'">
                                    {{ post.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-300">
                                {{ post.views }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-400">
                                {{ formatDate(post.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openModal(post)" class="p-2 hover:bg-white/10 rounded-lg text-blue-400 transition-colors">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="deletePost(post.id)" class="p-2 hover:bg-white/10 rounded-lg text-red-400 transition-colors">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <div class="text-xs text-zinc-500">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} posts
                </div>
                <div class="flex gap-2">
                    <button @click="fetchPosts(pagination.current_page - 1)" 
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1.5 border border-white/10 rounded-lg text-zinc-400 text-xs disabled:opacity-50 hover:bg-white/5">
                        Previous
                    </button>
                    <button @click="fetchPosts(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1.5 border border-white/10 rounded-lg text-zinc-400 text-xs disabled:opacity-50 hover:bg-white/5">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Post Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 text-start">
            <div class="bg-zinc-900 border border-white/10 rounded-2xl w-full max-w-4xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white">{{ isEditing ? 'Edit Post' : 'Create Post' }}</h2>
                    <button @click="closeModal" class="text-zinc-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="savePost" class="space-y-6">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-2 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Title</label>
                                <input v-model="form.title" type="text" required
                                    class="w-full px-4 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Content</label>
                                <textarea v-model="form.content" rows="12" required
                                    class="w-full px-4 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 font-mono text-sm"
                                    placeholder="Use Markdown or HTML..."></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Excerpt (Optional)</label>
                                <textarea v-model="form.excerpt" rows="3"
                                    class="w-full px-4 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 text-sm"></textarea>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Featured Image</label>
                                <div class="border-2 border-dashed border-white/10 rounded-lg p-4 text-center hover:bg-white/5 transition-colors cursor-pointer relative"
                                    @click="$refs.fileInput.click()">
                                    <input ref="fileInput" type="file" @change="handleImage" class="hidden" accept="image/*">
                                    <div v-if="imagePreview" class="relative">
                                        <img :src="imagePreview" class="w-full h-40 object-cover rounded-lg">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 hover:opacity-100 flex items-center justify-center transition-opacity rounded-lg">
                                            <span class="text-white text-xs">Change Image</span>
                                        </div>
                                    </div>
                                    <div v-else class="py-8">
                                        <div class="mx-auto w-12 h-12 rounded-full bg-white/5 flex items-center justify-center mb-2">
                                            <Image class="w-6 h-6 text-zinc-400" />
                                        </div>
                                        <p class="text-xs text-zinc-500">Click to upload</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-lg border border-white/10 space-y-4">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="is_published" v-model="form.is_published"
                                        class="w-4 h-4 rounded border-white/20 bg-zinc-800 text-amber-500 focus:ring-amber-500/50">
                                    <label for="is_published" class="text-sm font-medium text-white cursor-pointer select-none">Publish Immediately</label>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-zinc-500 mb-1">Tags (Comma separated)</label>
                                    <input v-model="tagsInput" type="text" placeholder="news, update, featured"
                                        class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <button type="button" @click="closeModal"
                            class="px-4 py-2 border border-white/10 rounded-lg text-zinc-400 hover:text-white hover:bg-white/5 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="saving"
                            class="px-6 py-2 bg-amber-500 text-black font-bold rounded-lg hover:bg-amber-400 transition-colors disabled:opacity-50">
                            {{ saving ? 'Saving...' : (isEditing ? 'Update Post' : 'Create Post') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Plus, Edit, Trash2, FileText, X, Image } from 'lucide-vue-next';
import axios from '../../axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const posts = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const imagePreview = ref(null);
const fileInput = ref(null);
const tagsInput = ref('');

const filters = ref({
    search: '',
    status: ''
});

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0
});

const form = ref({
    id: null,
    title: '',
    content: '',
    excerpt: '',
    image: null,
    is_published: false,
    tags: []
});

const fetchPosts = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            page,
            ...filters.value
        };
        const response = await axios.get('/admin/blogs', { params });
        posts.value = response.data.data;
        
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total,
            from: response.data.from,
            to: response.data.to
        };
    } catch (error) {
        console.error('Error fetching posts:', error);
        toast.error('Failed to load posts');
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    setTimeout(() => {
        fetchPosts(1);
    }, 500);
};

const openModal = (post = null) => {
    isEditing.value = !!post;
    if (post) {
        form.value = {
            id: post.id,
            title: post.title,
            content: post.content,
            excerpt: post.excerpt,
            image: null,
            is_published: !!post.is_published,
            tags: post.tags || []
        };
        imagePreview.value = post.image_url;
        tagsInput.value = post.tags ? post.tags.join(', ') : '';
    } else {
        form.value = {
            id: null,
            title: '',
            content: '',
            excerpt: '',
            image: null,
            is_published: false,
            tags: []
        };
        imagePreview.value = null;
        tagsInput.value = '';
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const handleImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.value.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const savePost = async () => {
    saving.value = true;
    try {
        const formData = new FormData();
        formData.append('title', form.value.title);
        formData.append('content', form.value.content);
        formData.append('is_published', form.value.is_published ? '1' : '0');
        
        if (form.value.excerpt) formData.append('excerpt', form.value.excerpt);
        if (form.value.image) formData.append('image', form.value.image);
        
        // Process tags
        if (tagsInput.value) {
            const tags = tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag);
            tags.forEach((tag, index) => formData.append(`tags[${index}]`, tag));
        }

        if (isEditing.value) {
            formData.append('_method', 'PUT');
            await axios.post(`/admin/blogs/${form.value.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            toast.success('Post updated successfully');
        } else {
            await axios.post('/admin/blogs', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            toast.success('Post created successfully');
        }
        
        closeModal();
        fetchPosts(pagination.value.current_page);
    } catch (error) {
        console.error('Error saving post:', error);
        toast.error(error.response?.data?.message || 'Failed to save post');
    } finally {
        saving.value = false;
    }
};

const deletePost = async (id) => {
    if (!confirm('Are you sure you want to delete this post?')) return;
    
    try {
        await axios.delete(`/admin/blogs/${id}`);
        toast.success('Post deleted successfully');
        fetchPosts(pagination.value.current_page);
    } catch (error) {
        console.error('Error deleting post:', error);
        toast.error('Failed to delete post');
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(() => {
    fetchPosts();
});
</script>
<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
