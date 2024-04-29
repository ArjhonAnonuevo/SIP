<?php
include '../configuration/interns_config.php';

// Database configuration
$config = getDatabaseConfig();
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Connect to database
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$limit = 20;
$start = ($page - 1) * $limit;

$dateFilter = isset($_GET['dateFilter']) ? $_GET['dateFilter'] : '';
$actionFilter = isset($_GET['actionFilter']) ? $_GET['actionFilter'] : '';
$timestampDateFilter = isset($_GET['timestampDateFilter']) ? $_GET['timestampDateFilter'] : '';

$query = "SELECT * FROM audits WHERE 1=1";
if (!empty($dateFilter)) {
    $query .= " AND DATE(audit_timestamp) = '$dateFilter'";
}
if (!empty($actionFilter)) {
    $query .= " AND actions = '$actionFilter'";
}
if (!empty($timestampDateFilter)) {
    $query .= " AND DATE(audit_timestamp) = '$timestampDateFilter'";
}
$query .= " ORDER BY audit_timestamp DESC LIMIT $start, $limit";

$result = $mysqli->query($query);
$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

$totalQuery = "SELECT COUNT(*) AS total FROM audits WHERE 1=1";
if (!empty($dateFilter)) {
    $totalQuery .= " AND DATE(audit_timestamp) = '$dateFilter'";
}
if (!empty($actionFilter)) {
    $totalQuery .= " AND actions = '$actionFilter'";
}
if (!empty($timestampDateFilter)) {
    $totalQuery .= " AND DATE(audit_timestamp) = '$timestampDateFilter'";
}
$totalResult = $mysqli->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];

$totalPages = ceil($totalRows / $limit);

$mysqli->close();

$response = [
    'data' => $logs,
    'pagination' => [
        'total_pages' => $totalPages,
        'current_page' => $page,
        'total_records' => $totalRows
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>
