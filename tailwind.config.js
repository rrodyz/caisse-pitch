import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './Modules/**/resources/views/**/*.blade.php',
        './app/Http/Livewire/**/*.php',
        './Modules/**/app/Http/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                night: {
                    950: '#05050c',
                    900: '#08080f',
                    800: '#0d0d18',
                    750: '#111120',
                    700: '#161625',
                    600: '#1e1e30',
                    500: '#28283c',
                    400: '#36364e',
                    300: '#545470',
                    200: '#88889a',
                    100: '#b8b8cc',
                    50:  '#e0e0ee',
                },
                gold: {
                    600: '#a87820',
                    500: '#c8982a',
                    400: '#d4af37',
                    300: '#e8c840',
                    200: '#f5dc6a',
                    100: '#faeea0',
                },
                neon: {
                    700: '#4c1d95',
                    600: '#5b21b6',
                    500: '#7c3aed',
                    400: '#8b5cf6',
                    300: '#a78bfa',
                    200: '#c4b5fd',
                },
            },
        },
    },

    plugins: [forms],
};
