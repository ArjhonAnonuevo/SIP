<?php
if (isset($_POST['control_number'])) {
    // Sanitize the input
    $controlNumberToDelete = filter_var($_POST['control_number'], FILTER_SANITIZE_STRING);
    
    include '../configuration/application_config.php';

    // Call the getDatabaseConfig function
    $config = getDatabaseConfig();
    
    $dbhost = $config['dbhost'];
    $dbuser = $config['dbuser'];
    $dbpass = $config['dbpass'];
    $dbname = $config['dbname'];

    // Create a new mysqli instance
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Step 1: Delete from interns_files table
        $queryDeleteFiles = "DELETE FROM interns_files WHERE control_number = ?";
        $stmtDeleteFiles = $conn->prepare($queryDeleteFiles);
        $stmtDeleteFiles->bind_param('s', $controlNumberToDelete);
        $stmtDeleteFiles->execute();
        $stmtDeleteFiles->close();

        // Step 2: Delete from application table
        $queryDeleteApplication = "DELETE FROM application WHERE control_number = ?";
        $stmtDeleteApplication = $conn->prepare($queryDeleteApplication);
        $stmtDeleteApplication->bind_param('s', $controlNumberToDelete);
        $stmtDeleteApplication->execute();
        $stmtDeleteApplication->close();

        // Step 3: Delete from status table
        $queryDeleteStatus = "DELETE FROM status WHERE control_number = ?";
        $stmtDeleteStatus = $conn->prepare($queryDeleteStatus);
        $stmtDeleteStatus->bind_param('s', $controlNumberToDelete);
        $stmtDeleteStatus->execute();
        $stmtDeleteStatus->close();

        // Commit the transaction
        $conn->commit();

        $response = array(
            'success' => true,
            'message' => 'Data deleted successfully!'
        );
    } catch (Exception $e) {
        $conn->rollback();

        // Check if the error message indicates a foreign key constraint failure
        if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
            // Construct a generic error message
            $errorMessage = "Cannot delete record due to a foreign key constraint.";
        } else {
            // Use the original error message
            $errorMessage = $e->getMessage();
        }

        $response = array(
            'success' => false,
            'message' => 'Failed to delete data from the database. Error: ' . $errorMessage
        );
    }

    // Close the connection
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);

} else {
    $response = array(
        'success' => false,
        'message' => 'Invalid request. Control number parameter is missing.'
    );

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
