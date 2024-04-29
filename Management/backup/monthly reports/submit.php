<?php
// Start the session
session_start();

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
 // Validate input data
 $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
 $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
 $time = filter_var($_POST['time'], FILTER_SANITIZE_STRING);
 $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
 $user_id = $_SESSION['user_id']; // Retrieve user_id from session
 include '../configuration/interns_config.php';

 // Call the getDatabaseConfig function
 $config = getDatabaseConfig();
 
 $dbhost = $config['dbhost'];
 $dbuser = $config['dbuser'];
 $dbpass = $config['dbpass'];
 $dbname = $config['dbname'];
 // Create connection
 $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

 // Check connection
 if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
 }

 // Check if user_id exists in interns_account table
 $stmt = $conn->prepare("SELECT * FROM interns_account WHERE username=?");
 $stmt->bind_param("s", $user_id);
 $stmt->execute();
 $result = $stmt->get_result();

 if($result->num_rows > 0){
    // Prepare an SQL statement
    $stmt = $conn->prepare("INSERT INTO acomplisment_report (type, date, time, status, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $type, $date, $time, $status, $user_id);

    // Execute the SQL statement
    if ($stmt->execute()) {
        echo "<script type='text/javascript'>alert('Task Recorded');</script>";
        // Redirect to acomplishment_tab.php
        ob_end_clean();
        header("Location: acomplishment_tab.html");
        exit();
    } else {
        echo "<script type='text/javascript'>alert('Error: " . $stmt->error . "');</script>";
    }
 } else {
    echo "<script type='text/javascript'>alert('Error: Invalid user_id');</script>";
 }

 // Close connection
 $conn->close();
 exit();
}
?>
