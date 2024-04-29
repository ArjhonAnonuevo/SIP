module.exports = {
  mode: 'jit',
  darkMode: 'class',
  content: [
    './application/**/*.{html,js}',
    './attendance/**/*.{html,js}',
    './calendar/**/*.{html,js}',
    './carousel/**/*.{html,js}',
    './certificate/**/*.{html,js}',
    './charts/**/*.{html,js}',
    './header/**/*.{html,js}',
    './monthly reports/**/*.{html,js}',
    './php_scripts/**/*.{html,js}',
    './fetch-api/**/*.{html,js}',
    './request/**/*.{html,js}',
    './send-emails/**/*.{html,js}',
    './audits/**/*.{html,js}',
    './request/**/*.{html,js}',
    './files/**/*.{html,js}',
    './interns/**/*.{html,js}',
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
        'roboto-slab': ['Roboto Slab', 'serif'], 
        
        
      },
      colors: {
        primary: '#3490dc',
        secondary: '#ffed4a',
        alert: '#6574cd',
        customGreen: '#2F855A',
        HomeThemes: '#355E3B'
      },
      width: {
        '900px': '900px', 
      },
      height: {
        '96': '24rem', 
        '120': '42rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'), 
    require('@tailwindcss/typography'), 
    require('tailwind-scrollbar-hide'), 
  ],
}
