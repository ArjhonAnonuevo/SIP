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

function isGmail($email) {
    $email = trim($email);
    return mb_substr($email, -10) === '@gmail.com';
}

function generateControlNumber($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $controlNumber = '';
    for ($i = 0; $i < $length; $i++) {
        $controlNumber .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $controlNumber;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION["primary_email"] = $_POST["primary_email"];
    $_SESSION["given_name"] = $_POST["given_name"];  
    $_SESSION["family_name"] = $_POST["family_name"];

  

    // Move the file contents retrieval here
    $school_id = file_get_contents($_FILES["school_id"]["tmp_name"]);
    $regi = file_get_contents($_FILES["regi"]["tmp_name"]);
    $schedule = file_get_contents($_FILES["schedule"]["tmp_name"]);
    $form1 = file_get_contents($_FILES["form1"]["tmp_name"]);
    $form2 = file_get_contents($_FILES["form2"]["tmp_name"]);
    $form3 = file_get_contents($_FILES["form3"]["tmp_name"]);
    $form4 = file_get_contents($_FILES["form4"]["tmp_name"]);


    $randomControlNumber = generateControlNumber();
    date_default_timezone_set('Asia/Manila');
    $application_date = date('Y-m-d'); 

    $sql = "INSERT INTO application (control_number, given_name, middle_name, family_name, address, place_birth, birthday, age, gender, contact, landline, primary_email, secondary_email, application_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Failed to prepare statement: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param(
        "ssssssssssssss",
        $randomControlNumber,
        $_SESSION["given_name"],
        $_POST["middle_name"],
        $_SESSION['family_name'],
        $_POST["address"],
        $_POST["place_birth"],
        $_POST["birthday"],
        $_POST["age"],
        $_POST["gender"],
        $_POST["contact"],
        $_POST["landline"],
        $_SESSION['primary_email'],
        $_POST["secondary_email"],
        $application_date
    );

    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        $sql_files = "INSERT INTO files (id, school_id, regi, schedule, form1, form2, form3, form4) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_files = $conn->prepare($sql_files);
        $stmt_files->bind_param(
            "isssssss",
            $last_id,
            $school_id,
            $regi,
            $schedule,
            $form1,
            $form2,
            $form3,
            $form4
        );

        if ($stmt_files->execute()) {
            $file_id = $conn->insert_id;
            $school_name = $_FILES["school_id"]["name"];
            $regi_name = $_FILES["regi"]["name"];
            $schedule_name = $_FILES["schedule"]["name"];
            $form1_name = $_FILES["form1"]["name"];
            $form2_name = $_FILES["form2"]["name"];
            $form3_name = $_FILES["form3"]["name"];
            $form4_name = $_FILES["form4"]["name"];

            $sql_file_names = "INSERT INTO file_names (file_id, school_name, regi_name, schedule_name, form1_name, form2_name, form3_name, form4_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_file_names = $conn->prepare($sql_file_names);
            $stmt_file_names->bind_param(
                "isssssss",
                $file_id,
                $school_name,
                $regi_name,
                $schedule_name,
                $form1_name,
                $form2_name,
                $form3_name,
                $form4_name
            );
            if ($stmt_file_names->execute()) {
                // Insert initial status as "On Process" in the status table
                $sql_initial_status = "INSERT INTO status (control_number, status_name) VALUES (?, 'On Process')";
                $stmt_initial_status = $conn->prepare($sql_initial_status);
                $stmt_initial_status->bind_param("s", $randomControlNumber);
    
                if ($stmt_initial_status->execute()) {
                    $response = array(
                        'status' => 'success',
                        'message' => 'Form submitted successfully!',
                    );
                } else {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Error inserting initial status in the status table: ' . $stmt_initial_status->error
                    );
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Error inserting into file_names table: ' . $stmt_file_names->error
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Error inserting into files table: ' . $stmt_files->error
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Error inserting in the database: ' . $stmt->error
        );
    }
    $stmt->close();
    $stmt_files->close();
    $stmt_file_names->close();
    $stmt_initial_status->close();
    echo json_encode($response);
    exit();
}
$conn->close();
?>
