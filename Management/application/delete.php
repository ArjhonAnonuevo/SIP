<?php
if (isset($_POST['id'])) {
    $idToDelete = $_POST['id'];
    
    // Assuming 'control_number' is a separate field in your form
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

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // 1. Delete from status table where control_number is equal to the provided value
        $queryDeleteStatus = "DELETE FROM status WHERE control_number = ?";
        $stmtDeleteStatus = $conn->prepare($queryDeleteStatus);
        $stmtDeleteStatus->bind_param('s', $controlNumberToDelete);  // Use the separate variable here
        $stmtDeleteStatus->execute();
        $stmtDeleteStatus->close();

        // 2. Delete from file_names table
        $queryFileNames = "DELETE FROM file_names WHERE file_id IN (SELECT file_id FROM files WHERE id = ?)";
        $stmtFileNames = $conn->prepare($queryFileNames);
        $stmtFileNames->bind_param('i', $idToDelete);
        $stmtFileNames->execute();
        $stmtFileNames->close();

        // 3. Delete from files table
        $queryFiles = "DELETE FROM files WHERE id = ?";
        $stmtFiles = $conn->prepare($queryFiles);
        $stmtFiles->bind_param('i', $idToDelete);
        $stmtFiles->execute();
        $stmtFiles->close();

        // 4. Delete from application table
        $queryApplication = "DELETE FROM application WHERE id = ?";
        $stmtApplication = $conn->prepare($queryApplication);
        $stmtApplication->bind_param('i', $idToDelete);
        $stmtApplication->execute();
        $stmtApplication->close();

        // Commit the transaction
        $conn->commit();

        $response = array(
            'success' => true,
            'message' => 'Data deleted successfully!'
        );
    } catch (Exception $e) {
        // Rollback the transaction on error
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
        'message' => 'Invalid request. ID parameter is missing.'
    );

    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
