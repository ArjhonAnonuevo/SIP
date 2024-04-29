<?php
require_once "../configuration/interns_config.php";
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    $response = array(
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    );
    echo json_encode($response);
    exit();
}

$usernames = explode(",", $_POST['user']); 
// Handle file upload
if(isset($_FILES['attendanceFile'])) {
    $errors = array();
    $file_name = $_FILES['attendanceFile']['name'];
    $file_size = $_FILES['attendanceFile']['size'];
    $file_tmp = $_FILES['attendanceFile']['tmp_name'];
    $file_type = $_FILES['attendanceFile']['type'];
    $file_ext = strtolower(end(explode('.',$_FILES['attendanceFile']['name'])));

    $extensions = array("pdf");

    if(in_array($file_ext, $extensions) === false){
        $errors[] = "extension not allowed, please choose a PDF file.";
    }

    if($file_size > 2097152) {
        $errors[] = 'File size must be less than 2 MB';
    }

    if(empty($errors) == true) {
        move_uploaded_file($file_tmp, "../uploaded certificate/".$file_name);

        // Insert into database for each username
        foreach ($usernames as $user) {
            $file_path = "../uploaded certificate/".$file_name;
            $sql = "INSERT INTO uploaded_certificates (username, file_path) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user, $file_path);
            $stmt->execute();
            $stmt->close();
        }

        $response = array(
            'success' => true,
            'message' => 'Certificate uploaded successfully.'
        );
    } else {
        $response = array(
            'success' => false,
            'message' => implode(",", $errors)
        );
    }

    echo json_encode($response);
}

$conn->close();
?>
