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
                // Upload directory
                $uploadDir = "../attendance-uploads/";
                
                // Generate unique filename to prevent overwriting
                $uniqueFilename = uniqid('', true) . '_' . $fileName;
                $fileDestination = $uploadDir . $uniqueFilename;
                
                // Move uploaded file to destination
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Insert data into attendance_submissions table with current date
                    $sql = "INSERT INTO attendance_submissions (filename, username, file_path, created_at) VALUES (?, ?, ?, CURDATE())";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param("sss", $fileName, $username, $fileDestination);
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "File uploaded successfully.";
                    } else {
                        // Rollback file upload if database insertion fails
                        unlink($fileDestination); // Delete uploaded file
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
