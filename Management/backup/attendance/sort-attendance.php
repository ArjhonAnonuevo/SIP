<?php
session_start();
include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];
$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (!isset($_SESSION['username'])) {
    header("Location: ../interns account/login.php");
    exit();
}

$username = $_SESSION['username'];

// Get the selected month from the POST request
$selectedMonth = $_POST['month'];

// Construct the query to filter records by the selected month
$query = "SELECT * FROM attendance WHERE username = ? AND MONTH(attendance_date) = ?";

$totalHours = 0;

if ($stmt = mysqli_prepare($connection, $query)) {
    mysqli_stmt_bind_param($stmt, "si", $username, $selectedMonth);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $attendanceData = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $morning_timein = ($row['morning_timein'] == '00:00:00') ? '00:00' : date('h:i A', strtotime($row['morning_timein']));
            $lunch_timeout = ($row['lunch_timeout'] == '00:00:00') ? '00:00' : date('h:i A', strtotime($row['lunch_timeout']));
            $after_lunch_timein = ($row['after_lunch_timein'] == '00:00:00') ? '00:00' : date('h:i A', strtotime($row['after_lunch_timein']));
            $end_of_day_timeout = ($row['end_of_day_timeout'] == '00:00:00') ? '00:00' : date('h:i A', strtotime($row['end_of_day_timeout']));
            
            $attendanceData[] = array(
                'morning_timein' => $morning_timein,
                'lunch_timeout' => $lunch_timeout,
                'after_lunch_timein' => $after_lunch_timein,
                'end_of_day_timeout' => $end_of_day_timeout,
                'attendance_date' => $row['attendance_date'],
                'rendered_hours' => intval(substr($row['rendered_hours'], 0, 2)),
                'overtime_hours' => intval(substr($row['overtime_hours'], 0, 2)),
            );
        }
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($attendanceData);

} else {
    // Send an error response
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Error preparing statement: ' . mysqli_error($connection)));
}

mysqli_close($connection);
?>
