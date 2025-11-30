/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.{html,js,php}",
    "./views/**/*.{html,js,php}",
    "./src/**/*.{html,js,php,ts,tsx}",
    "./*.{html,php}"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        accent: {
          DEFAULT: '#2563EB',
          soft: '#EFF4FF',
        },
        page: '#F7F8FA',
        surface: '#FFFFFF',
        muted: '#475569',
        border: '#E2E8F0',
        success: '#16A34A',
        warning: '#F59E0B',
        danger: '#DC2626',
        info: '#0EA5E9',
      },
      boxShadow: {
        card: '0 10px 30px -12px rgba(15,23,42,0.18)',
        modal: '0 18px 40px -16px rgba(15,23,42,0.32)',
      },
      borderRadius: {
        card: '12px',
        modal: '14px',
      },
    },
  },
  plugins: [],
}
