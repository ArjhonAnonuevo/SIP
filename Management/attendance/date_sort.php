<?php
  include '../configuration/interns_config.php';

  // Call the getDatabaseConfig function
  $config = getDatabaseConfig();
  
  $dbhost = $config['dbhost'];
  $dbuser = $config['dbuser'];
  $dbpass = $config['dbpass'];
  $dbname = $config['dbname'];
  
  $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username and date range from the request
$username = $_GET['username'];
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];

// Construct the SQL query with date range filtering
$sql = "SELECT * FROM attendance
        WHERE username = '$username'
        AND attendance_date BETWEEN '$startDate' AND '$endDate'
        ORDER BY attendance_date";

$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    // Fetch data and encode it as JSON
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(array('success' => true, 'data' => $data));
} else {
    // No data found
    echo json_encode(array('success' => true, 'data' => array()));
}

$conn->close();
?>
