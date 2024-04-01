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

    $stmt = $conn->prepare("SELECT id, morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, attendance_date, rendered_hours, overtime_hours FROM attendance WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $data = array();
        
        // Fetch each row individually
        while ($row = $result->fetch_assoc()) {
            // Convert time to 12-hour format with AM or PM
            $row['morning_timein'] = date("h:i A", strtotime($row['morning_timein']));
            $row['lunch_timeout'] = date("h:i A", strtotime($row['lunch_timeout']));
            $row['after_lunch_timein'] = date("h:i A", strtotime($row['after_lunch_timein']));
            $row['end_of_day_timeout'] = date("h:i A", strtotime($row['end_of_day_timeout']));

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

        // Return the data in JSON format
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        // Debugging statement
        error_log("Failed to execute the query: " . $stmt->error);

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
