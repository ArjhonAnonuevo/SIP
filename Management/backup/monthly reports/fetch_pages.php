<?php
session_start();
require_once "../configuration/interns_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    $errorResponse = array('error' => 'Connection failed: ' . $conn->connect_error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    exit();
}

if (!isset($_SESSION['username'])) {
    $errorResponse = array('error' => 'User not logged in');
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    exit();
}

$username = $_SESSION['username'];

$limit = 10;

// Count the total rows without LIMIT and OFFSET
$sql = "SELECT COUNT(*) AS totalRows FROM `acomplisment_report` WHERE `user_id` = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $errorResponse = array('error' => 'Prepare statement failed: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    exit();
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalRows = $row['totalRows'];
    $totalPages = ceil($totalRows / $limit); 
    $response = array('totalPages' => $totalPages);
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $errorResponse = array('error' => 'No data found');
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
}

$stmt->close();
$conn->close();
?>
