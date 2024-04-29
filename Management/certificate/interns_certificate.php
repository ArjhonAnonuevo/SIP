<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/dist/output.css" rel="stylesheet">
    <script src="../css/dist/jquery.min.js"></script>
    <title>Uploaded Files</title>
</head>
<body class="bg-gray-100">
<div id="interns-nav"></div>
<div class="md:ml-48 xl:ml-48 lg:48">       
    <div class="mx-auto md:max-w-7xl md:max-h-min xl:bg-white md:bg-white shadow-md p-6 mt-8 rounded-md max-w-screen-sm">
        <div class="flex flex-col md:flex-row md:justify-between items-center mb-5">
            <h2 class="text-2xl font-bold mb-6 md:mb-0 md:text-3xl font-kanit">Certificates</h2>
        </div>
        <div id="display-certificate" class="overflow-x-auto">
            <ul class="divide-y divide-gray-200">
            </ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#interns-nav").load("../header/interns_nav.html");
        // Fetch certificates from display.php and display them
       $.ajax({
            url: "fetch_certifications.php",
            type: "GET",
            dataType: "json",
            success: function(response) {
                // Iterate through file paths and create list items for certificates
                response.forEach(function(filePath) {
                    var fileName = filePath.split('/').pop();
                    // Fetch file size asynchronously
                    $.ajax({
                        url: filePath,
                        type: "HEAD",
                        success: function(data, textStatus, xhr) {
                            var fileSize = xhr.getResponseHeader('Content-Length');
                            // Convert bytes to kilobytes (optional)
                            var fileSizeKB = fileSize / 1024;
                            // Create list item with certificate data
                            var listItem = `
                                <li class="py-4 flex justify-between items-center border-b border-gray-200 shadow-lg rounded-lg">
                                    <div>
                                        <h3 class="text-xl font-poppins">${fileName}</h3>
                                        <p class="text-gray-600">${fileSizeKB.toFixed(2)} KB</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="${filePath}" download="${fileName}" class="inline-block text-customGreen underline font-bold py-2 px-4 rounded">
                                            Download
                                        </a>
                                        <a href="${filePath}" target="_blank" class="inline-block text-blue-500 underline font-bold py-2 px-4 rounded">
                                            View
                                        </a>
                                    </div>
                                </li>
                            `;
                            // Append list item to the list
                            $('#display-certificate ul').append(listItem);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching file size:", error);
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching certifications:", error);
            }
        });
    });
</script>
</body>
</html>
