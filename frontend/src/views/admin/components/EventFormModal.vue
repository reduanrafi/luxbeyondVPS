<template>
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-zinc-900 rounded-2xl shadow-2xl border border-white/10 max-w-4xl w-full my-8">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-white/10">
                <h3 class="text-2xl font-bold text-white">
                    {{ event ? 'Edit Event' : 'Create New Event' }}
                </h3>
                <button @click="$emit('close')"
                    class="p-2 hover:bg-white/10 rounded-lg transition-colors text-zinc-400 hover:text-white">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit"
                class="p-6 space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar">
                <!-- Tabs -->
                <div class="border-b border-white/10">
                    <div class="flex gap-4">
                        <button type="button" @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-b-2 bg-primary text-primary-500' : 'text-zinc-400 hover:text-white'"
                            class="pb-3 px-2 font-medium text-sm transition-colors">
                            General
                        </button>
                        <button type="button" @click="activeTab = 'display'"
                            :class="activeTab === 'display' ? 'border-b-2 bg-primary text-primary-500' : 'text-zinc-400 hover:text-white'"
                            class="pb-3 px-2 font-medium text-sm transition-colors">
                            Display Settings
                        </button>
                        <button type="button" @click="activeTab = 'products'"
                            :class="activeTab === 'products' ? 'border-b-2 bg-primary text-primary-500' : 'text-zinc-400 hover:text-white'"
                            class="pb-3 px-2 font-medium text-sm transition-colors">
                            Products ({{ selectedProducts.length }})
                        </button>
                        <button type="button" @click="activeTab = 'schedule'"
                            :class="activeTab === 'schedule' ? 'border-b-2 bg-primary text-primary-500' : 'text-zinc-400 hover:text-white'"
                            class="pb-3 px-2 font-medium text-sm transition-colors">
                            Schedule
                        </button>
                        <button type="button" @click="activeTab = 'notifications'"
                            :class="activeTab === 'notifications' ? 'border-b-2 bg-primary text-primary-500' : 'text-zinc-400 hover:text-white'"
                            class="pb-3 px-2 font-medium text-sm transition-colors">
                            Notifications
                        </button>
                    </div>
                </div>

                <!-- General Tab -->
                <div v-show="activeTab === 'general'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Event Name *</label>
                            <input v-model="form.name" type="text" required
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.name ? 'border-red-500/50' : ''"
                                placeholder="Flash Sale, Black Friday, etc.">
                            <p v-if="errors.name" class="text-xs text-red-400 mt-1">{{ errors.name[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Slug</label>
                            <input v-model="form.slug" type="text"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.slug ? 'border-red-500/50' : ''" placeholder="auto-generated">
                            <p v-if="errors.slug" class="text-xs text-red-400 mt-1">{{ errors.slug[0] }}</p>
                            <p v-else class="text-xs text-zinc-500 mt-1">Leave empty to auto-generate from name</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Short Description</label>
                        <input v-model="form.short_description" type="text"
                            class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                            :class="errors.short_description ? 'border-red-500/50' : ''"
                            placeholder="Brief description for banners">
                        <p v-if="errors.short_description" class="text-xs text-red-400 mt-1">{{
                            errors.short_description[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Description</label>
                        <textarea v-model="form.description" rows="4"
                            class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                            :class="errors.description ? 'border-red-500/50' : ''"
                            placeholder="Full event description..."></textarea>
                        <p v-if="errors.description" class="text-xs text-red-400 mt-1">{{ errors.description[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Custom URL</label>
                        <input v-model="form.url" type="text"
                            class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                            :class="errors.url ? 'border-red-500/50' : ''" placeholder="/shop?events=event-slug">
                        <p v-if="errors.url" class="text-xs text-red-400 mt-1">{{ errors.url[0] }}</p>
                        <p v-else class="text-xs text-zinc-500 mt-1">Optional custom URL for the event</p>
                    </div>
                </div>

                <!-- Display Settings Tab -->
                <div v-show="activeTab === 'display'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Position *</label>
                            <select v-model="form.position" required
                                class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.position ? 'border-red-500/50' : ''">
                                <option value="hero" class="bg-zinc-900">Hero Banner</option>
                                <option value="sidebar" class="bg-zinc-900">Sidebar</option>
                                <option value="both" class="bg-zinc-900">Both</option>
                            </select>
                            <p v-if="errors.position" class="text-xs text-red-400 mt-1">{{ errors.position[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Priority</label>
                            <input v-model.number="form.priority" type="number" min="0"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.priority ? 'border-red-500/50' : ''">
                            <p v-if="errors.priority" class="text-xs text-red-400 mt-1">{{ errors.priority[0] }}</p>
                            <p v-else class="text-xs text-zinc-500 mt-1">Higher priority shows first</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <input v-model="form.show_button" type="checkbox" id="show_button"
                            class="w-4 h-4 text-primary-500 rounded border-white/20 bg-white/5 focus:ring-primary-500">
                        <label for="show_button" class="text-sm font-medium text-zinc-300">Show Button</label>
                        <span class="text-xs text-zinc-500">If unchecked, only image will be displayed</span>
                    </div>

                    <div v-if="form.show_button" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Button Text</label>
                            <input v-model="form.button_text" type="text"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                placeholder="Shop Now">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Button Color</label>
                            <div class="flex gap-2">
                                <input v-model="form.button_color" type="color"
                                    class="h-10 w-20 border border-white/10 rounded-lg cursor-pointer bg-transparent">
                                <input v-model="form.button_color" type="text"
                                    class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                    placeholder="#f59e0b">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Event Image</label>
                            <input type="file" @change="handleImageUpload" accept="image/*"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-black hover:file:bg-primary">
                            <div v-if="imagePreview" class="mt-2">
                                <img :src="imagePreview"
                                    class="h-32 w-full object-contain rounded-lg border border-white/10">
                            </div>
                            <div v-else-if="form.image_url" class="mt-2">
                                <img :src="form.image_url"
                                    class="h-32 w-full object-contain rounded-lg border border-white/10">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">
                                Banner Image <span class="text-red-400">*</span>
                            </label>
                            <input type="file" @change="handleBannerUpload" accept="image/*"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-black hover:file:bg-primary"
                                :class="errors.banner_image ? 'border-red-500/50' : ''">
                            <div v-if="bannerPreview" class="mt-2">
                                <img :src="bannerPreview"
                                    class="h-32 w-full object-contain rounded-lg border border-white/10">
                            </div>
                            <div v-else-if="form.banner_image_url" class="mt-2">
                                <img :src="form.banner_image_url"
                                    class="h-32 w-full object-contain rounded-lg border border-white/10">
                            </div>
                            <p v-if="errors.banner_image" class="text-xs text-red-400 mt-1">{{ errors.banner_image[0] }}
                            </p>
                            <p v-else class="text-xs text-zinc-500 mt-1">Required for hero/sidebar display</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Price Type</label>
                        <select v-model="form.price_type"
                            class="w-full px-3 py-2 bg-zinc-800 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                            :class="errors.price_type ? 'border-red-500/50' : ''">
                            <option value="fixed" class="bg-zinc-900">Fixed Price</option>
                            <option value="percentage" class="bg-zinc-900">Percentage Discount</option>
                        </select>
                        <p v-if="errors.price_type" class="text-xs text-red-400 mt-1">{{ errors.price_type[0] }}</p>
                    </div>

                    <div v-if="form.price_type === 'fixed'">
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Fixed Price (for all
                            products)</label>
                        <input v-model.number="form.price" type="number" step="0.01" min="0" placeholder="0.00"
                            class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                            :class="errors.price ? 'border-red-500/50' : ''">
                        <p v-if="errors.price" class="text-xs text-red-400 mt-1">{{ errors.price[0] }}</p>
                        <p v-else class="text-xs text-zinc-500 mt-1">All products in this event will be sold at this
                            fixed price. Leave empty to use product's original price.</p>
                    </div>

                    <div v-if="form.price_type === 'percentage'">
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Discount Percentage</label>
                        <div class="flex items-center gap-2">
                            <input v-model.number="form.discount_percentage" type="number" step="0.01" min="0" max="100"
                                placeholder="0.00"
                                class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.discount_percentage ? 'border-red-500/50' : ''">
                            <span class="text-zinc-500">%</span>
                        </div>
                        <p v-if="errors.discount_percentage" class="text-xs text-red-400 mt-1">{{
                            errors.discount_percentage[0] }}</p>
                        <p v-else class="text-xs text-zinc-500 mt-1">Percentage discount will be applied to all products
                            in this event (e.g., 20% off).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Background Color (if no
                            image)</label>
                        <div class="flex gap-2">
                            <input v-model="form.bg_color" type="color"
                                class="h-10 w-20 border border-white/10 rounded-lg cursor-pointer bg-transparent">
                            <input v-model="form.bg_color" type="text"
                                class="flex-1 px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.bg_color ? 'border-red-500/50' : ''" placeholder="#f59e0b">
                        </div>
                        <p v-if="errors.bg_color" class="text-xs text-red-400 mt-1">{{ errors.bg_color[0] }}</p>
                        <p v-else class="text-xs text-zinc-500 mt-1">Background color will be used when no image is
                            provided</p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" id="is_active"
                                class="w-4 h-4 text-primary-500 rounded border-white/20 bg-white/5 focus:ring-primary-500">
                            <label for="is_active" class="text-sm font-medium text-zinc-300">Active</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Sort Order</label>
                            <input v-model.number="form.sort_order" type="number" min="0"
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                        </div>
                    </div>
                </div>

                <!-- Products Tab -->
                <div v-show="activeTab === 'products'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-white">Assign Products</h4>
                        <div class="flex gap-2">
                            <input v-model="productSearch" type="text" placeholder="Search products..."
                                class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                @input="searchProducts">
                            <button type="button" @click="showProductModal = true"
                                class="px-4 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-colors text-sm">
                                Add Products
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedProducts.length === 0" class="text-center py-8 text-zinc-500">
                        No products selected. Click "Add Products" to assign products to this event.
                    </div>

                    <div v-else class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
                        <div v-for="(product, index) in selectedProducts" :key="product.id"
                            class="flex items-center gap-3 p-3 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10">
                            <img :src="product.image_url || '/assets/placeholder.webp'" :alt="product.name"
                                class="w-12 h-12 object-contain rounded">
                            <div class="flex-1">
                                <div class="font-medium text-white">{{ product.name }}</div>
                                <div class="text-sm text-zinc-400">৳{{ formatPrice(product.sellable_price ||
                                    product.price) }}</div>
                            </div>
                            <button type="button" @click="removeProduct(index)"
                                class="p-2 hover:bg-white/10 rounded-lg transition-colors text-red-400 hover:text-red-300">
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Schedule Tab -->
                <div v-show="activeTab === 'schedule'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Start Date & Time *</label>
                            <input v-model="form.start_date" type="datetime-local" required
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.start_date ? 'border-red-500/50' : ''">
                            <p v-if="errors.start_date" class="text-xs text-red-400 mt-1">{{ errors.start_date[0] }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">End Date & Time *</label>
                            <input v-model="form.end_date" type="datetime-local" required
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50"
                                :class="errors.end_date ? 'border-red-500/50' : ''">
                            <p v-if="errors.end_date" class="text-xs text-red-400 mt-1">{{ errors.end_date[0] }}</p>
                        </div>
                    </div>

                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <Calendar class="w-5 h-5 text-blue-400 mt-0.5" />
                            <div>
                                <div class="font-medium text-blue-100">Event Status</div>
                                <div class="text-sm text-blue-300 mt-1">
                                    <div v-if="eventStatus === 'live'" class="text-emerald-400 font-semibold">● Live Now
                                    </div>
                                    <div v-else-if="eventStatus === 'upcoming'" class="text-primary-400 font-semibold">●
                                        Upcoming</div>
                                    <div v-else-if="eventStatus === 'expired'" class="text-red-400 font-semibold">●
                                        Expired</div>
                                    <div v-else class="text-zinc-400">● Not scheduled</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div v-show="activeTab === 'notifications'" class="space-y-4">
                    <div class="bg-primary/10 border bg-primary/20 rounded-lg p-4">
                        <div class="flex items-start gap-2">
                            <div>
                                <div class="font-medium text-primary-500 mb-2">Email Notifications</div>
                                <p class="text-sm text-primary-200/80">
                                    When enabled, all registered users will receive email notifications about this
                                    event.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <input v-model="form.send_notification" type="checkbox" id="send_notification"
                                class="w-4 h-4 text-primary-500 rounded border-white/20 bg-white/5 focus:ring-primary-500">
                            <label for="send_notification" class="text-sm font-medium text-zinc-300">
                                Send Email Notifications
                            </label>
                        </div>

                        <div v-if="form.send_notification"
                            class="bg-white/5 border border-white/10 rounded-lg p-4 space-y-2">
                            <div class="text-sm text-zinc-300">
                                <strong>Notifications will be sent:</strong>
                            </div>
                            <ul class="text-sm text-zinc-400 space-y-1 ml-4 list-disc">
                                <li>When the event is created (if enabled)</li>
                                <li>On the event start date (automatically)</li>
                            </ul>
                            <p class="text-xs text-zinc-500 mt-2">
                                All registered users with email addresses will receive notifications.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-white/10">
                    <button type="button" @click="$emit('close')"
                        class="flex-1 px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors font-medium text-zinc-300">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                        class="flex-1 px-4 py-2 bg-primary text-black rounded-lg hover:bg-primary transition-colors font-bold disabled:opacity-50">
                        {{ saving ? 'Saving...' : (event ? 'Update Event' : 'Create Event') }}
                    </button>
                </div>
            </form>

            <!-- Product Selection Modal -->
            <div v-if="showProductModal"
                class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
                <div
                    class="bg-zinc-900 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[80vh] overflow-hidden flex flex-col border border-white/10">
                    <div class="p-6 border-b border-white/10 flex items-center justify-between">
                        <h4 class="text-xl font-bold text-white">Select Products</h4>
                        <button @click="showProductModal = false"
                            class="p-2 hover:bg-white/10 rounded-lg text-zinc-400 hover:text-white">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                        <div v-if="availableProducts.length === 0" class="text-center py-8 text-zinc-500">
                            No products found
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="product in availableProducts" :key="product.id"
                                class="flex items-center gap-3 p-3 border border-white/10 rounded-lg hover:bg-white/5 cursor-pointer"
                                @click="toggleProduct(product)">
                                <input type="checkbox" :checked="isProductSelected(product.id)"
                                    class="w-4 h-4 text-primary-500 rounded border-white/20 bg-white/5 focus:ring-primary-500">
                                <img :src="product.image_url || '/assets/placeholder.webp'" :alt="product.name"
                                    class="w-12 h-12 object-contain rounded">
                                <div class="flex-1">
                                    <div class="font-medium text-white">{{ product.name }}</div>
                                    <div class="text-sm text-zinc-400">৳{{ formatPrice(product.sellable_price ||
                                        product.price) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-white/10 flex gap-3">
                        <button @click="showProductModal = false"
                            class="flex-1 px-4 py-2 border border-white/10 rounded-lg hover:bg-white/5 transition-colors font-medium text-zinc-300">
                            Cancel
                        </button>
                        <button @click="confirmProductSelection"
                            class="flex-1 px-4 py-2 bg-primary text-black rounded-lg hover:bg-primary transition-colors font-bold">
                            Add Selected ({{ tempSelectedProducts.length }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { X, Calendar } from 'lucide-vue-next';
import axios from '../../../axios';

const props = defineProps({
    event: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'saved']);

const activeTab = ref('general');
const saving = ref(false);
const showProductModal = ref(false);
const productSearch = ref('');
const availableProducts = ref([]);
const selectedProducts = ref([]);
const tempSelectedProducts = ref([]);
const errors = ref({});

const form = ref({
    name: '',
    slug: '',
    description: '',
    short_description: '',
    image: null,
    banner_image: null,
    bg_color: '#7c3aed',
    position: 'hero',
    url: '',
    show_button: true,
    button_text: 'Shop Now',
    button_color: '#7c3aed',
    price: null,
    price_type: 'fixed',
    discount_percentage: null,
    start_date: '',
    end_date: '',
    is_active: true,
    send_notification: false,
    sort_order: 0,
    priority: 0,
    products: []
});

const imagePreview = ref(null);
const bannerPreview = ref(null);

// Format date for datetime-local input
const formatDateTimeLocal = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const eventStatus = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return null;
    const now = new Date();
    const start = new Date(form.value.start_date);
    const end = new Date(form.value.end_date);

    if (start <= now && end >= now) return 'live';
    if (start > now) return 'upcoming';
    if (end < now) return 'expired';
    return null;
});

const resetForm = () => {
    form.value = {
        name: '',
        slug: '',
        description: '',
        short_description: '',
        image: null,
        banner_image: null,
        bg_color: '#7c3aed',
        position: 'hero',
        url: '',
        show_button: true,
        button_text: 'Shop Now',
        button_color: '#7c3aed',
        price: null,
        price_type: 'fixed',
        discount_percentage: null,
        start_date: '',
        end_date: '',
        is_active: true,
        send_notification: false,
        sort_order: 0,
        priority: 0,
        products: []
    };
    selectedProducts.value = [];
    imagePreview.value = null;
    bannerPreview.value = null;
};

watch(() => props.event, (newEvent) => {
    errors.value = {}; // Clear errors when event changes
    if (newEvent) {
        form.value = {
            name: newEvent.name || '',
            slug: newEvent.slug || '',
            description: newEvent.description || '',
            short_description: newEvent.short_description || '',
            image: null,
            banner_image: null,
            bg_color: newEvent.bg_color || '#7c3aed',
            position: newEvent.position || 'hero',
            url: newEvent.url || '',
            show_button: newEvent.show_button !== undefined ? newEvent.show_button : true,
            button_text: newEvent.button_text || 'Shop Now',
            button_color: newEvent.button_color || '#7c3aed',
            price: newEvent.price || null,
            price_type: newEvent.price_type || 'fixed',
            discount_percentage: newEvent.discount_percentage || null,
            start_date: newEvent.start_date ? formatDateTimeLocal(newEvent.start_date) : '',
            end_date: newEvent.end_date ? formatDateTimeLocal(newEvent.end_date) : '',
            is_active: newEvent.is_active !== undefined ? Boolean(newEvent.is_active) : true,
            send_notification: newEvent.send_notification !== undefined ? Boolean(newEvent.send_notification) : false,
            sort_order: newEvent.sort_order || 0,
            priority: newEvent.priority || 0,
            products: []
        };
        selectedProducts.value = newEvent.products || [];
        imagePreview.value = newEvent.image_url;
        bannerPreview.value = newEvent.banner_image_url;
    } else {
        resetForm();
    }
}, { immediate: true });

const formatPrice = (price) => {
    if (!price) return '0.00';
    return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleBannerUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.banner_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const searchProducts = async () => {
    try {
        const response = await axios.get('/admin/events/products/search', {
            params: {
                search: productSearch.value
            }
        });
        availableProducts.value = response.data || [];
    } catch (error) {
        console.error('Error searching products:', error);
        availableProducts.value = [];
    }
};

const isProductSelected = (productId) => {
    return tempSelectedProducts.value.some(p => p.id === productId) ||
        selectedProducts.value.some(p => p.id === productId);
};

const toggleProduct = (product) => {
    const index = tempSelectedProducts.value.findIndex(p => p.id === product.id);
    if (index > -1) {
        tempSelectedProducts.value.splice(index, 1);
    } else {
        tempSelectedProducts.value.push(product);
    }
};

const confirmProductSelection = () => {
    // Merge with existing selected products, avoiding duplicates
    const existingIds = selectedProducts.value.map(p => p.id);
    const newProducts = tempSelectedProducts.value.filter(p => !existingIds.includes(p.id));
    selectedProducts.value = [...selectedProducts.value, ...newProducts];
    tempSelectedProducts.value = [];
    showProductModal.value = false;
};

const removeProduct = (index) => {
    selectedProducts.value.splice(index, 1);
};

const handleSubmit = async () => {
    saving.value = true;
    try {
        const formData = new FormData();

        // Add all form fields
        Object.keys(form.value).forEach(key => {
            if (key !== 'image' && key !== 'banner_image' && key !== 'products' && key !== 'image_url' && key !== 'banner_image_url') {
                const value = form.value[key];
                // Always send the value, even if it's null or empty (Laravel will handle it)
                if (value !== null && value !== undefined) {
                    // Convert boolean values properly for Laravel validation
                    if (key === 'is_active' || key === 'show_button' || key === 'send_notification') {
                        formData.append(key, value ? '1' : '0');
                    } else {
                        // Send empty string for nullable fields if value is empty
                        formData.append(key, value === '' ? '' : value);
                    }
                }
            }
        });

        // Add images
        if (form.value.image) {
            formData.append('image', form.value.image);
        }
        if (form.value.banner_image) {
            formData.append('banner_image', form.value.banner_image);
        }

        // Add products
        const productIds = selectedProducts.value.map(p => p.id);
        productIds.forEach(id => formData.append('products[]', id));

        let response;
        if (props.event) {
            formData.append('_method', 'PUT');
            response = await axios.post(`/admin/events/${props.event.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            response = await axios.post('/admin/events', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }

        emit('saved');
        errors.value = {}; // Clear errors on success
    } catch (error) {
        console.error('Error saving event:', error);
        if (error.response && error.response.status === 422) {
            // Validation errors
            errors.value = error.response.data.errors || {};
            // Switch to the first tab with errors
            if (errors.value.name || errors.value.slug || errors.value.description || errors.value.short_description || errors.value.url) {
                activeTab.value = 'general';
            } else if (errors.value.start_date || errors.value.end_date) {
                activeTab.value = 'schedule';
            } else if (errors.value.position || errors.value.banner_image) {
                activeTab.value = 'display';
            }
        } else {
            // Other errors
            alert(error.response?.data?.message || 'Failed to save event');
        }
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    searchProducts();
});
</script>
