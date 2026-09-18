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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Warna utama identitas CV. Basyid Creative Architecture
                navy: {
                    50: '#eef3f9',
                    100: '#d7e3f1',
                    200: '#a0b4cc',
                    300: '#7c99bd',
                    400: '#4a6b96',
                    500: '#2a4a7f',
                    600: '#1a365d', // primary
                    700: '#152c4c',
                    800: '#11233d',
                    900: '#0d1b2f',
                },
                gold: {
                    50: '#fbf6e7',
                    100: '#f6ecc7',
                    200: '#ecd98d',
                    300: '#ddc158',
                    400: '#c9a227', // primary accent
                    500: '#b28f22',
                    600: '#8f731b',
                    700: '#6b5714',
                },
            },
        },
    },

    plugins: [forms],
};
