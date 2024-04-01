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
} else {
    $stmt = $mysqli->prepare("SELECT id, username, message, request_timestamp, file_name, file_path FROM attendance_request");
    if ($stmt === false) {
        // Error handling for prepare() function
        $response['status'] = 'error';
        $response['message'] = 'Failed to prepare SQL statement: ' . $mysqli->error;
    } elseif ($stmt->execute()) {
        // Bind result variables
        $stmt->bind_result($id, $username, $message, $request_timestamp, $file_name, $file_path);

        $requests = array();

        // Fetch data and store in array
        while ($stmt->fetch()) {
            $request_id = $id; // Store the request ID for reference
            
            // If the request ID is not in the array yet, initialize the request entry
            if (!isset($requests[$request_id])) {
                $requests[$request_id] = array(
                    'id' => $request_id,
                    'username' => $username,
                    'message' => $message,
                    'request_timestamp' => $request_timestamp,
                    'attachments' => array()
                );
            }
            
            // Add attachment information to the corresponding request entry
            if ($file_name !== null && $file_path !== null) {
                $attachment = array(
                    'file_name' => $file_name,
                    'file_path' => $file_path
                );
                $requests[$request_id]['attachments'][] = $attachment;
            }
        }

        // Close statement
        $stmt->close();

        // Convert associative array to indexed array
        $response['data'] = array_values($requests);
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to execute query: ' . $mysqli->error;
    }
}

$mysqli->close();

// Convert response to JSON
echo json_encode($response);
?>
