<?php
if (isset($_POST['control_number'])) {
    $controlNumberToDelete = $_POST['control_number'];
    
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

    $conn->begin_transaction();

    try {
        // 1. Delete from file_names table
        $queryFileNames = "DELETE FROM interns_files WHERE control_number = ?";
        $stmtFileNames = $conn->prepare($queryFileNames);
        $stmtFileNames->bind_param('s', $controlNumberToDelete);
        $stmtFileNames->execute();
        $stmtFileNames->close();

        // 2. Delete from status table
        $queryDeleteStatus = "DELETE FROM status WHERE control_number = ?";
        $stmtDeleteStatus = $conn->prepare($queryDeleteStatus);
        $stmtDeleteStatus->bind_param('s', $controlNumberToDelete);  
        $stmtDeleteStatus->execute();
        $stmtDeleteStatus->close();

        // 3. Delete from application table
        $queryApplication = "DELETE FROM application WHERE control_number = ?";
        $stmtApplication = $conn->prepare($queryApplication);
        $stmtApplication->bind_param('s', $controlNumberToDelete);
        $stmtApplication->execute();
        $stmtApplication->close();

        // Commit the transaction
        $conn->commit();

        $response = array(
            'success' => true,
            'message' => 'Data deleted successfully!'
        );
    } catch (Exception $e) {
        $conn->rollback();

        $response = array(
            'success' => false,
            'message' => 'Failed to delete data from the database. Error: ' . $e->getMessage()
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
