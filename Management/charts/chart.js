function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

// Add a script to fetch data and update the chart
fetch('school_total.php')
    .then(response => response.json())
    .then(data => {
        // Call a function to update the chart with the fetched data
        updateChart(data);
    })
    .catch(error => console.error('Error fetching data:', error));

function updateChart(data) {
    var canvas = document.getElementById('myChart');
    if (!canvas) {
        console.error('Canvas element with ID "myChart" not found.');
        return;
    }

    var ctx = canvas.getContext('2d');
    var totalInterns = data.reduce((sum, item) => sum + parseInt(item.total, 10), 0);

    // Display the total number of interns
   document.getElementById('totalValue').textContent = `${totalInterns}`;

    if (!window.myChart) {
        window.myChart = {};
    }

    if (!window.myChart.chart) {
        window.myChart.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.school),
                datasets: [{
                    label: 'Interns Data',
                    data: data.map(item => item.total),
                    backgroundColor: data.map(() => getRandomColor()),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                animation: true,
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        ticks: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: 'black',
                            boxWidth: 15
                        }
                    },
                    tooltips: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFontColor: 'white',
                        bodyFontColor: 'white',
                        displayColors: false
                    }
                }
            }
        });
    } else {
        window.myChart.chart.data.labels = data.map(item => item.school);
        window.myChart.chart.data.datasets[0].data = data.map(item => item.total);
        window.myChart.chart.data.datasets[0].backgroundColor = data.map(() => getRandomColor());
        window.myChart.chart.update('none');
    }
}

fetchAndUpdateChart();

setInterval(fetchAndUpdateChart, 5000);

function fetchAndUpdateChart() {
    fetch('school_total.php')
        .then(response => response.json())
        .then(data => {
            updateChart(data);
        })
        .catch(error => console.error('Error:', error));
}