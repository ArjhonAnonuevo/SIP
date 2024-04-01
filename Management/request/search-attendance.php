<?php
include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set the locale to ensure AM/PM indicator is recognized
$mysqli->set_charset("utf8");

// Check if date and username parameters are provided
if (isset($_GET['date']) && isset($_GET['username'])) {
    $date = $_GET['date'];
    $username = $_GET['username'];

    // SQL query to select attendance records with formatted time-ins and handle null values using COALESCE
    $query = "SELECT *,
    IFNULL(TIME_FORMAT(morning_timein, '%h:%i %p'), '00:00') AS formatted_morning_timein,
    IFNULL(TIME_FORMAT(lunch_timeout, '%h:%i %p'), '00:00') AS formatted_lunch_timeout,
    IFNULL(TIME_FORMAT(after_lunch_timein, '%h:%i %p'), '00:00') AS formatted_after_lunch_timein,
    IFNULL(TIME_FORMAT(end_of_day_timeout, '%h:%i %p'), '00:00') AS formatted_end_of_day_timeout,
    COALESCE(HOUR(rendered_hours), 0) AS rendered_hours, 
    COALESCE(HOUR(overtime_hours), 0) AS overtime_hours 
    FROM attendance 
    WHERE attendance_date = ? AND username = ?";
    
    $statement = $mysqli->prepare($query);

    $statement->bind_param("ss", $date, $username);

    $statement->execute();

    $result = $statement->get_result();

    $attendanceRecords = $result->fetch_all(MYSQLI_ASSOC);

    $statement->close();

    // Return the attendance records as JSON
    echo json_encode($attendanceRecords);
} else {
    echo json_encode(array('error' => 'Date or username parameter is missing'));
}

// Close the database connection
$mysqli->close();
?>
