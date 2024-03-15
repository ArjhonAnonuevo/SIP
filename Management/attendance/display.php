<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Reports</title>
    <link href="../css/dist/output.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

</head>

<body class="bg-gray-100">
<div id = "adminNav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="mx-auto md:max-w-7xl md:max-h-min bg-white shadow-md p-6 mt-8 rounded-md">
                <h1 class="text-2xl font-bold text-center mb-4">Attendance Table</h1>
                <div class="mb-4 flex">
                <div class="relative">
                    <input name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5" placeholder="Select date start">
                </div>
                <span class="mx-4 text-gray-500">to</span>
                <div class="relative">
                    <input name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5" placeholder="Select date end">
                </div>
            </div>
            </div>
            </div>
                <div class="overflow-x-auto">
                    <div class="bg-white shadow-md overflow-x-auto">
                        <table class="min-w-full w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-green-700 text-white">
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        Morning Time In
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        Lunch Time Out
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        After lunch Time In
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        End of the day Time Out
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        Attendance Date
                                    </th>
                                    <th class="px-5 py-2 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        Rendered Hours
                                    </th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                        Overtime
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    const params = new URLSearchParams(window.location.search);
    const username = params.get('username');

    $(document).ready(function () {
        $('#adminNav').load('../header/admin_navs.html');

        $.ajax({
            url: 'display_data.php',
            type: 'GET',
            data: {
                username: username
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    displayData(response.data);
                } else {
                    console.error('Failed to fetch data. Message:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Failed to fetch data. Status:', status, 'Error:', error);
            }
        });

        // Function to display data in table rows
        function displayData(data) {
            var tableBody = $('#tableBody');
            tableBody.empty();

            if (data && data.length > 0) {
                $.each(data, function (index, row) {
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
                    <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins>${row.attendance_date || ''}</td>
                    <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.rendered_hours || ''}</td>
                </tr>
            `;
        }
    });
</script>

<script src = "date_sort.js"></script>

</body>

</html>
