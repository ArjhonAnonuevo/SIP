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

$username = $_GET['username'];

// Pagination parameters
$currentPage = $_GET['page'] ?? 1;
$perPage = 10;
$start = ($currentPage - 1) * $perPage;

// Fetch limited number of records for the current page
$sortByMonth = $_GET['sortByMonth'] ?? '';
$sortByAction = $_GET['sortByAction'] ?? ''; 
$sql = "SELECT DATE_FORMAT(date, '%M %e, %Y') AS formatted_date, type, TIME_FORMAT(time, '%h:%i %p') AS formatted_time, status FROM acomplisment_report WHERE user_id = ?";
if (!empty($sortByMonth)) {
    $sql .= " AND MONTH(date) = ?";
}
if (!empty($sortByAction)) {
    $sql .= " AND status = ?";
}

$sql .= " LIMIT ?, ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $errorResponse = array('error' => 'Error preparing statement: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    $conn->close();
    exit();
}

if (!empty($sortByMonth) && !empty($sortByAction)) {
    $stmt->bind_param('sssii', $username, $sortByMonth, $sortByAction, $start, $perPage);
} elseif (!empty($sortByMonth)) {
    $stmt->bind_param('ssii', $username, $sortByMonth, $start, $perPage);
} elseif (!empty($sortByAction)) {
    $stmt->bind_param('ssii', $username, $sortByAction, $start, $perPage);
} else {
    $stmt->bind_param('sii', $username, $start, $perPage);
}


$stmt->execute();

$result = $stmt->get_result();
$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['date'] = $row['formatted_date'];
        $row['time'] = $row['formatted_time']; 
        unset($row['formatted_date']);
        unset($row['formatted_time']); 
        $data[] = $row;
    }
} else {
    $noRecordsResponse = array('message' => 'No records found for username: ' . $username);
    echo json_encode($noRecordsResponse);
    exit();
}

// Count total number of records
$countQuery = "SELECT COUNT(*) AS total FROM acomplisment_report WHERE user_id = ?";
$stmtCount = $conn->prepare($countQuery);

if ($stmtCount === false) {
    $errorResponse = array('error' => 'Error preparing statement: ' . $conn->error);
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    $conn->close();
    exit();
}

$stmtCount->bind_param('s', $username);
$stmtCount->execute();
$stmtCount->store_result();
$stmtCount->bind_result($totalRecords);
$stmtCount->fetch();
$stmtCount->close();

$totalPages = ceil($totalRecords / $perPage);

// Pagination data
$paginationData = array(
    'currentPage' => $currentPage,
    'perPage' => $perPage,
    'totalRecords' => $totalRecords,
    'totalPages' => $totalPages
);

$response = array(
    'pagination' => $paginationData,
    'data' => $data
);

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
