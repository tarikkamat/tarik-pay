/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{js,jsx,ts,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                'iyzico-blue': '#1d64ff',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')
    ],
}