import type { Config } from 'tailwindcss'
import typography from '@tailwindcss/typography'

export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
    './error.vue',
  ],
  theme: {
    extend: {
      colors: {
        matcha: {
          50: '#f4f9f0',
          100: '#e5f2db',
          200: '#cce5bc',
          300: '#a8d190',
          400: '#7fba63',
          500: '#5a9e3c',
          600: '#447d2d',
          700: '#366122',
          800: '#2c4f1e',
          900: '#243f1a',
          950: '#142210',
        },
        cream: {
          DEFAULT: '#faf8f2',
          dark: '#f0ebe0',
          100: '#fdf9f2',
        },
        charcoal: {
          DEFAULT: '#2d2d2d',
          light: '#4a4a4a',
        },
      },
      fontFamily: {
        serif: ['"Noto Serif JP"', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        'slide-up': 'slideUp 0.4s ease-out',
        'slide-down': 'slideDown 0.3s ease-out',
      },
      keyframes: {
        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
        slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
        slideDown: { '0%': { opacity: '0', transform: 'translateY(-10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
      },
      boxShadow: {
        'matcha': '0 4px 20px rgba(90, 158, 60, 0.15)',
        'card': '0 2px 12px rgba(0, 0, 0, 0.08)',
        'card-hover': '0 8px 30px rgba(0, 0, 0, 0.12)',
      },
    },
  },
  plugins: [typography],
} satisfies Config
