<template>
    <div>
        <h2 class="text-2xl font-bold text-slate-900 mb-6">My Coupons</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="coupon in coupons" :key="coupon.code"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative group hover:shadow-md transition-all">
                <!-- Decorative Circle -->
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-10 transition-transform group-hover:scale-150"
                    :class="coupon.active ? 'bg-primary' : 'bg-gray-400'"></div>

                <div class="p-6 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider mb-1 block"
                                :class="coupon.active ? 'text-primary' : 'text-gray-400'">
                                {{ coupon.type }}
                            </span>
                            <h3 class="text-3xl font-extrabold text-slate-900">{{ coupon.discount }}</h3>
                        </div>
                        <div class="p-2 rounded-lg bg-gray-50">
                            <Ticket class="w-6 h-6 text-slate-400" />
                        </div>
                    </div>

                    <p class="text-slate-600 text-sm mb-6 flex-1">{{ coupon.description }}</p>

                    <div class="pt-4 border-t border-dashed border-gray-200">
                        <div class="flex justify-between items-center">
                            <div
                                class="font-mono bg-gray-100 px-3 py-1.5 rounded text-sm font-bold text-slate-700 tracking-wide select-all">
                                {{ coupon.code }}
                            </div>
                            <button v-if="coupon.active"
                                class="text-sm font-bold text-primary hover:text-primary-hover">
                                Copy Code
                            </button>
                            <span v-else class="text-xs font-bold text-red-500 uppercase">Expired</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Expires: {{ coupon.expiry }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Ticket } from 'lucide-vue-next';

const coupons = [
    { code: 'WELCOME20', discount: '20% OFF', type: 'New User', description: 'Get 20% off your first order. Max discount ৳500.', expiry: 'Dec 31, 2024', active: true },
    { code: 'FREESHIP', discount: 'Free Shipping', type: 'Shipping', description: 'Free shipping on all orders over ৳5000.', expiry: 'Nov 30, 2024', active: true },
    { code: 'BLACKFRIDAY', discount: '30% OFF', type: 'Seasonal', description: 'Special Black Friday deal for electronics.', expiry: 'Nov 29, 2024', active: false },
];
</script>
