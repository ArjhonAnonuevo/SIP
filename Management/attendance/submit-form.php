<?php
include '../configuration/interns_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

$response = array(); 

if ($mysqli->connect_error) {
    $response['success'] = false;
    $response['message'] = "Connection failed: " . $mysqli->connect_error;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_FILES["attendanceFile"]) && isset($_POST['user'])) {
            $file = $_FILES["attendanceFile"];
            $username = $_POST['user'];

            // File details
            $fileName = basename($file["name"]); 
            $fileTmpName = $file["tmp_name"];
            $fileSize = $file["size"];
            $fileError = $file["error"];
            
            // File type validation
            $allowedTypes = array('application/pdf'); 
            if (!in_array($file['type'], $allowedTypes)) {
                $response['success'] = false;
                $response['message'] = "Invalid file type. Only PDF files are allowed.";
            } else if ($fileError === 0) {
                $uploadDir = "../attendance-uploads/";
                
                $uniqueFilename = uniqid('', true) . '_' . $fileName;
                $fileDestination = $uploadDir . $uniqueFilename;
                
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $sql = "INSERT INTO attendance_submissions (filename, username, file_path, created_at) VALUES (?, ?, ?, CURDATE())";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("sss", $fileName, $username, $fileDestination);

                        $action = "Upload";
                        $log = "The Intern with $username user ID uploads Attendance Files";
                        $role = "Intern";

                        $query_audits = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, NOW(), ?)";
                            $stmt_audits = $mysqli->prepare($query_audits);
                            $stmt_audits->bind_param("sss", $action, $log, $role);
                            $stmt_audits->execute();                        

                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "File uploaded successfully.";
                    } else {
                        unlink($fileDestination); 
                        $response['success'] = false;
                        $response['message'] = "Error inserting data into database.";
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = "Error moving uploaded file.";
                }
            } else {
                $response['success'] = false;
                $response['message'] = "Error uploading file.";
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Missing file or username.";
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid request.";
    }
}

// Encode response array to JSON format
echo json_encode($response);
?>
