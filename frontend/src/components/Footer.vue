<template>
    <footer class="relative bg-primary pt-20 pb-10 overflow-hidden">
        <!-- Background Gradient Overlay -->
        <div
            class="absolute inset-0 bg-gradient-to-br from-primary via-purple-900 to-secondary opacity-10 pointer-events-none">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Brand Section -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2">
                        <div class="bg-white/10 p-2 rounded-lg backdrop-blur-sm">
                            <span class="text-2xl font-bold text-white tracking-tight">LuxBusiness</span>
                        </div>
                    </div>
                    <p class="text-blue-100/80 leading-relaxed text-sm">
                        Your premium gateway to global shopping. We connect you with travellers to bring the world to
                        your doorstep with ease, security, and style.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-accent hover:scale-110 transition-all duration-300">
                            <Facebook class="w-5 h-5" />
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-accent hover:scale-110 transition-all duration-300">
                            <Twitter class="w-5 h-5" />
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-accent hover:scale-110 transition-all duration-300">
                            <Instagram class="w-5 h-5" />
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-accent hover:scale-110 transition-all duration-300">
                            <Linkedin class="w-5 h-5" />
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-6 relative inline-block">
                        Explore
                        <span class="absolute -bottom-2 left-0 w-12 h-1 bg-accent rounded-full"></span>
                    </h4>
                    <ul class="space-y-3">
                        <li><router-link to="/"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Home</router-link></li>
                        <li><router-link to="/shop"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Shop</router-link></li>
                        <li><router-link to="/travellers"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Travellers</router-link></li>
                        <li><router-link to="/blogs"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Blogs</router-link></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-6 relative inline-block">
                        Support
                        <span class="absolute -bottom-2 left-0 w-12 h-1 bg-accent rounded-full"></span>
                    </h4>
                    <ul class="space-y-3">
                        <li><a href="#"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Help
                                Center</a></li>
                        <li><a href="#"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Terms
                                of Service</a></li>
                        <li><a href="#"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Privacy
                                Policy</a></li>
                        <li><a href="#"
                                class="text-blue-100/70 hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Shipping
                                & Returns</a></li>
                    </ul>
                </div>

                <!-- Newsletter & Contact -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-6 relative inline-block">
                        Stay Updated
                        <span class="absolute -bottom-2 left-0 w-12 h-1 bg-accent rounded-full"></span>
                    </h4>
                    <p class="text-blue-100/70 mb-4 text-sm">Subscribe to our newsletter for exclusive offers and travel
                        tips.</p>
                    <form class="relative mb-8">
                        <input type="email" placeholder="Your email address"
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent placeholder-blue-100/30 transition-all">
                        <button
                            class="absolute right-2 top-2 p-1.5 bg-accent rounded-lg text-white hover:bg-pink-600 transition-colors">
                            <ArrowRight class="w-4 h-4" />
                        </button>
                    </form>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 text-blue-100/70">
                            <MapPin class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <span class="text-sm">123 Luxury Lane, Business District, New York, NY 10001</span>
                        </div>
                        <div class="flex items-center gap-3 text-blue-100/70">
                            <Mail class="w-5 h-5 text-accent shrink-0" />
                            <span class="text-sm">support@luxbusiness.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-blue-100/50 text-sm text-center md:text-left">
                    &copy; {{ new Date().getFullYear() }} LuxBusiness. All rights reserved.
                </p>

                <!-- Payment Methods -->
                <div class="flex items-center gap-3">
                    <span class="text-sm text-blue-100/50">We Accept:</span>
                    <div class="flex items-center gap-2 bg-white/5 px-3 py-1.5 rounded-full border border-white/10">
                        <CreditCard class="w-4 h-4 text-blue-100/70" />
                        <span class="text-xs text-blue-100/70 font-medium tracking-wide">VISA • MASTERCARD • AMEX</span>
                    </div>
                </div>

                <div class="flex gap-6 text-sm text-blue-100/50">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Facebook, Twitter, Instagram, Linkedin, ArrowRight, MapPin, Mail, CreditCard } from 'lucide-vue-next';
</script>
