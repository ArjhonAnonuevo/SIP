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

$sql = "SELECT interns_username, fname, mname, lname FROM interns";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

if ($result === false) {
    $data['error'] = 'Error in query: ' . $stmt->error;  
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data['users'][] = [
                'username' => $row["interns_username"],
                'firstName' => $row["fname"],
                'middleName' => $row["mname"],
                'lastName' => $row["lname"],
            ];
        }
    } else {
        $data['message'] = 'No usernames found';
    }
    $result->close();
}

$stmt->close();
$conn->close();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();
?>
