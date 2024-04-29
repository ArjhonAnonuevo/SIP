$(document).ready(function () {
    $("#admin-navs").load("../header/admin_navs.html");

    // Retrieve control_number from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const controlNumber = urlParams.get('control_number');

    if (controlNumber) {
        $.ajax({
            url: '../application/fetch-files.php',
            type: 'GET',
            dataType: 'json',
            data: { control_number: controlNumber },
            success: function (data) {
                // Clear existing rows from the table
                $('#file-container').empty().append('<div class="bg-white overflow-y-auto"><table id="fileTable" class="min-w-full bg-white"><thead><tr class ="bg-customGreen text-white font-rubik "><th class="px-6 py-3  text-left text-xs leading-4 font-medium uppercase tracking-wider">File Name</th><th class="px-6 py-3 text-left text-xs leading-4 font-medium  uppercase tracking-wider">File Size</th><th class="px-6 py-3  text-left text-xs leading-4 font-medium uppercase tracking-wider"></th></tr></thead><tbody id="fileTableBody"></tbody></table></div>');

                // Iterate through each file in the response
                $.each(data, function (index, file) {
                    // Calculate file size
                    var fileSize = (file.value.length / 1024).toFixed(2) + ' KB'; 

                    var $row = $('<tr>').append(
                        $('<td>').text(file.file_name).addClass('px-6 py-4 whitespace-nowrap font-poppins'),
                        $('<td>').text(fileSize).addClass('px-6 py-4 whitespace-nowrap font-poppins'),
                        $('<td>').append(
                            $('<div>').addClass('flex justify-end').append(
                                $('<a>').attr('href', 'data:application/pdf;base64,' + file.value)
                                    .attr('download', file.file_name + '.pdf')
                                    .addClass('font-poppins inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150 mr-2')
                                    .text('Download PDF'),
                                $('<button>').addClass('font-poppins inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-600 bg-white hover:text-indigo-500 focus:outline-none focus:border-indigo-300 focus:shadow-outline-indigo active:text-indigo-700 active:bg-gray-50 transition ease-in-out duration-150 view-pdf-btn')
                                    .text('View PDF')
                                    .on('click', function () {
                                        viewPDF(file.value);
                                    })
                            )
                        ).addClass('px-6 py-4 whitespace-nowrap')
                    );
                    // Append the row to the table body
                    $('#fileTableBody').append($row);
                });
            },
            error: function (xhr, status, error) {
                console.error('Error fetching data:', xhr, status, error);
                // Display error message on the page
                $('#file-container').html('<p>Error fetching files. Please try again later.</p>');
            }
        });
    } else {
        console.error('Control number is missing or empty.');
        // Display error message on the page
        $('#file-container').html('<p>Control number is missing or empty.</p>');
    }
});

// Function to view PDF using PDF.js
function viewPDF(pdfData) {
    // Convert base64 PDF data to a Blob
    var byteCharacters = atob(pdfData);
    var byteNumbers = new Array(byteCharacters.length);
    for (var i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    var byteArray = new Uint8Array(byteNumbers);
    var blob = new Blob([byteArray], { type: 'application/pdf' });

    // Create a URL for the Blob
    var url = URL.createObjectURL(blob);

    // Open the PDF in a new browser tab
    window.open(url, '_blank');
}
