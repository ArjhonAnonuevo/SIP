<?php
include "../configuration/application_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($con, 'utf8');

$rowsPerPage = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage; 

$sql = "SELECT A.control_number, A.given_name, A.middle_name, A.family_name, A.primary_email, A.application_date, S.status_name
        FROM application AS A
        INNER JOIN status AS S ON A.control_number = S.control_number
        WHERE S.status_name = 'Rejected'
        LIMIT $offset, $rowsPerPage";
$result = mysqli_query($con, $sql);
if (!$result) {
    die("Error fetching data: " . mysqli_error($con));
}

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$totalRowsQuery = "SELECT COUNT(*) as total FROM application AS A INNER JOIN status AS S ON A.control_number = S.control_number WHERE S.status_name = 'Rejected'";
$totalRowsResult = mysqli_query($con, $totalRowsQuery);
if (!$totalRowsResult) {
    die("Error getting total rows: " . mysqli_error($con));
}
$totalRows = mysqli_fetch_assoc($totalRowsResult)['total'];

mysqli_close($con);

$totalPages = ceil($totalRows / $rowsPerPage);

$response = array(
    'totalRows' => $totalRows,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'data' => $data
);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
?>
