<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md px-4">
        <div
            class="bg-black p-8 rounded-3xl shadow-2xl max-w-2xl w-full border border-primary overflow-hidden flex flex-col max-h-[90vh]">
            <div class="mb-6">
                <h3 class="text-2xl font-serif text-white uppercase tracking-widest mb-2">Review Your Order</h3>
                <p class="text-xs text-slate-400">Review your selected items and confirm shipping details.</p>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <!-- Selected Items Summary -->
                <div class="mb-8 space-y-4">
                    <h4 class="text-sm font-bold text-primary uppercase tracking-[0.2em] mb-4">Selected Items</h4>
                    <div class="space-y-3">
                        <div v-for="item in editableItems" :key="item.id"
                            class="bg-white/5 border border-white/5 rounded-2xl p-4 flex gap-4 hover:border-primary/20 transition-colors">
                            <div
                                class="w-16 h-16 bg-background rounded-xl overflow-hidden flex-shrink-0 border border-white/5">
                                <img :src="item.admin_image_url ? item.admin_image_url : '/assets/placeholder.webp'"
                                    class="w-full h-full object-cover opacity-80" alt="Product">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white line-clamp-1 mb-1">{{ item.product_name ||
                                    'Product Request #' + item.id }}</p>
                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center bg-background/50 rounded-lg p-1 border border-white/5">
                                        <button @click="updateQty(item.id, -1)"
                                            class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-white transition-colors">-</button>
                                        <span class="w-8 text-center text-xs font-mono text-white">{{ item.quantity
                                        }}</span>
                                        <button @click="updateQty(item.id, 1)"
                                            class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-white transition-colors">+</button>
                                    </div>
                                    <span class="text-sm font-serif text-white">৳{{ formatPrice(getItemTotal(item))
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 mt-6 space-y-3">
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Subtotal</span>
                            <span>৳{{ formatPrice(totalAmount) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-white/5">
                            <span class="text-sm font-serif text-white uppercase tracking-widest">Total Amount</span>
                            <span class="text-xl font-serif text-primary">৳{{ formatPrice(totalAmount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-8">
                    <h4 class="text-[10px] font-bold text-primary uppercase tracking-[0.2em] mb-4">Payment Method</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <label
                            class="relative flex flex-col items-center gap-3 p-4 bg-white/5 border rounded-2xl cursor-pointer transition-all"
                            :class="paymentMethod === 'bkash' ? 'border-primary ring-1 ring-primary' : 'border-white/5 hover:border-white/20'">
                            <input type="radio" v-model="paymentMethod" value="bkash" class="sr-only">
                            <div class="w-10 h-10 bg-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-white uppercase tracking-widest">bKash</span>
                        </label>
                        <label
                            class="relative flex flex-col items-center gap-3 p-4 bg-white/5 border rounded-2xl cursor-pointer transition-all"
                            :class="paymentMethod === 'bank_transfer' ? 'border-primary ring-1 ring-primary' : 'border-white/5 hover:border-white/20'">
                            <input type="radio" v-model="paymentMethod" value="bank_transfer" class="sr-only">
                            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-white uppercase tracking-widest">Bank
                                Transfer</span>
                        </label>
                    </div>

                    <!-- Payment Type Options (Partial/Full) -->
                    <div class="mt-4 space-y-3 p-4 bg-white/5 rounded-2xl border border-white/5">
                        <div class="flex gap-4">
                            <label class="flex-1 flex items-center gap-3 cursor-pointer group">
                                <input type="radio" v-model="paymentType" value="partial" class="sr-only">
                                <div class="w-4 h-4 rounded-full border border-primary flex items-center justify-center"
                                    :class="paymentType === 'partial' ? 'bg-primary' : 'bg-transparent'">
                                    <div class="w-1.5 h-1.5 bg-background rounded-full"
                                        v-if="paymentType === 'partial'"></div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-white uppercase tracking-wider">Partial (60%)
                                    </p>
                                    <p class="text-[10px] text-slate-400">৳{{ formatPrice(totalAmount * 0.6) }}</p>
                                </div>
                            </label>
                            <label class="flex-1 flex items-center gap-3 cursor-pointer group">
                                <input type="radio" v-model="paymentType" value="full" class="sr-only">
                                <div class="w-4 h-4 rounded-full border border-primary flex items-center justify-center"
                                    :class="paymentType === 'full' ? 'bg-primary' : 'bg-transparent'">
                                    <div class="w-1.5 h-1.5 bg-background rounded-full" v-if="paymentType === 'full'">
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-white uppercase tracking-wider">Full Payment
                                    </p>
                                    <p class="text-[10px] text-slate-400">৳{{ formatPrice(totalAmount) }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Bank Transfer Payment Options -->
                    <div v-if="paymentMethod === 'bank_transfer'"
                        class="mt-4 space-y-3 p-4 bg-white/5 rounded-2xl border border-white/5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Upload
                            Payment Slip (Optional)</label>
                        <input type="file" accept="image/*,application/pdf" @change="handleFileUpload"
                            class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-slate-900 hover:file:bg-white transition-all cursor-pointer">
                        <p class="text-xs text-slate-500 mt-2">Accepted formats: JPG, PNG, PDF. Max size: 5MB.</p>
                    </div>
                </div>

                <!-- Address Form -->
                <div class="space-y-6">
                    <h4 class="text-[10px] font-bold text-primary uppercase tracking-[0.2em] mb-4">Shipping Address</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Full
                                Name</label>
                            <input v-model="form.name" type="text" placeholder="John Doe"
                                class="w-full px-4 py-3 bg-white/5 border border-white/5 rounded-xl text-white focus:outline-none focus:border-primary transition-all text-sm placeholder:text-slate-600">
                        </div>
                        <div class="col-span-2">
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Full
                                Address</label>
                            <input v-model="form.street" type="text" placeholder="e.g. 123 Luxury Lane"
                                class="w-full px-4 py-3 bg-white/5 border border-white/5 rounded-xl text-white focus:outline-none focus:border-primary transition-all text-sm placeholder:text-slate-600">
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">District</label>
                            <input v-model="form.division" type="text" placeholder="Dhaka"
                                class="w-full px-4 py-3 bg-white/5 border border-white/5 rounded-xl text-white focus:outline-none focus:border-primary transition-all text-sm placeholder:text-slate-600">
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Thana</label>
                            <input v-model="form.thana" type="text" placeholder="Dhanmondi"
                                class="w-full px-4 py-3 bg-white/5 border border-white/5 rounded-xl text-white focus:outline-none focus:border-primary transition-all text-sm placeholder:text-slate-600">
                        </div>
                        <div class="col-span-2">
                            <label
                                class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Phone</label>
                            <input v-model="form.phone" type="text" placeholder="+880..."
                                class="w-full px-4 py-3 bg-white/5 border border-white/5 rounded-xl text-white focus:outline-none focus:border-primary transition-all text-sm placeholder:text-slate-600">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-white/5">
                <button @click="$emit('close')" :disabled="loading"
                    class="px-6 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-white transition-colors disabled:opacity-50">
                    Cancel
                </button>
                <button @click="submit" :disabled="loading || !isFormValid"
                    class="px-8 py-3 bg-primary text-slate-900 text-[10px] font-bold rounded-xl hover:bg-white transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3 uppercase tracking-widest">
                    <span v-if="loading"
                        class="animate-spin h-3 w-3 border-2 border-slate-900 border-t-transparent rounded-full"></span>
                    {{ loading ? 'Processing...' : (paymentMethod === 'bkash' ? 'Proceed to bKash' : 'Confirm Order') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    isOpen: Boolean,
    selectedRequests: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    initialAddress: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['close', 'confirm']);

const editableItems = ref([]);
const paymentMethod = ref('bkash');
const paymentType = ref('partial');
const paymentSlip = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        paymentSlip.value = file;
    } else {
        paymentSlip.value = null;
    }
};

const form = ref({
    name: '',
    division: '',
    thana: '',
    street: '',
    phone: '',
});

const isFormValid = computed(() => {
    if (!form.value.name || !form.value.street || !form.value.phone) return false;
    if (paymentMethod.value === 'bank_transfer' && !paymentSlip.value) return false;
    return true;
});

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        // Initialize editable items with deep copy
        editableItems.value = props.selectedRequests.map(item => ({
            ...item,
            quantity: item.quantity || 1
        }));

        if (props.initialAddress) {
            form.value = {
                ...form.value,
                ...props.initialAddress
            };
        }
    }
}, { immediate: true });

const updateQty = (id, delta) => {
    const item = editableItems.value.find(i => i.id === id);
    if (item) {
        const currentQty = Number(item.quantity);
        const change = Number(delta);

        const newQty = Math.max(1, currentQty + change);
        item.quantity = newQty;
    }
};

const getItemTotal = (item) => {
    // Basic approximate recalculation for the UI
    // The backend does the heavy lifting, but we need meaningful feedback here.
    // Logic: (Base Total / Base Qty) * New Qty
    // This maintains the ratio of charges + unit price.
    if (!item.quantity) return 0;
    const baseQty = props.selectedRequests.find(r => r.id === item.id)?.quantity || 1;
    const baseTotal = parseFloat(item.total_amount_bdt) || 0;
    return (baseTotal / baseQty) * item.quantity;
};

const totalAmount = computed(() => {
    return editableItems.value.reduce((sum, item) => sum + getItemTotal(item), 0);
});

const formatPrice = (price) => {
    return parseFloat(price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const submit = () => {
    if (!isFormValid.value) {
        toast.error('Please fill in required fields correctly.');
        return;
    }

    emit('confirm', {
        shipping_address: form.value,
        payment_method: paymentMethod.value,
        payment_type: paymentType.value,
        payment_slip: paymentSlip.value
    });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 215, 0, 0.3);
}
</style>
