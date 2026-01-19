/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          light: '#60a5fa',
          DEFAULT: '#3b82f6',
          dark: '#2563eb',
        },
        accent: {
          light: '#f87171',
          DEFAULT: '#ef4444',
          dark: '#dc2626',
        }
      }
    },
  },
  plugins: [],
}
