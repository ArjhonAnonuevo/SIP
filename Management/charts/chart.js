 // Add a script to fetch data and update the chart
 fetch('fetch school total.php') // Replace with the actual endpoint providing JSON data
 .then(response => response.json())
 .then(data => {
     // Call a function to update the chart with the fetched data
     updateChart(data);
 })
 .catch(error => console.error('Error fetching data:', error));

function updateChart(data) {
 var ctx = document.getElementById('myChart').getContext('2d');

 // Initialize window.myChart if it doesn't exist
 if (!window.myChart) {
     window.myChart = {};
 }

 // Check if the chart already exists
 if (!window.myChart.chart) {
     window.myChart.chart = new Chart(ctx, {
         type: 'bar',
         data: {
             labels: data.map(item => item.school),
             datasets: [{
                 label: 'Your Chart Title',
                 data: data.map(item => item.total),
                 backgroundColor: 'rgba(75, 192, 192, 0.2)',
                 borderColor: 'rgba(75, 192, 192, 1)',
                 borderWidth: 1
             }]
         },
         options: {
             animation: true, // enable animations
             scales: {
                 y: {
                     beginAtZero: true
                 }
             }
         }
     });
 } else {
     // If the chart has already been created, update it
     window.myChart.chart.data.labels = data.map(item => item.school);
     window.myChart.chart.data.datasets[0].data = data.map(item => item.total);
     window.myChart.chart.update('none'); // update without animation
 }
}

// Call fetchAndUpdateChart immediately to load the initial data
fetchAndUpdateChart();

// Then call fetchAndUpdateChart every 5 seconds to refresh the data
setInterval(fetchAndUpdateChart, 5000);

function fetchAndUpdateChart() {
 fetch('fetch school total.php') // Replace with the actual endpoint providing JSON data
     .then(response => response.json())
     .then(data => {
         updateChart(data);
     })
     .catch(error => console.error('Error:', error));
}