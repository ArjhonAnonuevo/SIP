<?php
require_once '../configuration/interns_config.php';

// Get database configuration
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve id and status from the form
    $id = $_POST['id'];
    $status = $_POST['status'];

    $query = "UPDATE server_status SET interns_status = '$status' WHERE id = '$id'";

    if ($conn->query($query) === TRUE) {
        echo json_encode(array("status" => "success", "message" => "Record updated successfully"));
    } else {
        // Return error response
        echo json_encode(array("status" => "error", "message" => "Error updating record: " . $conn->error));
    }
}

    $conn->close();
?>
