/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.php",
    "./src/**/*.php",
    "./public/assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'text-primary': '#000',
        'text-muted': '#6b7280',
        'surface': '#ffffff',
        'border': '#e2e8f0',
        'accent': '#6366f1',
      },
      borderRadius: {
        'card': '0.75rem',
      },
      boxShadow: {
        'card': '0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06)',
      }
    },
  },
  plugins: [],
};
