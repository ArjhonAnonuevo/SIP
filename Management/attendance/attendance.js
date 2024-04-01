$(document).ready(function() {
    $("#modal-form").load("request_form.html");
    $("#submission-form").load("submit-form.html");

    // Populate the month select dropdown
    populateMonthSelect();

    // Fetch all data initially when the page loads
    fetchData();

    // Fetch the username when the document is ready
    fetchUsername();
});

// Function to open modal
function openModal() {
    $('#myModal').fadeIn(300);
    $('#myModal .rounded-md').removeClass('hidden');
}

// Function to close modal
function closeModal() {
    $('#myModal').fadeOut(300);
}

// Function to open submission
function openSubmission() {
    $('#submitAttendance').fadeIn(300);
}

// Function to close submission
function closeSubmission() {
    $('#submitAttendance').fadeOut(300, function() {
        // Reset file name display
        $('#fileUploadLabel').text("Attendance File");
        $('#fileUploadText').text("Upload or drag & drop your PDF file");
        // Clear file input value
        $('#dropzone-file').val('');
    });
}
// Function to populate the month select dropdown
function populateMonthSelect() {
var select = $("#monthSelect");
var months = ["Select", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

// Clear existing options
select.empty();

// Add options for each month
months.forEach(function(month, index) {
    var option = $("<option>", { value: index, text: month });
    select.append(option);
});

}

function fetchUsername() {
    $.ajax({
        url: '../header/header.php',
        type: 'GET',
        success: function(username) {
            // Update the value of the hidden input field
            $('#username').val(username);
            calculateOverallTotal(username);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching username: " + error);
        }
    });
}

function generatePDF() {
    // Submit the form to generate the PDF
    $('#generateForm').submit();
}

function fetchData(page = 1) {
var selectedMonth = $("#monthSelect").val();

// Check if a month is selected
if (selectedMonth !== "0") {
    $.ajax({
        type: "POST",
        url: "sort-attendance.php",
        data: { month: selectedMonth },
        dataType: "json",
        success: function(data) {
            // Update the table with the fetched data
            updateTable(data);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching data: " + error);
        }
    });
} else {
    $.ajax({
        type: "POST",
        url: "get_attendance_data.php",
        dataType: "json",
        data: { page: page }, 
        success: function(response) {
            var data = response.data;
            var totalPages = response.totalPages;
            var currentPage = response.currentPage;
     

            // Update the table with the fetched data
            updateTable(data);

            // Update pagination
            updatePagination(currentPage, totalPages);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching data: " + error);
        }
    });
}
}

function updatePagination(currentPage, totalPages) {
var paginationContainer = $("#pagination");
paginationContainer.empty(); // Clear existing pagination links

// Check if there is more than one page
if (totalPages > 1) {
    // Create a button for each page
    for (var i = 1; i <= totalPages; i++) {
        var buttonClass = (i === currentPage) ? "current" : ""; 
        var button = "<button class='" + buttonClass + "' onclick='fetchData(" + i + ")'>" + i + "</button>";
        paginationContainer.append(button);
    }
}
// Update current page info
$("#currentPageInfo").text("Page " + currentPage + " of " + totalPages);
}
// Function to update table with fetched data
function updateTable(data) {
var tableBody = $("#attendanceTable tbody");
var totalRenderedHours = 0;

// Clear existing table rows
tableBody.empty();

if (data.length > 0) {
    // Append new rows to the table
    $.each(data, function(index, row) {
        var newRow = "<tr class='font-poppins text-gray-900 text-md'>";
        newRow += "<td class='py-2 px-4'>" + row.morning_timein + "</td>";
        newRow += "<td class='py-2 px-4'>" + row.lunch_timeout + "</td>";
        newRow += "<td class='py-2 px-4'>" + row.after_lunch_timein + "</td>";
        newRow += "<td class='py-2 px-4'>" + row.end_of_day_timeout + "</td>";
        newRow += "<td class='py-2 px-4'>" + row.attendance_date + "</td>";
        newRow += "<td class='py-2 px-4'>" + row.rendered_hours + "</td>";
        newRow += "<td class='py-2 px-4'>" + row.overtime_hours + "</td>";
        newRow += "</tr>";

        totalRenderedHours += parseFloat(row.rendered_hours); // Convert to float before adding

        tableBody.append(newRow);
    });
} else {
    // Display a message for no records found
    var noRecordsRow = "<tr><td colspan='7' class='py-2 px-4 text-center'>No attendance records found.</td></tr>";
    tableBody.append(noRecordsRow);
}

// Update total rendered hours
$("#totalRenderedHours").text(totalRenderedHours);

// Check if the page is loaded initially or if a month is selected
var selectedMonth = $("#monthSelect").val();
if (selectedMonth === "0") {
    // If the selected month is 0 (all months), calculate overall total rendered hours
    calculateOverallTotal();
}
}

// Function to calculate overall total rendered hours
function calculateOverallTotal(username) {
$.ajax({
    url: 'calculate-hours.php',
    method: 'GET',
    data: { username: username }, 
    success: function(response) {
        // Update the UI with the overall total
        $("#totalRenderedHours").text(response.total_rendered_hours);
    },
    error: function(xhr, status, error) {
        console.error("Error calculating overall total:", error);
    }
});
}

// Call the function to fetch the username and calculate overall total rendered hours
fetchUsername();



// Attach the fetch data function to the month selection change event
$("#monthSelect").change(function() {
    fetchData();
    // Update the value of the hidden input field for selected month
    $('#selectedMonth').val($(this).val());
});