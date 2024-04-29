<?php
include '../configuration/interns_config.php';

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
    $response['message'] = "Database connection failed: " . $mysqli->connect_error;
} else {
    // Add an echo statement to confirm the successful database connection
    // echo "Database connected successfully!";
}

// Initialize $username variable
$username = "";

// Check database connection
if ($mysqli->connect_error) {
    $response['status'] = 'error';
    $response['message'] = "Database connection failed: " . $mysqli->connect_error;
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username, message, and file are provided
    if (isset($_POST["username"]) && isset($_POST["message"]) && isset($_FILES["fileAttachment"])) {
        $username = $_POST["username"];
        $message = $_POST["message"];
        $file_name = $_FILES["fileAttachment"]["name"];
        $file_tmp = $_FILES["fileAttachment"]["tmp_name"];
        $file_path = "../request-upload/" . $file_name;

        // Generate and ensure UUID uniqueness
        do {
            $uuid = crc32(uniqid());
            $stmt_check_uuid = $mysqli->prepare("SELECT id FROM attendance_request WHERE id = ?");
            $stmt_check_uuid->bind_param("s", $uuid);
            $stmt_check_uuid->execute();
            $stmt_check_uuid->store_result();
            $uuid_exists = $stmt_check_uuid->num_rows > 0;
            $stmt_check_uuid->close();
        } while ($uuid_exists);

        // Move uploaded file to destination folder
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Set timezone to Asia/Manila
            date_default_timezone_set('Asia/Manila');

            // Get current timestamp
            $current_timestamp = date('Y-m-d H:i:s');

            // Prepare and execute SQL statement to insert data into database
            $stmt = $mysqli->prepare("INSERT INTO attendance_request (id, username, message, file_name, file_path, request_timestamp) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssss", $uuid, $username, $message, $file_name, $file_path, $current_timestamp);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Request submitted successfully!';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error executing SQL statement: ' . $stmt->error . ' for username: ' . $username;
                }
                $stmt->close();
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error preparing SQL statement: ' . $mysqli->error . ' for username: ' . $username;
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to move uploaded file to destination folder! for username: ' . $username;
        }
    } else {
        $missing_fields = [];
        if (!isset($_POST["username"])) {
            $missing_fields[] = "username";
        }
        if (!isset($_POST["message"])) {
            $missing_fields[] = "message";
        }
        if (!isset($_FILES["fileAttachment"])) {
            $missing_fields[] = "fileAttachment";
        }
        $response['status'] = 'error';
        $response['message'] = 'Required field(s) missing: ' . implode(", ", $missing_fields) . ' for username: ' . $username;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Form not submitted!';
}

$mysqli->close();

// Convert response to JSON
echo json_encode($response);
?>
