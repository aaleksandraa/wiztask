import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#f4f6fb',
                    100: '#e8edf7',
                    300: '#93a2f9',
                    400: '#7188f8',
                    500: '#4f6ef7',
                    600: '#3b57eb',
                    700: '#2f46d8',
                    900: '#1a2551',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(0,0,0,0.35), 0 8px 24px rgba(0,0,0,0.4)',
                card: '0 1px 3px rgba(0,0,0,0.4), 0 12px 32px rgba(0,0,0,0.45)',
                float: '0 8px 30px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06)',
                inset: 'inset 0 1px 0 rgba(255,255,255,0.06)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
            animation: {
                'fade-in': 'fadeIn 0.2s ease-out',
                'slide-up': 'slideUp 0.25s ease-out',
                'scale-in': 'scaleIn 0.2s ease-out',
            },
            keyframes: {
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                slideUp: {
                    from: { opacity: '0', transform: 'translateY(8px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                scaleIn: {
                    from: { opacity: '0', transform: 'scale(0.96)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
            },
        },
    },

    plugins: [forms],
};
