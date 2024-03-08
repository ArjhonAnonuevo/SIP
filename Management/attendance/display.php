<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Reports</title>
    <link href="../css/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body class="bg-gray-100">
<div id = "adminNav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="container mx-auto px-4 sm:px-8">
            <div class="mx-auto md:max-w-7xl md:max-h-min bg-white shadow-md p-6 mt-8 rounded-md">
                <h1 class="text-2xl font-bold text-center mb-4">Attendance Table</h1>
                <div class="flex flex-row gap-4">
                    <form method="post" action = "generate_attendance.php">
                        <button type="submit" name="generate_pdf" class="bg-green-900 hover:bg-green-700 mb-4 text-white font-bold py-2 px-4 rounded">
                            Generate PDF
                        </button>
                    </form>
                    <button class="bg-green-900 hover:bg-green-700 mb-4 text-white font-bold py-2 px-4 rounded">Edit Attendance</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="bg-white shadow-md overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
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
    
        $(document).ready(function () {
        $('#adminNav').load('../header/admin_navs.html');
            $.ajax({
                url: 'display_data.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    // Append data to the table body
                    var tableBody = $('#tableBody');
                    tableBody.empty(); 
                    $.each(data, function (index, row) {
                        tableBody.append(`
                            <tr>
                                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm">${row.morning_timein}</td>
                                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm">${row.lunch_timeout}</td>
                                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm">${row.after_lunch_timein}</td>
                                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm">${row.end_of_day_timeout}</td>
                                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm">${row.attendance_date}</td>
                                <td class="px-5 py-3 sm:py-5 md:py-3 lg:py-5 xl:py-3 border-b border-gray-200 bg-white text-sm">${row.rendered_hours}</td>
                            </tr>
                        `);
                    });
                },
                error: function () {
                    console.error('Failed to fetch data.');
                }
            });
        });
    </script>
</body>

</html>
