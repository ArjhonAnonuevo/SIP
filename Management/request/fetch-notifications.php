<?php
include '../configuration/interns_config.php';

if(isset($_GET['username'])) {
    $username = $_GET['username'];
    $config = getDatabaseConfig();
    $mysqli = new mysqli($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $query = "SELECT * FROM notifications WHERE username = ?";
    
    // Prepare the SQL statement
    $statement = $mysqli->prepare($query);

    if ($statement) {
        // Bind parameters
        $statement->bind_param("s", $username);
        
        // Execute the statement
        $statement->execute();
        
        // Get the result
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $notifications = array();
            while ($row = $result->fetch_assoc()) {
                $notifications[] = array(
                    'message' => $row['message'],
                );
            }

            // Close the statement
            $statement->close();
            
            // Close the connection
            $mysqli->close();

            // Return success response with notifications
            echo json_encode(array('status' => 'success', 'notifications' => $notifications));
        } else {
            // Close the statement
            $statement->close();
            
            // Close the connection
            $mysqli->close();

            // Return success response with empty notifications array
            echo json_encode(array('status' => 'success', 'notifications' => array()));
        }
    } else {
        // Close the connection
        $mysqli->close();

        // Return error response if failed to prepare SQL statement
        echo json_encode(array('status' => 'error', 'message' => 'Failed to prepare SQL statement'));
    }
} else {
    // Return error response if username not provided
    echo json_encode(array('status' => 'error', 'message' => 'Username not provided'));
}
?>
