<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Table</title>
    <link href="../css/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $(document).ready(function () {
            function fetchData() {
                var selectedMonth = $("#month").val();

                $.ajax({
                    type: "POST",
                    url: "get_attendance_data.php",
                    data: { month: selectedMonth },
                    dataType: "json",
                    success: function (data) {
                        // Update the table with the fetched data
                        updateTable(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching data: " + error);
                    }
                });
            }

            function updateTable(data) {
                // Clear existing table rows
                $("#attendanceTable tbody").empty();

                var totalRenderedHours = 0;

                if (data.length > 0) {
                    // Append new rows to the table
                    $.each(data, function (index, row) {
                        var newRow = "<tr>";
                        newRow += "<td class='py-2 px-4'>" + row.morning_timein + "</td>";
                        newRow += "<td class='py-2 px-4'>" + row.lunch_timeout + "</td>";
                        newRow += "<td class='py-2 px-4'>" + row.after_lunch_timein + "</td>";
                        newRow += "<td class='py-2 px-4'>" + row.end_of_day_timeout + "</td>";
                        newRow += "<td class='py-2 px-4'>" + row.attendance_date + "</td>";
                        newRow += "<td class='py-2 px-4'>" + row.rendered_hours + "</td>";
                        newRow += "</tr>";

                        totalRenderedHours += row.rendered_hours;

                        $("#attendanceTable tbody").append(newRow);
                    });
                } else {
                    // Display a message for no records found
                    var noRecordsRow = "<tr><td colspan='7' class='py-2 px-4 text-center'>No attendance records found.</td></tr>";
                    $("#attendanceTable tbody").append(noRecordsRow);
                }

                // Update total rendered hours
                $("#totalRenderedHours").text(totalRenderedHours);
            }

            // Attach the fetch data function to the month selection change event
            $("#month").change(function () {
                fetchData();
            });

            // Initial data fetch on page load
            fetchData();
        });
    </script>
</head>
<body class="font-sans bg-gray-100">
    <section class="py-8 bg-blueGray-50">
        <div class="container max-w-md">
            <div class="bg-white p-8 rounded-md shadow-md">
                <div class="flex sm:flex-col items-center justify-between mb-6">
                    <h3 class="text-2xl font-semibold text-blueGray-700" style="font-family: 'Montserrat', sans-serif;">Attendance Record</h3>
                    <div class="flex items-center mt-4 md:mt-0">
                        <form action="generate_attendance.php">
                            <button class="ml-4 px-6 py-3 bg-green-800 text-white rounded-md font-bold">Generate PDF</button>
                        </form>

                        <form action="edit_attendance.php">
                            <button class="ml-4 px-6 py-3 bg-green-800 text-white rounded-md font-bold">Request</button>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="attendanceTable" class="w-full table-auto divide-y divide-gray-200">
                        <!-- Table headers -->
                        <thead>
                            <tr class="bg-green-700 text-white">
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">Morning Time In</th>
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">Lunch Time out</th>
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">After lunch Time In</th>
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">End of the day Time Out</th>
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">Attendance date</th>
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">Rendered Hours</th>
                                <th class="px-6 py-3 text-xs uppercase font-semibold text-left">Overtime</th>
                            </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody>
                        </tbody>
                    </table>
                    <div class="mt-4 mr-44">
                        <span class="text-xl font-semibold text-blueGray-700 flex justify-end" style="font-family: 'Montserrat', sans-serif;">Total Rendered Hours: <span id="totalRenderedHours">0</span></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ... (Additional content) ... -->
</body>
</html>
