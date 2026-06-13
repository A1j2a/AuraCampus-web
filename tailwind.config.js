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
                "inverse-surface": "#283044",
                "on-secondary-fixed": "#002114",
                "surface-background": "#F8FAFC",
                "primary-container": "#3b00ba",
                "tertiary": "#3e1e00",
                "on-primary-container": "#a998ff",
                "on-error-container": "#93000a",
                "surface-container-lowest": "#ffffff",
                "surface-container": "#eaedff",
                "on-tertiary": "#ffffff",
                "surface-container-low": "#f2f3ff",
                "tertiary-fixed-dim": "#ffb77d",
                "on-surface-variant": "#484555",
                "error": "#ba1a1a",
                "primary-fixed": "#e6deff",
                "primary": "#26007f",
                "on-secondary-container": "#0b714e",
                "primary-fixed-dim": "#c9beff",
                "tertiary-container": "#5e3000",
                "surface": "#faf8ff",
                "surface-dim": "#d2d9f4",
                "surface-container-highest": "#dae2fd",
                "inverse-primary": "#c9beff",
                "on-background": "#131b2e",
                "on-primary-fixed": "#1b0063",
                "secondary-container": "#9af1c6",
                "on-secondary-fixed-variant": "#005237",
                "surface-variant": "#dae2fd",
                "surface-bright": "#faf8ff",
                "outline": "#797586",
                "tertiary-fixed": "#ffdcc3",
                "on-error": "#ffffff",
                "surface-tint": "#5f40dc",
                "surface-container-high": "#e2e7ff",
                "warning-amber": "#5e3000",
                "on-secondary": "#ffffff",
                "success-green": "#006c4a",
                "outline-variant": "#c9c4d7",
                "on-tertiary-container": "#db9860",
                "error-container": "#ffdad6",
                "secondary": "#006c4a",
                "secondary-fixed-dim": "#81d8ae",
                "sidebar-bg": "#1e253b",
                "on-primary": "#ffffff",
                "on-tertiary-fixed": "#2f1500",
                "error-red": "#ba1a1a",
                "on-surface": "#131b2e",
                "background": "#faf8ff",
                "on-primary-fixed-variant": "#461ec4",
                "secondary-fixed": "#9df4c9",
                "on-tertiary-fixed-variant": "#6b3a09",
                "inverse-on-surface": "#eef0ff"
            },
            spacing: {
                "density-compact": "8px",
                "density-comfortable": "16px",
                "gutter": "24px",
                "margin": "32px",
            }
        },
    },

    plugins: [forms],
};
