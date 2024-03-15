/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './application/**/*.{html,js}',
    './attendance/**/*.{html,js}',
    './calendar/**/*.{html,js}',
    './carousel/**/*.{html,js}',
    './certificate/**/*.{html,js}',
    './charts/**/*.{html,js}',
    './header/**/*.{html,js}',
    './monthly reports/**/*.{html,js}',
    './php scripts/**/*.{html,js}',
     './fetch-api/**/*.{html,js}',


    './index.html',
    './add_image.html',
    './interns_dashboard.html',
    './internsregister.html',
    './admin_dashboard.html',
  ],
  theme: {
    extend: {
      fontFamily: {
        'kanit': ['Kanit', 'sans-serif'],
        'rubik': ['Rubik', 'sans-serif'],
        'poppins': ['Poppins', 'sans-serif'],
      },
      colors: {
        primary: '#3490dc',
        success: '#2E8B57',
        secondary: '#ffed4a',
        alert: '#6574cd',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'), 
    require('@tailwindcss/typography'), 
    require('tailwind-scrollbar-hide')
  ],
}

