<?php
include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

$response = array();

// Check database connection
if ($mysqli->connect_error) {
    $response['status'] = 'error';
    $response['message'] = "Connection failed: " . $mysqli->connect_error;
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username, message, and file are provided
    if (isset($_POST["username"]) && isset($_POST["message"]) && isset($_FILES["fileAttachment"])) {
        $username = $_POST["username"];
        $message = $_POST["message"];
        $file_name = $_FILES["fileAttachment"]["name"];
        $file_tmp = $_FILES["fileAttachment"]["tmp_name"];
        $file_path = "../request-upload/" . $file_name;

        // Move uploaded file to destination folder
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Set timezone to Asia/Manila
            date_default_timezone_set('Asia/Manila');

            // Get current timestamp
            $current_timestamp = date('Y-m-d H:i:s');

            // Prepare and execute SQL statement to insert data into database
            $stmt = $mysqli->prepare("INSERT INTO attendance_request (username, message, file_name, file_path, request_timestamp) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $message, $file_name, $file_path, $current_timestamp);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Request submitted successfully!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error submitting request!';
            }

            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to move uploaded file!';
        }
    } else {
        // Handle the case when the username, message, or file is missing
        $response['status'] = 'error';
        $response['message'] = 'Username, message, or file is missing!';
    }
} else {
    // Handle the case when the form is not submitted
    $response['status'] = 'error';
    $response['message'] = 'Form not submitted!';
}

$mysqli->close();

// Convert response to JSON
echo json_encode($response);
?>
