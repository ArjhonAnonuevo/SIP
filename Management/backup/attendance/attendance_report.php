<?php
include '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to UTF-8
$conn->query("SET NAMES 'utf8'");

$rowsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;

$offset = ($page - 1) * $rowsPerPage;

// Query to fetch total rows
$totalRowsQuery = "SELECT COUNT(*) as total FROM interns_profile";
$totalRowsResult = $conn->query($totalRowsQuery);

if (!$totalRowsResult) {
    die("Error fetching total rows: " . $conn->error);
}

$totalRows = $totalRowsResult->fetch_assoc()["total"];
$totalPages = ceil($totalRows / $rowsPerPage);

$sql = "SELECT ip.name, ip.mname, ip.lname, ip.department, ia.username
        FROM interns_profile ip
        JOIN interns_account ia ON ia.profile_id = ip.id
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("ii", $offset, $rowsPerPage);

// Execute the statement
$result = $stmt->execute();

if (!$result) {
    die("Error executing query: " . $stmt->error);
}

// Get result set
$resultSet = $stmt->get_result();

// Fetch all rows from the result set
$rows = $resultSet->fetch_all(MYSQLI_ASSOC);

// Create an array with pagination data
$response = array(
    "success" => true,
    "data" => $rows,
    "totalPages" => $totalPages,
    "currentPage" => $page
);

// Set the Content-Type header to JSON
header('Content-Type: application/json');

// Echo the JSON response
$jsonResponse = json_encode($response, JSON_PRETTY_PRINT);
if ($jsonResponse === false) {
    die("Error encoding JSON: " . json_last_error_msg());
}

echo $jsonResponse;

$stmt->close();
$conn->close();
?>
