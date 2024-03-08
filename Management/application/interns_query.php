<?php
session_start();
include "../configuration/application_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

$rowsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

// Execute SQL query to get the total number of rows
$totalRowsQuery = "SELECT COUNT(*) as total FROM application";
$totalRowsResult = mysqli_query($con, $totalRowsQuery);

// Check for successful query execution
if (!$totalRowsResult) {
    die("Error in totalRowsQuery: " . mysqli_error($con));
}

// Fetch the total number of rows from the result
$totalRows = mysqli_fetch_assoc($totalRowsResult)['total'];

// Calculate the total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);

// Execute SQL query with LIMIT and OFFSET
$sql = "SELECT id, given_name, middle_name, family_name, primary_email, application_date, control_number
        FROM application
        LIMIT $offset, $rowsPerPage";

$result = mysqli_query($con, $sql);

if (!$result) {
    die("Error in SQL query: " . mysqli_error($con));
}

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

mysqli_close($con);

$response = array(
    'totalRows' => $totalRows,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'data' => $data
);

header('Content-Type: application/json');
echo json_encode($response);
?>
