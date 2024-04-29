<?php
session_start();
include "../configuration/application_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Connect to the database with UTF-8 charset
$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
mysqli_set_charset($con, 'utf8');

$rowsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

// Execute SQL query to get the total number of rows for the provided status
$status = array('Pending', 'Level 2 Interview', 'On Process');
$totalRowsQuery = "SELECT COUNT(*) as total FROM application AS A INNER JOIN status AS S ON A.control_number = S.control_number WHERE S.status_name IN ('".implode("', '", $status)."')";
$totalRowsResult = mysqli_query($con, $totalRowsQuery);

// Check for successful query execution
if (!$totalRowsResult) {
    die("Error in totalRowsQuery: " . mysqli_error($con));
}

// Fetch the total number of rows from the result
$totalRows = mysqli_fetch_assoc($totalRowsResult)['total'];

// Calculate the total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);

$sql = "SELECT A.given_name, A.middle_name, A.family_name, A.primary_email, A.application_date, A.control_number, S.status_name
        FROM application AS A
        INNER JOIN status AS S ON A.control_number = S.control_number
        WHERE S.status_name IN ('".implode("', '", $status)."')
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

// Set Content-Type header with UTF-8 charset
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>
