<?php
// Include configuration file
include '../configuration/interns_config.php';

// Initialize response array
$response = [];

// Get database configuration
$config = getDatabaseConfig();
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Create database connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    $response['error'] = "Connection failed: " . $conn->connect_error;
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve data from POST
        $username_input = $_POST["username"];
        $fname_input = $_POST["fname"];
        $mname_input = $_POST["mname"];
        $lname_input = $_POST["lname"];
        $school = $_POST["school"];
        $course = $_POST["course"];
        $department = $_POST["department"];
        $hours_required = $_POST["hours-required"];
        $emergency_contact = $_POST["emergency-contact"];
        $password_input = $_POST["password"];
        $email = $_POST["email"];
        $confirmPassword = isset($_POST["confirm-password"]) ? $_POST["confirm-password"] : '';

        // Additional form data
        $gender = $_POST["gender"];
        $age = $_POST["age"];
        $birthday = $_POST["birthday"];
        $contact_number = $_POST["contact-number"];

        // Check password match
        if (strtolower($password_input) !== strtolower($confirmPassword)) {
            $response['error'] = "Passwords do not match!";
        } else {
            // Hash password
            $hashedPassword = password_hash($password_input, PASSWORD_DEFAULT);

            // Insert data into interns_profile table
            $stmt_profile = $conn->prepare("INSERT INTO interns_profile (name, mname, lname, gender, age, birthday, contact_number, school, course, department, hours_required, emergency_contact, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_profile) {
                $response['error'] = "Prepare failed: " . $conn->error;
            } else {
                $stmt_profile->bind_param("ssssisssssiss", $fname_input, $mname_input, $lname_input, $gender, $age, $birthday, $contact_number, $school, $course, $department, $hours_required, $emergency_contact, $email);

                // Execute profile insert
                $success_profile = $stmt_profile->execute();
                if (!$success_profile) {
                    $response['error'] = "Execute failed: " . $stmt_profile->error;
                } else {
                    // Get inserted profile ID
                    $profile_id = $stmt_profile->insert_id;

                    // Create image directory
                    $image_directory = "../reference/" . $username_input . "/";
                    if (!file_exists($image_directory)) {
                        mkdir($image_directory, 0777, true);
                    }

                    // Process captured images
                    $capturedImages = isset($_POST["capturedImages"]) ? json_decode($_POST["capturedImages"], true) : [];
                    foreach ($capturedImages as $index => $imageData) {
                        $image_filename = $image_directory . "image_" . $index . ".png";
                        file_put_contents($image_filename, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));
                    }

                    // Insert data into interns_account table
                    $stmt_account = $conn->prepare("INSERT INTO interns_account (profile_id, username, password, image_url) VALUES (?, ?, ?, ?)");
                    if (!$stmt_account) {
                        $response['error'] = "Prepare failed: " . $conn->error;
                    } else {
                        $image_url = $image_directory . "image_0.png"; 
                        $stmt_account->bind_param("isss", $profile_id, $username_input, $hashedPassword, $image_url);

                        // Execute account insert
                        $success_account = $stmt_account->execute();
                        if (!$success_account) {
                            $response['error'] = "Execute failed: " . $stmt_account->error;
                        } else {
                            // Update server status to "offline" for the newly registered user
                            $update_status_sql = "INSERT INTO server_status (status, interns_status, username) VALUES ('offline', 'Active', ?)";
                            $update_status_stmt = $conn->prepare($update_status_sql);
                            if (!$update_status_stmt) {
                                $response['error'] = "Prepare failed: " . $conn->error;
                            } else {
                                $update_status_stmt->bind_param("s", $username_input);

                                // Execute server status update
                                $success_update_status = $update_status_stmt->execute();
                                if (!$success_update_status) {
                                    $response['error'] = "Execute failed: " . $update_status_stmt->error;
                                } else {
                                    // Retrieve the current total count for the school from the database
                                    $stmt_get_total = $conn->prepare("SELECT Total FROM schooldata WHERE schoolName = ?");
                                    if (!$stmt_get_total) {
                                        $response['error'] = "Prepare failed: " . $conn->error;
                                    } else {
                                        $stmt_get_total->bind_param("s", $school);

                                        // Execute total count retrieval
                                        $stmt_get_total->execute();
                                        $stmt_get_total->store_result();
                                        $stmt_get_total->bind_result($total);
                                        $stmt_get_total->fetch();
                                        $stmt_get_total->close();

                                        // Increment the total count by 1
                                        $total += 1;

                                        // Insert or update data in schooldata table
                                        $stmt_school_data = $conn->prepare("INSERT INTO schooldata (schoolName, Total) VALUES (?, ?) ON DUPLICATE KEY UPDATE Total = Total + 1");
                                        if (!$stmt_school_data) {
                                            $response['error'] = "Prepare failed: " . $conn->error;
                                        } else {
                                            $stmt_school_data->bind_param("si", $school, $total);

                                            // Execute school data insert/update
                                            $success_school_data = $stmt_school_data->execute();
                                            if (!$success_school_data) {
                                                $response['error'] = "Execute failed: " . $stmt_school_data->error;
                                            } else {
                                                // Registration successful
                                                $response['success'] = true;
                                                $response['message'] = "Registration successful!";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Close database connection
$conn->close();

// Set content type to JSON
header('Content-Type: application/json');

// Output JSON response
echo json_encode($response);
?>
