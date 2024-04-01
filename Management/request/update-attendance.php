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

// Check if attendance data is provided in the POST request
if(isset($_POST['attendanceData'])) {
    // Get the attendance data from the POST request
    $attendanceData = $_POST['attendanceData'];

    // Iterate through the attendance data and update records in the database
    foreach($attendanceData as $attendance) {
        // Convert time inputs with AM/PM to 24-hour format
        $morning_timein = date('H:i:s', strtotime($attendance['morning_timein']));
        $lunch_timeout = date('H:i:s', strtotime($attendance['lunch_timeout']));
        $after_lunch_timein = date('H:i:s', strtotime($attendance['after_lunch_timein']));
        $end_of_day_timeout = date('H:i:s', strtotime($attendance['end_of_day_timeout']));

        // Calculate the rendered hours
        $renderedHours = $attendance['rendered_hours'];
        $rhours = intval($renderedHours); // Extract the integer part of the hours
        $rminutes = ($renderedHours - $rhours) * 60; // Calculate the minutes

        // Calculate the overtime hours
        $overtimeHours = $attendance['overtime_hours'];
        $ohours = intval($overtimeHours); // Extract the integer part of the hours
        $ominutes = ($overtimeHours - $ohours) * 60; // Calculate the minutes

        // Construct the SQL query to update the attendance record
        $query = "UPDATE attendance SET 
                    morning_timein = '$morning_timein',
                    lunch_timeout = '$lunch_timeout',
                    after_lunch_timein = '$after_lunch_timein',
                    end_of_day_timeout = '$end_of_day_timeout',
                    attendance_date = '{$attendance['attendance_date']}',
                    rendered_hours = ADDTIME('00:00:00', '{$rhours}:{$rminutes}:00'), 
                    overtime_hours = ADDTIME('00:00:00', '{$ohours}:{$ominutes}:00')
                  WHERE id = '{$attendance['id']}'"; 

        // Execute the SQL query
        $result = $mysqli->query($query);

        if (!$result) {
            echo json_encode(array('status' => 'error', 'message' => 'Failed to update attendance records. Error: ' . $mysqli->error));
            exit; // Terminate the script execution if update fails
        }
    }

    // If all updates were successful, return success status
    echo json_encode(array('status' => 'success'));
} else {
    // If attendance data is not provided, return an error message
    echo json_encode(array('status' => 'error', 'message' => 'Attendance data not provided'));
}

// Close the database connection
$mysqli->close();
?>
