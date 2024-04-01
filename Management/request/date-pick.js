$(document).ready(function() {
    // Initialize datepicker
    $('#datepicker').datepicker({
      dateFormat: 'yy-mm-dd', // Set the date format
      onSelect: function(dateText) {
        // Get the username from the stored data
        var username = $('#datepicker').data('username');
        if (username) {
          // Perform search for attendance records corresponding to the selected date and username
          searchAttendance(dateText, username);
        } else {
          console.error('Username is not selected. Please select a username before selecting a date.');
        }
      }
    });
  
    // Event listener for accept button click to store the username
    $('#requestHistory').on('click', '.accept-btn', function() {
      var username = $(this).data('username');
      $('#datepicker').data('username', username); 
    });
  
    // Function to search for attendance records based on date and username
    function searchAttendance(date, username) {
      // AJAX request to fetch attendance records for the selected date and username
      $.ajax({
        url: 'search-attendance.php',
        type: 'GET',
        data: {
          date: date,
          username: username
        },
        dataType: 'json',
        success: function(response) {
          // Populate attendance records in the table
          populateAttendanceTable(response);
        },
        error: function(xhr, status, error) {
          console.error('Error searching attendance:', error);
        }
      });
    }
  
    // Function to populate attendance records in the table
    // Function to populate attendance records in the table
function populateAttendanceTable(attendance) {
    var tableBody = $('#attendanceTable tbody');
    // Clear existing table rows
    tableBody.empty();
  
    // Check if there are no records
    if (attendance.length === 0) {
      var emptyRow = '<tr><td colspan="7" class="px-4 py-2 text-center">No attendance records found</td></tr>';
      tableBody.append(emptyRow);
      return; // Exit the function early
    }
  
    // Populate table with attendance records
    $.each(attendance, function(index, record) {
      var tableRow = `
        <tr class="attendance-id" data-attendance-id="${record.id}">
          <td class="py-2 px-4"><input type="text" class="edit-input w-20 morning-timein" value="${record.formatted_morning_timein}"></td>
          <td class="py-2 px-4"><input type="text" class="edit-input w-20 lunch-timeout" value="${record.formatted_lunch_timeout}"></td>
          <td class="py-2 px-4"><input type="text" class="edit-input w-20 after-lunch-timein" value="${record.formatted_after_lunch_timein}"></td>
          <td class="py-2 px-4"><input type="text" class="edit-input w-20 end-of-day-timeout" value="${record.formatted_end_of_day_timeout}"></td>
          <td class="py-2 px-4"><input type="text" class="edit-input w-20 attendance-date" value="${record.attendance_date}"></td>
          <td class="py-2 px-4"><input type="text" class="edit-input w-20 rendered-hours" value="${record.rendered_hours}"></td>
          <td class="py-2 px-4"><input type="number" class="edit-input w-20 overtime-hours" value="${record.overtime_hours}"></td>
        </tr>`;
  
      tableBody.append(tableRow);
    });
  }  
  });
