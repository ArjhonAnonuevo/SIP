
<?php
session_start(); 
require_once __DIR__ . '/../vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Access the SECRET_KEY
$secretKey = $_ENV['SECRET_KEY'];

// Check if the username is set in the session
if (isset($_SESSION['username'])) {
    $sessionUsername = $_SESSION['username'];

    // Check if the username in the session matches the username in QR scanning
    if (isset($_GET['data'])) {
        $encodedData = $_GET['data'];
        // Reverse URL-safe encoding
        $decodedData = str_replace(['-', '_'], ['+', '/'], $encodedData);
        // Split the IV and encrypted data
        list($encodedIV, $encodedEncryptedData) = explode('|', $decodedData);
        // Base64 decode the IV and encrypted data
        $iv = base64_decode($encodedIV);
        $encryptedData = base64_decode($encodedEncryptedData);
        // Decrypt the data using the IV and secret key
        $decryptedData = openssl_decrypt($encryptedData, 'AES-256-CBC', $secretKey, OPENSSL_RAW_DATA, $iv);

        if ($decryptedData === $sessionUsername) {
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

            date_default_timezone_set('Asia/Manila');
            $current_time = date('H:i:s');
            $current_date = date('Y-m-d');
            $current_date_display = date('F j, Y');
            $username = $decryptedData;

            // Check if the user has already submitted attendance for the current date
            $sql_check = "SELECT morning_timein, lunch_timeout, after_lunch_timein, end_of_day_timeout, rendered_hours FROM attendance WHERE attendance_date = ? AND username = ?";
            $stmt_check = $mysqli->prepare($sql_check);
            $stmt_check->bind_param("ss", $current_date, $username);
            $stmt_check->execute();
            $stmt_check->store_result();
            $stmt_check->bind_result($morning_timein, $lunch_timeout, $lunch_timein, $end_out, $rendered_hours);
            $stmt_check->fetch();


            if ($stmt_check->num_rows > 0) {
                if ($end_out != "00:00:00") {
                    $response['status'] = 'error';
                    $response['message'] = 'You have already scanned attendance for this day.';
                } 
            }
            if ($stmt_check->num_rows > 0) {
                if ($morning_timein != null && $lunch_timeout == "00:00:00") {
                    // Update the record with lunch time out
                    $sql_update_lunchout = "UPDATE attendance SET lunch_timeout = ? WHERE attendance_date = ? AND username = ?";
                    $lunch_timeout_stmt = $mysqli->prepare($sql_update_lunchout);
                    $lunch_timeout_stmt->bind_param("sss", $current_time, $current_date, $username);
                    $lunch_timeout_stmt->execute();
                    if ($lunch_timeout_stmt->affected_rows > 0) {
                        $response['status'] = 'success';
                        $response['message'] = 'Lunch Time Out recorded successfully for ' . $current_date_display;
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Error updating Lunch Time Out: ' . $lunch_timeout_stmt->error;
                    }
                    $lunch_timeout_stmt->close();
                    
                } elseif ($lunch_timeout != null && $lunch_timein == "00:00:00") {
                    $sql_update_lunchin = "UPDATE attendance SET after_lunch_timein = ? WHERE attendance_date = ? AND username = ?";
                    $lunch_timein_stmt = $mysqli->prepare($sql_update_lunchin);
                    $lunch_timein_stmt->bind_param("sss", $current_time, $current_date, $username);
                    $lunch_timein_stmt->execute();
                    if ($lunch_timein_stmt->affected_rows > 0) {
                        $response['status'] = 'success';
                        $response['message'] = 'Lunch Time In recorded successfully for ' . $current_date_display;
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Error updating Lunch Time In: ' . $lunch_timein_stmt->error;
                    }
                    $lunch_timein_stmt->close();
                }elseif ($lunch_timein != null && $end_out == "00:00:00") {
                    // Calculate the total hours worked
                    $morning_timein_timestamp = strtotime($morning_timein);
                    $lunch_timeout_timestamp = strtotime($lunch_timeout);
                    $lunch_timein_timestamp = strtotime($lunch_timein);
                    $end_of_day_timeout_timestamp = strtotime($current_time);
                    $total_seconds_worked = $end_of_day_timeout_timestamp - $morning_timein_timestamp - ($lunch_timein_timestamp - $lunch_timeout_timestamp);
                
                    // Calculate rendered hours and overtime hours
                    $rendered_hours_seconds = min($total_seconds_worked, 28800); // 8 hours in seconds
                    $overtime_hours_seconds = max(0, $total_seconds_worked - $rendered_hours_seconds);
                
                    // Retrieve the last total from the most recent entry in the database
                    $sqlSelectLastTotal = "SELECT total FROM attendance WHERE username = ? ORDER BY attendance_date DESC LIMIT 1";
                    $selectLastTotalStmt = $mysqli->prepare($sqlSelectLastTotal);
                    $selectLastTotalStmt->bind_param("s", $username);
                    $selectLastTotalStmt->execute();
                    $selectLastTotalStmt->bind_result($last_total);
                    $selectLastTotalStmt->fetch();
                    $selectLastTotalStmt->close();
                
                    // If no last total exists, treat it as if the user has no prior entries
                    // and set the last total to null for accurate calculation of total hours
                    if ($last_total === null) {
                        $last_total_seconds = 0;
                    } else {
                        $last_total_seconds = strtotime($last_total);
                    }
                
                    // Calculate the new total by adding the last total to the rendered hours
                    $total_seconds_total = $last_total_seconds + $rendered_hours_seconds;
                    $new_total = gmdate('H:i:s', $total_seconds_total);
                
                    // Format the hours as H:i:s
                    $rendered_hours = gmdate('H:i:s', $rendered_hours_seconds);
                    $overtime_hours = gmdate('H:i:s', $overtime_hours_seconds);
                
                    // Update the database with the calculated rendered hours, overtime hours, and new total
                    $sqlUpdateEndOfDay = "UPDATE attendance SET end_of_day_timeout = ?, rendered_hours = ?, overtime_hours = ?, total = ? WHERE attendance_date = ? AND username = ?";
                    $endOfDayStmt = $mysqli->prepare($sqlUpdateEndOfDay);
                    $endOfDayStmt->bind_param("ssdsss", $current_time, $rendered_hours, $overtime_hours, $new_total, $current_date, $username);
                    $endOfDayStmt->execute();
                
                    if ($endOfDayStmt->affected_rows > 0) {
                        $response['status'] = 'success';
                        $response['message'] = 'End of Day Time out recorded successfully for ' . $current_date_display;
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Error updating End of Day: ' . $endOfDayStmt->error;
                    }
                    $endOfDayStmt->close();
                }
                
            } else {
                // Insert the record with morning time in
                $sql = "INSERT INTO attendance (attendance_date, username, morning_timein) VALUES (?, ?, ?)";
                $stmt_insert = $mysqli->prepare($sql);
                $stmt_insert->bind_param("sss", $current_date, $username, $current_time);
                $stmt_insert->execute();
                if ($stmt_insert->affected_rows > 0) {
                    $response['status'] = 'success';
                    $response['message'] = 'Morning Time In recorded successfully for ' . $current_date_display;
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error inserting Morning Time In: ' . $stmt_insert->error;
                }
                $stmt_insert->close();
            }

            $stmt_check->close();
            $mysqli->close(); // Close the database connection
        } else {
            $response['status'] = 'error';
            $response['message'] = 'QR does not match the with this user.';
        }
    } else {
        echo "No encrypted data found in the URL parameter.";
    }
} else {
    echo "Username not set in the session.";
}
echo json_encode($response);
?>