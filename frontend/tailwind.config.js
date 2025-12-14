/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                primary: '#2e004f', // Deep Violet
                'primary-hover': '#4a148c', // Lighter Violet
                secondary: '#d6006e', // Vibrant Pink
                accent: '#ff007f', // Hot Pink
                background: '#fdf2f8', // Light Pink Tint
                dark: '#1e293b', // Slate 800
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
