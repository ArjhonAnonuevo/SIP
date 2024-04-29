<?php

require_once '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

try {
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    $conn->set_charset("utf8");

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Pagination parameters
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $response = array();

    // Fetch rendered hours for each user
    $rendered_hours_query = "SELECT interns_account.username, 
                                    COALESCE(GROUP_CONCAT(COALESCE(attendance.rendered_hours, '00:00:00')), '00:00:00') AS rendered_hours
                            FROM interns_account
                            LEFT JOIN attendance ON interns_account.username = attendance.username
                            GROUP BY interns_account.username";

    $rendered_hours_stmt = $conn->prepare($rendered_hours_query);
    if (!$rendered_hours_stmt) {
        throw new Exception("Failed to prepare rendered hours statement: " . $conn->error);
    }
    $rendered_hours_stmt->execute();
    $rendered_hours_result = $rendered_hours_stmt->get_result();

    // Store rendered hours in an associative array
    $rendered_hours_by_user = array();
    while ($rendered_hours_row = $rendered_hours_result->fetch_assoc()) {
        $rendered_hours_by_user[$rendered_hours_row['username']] = $rendered_hours_row['rendered_hours'];
    }

    // Get the current timestamp
    $current_timestamp = time(); 

    // Count total rows for pagination
    $count_query = "SELECT COUNT(*) AS total_rows FROM interns_profile";
    $count_result = $conn->query($count_query);
    $total_rows = $count_result->fetch_assoc()['total_rows'];

    // Calculate total pages
    $total_pages = ceil($total_rows / $limit);

    // Fetch main data including interns' profile and status with pagination
    $main_query = "SELECT 
        interns_profile.id,
        interns_profile.name, 
        interns_profile.mname, 
        interns_profile.lname,  
        interns_profile.department, 
        interns_account.username, 
        server_status.status, 
        server_status.interns_status,
        interns_profile.hours_required
    FROM interns_profile 
    INNER JOIN interns_account ON interns_profile.id = interns_account.profile_id 
    INNER JOIN server_status ON interns_account.username = server_status.username
    LIMIT ? OFFSET ?";

    $main_stmt = $conn->prepare($main_query);
    if (!$main_stmt) {
        throw new Exception("Failed to prepare main query statement: " . $conn->error);
    }
    $main_stmt->bind_param("ii", $limit, $offset);
    $main_stmt->execute();
    $main_result = $main_stmt->get_result();

    // Process main data
    if ($main_result->num_rows > 0) {
        while ($row = $main_result->fetch_assoc()) {
            // Prepare intern's name
            $mname_initial = isset($row["mname"]) ? mb_substr($row["mname"], 0, 1, "UTF-8") : "";
            $mname_initial = mb_convert_case($mname_initial, MB_CASE_UPPER, "UTF-8");
            $lname = $row["lname"];
            $name = $row["name"] . " " . $mname_initial . ". " . $lname;

            // Get rendered hours for the current user
            $rendered_hours = isset($rendered_hours_by_user[$row['username']]) ? $rendered_hours_by_user[$row['username']] : '';

            // Calculate total rendered hours
           $total_rendered_hours = 0;
            if (!empty($rendered_hours)) {
                $rendered_hours_array = explode(',', $rendered_hours);
                foreach ($rendered_hours_array as $rendered_hour) {
                    list($hours, $minutes, $seconds) = explode(':', $rendered_hour);
                    $total_seconds = $hours * 3600 + $minutes * 60 + $seconds;
                    $total_rendered_hours += floor($total_seconds / 3600); // Round down to the nearest whole hour
                }
            }


            // Calculate remaining hours
            $hours_required = intval($row["hours_required"]);
            $remaining_hours = max(0, $hours_required - $total_rendered_hours);
            
              // Skip adding the row to the response if interns_status is "graduated"
            if ($row['interns_status'] === "graduated") {
                continue;
            }

            // Get previous status
            $previous_status_query = "SELECT interns_status FROM server_status WHERE username = ?";
            $previous_status_stmt = $conn->prepare($previous_status_query);
            if (!$previous_status_stmt) {
                throw new Exception("Failed to prepare previous status statement: " . $conn->error);
            }
            $previous_status_stmt->bind_param("s", $row['username']);
            $previous_status_stmt->execute();
            $previous_status_result = $previous_status_stmt->get_result();
            $previous_status_row = $previous_status_result->fetch_assoc();
            $previous_status = $previous_status_row['interns_status'];

            // Determine interns status
            $interns_status = ($remaining_hours <= 0) ? "graduated" : "active";

            if ($previous_status !== $interns_status) {
                $update_interns_status_query = "UPDATE server_status SET interns_status = ? WHERE username = ?";
                $update_interns_status_stmt = $conn->prepare($update_interns_status_query);
                if (!$update_interns_status_stmt) {
                    throw new Exception("Failed to prepare update interns status statement: " . $conn->error);
                }
                $update_interns_status_stmt->bind_param("ss", $interns_status, $row['username']);
                $update_interns_status_stmt->execute();
                
                if ($interns_status === "graduated") {
                    $two_weeks_from_now = date('Y-m-d H:i:s', strtotime("+2 weeks", $current_timestamp)); 
                    $account_expired_query = "UPDATE server_status SET account_expired = ? WHERE username = ?";
                    $account_expired_stmt = $conn->prepare($account_expired_query);
                    if (!$account_expired_stmt) {
                        throw new Exception("Failed to prepare account expired statement: " . $conn->error);
                    }
                    $account_expired_stmt->bind_param("ss", $two_weeks_from_now, $row['username']);
                    $account_expired_stmt->execute();
                }
            }

            $remaining_time = 0;
            if ($interns_status === "graduated" && !empty($row["graduation_timestamp"])) {
                $graduation_timestamp = strtotime($row["graduation_timestamp"]);
                $remaining_time = max(0, round(($graduation_timestamp - $current_timestamp) / 3600, 2));
            }

            $response[] = array(
                "name" => $name,
                "id" => $row["id"], 
                "department" => $row["department"],
                "username" => $row["username"],
                "status" => $row["status"],
                "interns_status" => $interns_status, 
                "total_rendered_hours" => $total_rendered_hours, 
                "remaining_hours" => $remaining_hours,
                "remaining_time" => $remaining_time
            );
        }
    }    
    else {
        $response["message"] = "0 results";
    }

    $conn->close();

    // Encode response array to JSON and add total pages information
    $response['total_pages'] = $total_pages;
    echo json_encode($response, JSON_UNESCAPED_UNICODE); 
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
