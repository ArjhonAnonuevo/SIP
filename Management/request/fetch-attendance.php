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

if (isset($_POST['username'])) {
    $username = $mysqli->real_escape_string($_POST['username']);

    // Prepare SQL query to fetch attendance data for the provided username
    $query = "SELECT id,
    username, 
    CASE WHEN morning_timein = '00:00:00' THEN '00:00' ELSE TIME_FORMAT(morning_timein, '%h:%i %p') END AS morning_timein, 
    CASE WHEN lunch_timeout = '00:00:00' THEN '00:00' ELSE TIME_FORMAT(lunch_timeout, '%h:%i %p') END AS lunch_timeout, 
    CASE WHEN after_lunch_timein = '00:00:00' THEN '00:00' ELSE TIME_FORMAT(after_lunch_timein, '%h:%i %p') END AS after_lunch_timein, 
    CASE WHEN end_of_day_timeout = '00:00:00' THEN '00:00' ELSE TIME_FORMAT(end_of_day_timeout, '%h:%i %p') END AS end_of_day_timeout, 
    attendance_date, 
    HOUR(rendered_hours) AS rendered_hours, 
    HOUR(overtime_hours) AS overtime_hours 
FROM 
    attendance 
WHERE 
    username = '$username'";


    // Execute the query
    $result = $mysqli->query($query);

    if ($result) {
        // Fetch data from the result set
        $attendanceData = array();
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $key => $value) {
                if ($value === null) {
                    $row[$key] = "0";
                }
            }
            // Add each row of attendance data to the array
            $attendanceData[] = $row;
        }

        echo json_encode(array('status' => 'success', 'data' => $attendanceData));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to fetch attendance data'));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Username is required'));
}
?>
