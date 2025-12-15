<template>
    <div class="space-y-6 pb-20">
        <!-- Sticky Header -->
        <div class="sticky top-16 z-20 bg-zinc-900/95 backdrop-blur-sm py-4 border-b border-white/5 -mx-6 px-6 mb-6">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <div>
                    <h2 class="text-2xl font-bold text-white">{{ isEditing ? 'Edit Product' : 'Add New Product' }}
                    </h2>
                    <p class="text-sm text-zinc-400 mt-1">
                        {{ isEditing
                        ? 'Update product details and inventory' :
                        "Create a new product with variants and media" }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <button @click="router.back()"
                        class="px-4 py-2 bg-transparent border border-white/10 rounded-lg hover:bg-white/5 transition-colors font-medium text-zinc-400 shadow-sm">
                        Cancel
                    </button>
                    <button @click="saveProduct" :disabled="loading"
                        class="px-6 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-all shadow-lg shadow-primary-500/20 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <Save class="w-5 h-5" />
                        {{ loading ? 'Saving...' : 'Save Product' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <!-- Tabs -->
            <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 overflow-hidden mb-6">
                <div class="flex border-b border-white/5 overflow-x-auto">
                    <button v-for="tab in tabs" :key="tab.id" @click="currentTab = tab.id"
                        class="px-6 py-4 text-sm font-bold transition-colors relative whitespace-nowrap"
                        :class="currentTab === tab.id ? 'text-primary-500 bg-primary/5' : 'text-zinc-500 hover:text-white hover:bg-white/5'">
                        {{ tab.label }}
                        <div v-if="currentTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary">
                        </div>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-zinc-900 rounded-2xl shadow-lg border border-white/5 p-6 md:p-8">
                <!-- General Tab -->
                <div v-show="currentTab === 'general'" class="space-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column: Basic Info -->
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Product Name *</label>
                                <input type="text" v-model="form.name" required
                                    placeholder="e.g. Premium Leather Jacket"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 transition-all">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">
                                    URL Slug
                                    <span class="text-xs font-normal text-zinc-500 ml-2">(Auto-generated from
                                        name)</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="text" v-model="form.slug" 
                                        placeholder="premium-leather-jacket"
                                        class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 transition-all font-mono text-sm">
                                    <button 
                                        type="button"
                                        @click="generateSlug"
                                        class="px-4 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-colors text-sm font-medium text-zinc-400 hover:text-white"
                                        title="Regenerate slug from product name">
                                        Regenerate
                                    </button>
                                </div>
                                <p class="text-xs text-zinc-500 mt-1">Used in product URL: /products/{{ form.slug ||
                                    'your-slug' }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-2">Brand</label>
                                    <select v-model="form.brand"
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                                        <option value="" class="bg-zinc-900">Select Brand (Optional)</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.name"
                                            class="bg-zinc-900">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-2">SKU *</label>
                                    <input type="text" v-model="form.sku" required placeholder="e.g. PRD-001"
                                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 transition-all font-mono">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Short Description</label>
                                <textarea v-model="form.short_description" rows="5"
                                    placeholder="Brief summary for product cards..."
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 transition-all"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-2">Long Description
                                    (R&D)</label>
                                <div class="prose max-w-none dark-editor">
                                    <QuillEditor :key="editorKey" v-model:content="form.description" contentType="html"
                                        theme="snow" toolbar="full" />
                                </div>
                                <p class="text-xs text-zinc-500 mt-2">Use this editor to provide detailed product
                                    information, specifications, and formatting.</p>
                            </div>
                        </div>

                        <!-- Right Column: Category & Organization -->
                        <div class="space-y-6">
                            <div class="bg-white/5 p-6 rounded-xl border border-white/10">
                                <h3 class="font-bold text-white mb-4">Organization</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-400 mb-2">Category *</label>
                                        <select v-model="form.category" required
                                            class="w-full px-4 py-3 bg-zinc-900 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                                            <option value="" class="bg-zinc-900">Select Category</option>
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.name"
                                                class="bg-zinc-900">
                                                {{ cat.parent ? cat.parent.name + ' → ' : '' }}{{ cat.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-400 mb-2">Status</label>
                                        <select v-model="form.status"
                                            class="w-full px-4 py-3 bg-zinc-900 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50">
                                            <option value="published" class="bg-zinc-900">Published</option>
                                            <option value="draft" class="bg-zinc-900">Draft</option>
                                            <option value="archived" class="bg-zinc-900">Archived</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 pt-2">
                                        <input type="checkbox" v-model="form.is_featured" id="is_featured"
                                            class="w-4 h-4 text-primary-500 rounded border-white/20 bg-white/5 focus:ring-primary-500">
                                        <label for="is_featured" class="text-sm font-medium text-zinc-300">
                                            Featured Product
                                        </label>
                                    </div>
                                    <p class="text-xs text-zinc-500">Featured products will be displayed on the home
                                        page</p>
                                </div>
                            </div>

                            <div class="bg-white/5 p-6 rounded-xl border border-white/10">
                                <h3 class="font-bold text-white mb-4">Pricing (Base)</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-400 mb-2">Regular Price
                                                (৳)
                                                *</label>
                                            <input type="number" v-model="form.price" required min="0" step="0.01"
                                                class="w-full px-4 py-3 bg-zinc-900 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 font-mono text-lg">
                                            <p class="text-xs text-zinc-500 mt-1">Base price before any discounts</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-zinc-400 mb-2">Sellable Price
                                                (৳)</label>
                                            <input type="number" v-model="form.sellable_price" min="0" step="0.01"
                                                placeholder="Leave empty if no discount"
                                                class="w-full px-4 py-3 bg-zinc-900 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 font-mono text-lg">
                                            <p class="text-xs text-zinc-500 mt-1">Discounted/sale price (optional)</p>
                                        </div>
                                    </div>
                                    <div v-if="!form.has_variants">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-400 mb-2">Stock Quantity *</label>
                                                <input type="number" v-model="form.stock" required min="0"
                                                    class="w-full px-4 py-3 bg-zinc-900 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 font-mono">
                                                <p class="text-xs text-zinc-500 mt-1">Available quantity</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-400 mb-2">Weight (kg)</label>
                                                <input type="number" v-model="form.weight" min="0" step="0.01"
                                                    class="w-full px-4 py-3 bg-zinc-900 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 font-mono">
                                                <p class="text-xs text-zinc-500 mt-1">Shipping weight</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants Tab -->
                <div v-show="currentTab === 'variants'" class="space-y-8">
                    <div
                        class="flex items-center justify-between bg-purple-500/10 p-4 rounded-xl border border-purple-500/20">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-500/20 rounded-lg shadow-sm">
                                <Layers class="w-6 h-6 text-purple-400" />
                            </div>
                            <div>
                                <h3 class="font-bold text-white">Product Variants</h3>
                                <p class="text-sm text-zinc-400">Manage different versions of this product (e.g. Size,
                                    Color)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.has_variants" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-zinc-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                                <span class="ml-3 text-sm font-medium text-zinc-300">Enable Variants</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="form.has_variants" class="space-y-8 animate-in fade-in slide-in-from-top-4 duration-300">
                        <!-- Variant Generator -->
                        <div class="bg-white/5 p-6 rounded-xl border border-white/10">
                            <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                                <Wand2 class="w-4 h-4 text-primary-500" /> Variant Generator (Attribute Based Pricing)
                            </h4>
                            <div class="space-y-6">
                                <div v-for="(attr, attrIndex) in variantAttributes" :key="attrIndex"
                                    class="bg-zinc-900 p-4 rounded-lg border border-white/10 shadow-sm">
                                    <div class="flex justify-between items-center mb-4">
                                        <input type="text" v-model="attr.name" placeholder="Attribute Name (e.g. Size)"
                                            class="font-bold bg-transparent text-white border-b border-transparent hover:border-white/20 focus:bg-primary focus:outline-none px-2 py-1 transition-colors w-1/3 placeholder:text-zinc-600">
                                        <button type="button" @click="removeAttribute(attrIndex)"
                                            class="text-red-400 hover:text-red-300 text-sm font-medium">
                                            Remove Attribute
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <div v-for="(value, valIndex) in attr.values" :key="valIndex"
                                            class="flex items-center gap-4">
                                            <div class="flex-1">
                                                <input type="text" v-model="value.name"
                                                    placeholder="Value (e.g. Red, XL)"
                                                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 text-sm">
                                            </div>
                                            <div class="w-32">
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2 text-zinc-500 text-sm">+৳</span>
                                                    <input type="number" v-model="value.price_adjustment"
                                                        placeholder="0"
                                                        class="w-full pl-8 pr-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 text-sm">
                                                </div>
                                            </div>
                                            <div class="w-12">
                                                <div
                                                    class="relative w-10 h-10 bg-white/5 rounded-lg border border-white/10 flex items-center justify-center overflow-hidden cursor-pointer hover:bg-primary transition-colors">
                                                    <input type="file"
                                                        @change="e => handleAttributeImageUpload(e, attrIndex, valIndex)"
                                                        accept="image/*"
                                                        class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                                    <img v-if="value.image_preview" :src="value.image_preview"
                                                        class="w-full h-full object-contain">
                                                    <ImageIcon v-else class="w-4 h-4 text-zinc-600" />
                                                </div>
                                            </div>
                                            <button type="button" @click="removeAttributeValue(attrIndex, valIndex)"
                                                class="p-2 text-zinc-500 hover:text-red-400 transition-colors">
                                                <X class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <button type="button" @click="addAttributeValue(attrIndex)"
                                            class="text-sm text-primary-500 font-medium hover:text-primary-400 flex items-center gap-1 mt-2">
                                            <Plus class="w-3 h-3" /> Add Value
                                        </button>
                                    </div>
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button type="button" @click="addAttribute"
                                        class="px-4 py-2 bg-transparent border border-white/10 text-zinc-300 font-bold rounded-lg hover:bg-white/5 transition-colors shadow-sm flex items-center gap-2">
                                        <Plus class="w-4 h-4" /> Add Attribute
                                    </button>
                                    <button type="button" @click="generateVariants"
                                        class="ml-auto px-6 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary transition-colors shadow-lg shadow-primary-500/20 flex items-center gap-2">
                                        <Wand2 class="w-4 h-4" /> Generate Variants
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Variants Table -->
                        <div v-if="form.variants.length > 0" class="border border-white/10 rounded-xl overflow-hidden">
                            <table class="min-w-full divide-y divide-white/10">
                                <thead class="bg-white/5">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            Variant</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            Image</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            SKU</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            Price</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            Stock</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            Weight (kg)</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-zinc-900 divide-y divide-white/10">
                                    <tr v-for="(variant, index) in form.variants" :key="index"
                                        class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                                            <div class="flex gap-2">
                                                <span v-for="(value, key) in parseAttributes(variant.attributes_json)"
                                                    :key="key"
                                                    class="px-2 py-1 bg-white/5 rounded text-xs text-zinc-400 border border-white/10">
                                                    {{ key }}: {{ value }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="relative w-12 h-12 bg-white/5 rounded-lg border border-white/10 flex items-center justify-center overflow-hidden group cursor-pointer">
                                                <input type="file" @change="e => handleVariantImageUpload(e, index)"
                                                    accept="image/*"
                                                    class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                                <img v-if="variant.image_preview" :src="variant.image_preview"
                                                    class="w-full h-full object-contain">
                                                <img v-else-if="variant.image_url" :src="variant.image_url"
                                                    class="w-full h-full object-contain">
                                                <img v-else-if="variant.image" 
                                                    :src="variant.image.startsWith('http') || variant.image.startsWith('/') ? variant.image : `/storage/${variant.image}`"
                                                    class="w-full h-full object-contain">
                                                <ImageIcon v-else class="w-5 h-5 text-zinc-600" />
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" v-model="variant.sku"
                                                class="w-full px-2 py-1 bg-white/5 border border-white/10 rounded text-sm text-white focus:outline-none focus:bg-primary">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" v-model="variant.price"
                                                class="w-32 px-2 py-1 bg-white/5 border border-white/10 rounded text-sm text-white focus:outline-none focus:bg-primary">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" v-model="variant.stock"
                                                class="w-24 px-2 py-1 bg-white/5 border border-white/10 rounded text-sm text-white focus:outline-none focus:bg-primary">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" v-model="variant.weight" step="0.01"
                                                class="w-24 px-2 py-1 bg-white/5 border border-white/10 rounded text-sm text-white focus:outline-none focus:bg-primary">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button type="button" @click="removeVariant(index)"
                                                class="text-red-400 hover:text-red-300 transition-colors">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 bg-white/5 rounded-xl border border-dashed border-white/20">
                        <Layers class="w-12 h-12 text-zinc-600 mx-auto mb-3" />
                        <h3 class="text-lg font-medium text-white">No Variants Enabled</h3>
                        <p class="text-zinc-500">Enable variants to manage different options for this product.</p>
                    </div>
                </div>

                <!-- Gallery Tab -->
                <div v-show="currentTab === 'gallery'" class="space-y-8">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-4">Main Product Image</label>
                        <div class="flex items-start gap-6">
                            <div
                                class="w-48 h-48 bg-white/5 rounded-xl border-2 border-dashed border-white/20 flex items-center justify-center relative overflow-hidden group hover:bg-primary transition-colors cursor-pointer">
                                <input type="file" @change="handleMainImageUpload" accept="image/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                <img v-if="mainImagePreview" :src="mainImagePreview" class="w-full h-full object-contain">
                                <img v-else-if="form.image_url" :src="form.image_url"
                                    class="w-full h-full object-contain">
                                <div v-else class="text-center p-4">
                                    <ImageIcon
                                        class="w-8 h-8 text-zinc-600 mx-auto mb-2 group-hover:text-primary-500 transition-colors" />
                                    <span class="text-xs text-zinc-500 font-medium">Upload Main Image</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-white mb-2">Image Guidelines</h4>
                                <ul class="list-disc list-inside text-sm text-zinc-400 space-y-1">
                                    <li>Recommended size: 1000x1000 pixels</li>
                                    <li>Format: JPG, PNG, or WEBP</li>
                                    <li>Max file size: 5MB</li>
                                    <li>White background preferred</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-white/5 pt-8">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-bold text-white">Gallery</h3>
                                <p class="text-sm text-zinc-400">Add additional images or videos to showcase your
                                    product.</p>
                            </div>
                            <button type="button" @click="addGalleryItem"
                                class="px-4 py-2 bg-white/5 text-zinc-300 font-bold rounded-lg hover:bg-white/10 transition-colors flex items-center gap-2 border border-white/10">
                                <Plus class="w-4 h-4" /> Add Media
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div v-for="(item, index) in form.gallery" :key="index"
                                class="group relative aspect-square bg-white/5 rounded-xl border border-white/10 overflow-hidden">
                                <!-- Existing Item -->
                                <template v-if="item.id">
                                    <img v-if="item.type === 'image'" :src="item.url"
                                        class="w-full h-full object-contain">
                                    <video v-else :src="item.url" class="w-full h-full object-contain"></video>
                                    <div
                                        class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <span class="text-white text-xs font-bold uppercase">{{ item.type }}</span>
                                    </div>
                                </template>
                                <!-- New Item -->
                                <template v-else>
                                    <div v-if="item.preview" class="w-full h-full">
                                        <img v-if="item.type === 'image'" :src="item.preview"
                                            class="w-full h-full object-contain">
                                        <video v-else :src="item.preview" class="w-full h-full object-contain"></video>
                                    </div>
                                    <div v-else
                                        class="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                                        <input type="file" @change="e => handleGalleryFileUpload(e, index)"
                                            accept="image/*,video/*"
                                            class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                        <Plus class="w-6 h-6 text-zinc-600 mb-2" />
                                        <span class="text-xs text-zinc-500">Upload</span>
                                    </div>
                                </template>

                                <button type="button" @click="removeGalleryItem(index)"
                                    class="absolute top-2 right-2 p-1.5 bg-black/80 text-red-400 rounded-full shadow-sm opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500/20 z-20">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div v-show="currentTab === 'seo'" class="space-y-6">
                    <div class="bg-blue-500/10 p-6 rounded-xl border border-blue-500/20 mb-6">
                        <h3 class="font-bold text-blue-400 mb-2">Search Engine Optimization</h3>
                        <p class="text-sm text-blue-300">Optimize how your product appears in search engine results like
                            Google.</p>
                    </div>

                    <div class="space-y-6 max-w-3xl">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-2">Meta Title</label>
                            <input type="text" v-model="form.meta_title" placeholder="Product Name | Brand"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 transition-all">
                            <p class="text-xs text-zinc-500 mt-2 flex justify-between">
                                <span>Recommended length: 50-60 characters.</span>
                                <span :class="form.meta_title.length > 60 ? 'text-red-400' : 'text-emerald-400'">{{
                                    form.meta_title.length }} chars</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-2">Meta Description</label>
                            <textarea v-model="form.meta_description" rows="4"
                                placeholder="A brief summary of the product..."
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder:text-zinc-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:bg-primary/50 transition-all"></textarea>
                            <p class="text-xs text-zinc-500 mt-2 flex justify-between">
                                <span>Recommended length: 150-160 characters.</span>
                                <span
                                    :class="form.meta_description.length > 160 ? 'text-red-400' : 'text-emerald-400'">{{
                                    form.meta_description.length }} chars</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Save, Plus, X, Image as ImageIcon, Video, Layers, Wand2, Trash2 } from 'lucide-vue-next';
import axios from '../../axios';

const route = useRoute();
const router = useRouter();
const isEditing = computed(() => route.params.id !== undefined);
const loading = ref(false);
const categories = ref([]);
const brands = ref([]);
const editorKey = ref(0);

const currentTab = ref('general');
const tabs = [
    { id: 'general', label: 'General Info' },
    { id: 'variants', label: 'Variants & Inventory' },
    { id: 'gallery', label: 'Media Gallery' },
    { id: 'seo', label: 'SEO' },
];

const form = ref({
    name: '',
    slug: '',
    brand: '',
    category: '',
    sku: '',
    short_description: '',
    description: '',
    price: 0,
    sellable_price: null,
    stock: 0,
    weight: 0,
    image: '',
    image_url: '',
    has_variants: false,
    is_featured: false,
    status: 'published',
    variants: [],
    gallery: [],
    meta_title: '',
    meta_description: '',
    attribute_definitions: []
});

// Generate slug from name
const generateSlug = () => {
    if (form.value.name) {
        form.value.slug = form.value.name
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
    }
};

// Variant Generator State
const variantAttributes = ref([
    {
        name: 'Size',
        values: [
            { name: 'S', price_adjustment: 0, image_preview: null, image_file: null },
            { name: 'M', price_adjustment: 0, image_preview: null, image_file: null },
            { name: 'L', price_adjustment: 0, image_preview: null, image_file: null }
        ]
    }
]);

const mainImageFile = ref(null);
const mainImagePreview = ref(null);

const fetchCategories = async () => {
    try {
        const response = await axios.get('/admin/categories?all=true');
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const fetchBrands = async () => {
    try {
        const response = await axios.get('/admin/brands?all=true');
        brands.value = response.data;
    } catch (error) {
        console.error('Error fetching brands:', error);
    }
};

const fetchProduct = async () => {
    if (!isEditing.value) return;
    loading.value = true;
    try {
        const response = await axios.get(`/admin/products/${route.params.id}`);
        const product = response.data;

        // Transform variants attributes from object to JSON string for editing
        const variants = product.variants ? product.variants.map(v => ({
            ...v,
            attributes_json: JSON.stringify(v.attributes),
            // Use image_url from API (full URL) or construct from image path
            image_preview: v.image_url || (v.image ? (v.image.startsWith('http') || v.image.startsWith('/storage/') ? v.image : `/storage/${v.image}`) : null)
        })) : [];

        form.value = {
            name: product.name || '',
            slug: product.slug || '',
            brand: product.brand || '',
            category: product.category || '',
            sku: product.sku || '',
            short_description: product.short_description || '',
            description: product.description || '',
            price: product.price || 0,
            sellable_price: product.sellable_price || 0,
            stock: product.stock || 0,
            weight: product.weight || 0,
            image: product.image || '',
            image_url: product.image_url || '',
            has_variants: product.has_variants || false,
            is_featured: product.is_featured || false,
            status: product.status || 'published',
            variants,
            gallery: product.images || [],
            meta_title: product.meta_title || '',
            meta_description: product.meta_description || '',
            attribute_definitions: product.attribute_definitions || []
        };

        // Populate generator if definitions exist
        if (product.attribute_definitions && product.attribute_definitions.length > 0) {
            variantAttributes.value = product.attribute_definitions.map(attr => ({
                name: attr.name || '',
                values: (attr.values || []).map(val => {
                    // Try to find an image from existing variants
                    let imagePreview = null;
                    let imagePath = null;

                    if (product.variants && product.variants.length > 0) {
                        const matchingVariant = product.variants.find(v =>
                            v.attributes && v.attributes[attr.name] === val.name && v.image
                        );
                        if (matchingVariant) {
                            imagePath = matchingVariant.image;
                            // Use image_url if available, otherwise construct from image path
                            imagePreview = matchingVariant.image_url || (matchingVariant.image ? `/storage/${matchingVariant.image}` : null);
                        }
                    }

                    return {
                        name: val.name || '',
                        price_adjustment: parseFloat(val.price_adjustment) || 0,
                        image: imagePath,
                        image_preview: imagePreview,
                        image_file: null
                    };
                })
            }));
        } else {
            // If no attribute definitions, reset to default
            variantAttributes.value = [
                {
                    name: 'Size',
                    values: [
                        { name: 'S', price_adjustment: 0, image_preview: null, image_file: null },
                        { name: 'M', price_adjustment: 0, image_preview: null, image_file: null },
                        { name: 'L', price_adjustment: 0, image_preview: null, image_file: null }
                    ]
                }
            ];
        }
    } catch (error) {
        console.error('Error fetching product:', error);
        alert('Failed to load product details');
    } finally {
        loading.value = false;

        // Force Quill editor to re-render with new content
        editorKey.value++;
        await nextTick();
    }
};

const handleMainImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        mainImageFile.value = file;
        mainImagePreview.value = URL.createObjectURL(file);
    }
};

// Variant Logic
const addAttribute = () => {
    variantAttributes.value.push({ name: '', values: [{ name: '', price_adjustment: 0, image_preview: null, image_file: null }] });
};

const removeAttribute = (index) => {
    variantAttributes.value.splice(index, 1);
};

const addAttributeValue = (attrIndex) => {
    variantAttributes.value[attrIndex].values.push({ name: '', price_adjustment: 0, image_preview: null, image_file: null });
};

const removeAttributeValue = (attrIndex, valIndex) => {
    variantAttributes.value[attrIndex].values.splice(valIndex, 1);
};

const handleAttributeImageUpload = (event, attrIndex, valIndex) => {
    const file = event.target.files[0];
    if (file) {
        variantAttributes.value[attrIndex].values[valIndex].image_file = file;
        variantAttributes.value[attrIndex].values[valIndex].image_preview = URL.createObjectURL(file);
    }
};

const generateVariants = () => {
    if (variantAttributes.value.length === 0) return;

    // Filter out empty attributes
    const validAttributes = variantAttributes.value.filter(a => a.name && a.values.some(v => v.name));
    if (validAttributes.length === 0) return;

    // Prepare attributes for cartesian product
    const attributeMap = validAttributes.map(attr => ({
        name: attr.name.trim(),
        values: attr.values.filter(v => v.name).map(v => ({
            name: v.name.trim(),
            price_adjustment: parseFloat(v.price_adjustment) || 0,
            image: v.image,
            image_file: v.image_file,
            image_preview: v.image_preview
        }))
    }));

    // Generate Cartesian Product
    const cartesian = (args) => {
        const result = [];
        const max = args.length - 1;
        function helper(arr, i) {
            for (let j = 0, l = args[i].values.length; j < l; j++) {
                const a = arr.slice(0); // clone arr
                a.push({
                    key: args[i].name,
                    value: args[i].values[j].name,
                    price_adjustment: args[i].values[j].price_adjustment,
                    image: args[i].values[j].image,
                    image_file: args[i].values[j].image_file,
                    image_preview: args[i].values[j].image_preview
                });
                if (i == max)
                    result.push(a);
                else
                    helper(a, i + 1);
            }
        }
        helper([], 0);
        return result;
    };

    const combinations = cartesian(attributeMap);

    // Create variants
    form.value.variants = combinations.map(combo => {
        // Calculate price
        const priceAdjustment = combo.reduce((sum, item) => sum + item.price_adjustment, 0);
        const finalPrice = parseFloat(form.value.price) + priceAdjustment;

        // Construct attributes object
        const attributes = {};
        combo.forEach(item => {
            attributes[item.key] = item.value;
        });

        // Determine image (use the first one found in the combination)
        const imageItem = combo.find(item => item.image_file || item.image_preview);

        return {
            attributes_json: JSON.stringify(attributes),
            sku: `${form.value.sku}-${Object.values(attributes).join('-').toUpperCase()}`,
            price: finalPrice,
            stock: 0,
            weight: 0,
            image: imageItem ? imageItem.image : null,
            image_file: imageItem ? imageItem.image_file : null,
            image_preview: imageItem ? imageItem.image_preview : null
        };
    });

    // Save definitions to form for persistence
    form.value.attribute_definitions = variantAttributes.value.map(attr => ({
        name: attr.name,
        values: attr.values.map(v => ({
            name: v.name,
            price_adjustment: v.price_adjustment
        }))
    }));
};

const parseAttributes = (json) => {
    try {
        return JSON.parse(json);
    } catch (e) {
        return {};
    }
};

const removeVariant = (index) => {
    form.value.variants.splice(index, 1);
};

const handleVariantImageUpload = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        form.value.variants[index].image_file = file;
        form.value.variants[index].image_preview = URL.createObjectURL(file);
    }
};

// Gallery Logic
const addGalleryItem = () => {
    form.value.gallery.push({
        file: null,
        preview: null,
        type: 'image'
    });
};

const handleGalleryFileUpload = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        form.value.gallery[index].file = file;
        form.value.gallery[index].preview = URL.createObjectURL(file);
        form.value.gallery[index].type = file.type.startsWith('video') ? 'video' : 'image';
    }
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

        const formData = new FormData();
        formData.append('name', form.value.name);
        if (form.value.slug) {
            formData.append('slug', form.value.slug);
        }
        formData.append('sku', form.value.sku);
        formData.append('category', form.value.category);
        formData.append('brand', form.value.brand || '');
        formData.append('price', form.value.price);
        if (form.value.sellable_price) {
            formData.append('sellable_price', form.value.sellable_price);
        }
        formData.append('stock', form.value.stock);
        formData.append('weight', form.value.weight || 0);
        formData.append('description', form.value.description || '');
        formData.append('short_description', form.value.short_description || '');
        formData.append('meta_title', form.value.meta_title || '');
        formData.append('meta_description', form.value.meta_description || '');
        formData.append('has_variants', form.value.has_variants ? 1 : 0);
        formData.append('is_featured', form.value.is_featured ? 1 : 0);
        formData.append('status', form.value.status || 'published');
        formData.append('variants', JSON.stringify(variantsData));

        // Only append attribute_definitions if it exists and is not empty
        if (form.value.attribute_definitions && form.value.attribute_definitions.length > 0) {
            formData.append('attribute_definitions', JSON.stringify(form.value.attribute_definitions));
        }

        if (mainImageFile.value) {
            formData.append('image', mainImageFile.value);
        }

        // Handle Gallery
        const existingIds = form.value.gallery.filter(item => item.id).map(item => item.id);
        formData.append('existing_gallery_ids', JSON.stringify(existingIds));

        form.value.gallery.forEach(item => {
            if (item.file) {
                formData.append('gallery[]', item.file);
            }
        });

        // Append Variant Images
        form.value.variants.forEach((variant, index) => {
            if (variant.image_file) {
                formData.append(`variant_images[${index}]`, variant.image_file);
            }
        });

        if (isEditing.value) {
            formData.append('_method', 'PUT');
            await axios.post(`/admin/products/${route.params.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            alert('Product updated successfully');
        } else {
            await axios.post('/admin/products', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
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
    fetchBrands();
    fetchProduct();
});

// Auto-generate slug from name
watch(() => form.value.name, (newName) => {
    if (newName && (!form.value.slug || form.value.slug === generateSlugFromName(form.value.name))) {
        generateSlug();
    }
});

// Helper function to generate slug (used for comparison)
const generateSlugFromName = (name) => {
    if (!name) return '';
    return name
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

// Auto-fill meta title and description based on product name and brand
watch([() => form.value.name, () => form.value.brand], ([newName, newBrand]) => {
    // Auto-fill meta title if empty or matches previous auto-generated format
    if (newName) {
        const autoTitle = newBrand ? `${newName} | ${newBrand}` : newName;
        // Only auto-fill if meta_title is empty or was previously auto-generated
        if (!form.value.meta_title || form.value.meta_title === form.value.name ||
            form.value.meta_title.includes('|')) {
            form.value.meta_title = autoTitle;
        }
    }

    // Auto-fill meta description if empty
    if (newName && !form.value.meta_description) {
        const brandPart = newBrand ? ` by ${newBrand}` : '';
        form.value.meta_description = `Shop ${newName}${brandPart}. High quality product with great features.`;
    }
});
</script>

<style>
/* Quill Editor Customization */
.dark-editor .ql-toolbar.ql-snow {
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
    border-color: rgba(255, 255, 255, 0.1);
        background-color: rgba(255, 255, 255, 0.05);
}

.dark-editor .ql-toolbar.ql-snow .ql-stroke {
    stroke: #d4d4d8;
    /* zinc-300 */
}

.dark-editor .ql-toolbar.ql-snow .ql-fill {
    fill: #d4d4d8;
}

.dark-editor .ql-toolbar.ql-snow .ql-picker {
    color: #d4d4d8;
}

.dark-editor .ql-container.ql-snow {
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
    border-color: rgba(255, 255, 255, 0.1);
        background-color: transparent;
    min-height: 200px;
    color: white;
}
</style>
