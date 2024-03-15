<?php
// Include the configuration file to get the database credentials
include '../configuration/interns_config.php';

// Start the session
session_start();

// Check if the user is logged in and their username is stored in the session
if (!isset($_SESSION['username'])) {
    // Redirect the user to the login page or handle unauthorized access
    exit('Unauthorized access');
}

// Get the username from the session
$username = $_SESSION['username'];

// Call the getDatabaseConfig function to retrieve database credentials
$config = getDatabaseConfig();

// Extract database credentials
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Create a new MySQLi object for database connection
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the month parameter is set in the request
if(isset($_POST['month'])) {
    // Sanitize and validate the month value
    $selectedMonth = intval($_POST['month']); // Convert to integer to prevent SQL injection
    if($selectedMonth >= 1 && $selectedMonth <= 12) {
        // Prepare the SQL query
        $sql = "SELECT id, type, date, time, status, user_id FROM acomplisment_report WHERE MONTH(date) = ? AND user_id = ?";
        
        // Prepare and bind the statement
        if($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("is", $selectedMonth, $username);
            
            // Execute the statement
            if($stmt->execute()) {
                // Bind the result variables
                $stmt->bind_result($id, $type, $date, $time, $status, $user_id);
                
                // Fetch the results
                $results = array();
                while($stmt->fetch()) {
                    // Store each row in an array
                    $row = array(
                        'id' => $id,
                        'type' => $type,
                        'date' => $date,
                        'time' => $time,
                        'status' => $status,
                        'user_id' => $user_id // Include the user_id in the row
                    );
                    // Append the row to the results array
                    $results[] = $row;
                }
                
                // Close the statement
                $stmt->close();
                
                // Return the results as JSON
                echo json_encode($results);
            } else {
                echo json_encode(array('error' => 'Failed to execute the query.'));
            }
        } else {
            echo json_encode(array('error' => 'Failed to prepare the statement.'));
        }
    } else {
        echo json_encode(array('error' => 'Invalid month value.'));
    }
} else {
    echo json_encode(array('error' => 'Month parameter is not set.'));
}

// Close the database connection
$mysqli->close();
?>
