<?php
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

// Assuming 'username' is passed as a parameter in the URL
$username = $_GET['username'];

$sql = "SELECT date, type, time, status FROM acomplisment_report WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $errorResponse = array('error' => 'Error preparing statement: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    $conn->close();
    exit();
}

$stmt->bind_param('s', $username);
$stmt->execute();

$result = $stmt->get_result();
$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    // Provide a custom response for "No records found"
    $noRecordsResponse = array('message' => 'No records found for username: ' . $username);
    echo json_encode($noRecordsResponse);
    exit();
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
