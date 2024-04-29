<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Reports</title>
    <link href="../css/dist/output.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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
                        <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-2xl font-kanit mb-6">Interns Attendance</h2>
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
                <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-2xl font-kanit mb-6">Interns Attendance</h2>
                <div id="files"></div>
            </div>
        </div>
    </div>
    <div id="pdfModal" class="hidden fixed top-0 left-0 w-full h-full bg-gray-900 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white rounded-lg p-6">
            <div class="flex justify-end">
                <button id="closePdfModal" class="text-gray-700 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div id="pdfContainer"></div>
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

            // Initialize pdfjsLib
            const pdfjsLib = window['pdfjs-dist/build/pdf'];
            pdfjsLib.GlobalWorkerOptions.workerSrc = '../node_modules/pdfjs-dist/build/pdf.worker.min.js';

            function fetchData(page) {
                $.ajax({
                    url: 'display_data.php',
                    type: 'GET',
                    data: {
                        username: username,
                        page: page
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
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.morning_timein || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.lunch_timeout || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.after_lunch_timein || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.end_of_day_timeout || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.attendance_date || ''}</td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins"><span class="rendered-hours">${row.rendered_hours || ''}</span></td>
                    <td class="px-5 py-3 sm:py-3 md:py-3 lg:py-3 xl:py-3 border-b border-gray-200 bg-white text-sm font-poppins">${row.overtime_hours || ''}</td>
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

            // Function to display attendance files in cards
            function displayAttendanceFiles(data) {
                var attendanceFilesContainer = $('#files'); // Select the container with id "files"
                attendanceFilesContainer.empty(); // Clear any existing content

                if (data && data.length > 0) {
                    $.each(data, function (index, file) {
                        var card = `
                            <div class="max-w-sm rounded overflow-hidden shadow-lg mt-6">
                                <div class="px-6 py-4">
                                    <div class="font-bold text-md font-poppins mb-2">${file.filename || ''}</div>
                                    <p class="text-gray-700 text-base font-poppins">${file.created_at || ''}</p>
                                </div>
                                <div class="px-6 pt-4 pb-2 flex">
                                    <a href="${file.file_path}" class="block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2" download>Download</a>
                                    <button class="view-button bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded" data-filepath="${file.file_path}">View</button>
                                </div>
                            </div>
                        `;
                        attendanceFilesContainer.append(card); 
                    });
                } else {
                    attendanceFilesContainer.html('<p class="text-center py-6 px-4 font-poppins">No attendance files available</p>'); 
                }
            }

            // Load initial attendance files
            fetchAttendanceFiles();

            // Open PDF modal and display the PDF using PDF.js
            $(document).on('click', '.view-button', function () {
                const filePath = $(this).data('filepath');
                openPdfModal(filePath);
            });

            function openPdfModal(filePath) {
                $('#pdfModal').removeClass('hidden');
                displayPdf(filePath);
            }

            function displayPdf(filePath) {
                const pdfContainer = document.getElementById('pdfContainer');

                // Asynchronous download PDF
                pdfjsLib.getDocument(filePath).promise.then(function (pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    renderPage(pdfDoc, 1);
                }).catch(function (error) {
                    console.error('Error loading PDF:', error);
                });

               function renderPage(pdfDoc, pageNum) {
                pdfDoc.getPage(pageNum).then(function (page) {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.5 });

                    // Set maximum width and height for the canvas
                    const maxWidth = 1000; 
                    const maxHeight = 1050; 
                    const scaleFactor = Math.min(maxWidth / viewport.width, maxHeight / viewport.height);
                    const scaledViewport = page.getViewport({ scale: scaleFactor });

                    canvas.height = scaledViewport.height;
                    canvas.width = scaledViewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: scaledViewport
                    };

                    page.render(renderContext).promise.then(function () {
                        pdfContainer.appendChild(canvas);
                    });
                });
            }

            }

            // Close PDF modal
            $('#closePdfModal').on('click', function () {
                $('#pdfModal').addClass('hidden');
                const pdfContainer = document.getElementById('pdfContainer');
                pdfContainer.innerHTML = '';
            });
        });
    </script>

</body>

</html>
