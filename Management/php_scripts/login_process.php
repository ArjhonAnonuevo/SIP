<?php
session_start();
require_once '../configuration/interns_config.php';
$admin_config = include '../configuration/admin_config.php';

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
    $input_username = $_POST["username"];
    $stmt = $conn->prepare("SELECT username, password FROM interns_account WHERE username = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashedPassword);
        $stmt->fetch();

        if (password_verify($_POST["password"], $hashedPassword)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $input_username;
            $_SESSION["hashed_password"] = password_hash($_POST["password"], PASSWORD_DEFAULT);


            $update_sql = "UPDATE server_status SET status = 'online' WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt === false) {
                die("Error preparing update statement: " . $conn->error);
            }
            $update_stmt->bind_param("s", $input_username);
            if ($update_stmt->execute() === FALSE) {
                echo "Error updating server status: " . $conn->error;
            } else {
                echo "Server status updated successfully";
            }
            $update_stmt->close();

            // Set timezone to Philippine timezone
            date_default_timezone_set('Asia/Manila');
            
            $action = 'Login';
            $log = "User " . $input_username . " logged in.";
            $role = "Intern"; 

            // Get current date and time in Philippine timezone
            $audit_timestamp = date('Y-m-d H:i:s');

            $insertAuditQuery = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insertAuditQuery);
            if ($insert_stmt === false) {
                die("Error preparing insert statement: " . $conn->error);
            }
            $insert_stmt->bind_param('ssss', $action, $log, $audit_timestamp, $role);
            if ($insert_stmt->execute() === FALSE) {
                echo "Error inserting audit record: " . $insert_stmt->error;
            } else {
                echo "Audit record inserted successfully";
            }
            $insert_stmt->close();

            header("Location: ../interns_dashboard.html");
            exit();
        } else {
            echo '<script>alert("Incorrect password for username: ' . $input_username . '"); window.location.href = "/index.html";</script>';
            exit();
        }
    } else {
        $admin_accounts = $admin_config['admin_accounts'];
        if (array_key_exists($input_username, $admin_accounts)) {
            $dashboard_type = $admin_accounts[$input_username]['dashboard_type'];
            $dashboard_url = $admin_accounts[$input_username]['dashboard_url'];
            $admin_password = $admin_accounts[$input_username]['admin_password'];

            if ($_POST["password"] == $admin_password) {
                $_SESSION["username"] = $input_username; 
                header("Location: ../$dashboard_url");
                exit();
            } else {
                echo '<script>alert("Incorrect password for username: ' . $input_username . '"); window.location.href = "/index.html";</script>';
                exit();
            }
        } else {
            echo '<script>alert("Invalid username: ' . $input_username . '"); window.location.href = "index.html";</script>';
            exit();
        }
    }
}

$conn->close();
?>
