$(function() {
    // Declare variables to store start date and end date
    let startDate;
    let endDate;

    // Extract username from the URL
    const params = new URLSearchParams(window.location.search);
    const username = params.get('username');

    $('input[name="start"], input[name="end"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('input[name="start"], input[name="end"]').on('apply.daterangepicker', function(ev, picker) {
        // Update the input field with the selected date range
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));

        // Update the variables with start date and end date
        startDate = picker.startDate.format('YYYY-MM-DD');
        endDate = picker.endDate.format('YYYY-MM-DD');

        // Trigger the AJAX request to fetch data based on the selected date range
        fetchData();
    });

    $('input[name="start"], input[name="end"]').on('cancel.daterangepicker', function(ev, picker) {
        // Clear the input field
        $(this).val('');

        // Reset the variables
        startDate = null;
        endDate = null;

        // Trigger the AJAX request to fetch all data
        fetchData();
    });

    // Initial fetch of data when the page loads
    fetchData();

    // Function to make the AJAX request and update the table
    function fetchData() {
        // Check if both start date and end date are selected
        if (startDate && endDate) {
            // Make the AJAX request with the selected date range and username
            $.ajax({
                url: 'date_sort.php',
                type: 'GET',
                data: {
                    username: username,
                    startDate: startDate,
                    endDate: endDate
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayData(response.data);
                    } else {
                        console.error('Failed to fetch data. Message:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to fetch data. Status:', status, 'Error:', error);
                }
            });
        } else {
            // Make the AJAX request to fetch all data if either start date or end date is not selected
            $.ajax({
                url: 'date_sort.php',
                type: 'GET',
                data: {
                    username: username
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayData(response.data);
                    } else {
                        console.error('Failed to fetch data. Message:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to fetch data. Status:', status, 'Error:', error);
                }
            });
        }
    }

    // Function to display data in table rows
    function displayData(data) {
        var tableBody = $('#tableBody');
        tableBody.empty();

        if (data && data.length > 0) {
            $.each(data, function(index, row) {
                var tableRow = createTableRow(row);
                tableBody.append(tableRow);
            });
        } else {
            // If there is no data, add a row indicating no data
            var noDataRow = '<tr><td colspan="6" class="text-center py-6 px-4 font-poppins">No data available</td></tr>';
            tableBody.append(noDataRow);
        }
    }

    // Function to create a table row based on the data
    function createTableRow(row) {
        return `
            <tr>
                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.morning_timein || ''}</td>
                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.lunch_timeout || ''}</td>
                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.after_lunch_timein || ''}</td>
                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.end_of_day_timeout || ''}</td>
                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.attendance_date || ''}</td>
                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.rendered_hours || ''}</td>
            </tr>
        `;
    }
});
