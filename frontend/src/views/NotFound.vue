<template>
    <div class="min-h-screen bg-background flex items-center justify-center py-20 px-4">
        <!-- Background Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-purple-900/10 to-secondary/10 opacity-30 pointer-events-none"></div>

        <div class="max-w-md w-full text-center relative z-10">
            <!-- Glitch effect or prominent 404 -->
            <div class="relative">
                <h1 class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent opacity-20 select-none">
                    404
                </h1>
                <div class="absolute inset-0 flex items-center justify-center">
                    <p class="text-4xl md:text-5xl font-bold text-white tracking-tight">Page Not Found</p>
                </div>
            </div>

            <div class="mt-8 space-y-6">
                <p class="text-zinc-400 text-lg">
                    Oops! The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    <router-link to="/" class="w-full sm:w-auto px-8 py-3.5 bg-primary text-black font-bold rounded-full hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2">
                        <Home class="w-5 h-5" />
                        Back to Home
                    </router-link>
                    
                    <button @click="$router.go(-1)" class="w-full sm:w-auto px-8 py-3.5 bg-white/5 text-white font-bold rounded-full hover:bg-white/10 transition-all duration-300 border border-white/10 flex items-center justify-center gap-2">
                        <ArrowLeft class="w-5 h-5" />
                        Go Back
                    </button>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/5">
                <p class="text-sm text-zinc-500">
                    If you believe this is an error, please contact our
                    <router-link to="/help-center" class="text-primary hover:underline">Support Team</router-link>.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Home, ArrowLeft } from 'lucide-vue-next';
</script>
