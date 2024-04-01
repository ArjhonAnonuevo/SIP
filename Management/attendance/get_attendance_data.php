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

// Pagination variables
$page = isset($_POST['page']) ? intval($_POST['page']) : 1; // Default page is 1
$perPage = 10; // Number of records per page
$offset = ($page - 1) * $perPage; // Calculate offset

$query = "SELECT COUNT(*) AS total FROM attendance WHERE username = ?";
$totalHours = 0;

// Prepare statement to get total number of records
if ($stmt_total = mysqli_prepare($connection, $query)) {
    mysqli_stmt_bind_param($stmt_total, "s", $username);
    mysqli_stmt_execute($stmt_total);
    $result_total = mysqli_stmt_get_result($stmt_total);
    $row_total = mysqli_fetch_assoc($result_total);
    $total_records = $row_total['total'];
    mysqli_stmt_close($stmt_total);
}

// Calculate total pages
$totalPages = ceil($total_records / $perPage);

// Prepare statement to fetch paginated data
$query = "SELECT * FROM attendance WHERE username = ? LIMIT ?, ?";
if ($stmt = mysqli_prepare($connection, $query)) {
    mysqli_stmt_bind_param($stmt, "sii", $username, $offset, $perPage);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $attendanceData = array();

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $morning_timein = $row['morning_timein'];
            $lunch_timeout = $row['lunch_timeout'];
            $after_lunch_timein = $row['after_lunch_timein'];
            $end_of_day_timeout = $row['end_of_day_timeout'];

            // Check if time is midnight, if yes, display '00:00' instead of '00:00:00'
            $morning_timein_formatted = ($morning_timein === '00:00:00') ? '00:00' : date('h:i A', strtotime($morning_timein));
            $lunch_timeout_formatted = ($lunch_timeout === '00:00:00') ? '00:00' : date('h:i A', strtotime($lunch_timeout));
            $after_lunch_timein_formatted = ($after_lunch_timein === '00:00:00') ? '00:00' : date('h:i A', strtotime($after_lunch_timein));
            $end_of_day_timeout_formatted = ($end_of_day_timeout === '00:00:00') ? '00:00' : date('h:i A', strtotime($end_of_day_timeout));

            $attendanceData[] = array(
                'morning_timein' => $morning_timein_formatted,
                'lunch_timeout' => $lunch_timeout_formatted,
                'after_lunch_timein' => $after_lunch_timein_formatted,
                'end_of_day_timeout' => $end_of_day_timeout_formatted,
                'attendance_date' => $row['attendance_date'],
                'rendered_hours' => intval(substr($row['rendered_hours'], 0, 2)),
                'overtime_hours' => intval(substr($row['overtime_hours'], 0, 2)),
            );
        }
    }

    // Send JSON response with total pages and current page
    header('Content-Type: application/json');
    echo json_encode(array('data' => $attendanceData, 'totalPages' => $totalPages, 'currentPage' => $page));

} else {
    // Send an error response
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Error preparing statement: ' . mysqli_error($connection)));
}

mysqli_close($connection);
?>
