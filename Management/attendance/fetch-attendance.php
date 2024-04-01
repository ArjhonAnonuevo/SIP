<?php
include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Retrieve the user from the request
$user = $_GET['user'];

// Prepare and execute the query to fetch attendance records
$sql = "SELECT file_path, created_at, filename FROM attendance_submissions WHERE username = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

// Create an array to store records
$records = array();

// Check if any records are found
if ($result->num_rows > 0) {
    // Loop through each row and add to the array
    while ($row = $result->fetch_assoc()) {
        $records[] = array(
            "file_path" => $row["file_path"],
            "created_at" => $row["created_at"],
            "filename" => $row["filename"]
        );
    }
} else {
    // No records found
    $records = array("message" => "No attendance records found for user: " . $user);
}

// Close the statement and database connection
$stmt->close();
$mysqli->close();

// Encode the array to JSON and output
echo json_encode($records);
?>
