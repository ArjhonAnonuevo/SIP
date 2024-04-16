/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './application forms/**/*.{html,js}',
    './emails/**/*.{html,js}',
    './header/**/*.{html,js}',
    './index.html',
    './homepage.html',
    './template.html',
    './sidebar.html',


  ],
  theme: {
    extend: {
      fontFamily: {
        'inter': ['Inter', 'sans-serif'],
        'lora': ['Lora', 'serif'],
        'poppins': ['Poppins', 'sans-serif'],
    },
    
      colors: {
        primary: '#3490dc',
        customgreen: '#047857',
        customdarkgreen: '#097969',
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