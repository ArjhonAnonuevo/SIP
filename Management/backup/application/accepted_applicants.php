<?php
include "../configuration/application_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Connect to the database with UTF-8 charset
$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
mysqli_set_charset($con, 'utf8');

// Pagination parameters
$rowsPerPage = 10; // Number of rows per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1; // Current page number
$offset = ($page - 1) * $rowsPerPage; // Offset for pagination

// Execute SQL query to fetch data with pagination
$sql = "SELECT A.control_number, A.given_name, A.middle_name, A.family_name, A.primary_email, A.application_date, S.status_name
        FROM application AS A
        INNER JOIN status AS S ON A.control_number = S.control_number
        WHERE S.status_name = 'Accepted'
        LIMIT $offset, $rowsPerPage";
$result = mysqli_query($con, $sql);

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Execute SQL query to get total number of rows
$totalRowsQuery = "SELECT COUNT(*) as total FROM application AS A INNER JOIN status AS S ON A.control_number = S.control_number WHERE S.status_name = 'Accepted'";
$totalRowsResult = mysqli_query($con, $totalRowsQuery);
$totalRows = mysqli_fetch_assoc($totalRowsResult)['total'];

mysqli_close($con);

// Calculate total number of pages
$totalPages = ceil($totalRows / $rowsPerPage);

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
