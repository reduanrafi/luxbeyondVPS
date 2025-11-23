<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ isEditing ? 'Edit Product' : 'Add Product' }}</h2>
                <p class="text-sm text-slate-600 mt-1">{{ isEditing ? 'Update existing product details' : 'Create a new
                    product' }}</p>
            </div>
            <div class="flex gap-3">
                <button @click="router.back()"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button @click="saveProduct" :disabled="loading"
                    class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary-hover transition-all shadow-md flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <Save class="w-5 h-5" />
                    {{ loading ? 'Saving...' : 'Save Product' }}
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex border-b border-gray-200">
                <button v-for="tab in tabs" :key="tab.id" @click="currentTab = tab.id"
                    class="px-6 py-4 text-sm font-medium transition-colors relative"
                    :class="currentTab === tab.id ? 'text-primary' : 'text-slate-600 hover:text-slate-900 hover:bg-gray-50'">
                    {{ tab.label }}
                    <div v-if="currentTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></div>
                </button>
            </div>

            <div class="p-6">
                <!-- General Tab -->
                <div v-show="currentTab === 'general'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Product Name *</label>
                                <input type="text" v-model="form.name" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Brand</label>
                                <input type="text" v-model="form.brand"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Category *</label>
                                <select v-model="form.category" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 bg-white">
                                    <option value="">Select Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">SKU *</label>
                                <input type="text" v-model="form.sku" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Short Description</label>
                        <textarea v-model="form.short_description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                        <p class="text-xs text-slate-500 mt-1">Brief summary for product cards and search results.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Long Description</label>
                        <textarea v-model="form.description" rows="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                        <p class="text-xs text-slate-500 mt-1">Detailed product information.</p>
                    </div>
                </div>

                <!-- Data Tab (Pricing & Inventory) -->
                <div v-show="currentTab === 'data'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Base Price (৳) *</label>
                            <input type="number" v-model="form.price" required min="0" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Total Stock</label>
                            <input type="number" v-model="form.stock" required min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"
                                :disabled="form.has_variants">
                            <p v-if="form.has_variants" class="text-xs text-orange-500 mt-1">Managed by variants</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.has_variants" id="has_variants"
                                    class="w-4 h-4 text-primary">
                                <label for="has_variants" class="font-medium text-slate-900">Enable Attribute-based
                                    Pricing (Variants)</label>
                            </div>
                            <button v-if="form.has_variants" @click="addVariant"
                                class="text-sm text-primary hover:text-primary-hover font-medium flex items-center gap-1">
                                <Plus class="w-4 h-4" /> Add Variant
                            </button>
                        </div>

                        <div v-if="form.has_variants" class="space-y-4">
                            <div v-for="(variant, index) in form.variants" :key="index"
                                class="bg-gray-50 rounded-lg p-4 border border-gray-200 relative group">
                                <button @click="removeVariant(index)"
                                    class="absolute top-2 right-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <X class="w-4 h-4" />
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Attributes
                                            (JSON)</label>
                                        <input type="text" v-model="variant.attributes_json"
                                            placeholder='{"Color": "Red", "Size": "XL"}'
                                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">SKU</label>
                                        <input type="text" v-model="variant.sku"
                                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Price</label>
                                        <input type="number" v-model="variant.price" min="0" step="0.01"
                                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Stock</label>
                                        <input type="number" v-model="variant.stock" min="0"
                                            class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.variants.length === 0" class="text-sm text-slate-500 text-center py-4">No
                                variants added yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Gallery Tab -->
                <div v-show="currentTab === 'gallery'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Main Image URL</label>
                        <div class="flex gap-4">
                            <input type="text" v-model="form.image" placeholder="https://..."
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <div
                                class="w-16 h-10 bg-gray-100 rounded border border-gray-200 overflow-hidden flex-shrink-0">
                                <img v-if="form.image" :src="form.image" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-medium text-slate-900">Gallery Images & Videos</h3>
                            <button @click="addGalleryItem"
                                class="text-sm text-primary hover:text-primary-hover font-medium flex items-center gap-1">
                                <Plus class="w-4 h-4" /> Add Item
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="(item, index) in form.gallery" :key="index"
                                class="flex gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200 items-start">
                                <div class="flex-1 space-y-2">
                                    <input type="text" v-model="item.path" placeholder="URL"
                                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
                                    <select v-model="item.type"
                                        class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm bg-white">
                                        <option value="image">Image</option>
                                        <option value="video">Video</option>
                                    </select>
                                </div>
                                <div
                                    class="w-16 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <img v-if="item.type === 'image' && item.path" :src="item.path"
                                        class="w-full h-full object-cover">
                                    <Video v-else-if="item.type === 'video'" class="w-6 h-6 text-gray-400" />
                                    <Image v-else class="w-6 h-6 text-gray-400" />
                                </div>
                                <button @click="removeGalleryItem(index)" class="text-gray-400 hover:text-red-500">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <p v-if="form.gallery.length === 0" class="text-sm text-slate-500 text-center py-4">No gallery
                            items added.</p>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div v-show="currentTab === 'seo'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Meta Title</label>
                        <input type="text" v-model="form.meta_title"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <p class="text-xs text-slate-500 mt-1">Recommended length: 50-60 characters.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Meta Description</label>
                        <textarea v-model="form.meta_description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                        <p class="text-xs text-slate-500 mt-1">Recommended length: 150-160 characters.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Save, Plus, X, Image, Video } from 'lucide-vue-next';
import axios from '../../axios';

const route = useRoute();
const router = useRouter();
const isEditing = computed(() => route.params.id !== undefined);
const loading = ref(false);
const categories = ref([]);

const currentTab = ref('general');
const tabs = [
    { id: 'general', label: 'General' },
    { id: 'data', label: 'Data & Inventory' },
    { id: 'gallery', label: 'Gallery' },
    { id: 'seo', label: 'SEO' },
];

const form = ref({
    name: '',
    brand: '',
    category: '',
    sku: '',
    short_description: '',
    description: '',
    price: 0,
    stock: 0,
    image: '',
    has_variants: false,
    variants: [],
    gallery: [],
    meta_title: '',
    meta_description: ''
});

const fetchCategories = async () => {
    try {
        const response = await axios.get('/categories?all=true');
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const fetchProduct = async () => {
    if (!isEditing.value) return;
    loading.value = true;
    try {
        const response = await axios.get(`/products/${route.params.id}`);
        const product = response.data;

        // Transform variants attributes from object to JSON string for editing
        const variants = product.variants ? product.variants.map(v => ({
            ...v,
            attributes_json: JSON.stringify(v.attributes)
        })) : [];

        form.value = {
            ...product,
            variants,
            gallery: product.images || []
        };
    } catch (error) {
        console.error('Error fetching product:', error);
        alert('Failed to load product details');
    } finally {
        loading.value = false;
    }
};

const addVariant = () => {
    form.value.variants.push({
        attributes_json: '',
        sku: '',
        price: form.value.price,
        stock: 0
    });
};

const removeVariant = (index) => {
    form.value.variants.splice(index, 1);
};

const addGalleryItem = () => {
    form.value.gallery.push({
        path: '',
        type: 'image'
    });
};

const removeGalleryItem = (index) => {
    form.value.gallery.splice(index, 1);
};

const saveProduct = async () => {
    loading.value = true;
    try {
        // Parse variant attributes JSON
        const variantsData = form.value.variants.map(v => {
            try {
                return {
                    ...v,
                    attributes: v.attributes_json ? JSON.parse(v.attributes_json) : null
                };
            } catch (e) {
                console.error('Invalid JSON in variant attributes', e);
                return v;
            }
        });

        const payload = {
            ...form.value,
            variants: variantsData
        };

        if (isEditing.value) {
            await axios.put(`/products/${route.params.id}`, payload);
            alert('Product updated successfully');
        } else {
            await axios.post('/products', payload);
            alert('Product created successfully');
        }
        router.push('/admin/products');
    } catch (error) {
        console.error('Error saving product:', error);
        alert('Failed to save product. Please check your input.');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCategories();
    fetchProduct();
});
</script>
