<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity ease-out duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity ease-in duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="modalStore.isOpen" class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="modalStore.closeModal">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="modalStore.closeModal"></div>

                <!-- Modal Container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition enter-active-class="transition-all ease-out duration-300"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all ease-in duration-200"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-4">
                        <div v-if="modalStore.isOpen"
                            class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-4xl transform transition-all overflow-hidden"
                            @click.stop>
                            <!-- Close Button -->
                            <button @click="modalStore.closeModal"
                                class="absolute top-4 right-4 z-10 text-gray-400 hover:text-white transition-colors">
                                <X class="w-6 h-6" />
                            </button>

                            <div class="flex min-h-[600px]">
                                <!-- Left Side - Info Panel (Hidden on mobile) -->
                                <div
                                    class="hidden md:flex md:w-2/5 bg-gradient-to-br from-primary/10 to-primary/100 p-8 flex-col justify-center items-center text-center">
                                    <div class="mb-6">
                                        <div
                                            class="w-32 h-32 mx-auto bg-surface rounded-full flex items-center justify-center shadow-lg mb-4">
                                            <img src="/logo.png" alt="luxbeyond">
                                        </div>
                                    </div>
                                    <h3 class="text-2xl font-bold !text-white mb-4">Login to your account</h3>
                                    <p class="text-gray-200 text-sm leading-relaxed">
                                        LuxBeyond will receive your order and be able to reply to you once you place an
                                        order and ask for help.
                                    </p>
                                </div>

                                <!-- Right Side - Form -->
                                <div class="w-full md:w-3/5 p-8 md:p-12 flex flex-col justify-center">
                                    <!-- Login Form -->
                                    <LoginForm v-if="modalStore.currentModal === 'login'" />

                                    <!-- Register Form -->
                                    <RegisterForm v-if="modalStore.currentModal === 'register'" />

                                    <!-- Forgot Password Form -->
                                    <ForgotPasswordForm v-if="modalStore.currentModal === 'forgot-password'" />
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { X } from 'lucide-vue-next';
import { useModalStore } from '../stores/modal';
import LoginForm from './auth/LoginForm.vue';
import RegisterForm from './auth/RegisterForm.vue';
import ForgotPasswordForm from './auth/ForgotPasswordForm.vue';

const modalStore = useModalStore();
</script>
