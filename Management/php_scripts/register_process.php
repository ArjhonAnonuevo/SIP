<?php
  include '../configuration/interns_config.php';

            // Call the getDatabaseConfig function
            $config = getDatabaseConfig();
            
            $dbhost = $config['dbhost'];
            $dbuser = $config['dbuser'];
            $dbpass = $config['dbpass'];
            $dbname = $config['dbname'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST["username"];
    $fname_input = $_POST["fname"];
    $mname_input = $_POST["mname"];
    $lname_input = $_POST["lname"];

    // Check if the username exists in the interns table
    $stmt_check_username = $conn->prepare("SELECT interns_username, fname, mname, lname FROM interns WHERE interns_username = ? AND fname = ? AND mname = ? AND lname = ?");
    if (!$stmt_check_username) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_check_username->bind_param("ssss", $username_input, $fname_input, $mname_input, $lname_input);

    $stmt_check_username->execute();
    $stmt_check_username->store_result();

    if ($stmt_check_username->num_rows > 0) {
        $name = $_POST["fname"];
        $mname = $_POST["mname"];
        $lname = $_POST["lname"];
        $gender = $_POST["gender"];
        $age = $_POST["age"];
        $birthday = $_POST["birthday"];
        $contact_number = $_POST["contact-number"];
        $school = $_POST["school"];
        $course = $_POST["course"];
        $department = $_POST["department"];
        $hours_required = $_POST["hours-required"];
        $emergency_contact = $_POST["emergency-contact"];
        $password_input = $_POST["password"];
        $confirmPassword = $_POST["confirm-password"];

        if ($password_input !== $confirmPassword) {
            echo '<script>alert("Passwords do not match!");</script>';
        } else {
            $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);

            $stmt_profile = $conn->prepare("INSERT INTO interns_profile (name, mname, lname, gender, age, birthday, contact_number, school, course, department, hours_required, emergency_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_profile) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt_profile->bind_param("ssssisssssis", $name, $mname, $lname, $gender, $age, $birthday, $contact_number, $school, $course, $department, $hours_required, $emergency_contact);

            $success_profile = $stmt_profile->execute();
            if (!$success_profile) {
                die("Execute failed: " . $stmt_profile->error);
            }

            $profile_id = $stmt_profile->insert_id; // Get the inserted profile's ID

            $stmt_profile->close(); // Close the profile statement

            $stmt_account = $conn->prepare("INSERT INTO interns_account (profile_id, username, password) VALUES (?, ?, ?)");
            if (!$stmt_account) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt_account->bind_param("iss", $profile_id, $username_input, $hashedPassword);

            $success_account = $stmt_account->execute(); 
            if (!$success_account) {
                die("Execute failed: " . $stmt_account->error);
            }

            $stmt_account->close(); 

            if ($success_profile && $success_account) {
                echo '<script>alert("Registration successful!"); window.location.href = "index.html";</script>';
            } else {
                echo '<script>alert("Error: ' . $stmt_account->error . '");</script>';
            }
        }
    } else {
echo '<script>alert("Name or Username is not found. Input values: Username=' . $username_input . ', First Name=' . $fname_input . ', Middle Name=' . $mname_input . ', Last Name=' . $lname_input . ', Number of Rows=' . $num_rows . '"); window.location.href = "/internsregister.html";</script>';
    }

    $stmt_check_username->close();
}

$conn->close();
?>
