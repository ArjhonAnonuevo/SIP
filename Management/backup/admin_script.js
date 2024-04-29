$(document).ready(function() {
    $("#dashboard-panel-container").load("header/dashboar_panel.html");
});
$(document).ready(function() {
    $("#image-container").load("add_image.html");
});
$(document).ready(function() {
    $("#carousel-container").load("carousel/carousel.html");
});
  $(document).ready(function() {
    $("#calendar-container").load("calendar/calendar.html");
});
$(document).ready(function() {
  $("#accounts").load("interns-account.html");
});

function modalOpen() {
  $('#AddEvent').css('display', 'flex').hide().fadeIn();
}

  function displayModal() {
  $('#myModal').fadeIn(); 
  $('#overlay').fadeIn();
document.getElementById('myModal').classList.remove('hidden');
document.getElementById('overlay').classList.remove('hidden');
document.body.style.overflow = 'hidden';
document.body.style.height = '100%';
}

function closeModal() {
$('#myModal').fadeOut(); 
$('#overlay').fadeOut();
document.getElementById('myModal').classList.add('hidden');
document.getElementById('overlay').classList.add('hidden');
document.body.style.overflow = 'auto';
document.body.style.height = 'auto';
// Reset the form when closing the modal
document.getElementById('imageForm').reset();
document.getElementById('imageContainer').style.backgroundImage = 'none'; // Clear background image

}
// Define variables
let chartSchoolTotal = null;
let chartStatusTotal = null;
let chartAppStatus = null;

// Function to fetch school total data and render chart
function fetchSchoolTotalAndRenderChart() {
    $.ajax({
        url: 'charts/school_total.php',
        type: 'GET',
        dataType: 'json',
        success: function(chartData) {
            // Define alternating color scheme
            var colors = ['#3490dc', '#2E8B57'];
            // Initialize the chart using ApexCharts
            var options = {
                series: [{
                    name: 'Total',
                    data: chartData.map((item, index) => ({
                        x: item.schoolName,
                        y: parseInt(item.total),
                        color: colors[index % colors.length]
                    }))
                }],
                chart: {
                    type: 'bar',
                    height: '400px',
                    width: '100%',
                    foreColor: '#333'
                },
                xaxis: {
                    categories: chartData.map(item => item.schoolName),
                    labels: {
                        show: false
                    }
                }
            };

            // Render the chart
            if (chartSchoolTotal) {
                chartSchoolTotal.destroy();
            }
            chartSchoolTotal = new ApexCharts(document.getElementById('chart'), options);
            chartSchoolTotal.render();
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', error);
        }
    });
}

// Function to fetch status total data and render chart
function fetchStatusTotalAndRenderChart() {
    $.ajax({
        url: 'charts/status_total.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Process data and update chart
            createChart(data);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

// Function to create or update bar chart using ApexCharts
function createChart(data) {
    const labels = data.map(entry => entry.label);
    const counts = data.map(entry => entry.data);

    // Initialize the chart if not already initialized
    if (!chartStatusTotal) {
        const options = {
            chart: {
                type: 'bar',
                height: '400px',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                    },
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150,
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350,
                    },
                },
            },
            series: [{
                name: 'Intern Status',
                data: counts
            }],
            xaxis: {
                categories: labels
            },
            tooltip: {
                enabled: true,
                followCursor: true,
                theme: 'dark',
            },
            dataLabels: {
                enabled: true,
                formatter: function(value) {
                    return value.toFixed(2);
                },
                textAnchor: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    fontSize: '12px',
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fontWeight: 'bold',
                },
                dropShadow: {
                    enabled: false,
                },
            },
        };

        chartStatusTotal = new ApexCharts(document.querySelector("#status-chart"), options);
        chartStatusTotal.render();
    } else {
        chartStatusTotal.updateSeries([{ data: counts }]);
    }
}

// Function to fetch app status data and render chart
function fetchAppStatusAndRenderChart() {
    $.ajax({
        url: 'charts/app_status.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // If the chart instance exists, destroy it
            if (chartAppStatus) {
                chartAppStatus.destroy();
            }
            // Data fetched successfully, now render the chart
            renderChart(data);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

// Function to render chart
function renderChart(data) {
    var options = {
        chart: {
            type: 'line',
            height: 350,
            curve: 'smooth'
        },
        series: [],
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeFormatter: {
                    year: 'yyyy',
                    month: 'MMM yyyy',
                    day: 'dd MMM',
                    hour: 'HH:mm'
                }
            }
        }
    };

    // Process data
    var series = [];
    var categories = [];
    for (var i = 0; i < data.length; i++) {
        var monthData = data[i];
        var year = monthData.year;
        var month = monthData.month;
        var statuses = monthData.statuses;
        var monthYear = new Date(year, month - 1);

        categories.push(monthYear);

        // Prepare series data
        for (var status in statuses) {
            var seriesIndex = series.findIndex(s => s.name === status);
            if (seriesIndex === -1) {
                series.push({
                    name: status,
                    data: []
                });
                seriesIndex = series.length - 1;
            }
            // Push the data for each month
            series[seriesIndex].data.push([monthYear.getTime(), parseInt(statuses[status])]);
        }
    }

    options.series = series;
    options.xaxis.categories = categories;

    // Render the chart
    chartAppStatus = new ApexCharts(document.querySelector("#line-chart"), options);
    chartAppStatus.render();
}

// Fetch data on page load
$(document).ready(function() {
    fetchSchoolTotalAndRenderChart();
    fetchStatusTotalAndRenderChart();
    fetchAppStatusAndRenderChart();

    // Update status chart data every 2 seconds
    setInterval(fetchStatusTotalAndRenderChart, 2000);
    // Update app status chart data every 10 seconds
    setInterval(fetchAppStatusAndRenderChart, 10000);
});



function displayChosenImage() {
        // Display the chosen image in the input container
        const input = document.getElementById('image');
        const container = document.getElementById('imageContainer');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                container.style.backgroundImage = `url(${e.target.result})`;
                container.style.backgroundSize = 'cover';
                container.style.backgroundPosition = 'center';
                container.style.backgroundRepeat = 'no-repeat';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: 'calendar/fetch_events.php',
      eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: 'short'
      },
      eventClick: function(info) {
        openModal(info.event.title, info.event.start);
      }
    });
    calendar.render();
    function openModal(title, start) {
      var modal = document.getElementById('eventModal');
      modal.classList.remove('hidden');
      document.getElementById('eventTitle').textContent = title;
      document.getElementById('eventStart').textContent = `Start: ${moment(start).format('YYYY-MM-DD HH:mm')}`;
      document.getElementById('closeModalBtn').addEventListener('click', function() {
        modal.classList.add('hidden');
      });
    }


});