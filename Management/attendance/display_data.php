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

if (isset($_GET['username']) && !empty($_GET['username'])) {
    $username = $_GET['username'];

    // Pagination parameters
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10; 
    $offset = ($page - 1) * $limit; 

    // Check if sortByMonth parameter is set and not empty
    if (isset($_GET['sortByMonth']) && !empty($_GET['sortByMonth'])) {
        $selectedMonth = $_GET['sortByMonth'];
        // Add sorting condition based on selected month
        $stmt = $conn->prepare("SELECT id, morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, IFNULL(rendered_hours, '0') as rendered_hours, overtime_hours FROM attendance WHERE username = ? AND MONTH(attendance_date) = ? ORDER BY attendance_date ASC LIMIT ?, ?");
        $stmt->bind_param("siii", $username, $selectedMonth, $offset, $limit);
    } else {
        // Default query without sorting by month
        $stmt = $conn->prepare("SELECT id, morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, IFNULL(rendered_hours, '0') as rendered_hours, overtime_hours FROM attendance WHERE username = ? LIMIT ?, ?");
        $stmt->bind_param("sii", $username, $offset, $limit);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $data = array();

        // Fetch each row individually
        while ($row = $result->fetch_assoc()) {
            // Convert time to 12-hour format with AM or PM
            $row['morning_timein'] = ($row['morning_timein'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['morning_timein']));
            $row['lunch_timeout'] = ($row['lunch_timeout'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['lunch_timeout']));
            $row['after_lunch_timein'] = ($row['after_lunch_timein'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['after_lunch_timein']));
            $row['end_of_day_timeout'] = ($row['end_of_day_timeout'] == '00:00:00') ? '00:00' : date("h:i A", strtotime($row['end_of_day_timeout']));

            // Format rendered_hours and overtime_hours
            $row['rendered_hours'] = ($row['rendered_hours'] == '00:00:00') ? '0' : date("g", strtotime($row['rendered_hours']));
            $row['overtime_hours'] = ($row['overtime_hours'] == '00:00:00') ? '0' : date("g", strtotime($row['overtime_hours']));

            $data[] = $row;
        }

        // Calculate total pages
        $total_records_query = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE username = '$username'");
        $total_records_data = $total_records_query->fetch_assoc();
        $total_records = $total_records_data['total'];
        $total_pages = ceil($total_records / $limit);

        // Debugging statement
        error_log("Data retrieved: " . json_encode($data));

        // Close the database connection
        $stmt->close();
        $conn->close();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data, 'total_pages' => $total_pages]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to execute the query']);
    }
} else {
    error_log("Invalid or missing username parameter");

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid or missing username parameter']);
}
?>
