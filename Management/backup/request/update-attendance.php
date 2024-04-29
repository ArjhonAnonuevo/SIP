<?php
include '../configuration/interns_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Connection failed: ' . $mysqli->connect_error)));
}

// Check if requestId is set in POST data
if(isset($_POST['requestId'])) {
    $requestId = $_POST['requestId'];
}

if(isset($_POST['attendanceData'])) {
    $attendanceData = $_POST['attendanceData'];

    // Iterate through the attendance data and update records in the database
    $attendanceIds = array();
    foreach($attendanceData as $attendance) {
        $attendanceId = $attendance['id'];
        if(!in_array($attendanceId, $attendanceIds)) {
            // Process the attendance data and perform update operation
            $morning_timein = date('H:i:s', strtotime($attendance['morning_timein']));
            $lunch_timeout = date('H:i:s', strtotime($attendance['lunch_timeout']));
            $after_lunch_timein = date('H:i:s', strtotime($attendance['after_lunch_timein']));
            $end_of_day_timeout = date('H:i:s', strtotime($attendance['end_of_day_timeout']));

            // Calculate the rendered hours
            $renderedHours = $attendance['rendered_hours'];
            $rhours = intval($renderedHours);
            $rminutes = ($renderedHours - $rhours) * 60; 

            // Calculate the overtime hours
            $overtimeHours = $attendance['overtime_hours'];
            $ohours = intval($overtimeHours);
            $ominutes = ($overtimeHours - $ohours) * 60; 

            // Fetch username associated with the attendance record
            $fetchUsernameQuery = "SELECT username FROM attendance WHERE id = '$attendanceId'";
            $usernameResult = $mysqli->query($fetchUsernameQuery);
            if (!$usernameResult) {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to fetch username. Error: ' . $mysqli->error));
                exit;
            }
            $row = $usernameResult->fetch_assoc();
            $username = $row['username'];

            // Update the attendance record
            $query = "UPDATE attendance SET 
                        morning_timein = '$morning_timein',
                        lunch_timeout = '$lunch_timeout',
                        after_lunch_timein = '$after_lunch_timein',
                        end_of_day_timeout = '$end_of_day_timeout',
                        attendance_date = '{$attendance['attendance_date']}',
                        rendered_hours = ADDTIME('00:00:00', '{$rhours}:{$rminutes}:00'), 
                        overtime_hours = ADDTIME('00:00:00', '{$ohours}:{$ominutes}:00')
                      WHERE id = '{$attendance['id']}'"; 

            $result = $mysqli->query($query);

            if (!$result) {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to update attendance records. Error: ' . $mysqli->error));
                exit; 
            }

            // Add the processed attendance ID to the array
            $attendanceIds[] = $attendanceId;
        }
    }
    $notificationId = time(); 
    $notificationMessage = "Attendance records have been updated.";
    $insertNotificationQuery = "INSERT INTO notifications (id, message, username) VALUES ('$notificationId', '$notificationMessage', '$username')";

    $insertNotificationResult = $mysqli->query($insertNotificationQuery);

    if (!$insertNotificationResult) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to insert notification. Error: ' . $mysqli->error));
        exit;
    }

    $deleteRequestQuery = "DELETE FROM attendance_request WHERE id = '$requestId'";
    $deleteRequestResult = $mysqli->query($deleteRequestQuery);

    if (!$deleteRequestResult) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to delete request. Error: ' . $mysqli->error));
        exit;
    }

    echo json_encode(array('status' => 'success'));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Attendance data not provided'));
}

$mysqli->close();
?>
