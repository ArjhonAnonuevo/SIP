<?php
session_start();
include '../configuration/application_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    error_log("Database connection successful");
}

function isGmail($email)
{
    $email = trim($email);
    return mb_substr($email, -10) === '@gmail.com';
}

function generateApplicantId($prefix = "INTRN")
{
    global $conn;
    $year = date("Y");
    $likePattern = "$prefix-$year%";
    $sql = "SELECT COUNT(*) as count FROM interns_files WHERE control_number LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;

    $paddedSequentialNumber = str_pad($count, 4, '0', STR_PAD_LEFT);
    $applicantId = "$prefix-$year-$paddedSequentialNumber";
    return $applicantId;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate a unique control number
    $randomControlNumber = generateApplicantId();

    // Assign form values to session variables
    $_SESSION["primary_email"] = $_POST["primary_email"];
    $_SESSION["given_name"] = $_POST["given_name"];
    $_SESSION["family_name"] = $_POST["family_name"];
    $_SESSION["random_control_number"] = $randomControlNumber;

    // Directory where files will be stored
    $directory = '../applicants-files/';
    if (!file_exists($directory)) {
        if (!mkdir($directory, 0777, true)) {
            die('Failed to create directory: ' . $directory);
        }
    }

    // Move uploaded files to the directory and construct URLs
    $fileUrls = [];
    foreach ($_FILES as $fileKey => $fileInfo) {
        $fileExtension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        $fileUrl = $directory . $randomControlNumber . "_" . $fileKey . "." . $fileExtension;
        if (move_uploaded_file($fileInfo['tmp_name'], $fileUrl)) {
            $fileUrls[$fileKey] = $fileUrl;
        } else {
            die('Failed to move uploaded file: ' . $fileKey);
        }
    }
    // Insert application data into the database
    $application_date = date('Y-m-d');
    $sql = "INSERT INTO application (control_number, given_name, middle_name, family_name, address, place_birth, birthday, age, gender, contact, landline, primary_email, secondary_email, application_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssss",
        $randomControlNumber,
        $_POST["given_name"],
        $_POST["middle_name"],
        $_POST['family_name'],
        $_POST["address"],
        $_POST["place_birth"],
        $_POST["birthday"],
        $_POST["age"],
        $_POST["gender"],
        $_POST["contact"],
        $_POST["landline"],
        $_POST["primary_email"],
        $_POST["secondary_email"],
        $application_date
    );
    if (!$stmt->execute()) {
        die('Error inserting into application table: ' . $stmt->error);
    }

    // Insert file URLs into the database
    $sql_files = "INSERT INTO interns_files (school_name, regi_name, schedule_name, form1_name, form2_name, form3_name, control_number,form4_name) VALUES (?, ?, ?, ?, ?, ?, ?,?)";
    $stmt_files = $conn->prepare($sql_files);
    $stmt_files->bind_param("ssssssss", $fileUrls['school_id'], $fileUrls['regi'], $fileUrls['schedule'], $fileUrls['form1'], $fileUrls['form2'], $fileUrls['form3'], $randomControlNumber, $fileUrls['form4']);
    if (!$stmt_files->execute()) {
        die('Error inserting into files table: ' . $stmt_files->error);
    }

    // Insert initial status as "On Process" in the status table
    $sql_initial_status = "INSERT INTO status (control_number, status_name) VALUES (?, 'On Process')";
    $stmt_initial_status = $conn->prepare($sql_initial_status);
    $stmt_initial_status->bind_param("s", $randomControlNumber);
    if (!$stmt_initial_status->execute()) {
        die('Error inserting initial status in the status table: ' . $stmt_initial_status->error);
    }

    // Close statements
    $stmt->close();
    $stmt_files->close();
    $stmt_initial_status->close();

    // Send response
    $response = [
        'status' => 'success',
        'message' => 'Form submitted successfully!',
    ];
    echo json_encode($response);
    exit();
}

$conn->close();
?>
