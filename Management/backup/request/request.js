$(document).ready(function () {
    $('#admin-navs').load('../header/admin_navs.html');
    // Set worker source for PDF.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = '../node_modules/pdfjs-dist/build/pdf.worker.min.js';

  function renderPdfInModal(fileUrl) {
    var pdfModal = $("#pdfModal");
    var pdfCanvas = document.getElementById('pdfCanvas');
    var context = pdfCanvas.getContext('2d');

    pdfModal.hide();
    context.clearRect(0, 0, pdfCanvas.width, pdfCanvas.height);
    pdfCanvas.height = 0;
    pdfCanvas.width = 0;

    pdfjsLib.getDocument(fileUrl).promise.then(function (pdf) {
        pdf.getPage(1).then(function (page) {
            var viewport = page.getViewport({
                scale: 1
            });

            var maxWidth = 1000; 
            var maxHeight = 1050; // Set your maximum height here
            var scaleFactor = Math.min(maxWidth / viewport.width, maxHeight / viewport.height);
            viewport = page.getViewport({ scale: scaleFactor });

            pdfCanvas.height = viewport.height;
            pdfCanvas.width = viewport.width;
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            page.render(renderContext).promise.then(function () {
                console.log('PDF rendered successfully inside modal');
                // Show the modal
                pdfModal.show();
            }).catch(function (error) {
                console.error('Error rendering PDF:', error);
            });
        }).catch(function (error) {
            console.error('Error getting PDF page:', error);
        });
    }).catch(function (error) {
        console.error('Error loading PDF:', error);
    });
}


    function closeModal() {
        var pdfModal = $("#pdfModal");
        pdfModal.fadeOut();
        pdfModal.addClass('hidden');
    }

    $("#closeModalBtn").click(function () {
        closeModal();
    });

    $('#requestHistory').on('click', '.view-pdf', function () {
        var fileUrl = $(this).data('url');

        console.log("PDF link clicked. File URL:", fileUrl);
        renderPdfInModal(fileUrl);
    });

    // Fetch request history data
    $.ajax({
        url: "fetch-request.php",
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response.status === "success") {
                if (response.data.length === 0) {
                    // If there are no requests, display a message
                    $("#requestHistory").html(`
                    <div class="flex justify-center items-center h-full">
                        <div class="text-gray-500 text-center">
                            <p class="text-lg font-bold">No request at the moment</p>
                            <p class="mt-2">Please check back later.</p>
                        </div>
                    </div>
                `);
                } else {
                    // Iterate through each request and add it to the DOM
                    $.each(response.data, function (index, request) {
                        var requestItem = `
                      <div class="bg-white p-4 rounded shadow-md">
                          <p class="font-bold">${request.username}</p>
                          <p class="text-sm text-gray-500 prose font-poppins">${request.message}</p>
                          <div class="mt-2">
                              <p class="text-sm font-medium text-gray-700">Attachments:</p>
                              <ul class="list-disc list-inside text-sm text-gray-600">`;
                        // Add attachment file names and "View" button
                        $.each(request.attachments, function (_, attachment) {
                            requestItem += `<li>${attachment.file_name}</li>`;
                        });
                        requestItem += `
                              </ul>
                          </div>
                          <div class="mt-4">
                              <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded view-pdf" data-url="${request.attachments[0].file_path}">View</button>
                              <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-2 accept-btn" data-username="${request.username}" data-request-id="${request.id}">Accept</button>
                              <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2 reject-btn" data-request-id="${request.id}" data-username="${request.username}">Reject</button>
                          </div>
                      </div>`;
                        $("#requestHistory").append(requestItem);
                    });
                }
            } else {
                console.error("Failed to fetch request history: " + response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching request history: " + error);
        }
    });


    // Function to fetch attendance data based on username
    function fetchAttendanceData(requestedUsername, requestId) {
        // AJAX call to fetch attendance data based on the provided username
        $.ajax({
            url: "fetch-attendance.php",
            type: "POST",
            dataType: "json",
            data: {
                username: requestedUsername,
                requestId: requestId // Include the requestId
            },
            success: function (response) {
                console.log("Attendance data for username:", response);
                if (response.status === "success") {
                    // Show attendance modal with fetched data
                    showAttendanceModal(response.data, requestedUsername);
                } else {
                    console.error("Failed to fetch attendance data: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching attendance data: " + error);
            }
        });
    }

    // Store requestId in a variable accessible within the scope
    var requestId, username;

    // Event listener for Accept button to fetch and show attendance modal
    $('#requestHistory').on('click', '.accept-btn', function () {
        // Get the username from the corresponding request item
        var requestedUsername = $(this).data('username');
        requestId = $(this).data('request-id'); // Store requestId in the variable

        console.log("Username:", requestedUsername); // Log username
        console.log("Request ID:", requestId); // Log requestId

        // Fetch attendance data based on username
        fetchAttendanceData(requestedUsername, requestId);

        // Log the request ID to verify if it's retrieved correctly
        console.log("Request ID:", requestId);

        console.log("Data sent in AJAX request:");
        console.log({
            requestId: requestId,
        })
    });

    $('#requestHistory').on('click', '.reject-btn', function () {
        var requestId = $(this).data('request-id');
        var username = $(this).data('username');

        console.log("Reject button clicked for Request ID:", requestId);
        console.log("Username:", username);

        console.log("Request ID:", requestId);
        console.log("Username:", username);
        
        console.log("Data sent in AJAX request:", {
            requestId: requestId,
            username: username
        });

        $.ajax({
            url: "reject-request.php",
            type: "POST",
            dataType: "json",
            data: {
                requestId: requestId,
                username: username
            },
            success: function (response) {
                if (response.status === "success") {
                    toastr.success("Request rejected successfully", "", { positionClass: "toast-bottom-right" });
                } else {
                    toastr.error("Failed to reject request: " + response.message, "", { positionClass: "toast-bottom-right" });
                }
            },
            error: function (xhr, status, error) {
                toastr.error("Error rejecting request: " + error, "", { positionClass: "toast-bottom-right" });
            }
        });
    });


    // Close attendance modal when close button is clicked
    $("#closeAttendanceModalBtn").click(function () {
        $("#attendanceModal").fadeOut();
    });

    // Function to show attendance modal with fetched data
    function showAttendanceModal(attendanceData, username) {
        var attendanceModal = $("#attendanceModal");
        var attendanceTableBody = $("#attendanceTable tbody");

        // Set the username in the modal
        attendanceModal.find('.modal-username').text(username);

        // Clear existing table rows
        attendanceTableBody.empty();


        // Populate table with attendance data
        $.each(attendanceData, function (index, attendance) {
            var tableRow = `
              <tr class="attendance-id" data-attendance-id="${attendance.id}">
                  <td class="py-2 px-4"><input type="text" class="edit-input w-20 morning-timein" name="attendanceData[${index}][morning_timein]" value="${attendance.morning_timein}"></td>
                  <td class="py-2 px-4"><input type="text" class="edit-input w-20 lunch-timeout" name="attendanceData[${index}][lunch_timeout]" value="${attendance.lunch_timeout}"></td>
                  <td class="py-2 px-4"><input type="text" class="edit-input w-20 after-lunch-timein" name="attendanceData[${index}][after_lunch_timein]" value="${attendance.after_lunch_timein}"></td>
                  <td class="py-2 px-4"><input type="text" class="edit-input w-20 end-of-day-timeout" name="attendanceData[${index}][end_of_day_timeout]" value="${attendance.end_of_day_timeout}"></td>
                  <td class="py-2 px-4"><input type="text" class="edit-input w-20 attendance-date" name="attendanceData[${index}][attendance_date]" value="${attendance.attendance_date}"></td>
                  <td class="py-2 px-4"><input type="text" class="edit-input w-20 rendered-hours" name="attendanceData[${index}][rendered_hours]" value="${attendance.rendered_hours}"></td>
                  <td class="py-2 px-4"><input type="number" class="edit-input w-20 overtime-hours" name="attendanceData[${index}][overtime_hours]" value="${attendance.overtime_hours}"></td>            
              </tr>`;

            attendanceTableBody.append(tableRow);
        });

        // Show the attendance modal
        attendanceModal.fadeIn();
    }

    $(document).on('click', '#attendanceTable tbody tr', function () {
        // Remove the 'selected' class from all table rows
        $('#attendanceTable tbody tr').removeClass('selected');

        // Add the 'selected' class to the clicked row
        $(this).addClass('selected');

        // Get the attendance ID from the data attribute
        var attendanceId = $(this).data('attendance-id');

        // Store the attendance ID for later use
        $(this).closest('table').data('selected-attendance-id', attendanceId);
    });

    // Save button click event listener to update attendance records
    $('#attendanceForm').submit(function (event) {
        event.preventDefault(); // Prevent the default form submission behavior

        // Get the attendance ID from the currently selected row
        var selectedRow = $("#attendanceTable tbody tr.selected");
        var attendanceId = selectedRow.data('attendance-id');

        var attendanceRows = $("#attendanceTable tbody tr");
        var updatedAttendanceData = [];
        attendanceRows.each(function (index, row) {
            var rowData = {
                id: $(row).data('attendance-id'),
                username: $(row).data('username'),
                attendance: $(row).find('.morning-timein').val(),
                morning_timein: $(row).find('.morning-timein').val(),
                lunch_timeout: $(row).find('.lunch-timeout').val(),
                after_lunch_timein: $(row).find('.after-lunch-timein').val(),
                end_of_day_timeout: $(row).find('.end-of-day-timeout').val(),
                attendance_date: $(row).find('.attendance-date').val(),
                rendered_hours: $(row).find('.rendered-hours').val(),
                overtime_hours: $(row).find('.overtime-hours').val()
            };

            // Push the extracted data object into the array
            updatedAttendanceData.push(rowData);
        });

        // Get the ID of the form
        var formId = $(this).attr('id');

        console.log("Data sent in AJAX request:", {
            attendanceId: attendanceId,
            requestId: requestId,
            attendanceData: updatedAttendanceData
        });
        $.ajax({
            url: "update-attendance.php",
            type: "POST",
            dataType: "json",
            data: {
                attendanceId: attendanceId,
                requestId: requestId,
                attendanceData: updatedAttendanceData,
                username: username
            },

            success: function (response) {
                if (response.status === "success") {
                    toastr.success("Attendance records updated successfully", "", { positionClass: "toast-bottom-right" });
                    $('#attendanceModal').fadeOut();
                } else {
                    toastr.error("Failed to update attendance records: " + response.message, "", { positionClass: "toast-bottom-right" });
                }
            },
            error: function (xhr, status, error) {
                toastr.error("Error updating attendance records: " + error, "", { positionClass: "toast-bottom-right" });
            }
        });
    });
});
