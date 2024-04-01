$(document).ready(function() {
  $('#admin-navs').load('../header/admin_navs.html');
  // Set worker source for PDF.js
  pdfjsLib.GlobalWorkerOptions.workerSrc = '../node_modules/pdfjs-dist/build/pdf.worker.min.js';
  // Function to render PDF file onto canvas inside modal
  function renderPdfInModal(fileUrl) {
    var pdfModal = $("#pdfModal");
    var pdfCanvas = document.getElementById('pdfCanvas');
    var context = pdfCanvas.getContext('2d');
    // Reset modal state
    pdfModal.hide(); // Hide modal initially
    context.clearRect(0, 0, pdfCanvas.width, pdfCanvas.height); // Clear canvas
    pdfCanvas.height = 0; // Reset canvas height
    pdfCanvas.width = 0; // Reset canvas width
    pdfjsLib.getDocument(fileUrl).promise.then(function(pdf) {
      pdf.getPage(1).then(function(page) {
        var viewport = page.getViewport({
          scale: 1
        });
        pdfCanvas.height = viewport.height;
        pdfCanvas.width = viewport.width;
        var renderContext = {
          canvasContext: context,
          viewport: viewport
        };
        page.render(renderContext).promise.then(function() {
          console.log('PDF rendered successfully inside modal');
          // Show the modal
          pdfModal.show();
        }).catch(function(error) {
          console.error('Error rendering PDF:', error);
        });
      }).catch(function(error) {
        console.error('Error getting PDF page:', error);
      });
    }).catch(function(error) {
      console.error('Error loading PDF:', error);
    });
  }
  // Function to close modal with fade-out effect
  function closeModal() {
    var pdfModal = $("#pdfModal");
    // Hide the modal with fade-out effect
    pdfModal.fadeOut();
    // Add the 'hidden' class back to the modal
    pdfModal.addClass('hidden');
  }
  // Attach click event handler to close modal button
  $("#closeModalBtn").click(function() {
    closeModal();
  });
  // Event listener for view PDF button
  $('#requestHistory').on('click', '.view-pdf', function() {
    var fileUrl = $(this).data('url');
    console.log("PDF link clicked. File URL:", fileUrl);
    renderPdfInModal(fileUrl);
  });
  // Fetch request history data
  $.ajax({
    url: "fetch-request.php",
    type: "GET",
    dataType: "json",
    success: function(response) {
      if (response.status === "success") {
        // Iterate through each request and add it to the DOM
        $.each(response.data, function(index, request) {
          var requestItem = `
                          <div class="bg-white p-4 rounded shadow-md">
                              <p class="font-bold">${request.username}</p>
                              <p class="text-sm text-gray-500 prose font-poppins">${request.message}</p>
                              <div class="mt-2">
                                  <p class="text-sm font-medium text-gray-700">Attachments:</p>
                                  <ul class="list-disc list-inside text-sm text-gray-600">`;
          // Add attachment file names and "View" button
          $.each(request.attachments, function(_, attachment) {
            requestItem += `<li>${attachment.file_name}</li>`;
          });
          requestItem += `
                                  </ul>
                              </div>
                              <div class="mt-4">
                                  <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded view-pdf" data-url="${request.attachments[0].file_path}">View</button>
                                  <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-2 accept-btn" data-username="${request.username}">Accept</button>
                                  <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2">Reject</button>
                              </div>
                          </div>`;
          $("#requestHistory").append(requestItem);
        });
      } else {
        console.error("Failed to fetch request history: " + response.message);
      }
    },
    error: function(xhr, status, error) {
      console.error("Error fetching request history: " + error);
    }
  });

  // Function to fetch attendance data based on username
  function fetchAttendanceData(username) {
    // AJAX call to fetch attendance data based on the provided username
    $.ajax({
      url: "fetch-attendance.php",
      type: "POST",
      dataType: "json",
      data: {
        username: username
      }, // Send the username as data
      success: function(response) {
        console.log("Attendance data for username:", response);
        if (response.status === "success") {
          // Show attendance modal with fetched data
          showAttendanceModal(response.data, username);
        } else {
          console.error("Failed to fetch attendance data: " + response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error("Error fetching attendance data: " + error);
      }
    });
  }

  // Event listener for Accept button to fetch and show attendance modal
  $('#requestHistory').on('click', '.accept-btn', function() {
    // Get the username from the corresponding request item
    var username = $(this).data('username');
    // Fetch attendance data based on username
    fetchAttendanceData(username);
    console.log(username)
  });

  // Close attendance modal when close button is clicked
  $("#closeAttendanceModalBtn").click(function() {
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
    $.each(attendanceData, function(index, attendance) {
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

  $(document).on('click', '#attendanceTable tbody tr', function() {
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
$('#attendanceForm').submit(function(event) {
  event.preventDefault(); // Prevent the default form submission behavior
  
  // Get the attendance ID from the currently selected row
  var selectedRow = $("#attendanceTable tbody tr.selected");
  var attendanceId = selectedRow.data('attendance-id');
  var attendanceUsername = selectedRow.data('username');



  // Select all rows in the attendance table body
  var attendanceRows = $("#attendanceTable tbody tr");

  // Create an array to store the updated attendance data
  var updatedAttendanceData = [];

  // Loop through each row of the attendance table
  attendanceRows.each(function(index, row) {
    // Extract data from input fields within each row and create an object
    var rowData = {
      id: $(row).data('attendance-id'), // Get the attendance ID from the row's data attribute
      username: $(row).data('username'), // Get the username from the row's data attribute
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

  // Send the updated attendance data to the server for updating
  $.ajax({
    url: "update-attendance.php",
    type: "POST",
    dataType: "json",
    data: {
      attendanceId: attendanceId,
      attendanceData: updatedAttendanceData
    },
    success: function(response) {
      // Handle the response from the server
      if (response.status === "success") {
        toastr.success("Attendance records updated successfully", "", { positionClass: "toast-bottom-right" });
        $('#attendanceModal').fadeOut();
      } else {
        toastr.error("Failed to update attendance records: " + response.message, "", { positionClass: "toast-bottom-right" });
      }
    },
    error: function(xhr, status, error) {
      toastr.error("Error updating attendance records: " + error, "", { positionClass: "toast-bottom-right" });
    }
  });
});
});