<template>
    <section class="relative bg-background text-primary pt-32 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <!-- Background Gradient/Image -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#d6006e] via-[#f3c6e0] to-[#fdf2f8]"></div>
        
        <div class="relative max-w-7xl mx-auto text-center z-10">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold tracking-tight mb-8 animate-fade-in-up leading-tight text-primary">
                Global Shopping, <br class="hidden sm:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">Simplified.</span>
            </h1>
            <p class="text-xl sm:text-2xl max-w-2xl mx-auto mb-12 text-primary/80 animate-fade-in-up delay-100 leading-relaxed font-medium">
                Request any product from anywhere in the world. We handle the purchase, shipping, and delivery to your doorstep.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 animate-fade-in-up delay-200">
                <router-link to="/request-product" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-lg font-bold rounded-full text-white bg-primary hover:bg-primary-hover transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    Request a Product
                </router-link>
                <a href="#how-it-works" class="inline-flex items-center justify-center px-8 py-4 border-2 border-primary text-lg font-bold rounded-full text-primary hover:bg-primary hover:text-white transition-all duration-200">
                    How It Works
                </a>
            </div>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute top-1/4 left-10 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000"></div>
    </section>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
