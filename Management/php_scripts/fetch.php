<?php
include '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error])); 
}

$rowsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

$sql = "SELECT interns_username, fname, mname, lname FROM interns LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $offset, $rowsPerPage);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($result === false) {
    $data[] = ['error' => 'Error in query: ' . $stmt->error];  
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'interns_username' => $row["interns_username"],
                'fname' => $row["fname"],
                'mname' => $row["mname"],
                'lname' => $row["lname"],
            ];
        }
    } else {
        $data[] = ['message' => 'No usernames found'];
    }
    $result->close();
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();
?>
