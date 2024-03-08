/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './application forms/**/*.{html,js}',
    './index.html',
    './template.html',
  ],
  theme: {
    extend: {
      fontFamily: {
        'open-sans': ['"Open Sans"', 'sans-serif'],
        'lora': ['Lora', 'serif'],
        'trirong': ['Trirong', 'serif'],
      },
      colors: {
        primary: '#3490dc',
        success: '#2E8B57',
        secondary: '#ffed4a',
        alert: '#6574cd',
        formcolors: '#9CFACE',
      },
      spacing: {
        '72': '18rem',
        '84': '21rem',
        '96': '24rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'), 
    require('@tailwindcss/typography'), 
    require('@tailwindcss/aspect-ratio'), 
    require('tailwind-scrollbar-hide')
  ],
};