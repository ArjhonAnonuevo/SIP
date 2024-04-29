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

$sortByMonth = $_GET['sortByMonth'] ?? '';
$sortByAction = $_GET['sortByAction'] ?? ''; 
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1; 
$offset = ($page - 1) * $limit;

$sql = "SELECT `date`, `type`, `time`, `status` FROM `acomplisment_report` WHERE `user_id` = ?";
$params = array($username);

if (!empty($sortByMonth)) {
    $sql .= " AND MONTH(`date`) = ?";
    $params[] = $sortByMonth;
}

if (!empty($sortByAction)) {
    $sql .= " AND `status` = ?";
    $params[] = $sortByAction;
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $errorResponse = array('error' => 'Prepare statement failed: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    exit();
}

// Bind parameters dynamically based on the number of placeholders in the SQL statement
$paramTypes = str_repeat('s', count($params)); 
$stmt->bind_param($paramTypes, ...$params);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    $errorResponse = array('error' => 'No data found');
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
}

$stmt->close();
$conn->close();
?>
