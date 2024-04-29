<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Form</title>
    <link href="../css/dist/tailwind.min.css" rel="stylesheet">
    <script src="../css/dist/jquery.min.js"></script>
    <script src="../node_modules/html5-qrcode/html5-qrcode.min.js"></script>
    <link rel="stylesheet" href="../node_modules/toastr/build/toastr.min.css">
    <script src="../node_modules/toastr/build/toastr.min.js"></script>
    <audio id="scannerSound" src="scan.mp3"></audio>
    <style>
        #reader video {
            transform: rotateY(180deg);
        }
    </style>
</head>

<body class="bg-gray-100">
    <div id="interns-nav"></div>
    <div class="md:ml-48 xl:ml-48 lg:48">
        <div class="container mx-auto px-4 py-6">
            <div class="xl:bg-white lg:bg-white p-8 rounded-md xl:shadow-md">
                <div class="xl:text-left lg:text-left text-center">
                    <h3 class="text-2xl font-semibold text-blueGray-700 font-kanit">Attendance Record</h3>
                </div>
                <div id="scanned-data" class="mt-8 bg-opacity-70 text-white p-2 rounded-md text-lg"></div>
                <div class="container mx-auto flex justify-center">
                    <div id="reader" class="w-full sm:w-1/2 md:w-5/6 h- lg:w-2/4 xl:w-2/4 relative">
                        <video class="absolute inset-0 object-cover w-full h-full" id="reader-video"></video>
                    </div>
                </div>
                <div id="attendance-table"></div>
                <script src="../header/session_timeout_interns.js"></script>
                <script>
                    $(document).ready(function () {
                        $("#interns-nav").load("../header/interns_nav.html");
                    });

                    // Function to calculate QR box size dynamically
                    function calculateQrBoxSize(viewfinderWidth, viewfinderHeight) {
                        let minEdgePercentage = 0.7; // 70%
                        let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                        let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                        return {
                            width: qrboxSize,
                            height: qrboxSize
                        };
                    }

                    const qrCodeScanner = new Html5QrcodeScanner(
                        "reader", {
                            fps: 10,
                            qrbox: calculateQrBoxSize,
                            supportedScanTypes: [
                                Html5QrcodeScanType.SCAN_TYPE_CAMERA
                            ]
                        }
                    );

                    function onScanSuccess(qrCodeMessage) {
                        console.log("QR Code decoded. Message =", qrCodeMessage);
                        displayScannedData(qrCodeMessage);
                        qrCodeScanner.pause(); // Pause the scanning
                        document.getElementById('scannerSound').play();
                        setTimeout(function () {
                            qrCodeScanner.resume(); 
                        }, 3000); // 3 seconds delay
                    }

                    function onScanError(error) {
                        console.error("Error processing QR code:", error);
                    }

                    function displayScannedData(data) {
                        fetch('qr read.php?data=' + encodeURIComponent(data), {
                                method: 'GET'
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.text();
                            })
                            .then(responseText => {
                                try {
                                    const jsonResponse = JSON.parse(responseText);
                                    console.log('Server Response:', jsonResponse);
                                    showModal(jsonResponse.status, jsonResponse.message);
                                } catch (jsonError) {
                                    console.error('Error parsing JSON:', jsonError);
                                }
                            })
                            .catch(fetchError => {
                                console.error('Error fetching data:', fetchError);
                            });
                    }

                    function showModal(status, message) {
                        toastr.info(message);
                    }

                    $(document).ready(function () {
                        $("#attendance-table").load("interns_attendance.html");
                        qrCodeScanner.render(onScanSuccess, onScanError);
                    });
                </script>
            </div>
        </div>
    </div>
</body>

</html>
