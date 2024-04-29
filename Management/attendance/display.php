<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Reports</title>
    <link href="../css/dist/output.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="../node_modules/pdfjs-dist/build/pdf.min.js"></script>
</head>

<body class="bg-gray-100">
    <div id="adminNav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="max-w-6xl mx-auto rounded-md">
        <div class="container mx-auto px-4 sm:px-8 mt-8">
            <div class="overflow-x-auto">
                <div class="bg-white shadow-md rounded-md">
                    <div class="px-6 py-4">
                              <div class="flex flex-col md:flex-row md:justify-between items-center mb-5">
                            <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-3xl font-kanit ml-4">Interns Attendance</h2>
                            <button id="print" type="button" class="middle none center mr-4 rounded-lg bg-customGreen py-3 px-6 font-sans text-xs font-bold uppercase text-white shadow-md shadow-green-500/20 transition-all hover:shadow-lg hover:shadow-green-500/40 focus:opacity-[0.85] focus:shadow-none active:opacity-[0.85] active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" data-ripple-light="true">Print</button>
                            </div>
                         <div class="flex items-center mt-5">
                        <label for="monthFilter" class="mr-4">Filter by Month:</label>
                        <select id="monthFilter" class="px-4 py-2 border border-gray-300 rounded-md">
                            <option value="">All</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                            </select>
                        </div>
                        <div class="max-h-96 overflow-y-auto"">
                            <table class="min-w-full w-full divide-y divide-gray-200 mt-8">
                                <thead>
                                    <tr class="bg-customGreen text-white">
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">Morning Time In</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">Lunch Time Out</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">After lunch Time In</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">End of the day Time Out</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">Attendance Date</th>
                                        <th class="px-5 py-2 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">Rendered Hours</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider font-rubik">Overtime</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="px-5 py-3 border-b-2 border-gray-200 text-right text-xs font-semibold uppercase tracking-wider">Total Rendered Hours:</td>
                                        <td id="totalRenderedHours" class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider"></td>
                                        <td class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="flex justify-end items-center mt-4">
                            <span id="pageInfo"></span>
                            <img id="prevPage" src="../icons/prev.svg" class="h-4 w-auto ml-2" alt="Previous Page">
                            <img id="nextPage" src="../icons/next.svg" class="h-4 w-auto ml-2" alt="Next Page">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 bg-white shadow-lg px-6 py-4" >
                <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-2xl font-kanit mb-6">Interns Attendance Files</h2>
                <div id="files"></div>
            </div>
        </div>
    </div>
    <script src="../header/session_timeout.js"></script>
    <script>
        const params = new URLSearchParams(window.location.search);
        const username = params.get('username');
        let currentPage = 1;
        let totalPages = 1;

        $(document).ready(function () {
            $('#adminNav').load('../header/admin_navs.html');
            function fetchData(page, sortByMonth,) {
                $.ajax({
                    url: 'display_data.php',
                    type: 'GET',
                    data: {
                        username: username,
                        page: page,
                        sortByMonth: sortByMonth

                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            displayData(response.data);
                            updatePaginationInfo(page, response.total_pages);
                        } else {
                            console.error('Failed to fetch data. Message:', response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Failed to fetch data. Status:', status, 'Error:', error);
                    }
                });
            }

            function displayData(data) {
                var tableBody = $('#tableBody');
                tableBody.empty();
                var totalRenderedHours = 0;

                if (data && data.length > 0) {
                    $.each(data, function (index, row) {
                        var tableRow = createTableRow(row);
                        tableBody.append(tableRow);
                        if (!isNaN(row.rendered_hours)) {
                            totalRenderedHours += parseFloat(row.rendered_hours);
                        }
                    });
                } else {
                    // If there is no data, add a row indicating no data
                    var noDataRow = '<tr><td colspan="6" class="text-center py-6 px-4 font-poppins">No data available</td></tr>';
                    tableBody.append(noDataRow);
                }

                // Set total rendered hours without decimal places
                $('#totalRenderedHours').text(Math.floor(totalRenderedHours));
            }

            function createTableRow(row) {
                return `
                <tr>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins">${row.morning_timein || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins">${row.lunch_timeout || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins">${row.after_lunch_timein || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins">${row.end_of_day_timeout || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins">${row.attendance_date || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins"><span class="rendered-hours">${row.rendered_hours || ''}</span></td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-md font-poppins">${row.overtime_hours || ''}</td>
                </tr>
            `;
            }

            function calculateDisplayedRenderedHours() {
                var totalDisplayedRenderedHours = 0;
                $('.rendered-hours').each(function () {
                    var renderedHours = parseFloat($(this).text());
                    if (!isNaN(renderedHours)) {
                        totalDisplayedRenderedHours += renderedHours;
                    }
                });
                return totalDisplayedRenderedHours;
            }

            function updateTotalRenderedHours() {
                var totalRenderedHours = calculateDisplayedRenderedHours();
                $('#totalRenderedHours').text(Math.floor(totalRenderedHours));
            }

            function updatePaginationInfo(currentPage, totalPages) {
                $('#pageInfo').text('Page ' + currentPage + ' of ' + totalPages);
            }

            // Load initial data
            fetchData(currentPage);

            // Previous page button click event
            $('#prevPage').on('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    fetchData(currentPage);
                }
            });
                   $('#monthFilter').change(function () {
                const selectedMonth = $(this).val();
                fetchData(currentPage, selectedMonth);
            });
              $('#print').click(function() {
              const selectedMonth = $('#monthFilter').val();
              printData(selectedMonth);
            });

            // Next page button click event
            $('#nextPage').on('click', function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    fetchData(currentPage);
                }
            });

            // Function to fetch and display attendance files
            function fetchAttendanceFiles() {
                $.ajax({
                    url: 'fetch-files.php',
                    type: 'GET',
                    data: {
                        username: username
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            displayAttendanceFiles(response.data);
                        } else {
                            console.error('Failed to fetch attendance files. Message:', response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Failed to fetch attendance files. Status:', status, 'Error:', error);
                    }
                });
            }
            function printData(selectedMonth) {
              $.ajax({
                url: 'print_sortedAttendance.php',
                type: 'POST',
                data: {
                  selectedMonth: selectedMonth,
                  username: username
                },
                success: function(response) {
                  console.log('Data received from server:', response);
                  // Check if the server response contains printable content
                  if (response && typeof response === 'string' && response.trim().startsWith('<!DOCTYPE html>')) {
                    // Open a new window and print the data received from the server
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(response);
                    printWindow.document.close();
                    printWindow.print();
                  } else {
                    console.error('No printable content received from server.');
                  }
                },
                error: function(xhr, status, error) {
                  console.error('Error fetching data from server:', error);
                  console.log('Status:', status);
                  console.log('XHR:', xhr);
                }
              });
            }

          function displayAttendanceFiles(data) {
                var attendanceFilesContainer = $('#files');
                attendanceFilesContainer.empty();

                if (data && data.length > 0) {
                    $.each(data, function (index, file) {
                       var listItem = `
                        <li class="py-4 flex justify-between items-center border-b border-gray-200 shadow-lg rounded-lg">
                            <div class="px-6 py-4">
                                <div class="font-bold text-md font-poppins mb-2">${file.filename || ''}</div>
                                <p class="text-gray-700 text-base font-poppins">${file.created_at || ''}</p>
                            </div>
                            <div class="px-6 pt-4 pb-2 flex">
                                <a href="${file.file_path}" class="block text-customGreen underline font-bold py-2 px-4 rounded mr-2" download>Download</a>
                                <a href="${file.file_path}" class="view-button text-blue-500 underline  font-bold py-2 px-4 rounded" target="_blank">View</a>
                            </div>
                        </li>
                    `;

                        attendanceFilesContainer.append(listItem);
                    });
                } else {
                    attendanceFilesContainer.html('<p class="text-center py-6 px-4 font-poppins">No attendance files available</p>');
                }
            }

            // Load initial attendance files
            fetchAttendanceFiles();


        });
    </script>

</body>

</html>
