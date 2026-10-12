import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eef4ff', 100: '#dbe7ff', 500: '#2563eb', 600: '#1d4ed8', 700: '#1e40af',
                },
                success: { 50: '#ecfdf5', 500: '#10b981', 600: '#059669' },
                warning: { 50: '#fffbeb', 500: '#f59e0b', 600: '#d97706' },
                danger:  { 50: '#fef2f2', 500: '#ef4444', 600: '#dc2626' },
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: { xl: '0.875rem', '2xl': '1.25rem' },
        },
    },

    plugins: [forms],
};