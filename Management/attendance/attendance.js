$(document).ready(function () {
    $("#modal-form").load("request_form.html");
    $("#submission-form").load("submit-form.html");

    populateMonthSelect();

    fetchData();

    fetchUsername();
});

function openModal() {
    $('#myModal').fadeIn(300);
    $('#myModal .rounded-md').removeClass('hidden');
}

function closeModal() {
    $('#myModal').fadeOut(300);
}

function openSubmission() {
    $('#submitAttendance').fadeIn(300);
}

function closeSubmission() {
    $('#submitAttendance').fadeOut(300, function () {

        $('#fileUploadLabel').text("Attendance File");
        $('#fileUploadText').text("Upload or drag & drop your PDF file");

        $('#dropzone-file').val('');
    });
}

function populateMonthSelect() {
    var select = $("#monthSelect");
    var months = ["Select", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    select.empty();

    months.forEach(function (month, index) {
        var option = $("<option>", { value: index, text: month });
        select.append(option);
    });

}

function fetchUsername() {
    $.ajax({
        url: '../header/header.php',
        type: 'GET',
        success: function (username) {

            $('#username').val(username);
            calculateOverallTotal(username);
        },
        error: function (xhr, status, error) {
            console.error("Error fetching username: " + error);
        }
    });
}

function generatePDF() {

    $('#generateForm').submit();
}



function fetchData(page = 1) {
    var selectedMonth = $("#monthSelect").val();

    if (selectedMonth !== "0") {
        $.ajax({
            type: "POST",
            url: "sort-attendance.php",
            data: { month: selectedMonth },
            dataType: "json",
            success: function (data) {

                updateTable(data);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data: " + error);
            }
        });
    } else {
        $.ajax({
            type: "POST",
            url: "get_attendance_data.php",
            dataType: "json",
            data: { page: page },
            success: function (response) {
                var data = response.data;
                var totalPages = response.totalPages;
                var currentPage = response.currentPage;

                updateTable(data);

                updatePagination(currentPage, totalPages);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data: " + error);
            }
        });
    }
}

function updatePagination(currentPage, totalPages) {
    var paginationContainer = $("#pagination");
    paginationContainer.empty();

    if (totalPages > 1) {

        for (var i = 1; i <= totalPages; i++) {
            var buttonClass = (i === currentPage) ? "current" : "";
            var button = "<button class='" + buttonClass + "' onclick='fetchData(" + i + ")'>" + i + "</button>";
            paginationContainer.append(button);
        }
    }

    $("#currentPageInfo").text("Page " + currentPage + " of " + totalPages);
}

function updateTable(data) {
    var tableBody = $("#attendanceTable tbody");
    var totalRenderedHours = 0;

    tableBody.empty();

    if (data.length > 0) {

        $.each(data, function (index, row) {
            var newRow = "<tr class='font-poppins text-gray-900 text-md'>";
            newRow += "<td class='py-2 px-4'>" + row.morning_timein + "</td>";
            newRow += "<td class='py-2 px-4'>" + row.lunch_timeout + "</td>";
            newRow += "<td class='py-2 px-4'>" + row.after_lunch_timein + "</td>";
            newRow += "<td class='py-2 px-4'>" + row.end_of_day_timeout + "</td>";
            newRow += "<td class='py-2 px-4'>" + row.attendance_date + "</td>";
            newRow += "<td class='py-2 px-4'>" + row.rendered_hours + "</td>";
            newRow += "<td class='py-2 px-4'>" + row.overtime_hours + "</td>";
            newRow += "</tr>";

            totalRenderedHours += parseFloat(row.rendered_hours);

            tableBody.append(newRow);
        });
    } else {

        var noRecordsRow = "<tr><td colspan='7' class='py-2 px-4 text-center'>No attendance records found.</td></tr>";
        tableBody.append(noRecordsRow);
    }

    $("#totalRenderedHours").text(totalRenderedHours);

    var selectedMonth = $("#monthSelect").val();
    if (selectedMonth === "0") {

        calculateOverallTotal();
    }
}

function calculateOverallTotal(username) {
    $.ajax({
        url: 'calculate-hours.php',
        method: 'GET',
        data: { username: username },
        success: function (response) {

            $("#totalRenderedHours").text(response.total_rendered_hours);
        },
        error: function (xhr, status, error) {
            console.error("Error calculating overall total:", error);
        }
    });
}

fetchUsername();

$("#monthSelect").change(function () {
    var selectedMonth = $(this).val();
    console.log("Selected month:", selectedMonth);
    fetchData();

    $('#selectedMonth').val($(this).val());

});


function printFile() {
    var username = $('#username').val();
    var selectedMonth = $("#monthSelect").val();

    console.log("Username:", username);
    console.log("Selected Month:", selectedMonth);

    $.ajax({
        url: "print_attendance.php",
        type: "POST",
        data: { username: username, selectedMonth: selectedMonth },
        success: function (dynamicContent) {
            var printWindow = window.open('', '_blank');
            $(printWindow.document.body).html(dynamicContent);
            printWindow.print();
            printWindow.close();
        },
        error: function (xhr, status, error) {
            console.error("Error fetching dynamic content:", error);
        }
    });
}
