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
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Default to page 1 if not provided
    $limit = 10; // Number of records per page
    $offset = ($page - 1) * $limit; // Offset calculation

    $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE username = ?");
    $stmt_count->bind_param("s", $username);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();

    if ($result_count) {
        $row_count = $result_count->fetch_assoc();
        $total_records = $row_count['total'];
        $total_pages = ceil($total_records / $limit);

        $stmt = $conn->prepare("SELECT id, morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, IFNULL(rendered_hours, '0') as rendered_hours, overtime_hours FROM attendance WHERE username = ? LIMIT ?, ?");
        $stmt->bind_param("sii", $username, $offset, $limit);
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

            // Debugging statement
            error_log("Data retrieved: " . json_encode($data));

            // Close the database connection
            $stmt->close();
            $conn->close();

            // Return the data in JSON format along with pagination information
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data, 'total_pages' => $total_pages]);
        } else {
            // Debugging statement
            error_log("Failed to execute the query: " . $stmt->error);

            // Handle query execution error
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to execute the query']);
        }
    } else {
        // Debugging statement
        error_log("Failed to execute the query: " . $stmt_count->error);

        // Handle query execution error
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to execute the query']);
    }
} else {
    // Debugging statement
    error_log("Invalid or missing username parameter");

    // Handle missing or empty username parameter
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid or missing username parameter']);
}
?>
