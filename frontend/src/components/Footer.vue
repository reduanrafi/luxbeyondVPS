<template>
    <footer class="relative bg-background pt-20 pb-10 overflow-hidden">
        <!-- Background Gradient Overlay -->
        <div
            class="absolute inset-0 bg-gradient-to-br from-primary/10 via-purple-900/10 to-secondary/10 opacity-30 pointer-events-none">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Brand Section -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2">
                        <router-link to="/" class="bg-white/5 p-2 rounded-lg backdrop-blur-sm">
                            <span class="text-2xl font-bold text-primary tracking-tight">Luxbeyond</span>
                        </router-link>
                    </div>
                    <p class="text-[#00FFFF] leading-relaxed text-sm">
                        Your premium gateway to global shopping. We connect you with travellers to bring the world to
                        your doorstep with ease, security, and style.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="https://www.facebook.com/luxbeyond.store" target="_blank"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-primary hover:bg-accent hover:text-black hover:scale-110 transition-all duration-300">
                            <Facebook class="w-5 h-5" />
                        </a>
                        <a href="https://www.instagram.com/luxbeyondbd" target="_blank"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-primary hover:bg-accent hover:text-black hover:scale-110 transition-all duration-300">
                            <Instagram class="w-5 h-5" />
                        </a>
                        <a href="https://www.linkedin.com/company/luxbeyondbd" target="_blank"
                            class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-primary hover:bg-accent hover:text-black hover:scale-110 transition-all duration-300">
                            <Linkedin class="w-5 h-5" />
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold !text-primary mb-6 relative inline-block">
                        Explore
                        <span class="absolute -bottom-2 left-0 w-12 h-1 bg-accent rounded-full"></span>
                    </h4>
                    <ul class="space-y-3">
                        <li><router-link to="/"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Home</router-link></li>
                        <li><router-link to="/shop"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Shop</router-link></li>
                        <li><router-link to="/travellers"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Travellers</router-link></li>
                        <li><router-link to="/blogs"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Blogs</router-link></li>
                        <li><router-link to="/track-order"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 rounded-full bg-accent"></span>Track Order</router-link></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="text-lg font-bold !text-primary mb-6 relative inline-block">
                        Support
                        <span class="absolute -bottom-2 left-0 w-12 h-1 bg-accent rounded-full"></span>
                    </h4>
                    <ul class="space-y-3">
                        <li><a href="#"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Help
                                Center</a></li>
                        <li><a href="#"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Terms
                                of Service</a></li>
                        <li><a href="#"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Privacy
                                Policy</a></li>
                        <li><a href="#"
                                class="text-[#00FFFF] hover:text-white hover:translate-x-1 transition-all duration-300 flex items-center gap-2">Shipping
                                & Returns</a></li>
                    </ul>
                </div>

                <!-- Newsletter & Contact -->
                <div>
                    <h4 class="text-lg font-bold !text-primary mb-6 relative inline-block">
                        Stay Updated
                        <span class="absolute -bottom-2 left-0 w-12 h-1 bg-accent rounded-full"></span>
                    </h4>
                    <p class="text-[#00FFFF] mb-6 text-sm">Subscribe to our newsletter for exclusive offers and travel
                        tips.</p>
                    <form class="relative mb-8 group">
                        <input type="email" placeholder="YOUR EMAIL ADDRESS"
                            class="w-full px-0 py-3 bg-transparent border-b border-white/20 text-white focus:outline-none focus:border-primary placeholder-[#00ffff]/70 transition-all font-medium tracking-wide text-sm rounded-none">
                        <button
                            class="absolute right-0 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-white transition-colors p-2">
                            <ArrowRight class="w-5 h-5" />
                        </button>
                    </form>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 text-[#00FFFF]">
                            <MapPin class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                            <span class="text-sm">Flat 4B,House 33,Road 03,Sector 09,Uttara, Dhaka, Bangladesh,
                                1230</span>
                        </div>
                        <div class="flex items-center gap-3 text-[#00FFFF]">
                            <Mail class="w-5 h-5 text-accent shrink-0" />
                            <span class="text-sm">support@luxbeyond.store</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[#00ffff] text-sm text-center md:text-left">
                    &copy; {{ new Date().getFullYear() }} Luxbeyond. All rights reserved.
                </p>

                <div class="flex gap-6 text-sm text-[#00ffff]">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Sitemap</a>
                </div>
                <!-- Payment Methods -->
                <div class="flex flex-col items-center gap-3">
                    <span class="text-sm text-[#00ffff]">We Accept:</span>
                    <div class="flex flex-col items-center gap-2 px-3 py-1.5 ">
                        <img src="/eastern-bank-plc.webp" alt="luxbeyond ebl" width="120%">
                        <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="88" height="40"
                            viewBox="0 0 122 54">
                            <title>bKash Logo</title>
                            <g fill="currentColor">
                                <path
                                    d="m82.9 25.9 3.3 14.6 21.5-10.7zM89 3.8 83.2 25l24 3.8zM62.8.6l25.5 3.1-6 21.8zM62.5 4.8h3l8 10.3zM108.4 29.6l-7.5-10.3 12-2.3zM107.2 32.5l.7-2.2-18.7 9.6zM82.4 26.3 86.3 44l-11.6 9.4zM111.8 22.3h7l-5.1-5.1z">
                                </path>
                            </g>
                            <path fill="currentColor"
                                d="M5.4 21.2h15.2v2.2H5.2v17.8s-.7-.1-1.1-.1c-.4 0-.9.1-.9.1V21.1c0-5.2 2-8.2 7-8.5 4.5-.3 8 2.3 10.4 3.6v2.6l-.3.1c-.4-1-5.8-5.5-9.3-5.1-3.6.4-5.8 2.9-5.8 6.2 0 0 0 .9.2 1.2z">
                            </path>
                            <path fill="currentColor"
                                d="M36.6 24.9c3.8-.1 5.7 1.2 5.8 4.1.1 1.6-.6 3.1-1.7 4.4l-1.9.1v-.3c.7-.2 2.3-1.3 2.2-3.3s-1.6-3.6-4-3.5c0 0-.4 0-.6.1l.2-1.6z">
                            </path>
                            <path fill="currentColor" d="M18.5 23h2.1v18.2l-2.1-.7z"></path>
                            <path fill="currentColor"
                                d="M57.3 25.5c-.8-2.7-3.1-4.3-4.8-4.3H48v2.2h3c1.3 0 2.6-.1 3.8 1 .5.5.7 1 .9 1.7.4 1.7-.4 3.8-2.4 3.8-.6 0-1.3 0-1.9-.2v.1c.5.6.8 1.4 1.2 2.1 1.2-.2 2.4-.9 3.2-1.7 1.1-1.3 1.8-2.9 1.5-4.7">
                            </path>
                            <path fill="currentColor"
                                d="M60.6 21.5c-1.3.2-3.3 1.3-4 4.1-.2 1.8.3 3.4 1.5 4.6.7.8 1.8 1.5 2.8 1.7.3-.7.7-1.4 1.1-2.1l-.1-.1c-.5.2-1.1.2-1.7.2-2.1-.1-2.4-2.2-2.1-3.8.3-1.4 1.5-2.8 2.8-2.9 1.4-.1 3.1 1 3.4 2.5.2.8.2 1.6.2 2.4v13.2s.6-.1 1-.1 1 .1 1 .1V21.4h-2.2v2.2c0-.1.1-.1 0-.3-.7-1.3-2.5-2-3.7-1.8M47.6 21.2v20s-.6-.1-1-.1-1 .1-1 .1V23.4H21.1v-2.2h26.5z">
                            </path>
                            <path fill="currentColor"
                                d="M20.6 41.2h-.3c-7.8 0-12.2-4.3-12.2-8.6 0-3.6 3.3-7.8 10.9-7.8l1.3 1c-5.3 0-9.6 3.4-9.6 6.8 0 3 3.8 7.8 9.9 7.8v.8z">
                            </path>
                            <path fill="currentColor" d="M34.8 23h2.1v18.2l-2.1-.7z"></path>
                            <path fill="currentColor"
                                d="M36.9 41.2h-.3c-7.8 0-12.2-4.3-12.2-8.6 0-3.6 3.3-7.8 10.9-7.8l1.3 1c-5.3 0-9.6 3.4-9.6 6.8 0 3 3.8 7.8 9.9 7.8v.8z">
                            </path>
                        </svg>
                    </div>
                </div>

            </div>
        </div>
    </footer>
</template>

<script setup>
import { Facebook, Instagram, Linkedin, ArrowRight, MapPin, Mail, CreditCard } from 'lucide-vue-next';
</script>
