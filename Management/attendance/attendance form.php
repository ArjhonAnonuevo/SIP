<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Form</title>
    <link href="../css/dist/tailwind.min.css" rel="stylesheet">
    <script src="../css/dist/jquery.min.js"></script>
    <script src="../node_modules/html5-qrcode/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Istok+Web">
    <style>
        #reader video {
            transform: scaleX(-1);
        }
    </style>
</head>

<body class="bg-gray-100">
    <div id="interns-nav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="container mx-auto px-4 py-6">
            <div id="scanned-data" class="mt-8 bg-opacity-70 text-white p-2 rounded-md text-lg"></div>
            <div class="container mx-auto flex justify-center">
                <div id="reader" class="w-full sm:w-1/2 md:w-5/6 h-auto lg:w-2/4 xl:w-2/4 relative">
                    <video class="absolute inset-0 object-cover w-full h-full" id="reader video"></video>
                </div>
            </div>
            <div id="responseModal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-x-hidden overflow-y-auto">
                <div class="fixed inset-0 bg-black opacity-70"></div>
                <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md mx-auto relative">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-800" id="modalTitle">Record Attendance</h2>
                    </div>
                    <p id="modalMessage" class="text-md font-semibold mb-6 text-gray-800"></p>
                    <div class="flex justify-end">
                        <button class="bg-red-500 text-white py-2 px-4 rounded-full hover:bg-red-600 focus:outline-none focus:shadow-outline-green active:bg-green-800" onclick="closeModal()">Close</button>
                    </div>
                </div>
            </div>
            <div id="attendance-table"></div>
            <script>
    $(document).ready(function () {
        $("#interns-nav").load("../header/interns_nav.html");
    });

    const qrCodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 350 });

    qrCodeScanner.render(onScanSuccess, onScanError);

    function onScanSuccess(qrCodeMessage) {
        console.log("QR Code decoded. Message =", qrCodeMessage);
        displayScannedData(qrCodeMessage);
    }

    function onScanError(error) {
        console.error("Error processing QR code:", error);
        // Optionally, handle the error in your application (e.g., display an error message to the user).
    }

    function displayScannedData(data) {
        fetch('qr read.php?data=' + encodeURIComponent(data), {
            method: 'GET'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();  // Read the response as text instead of JSON
        })
        .then(responseText => {
            try {
                // Try parsing the response as JSON
                const jsonResponse = JSON.parse(responseText);
                // Display the response in the console
                console.log('Server Response:', jsonResponse);
                // Show the response in the modal
                showModal(jsonResponse.status, jsonResponse.message);
            } catch (jsonError) {
                // Handle the case where the response is not valid JSON
                console.error('Error parsing JSON:', jsonError);
                // Optionally, display an error message to the user or take appropriate action
            }
        })
        .catch(fetchError => {
            console.error('Error fetching data:', fetchError);
            // Optionally, handle the error in your application (e.g., display an error message to the user).
        });
    }

    function showModal(status, message) {
        document.getElementById('modalMessage').innerText = message;
        $('#responseModal').fadeIn(300);
    }

    function closeModal() {
        $('#responseModal').fadeOut(300, function () {
            // Redirect or perform additional actions if needed after fade out
            window.location.href = "attendance form.php";
        });
    }

    $(document).ready(function () {
        $("#attendance-table").load("attendance_table.php");
    });
</script>

        </div>
    </div>
</body>

</html>
