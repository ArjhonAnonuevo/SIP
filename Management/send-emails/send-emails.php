<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../configuration/application_config.php';

$config = getDatabaseConfig();

$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

$smtpUsername = 'sip-management@sec-internsmanagement.elementfx.com';
$smtpPassword = 'sip-program';

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$auditDbhost = 'localhost';
$auditDbuser = 'jplypjcx_sip_admin';
$auditDbpass = 'sip@dm1n';
$auditDbname = 'jplypjcx_interns_management';

$auditMysqli = new mysqli($auditDbhost, $auditDbuser, $auditDbpass, $auditDbname);

if ($auditMysqli->connect_error) {
    die("Audit database connection failed: " . $auditMysqli->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = $_POST['primaryEmail'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
        
    $selectedInterviewValue = $_POST['selectedInterviewValue'];
    $controlNumber = $_POST['controlNumber'];
    $html_template_file = '../send-emails/content.html';
    
    
    $attachments = $_FILES['attachments'];

    $to = htmlspecialchars($to);
    $subject = htmlspecialchars($subject);
    switch ($selectedInterviewValue) {
        case 'interview1':
            $status = 'Pending';
            $app_status = "Pending";
            break;
        case 'interview2':
            $status = 'Level 2 Interview';
            $app_status = "Level 2 Interview";
            break;
        case 'interview3':
            $status = 'Accepted';
            $app_status = "Accepted";
            break;
        case 'rejected':
            $status = 'Rejected';
            $app_status = "Rejected";
            break;
        default:
            $status = 'Unknown';
            break;
    }

    $stmt = $mysqli->prepare("UPDATE status SET status_name = ? WHERE control_number = ?");
    $stmt->bind_param('ss', $status, $controlNumber);
    if (!$stmt->execute()) {
        echo "Database error: " . $stmt->error;
    }
    $stmt->close();
    $updateCount = true;

    if ($selectedInterviewValue === 'interview1') {
        $app_status = "Pending";

        $result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");
        if ($result && $result->num_rows == 0) {
            $insertQuery = "INSERT INTO application_status_counts (status_name, count, last_updated_date) VALUES ('$app_status', 1, NOW())";

            if (!$mysqli->query($insertQuery)) {
                echo "Error inserting application status count: " . $mysqli->error;
            }
        } else {
            $updateQuery = "UPDATE application_status_counts SET count = count + 1 WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())";

            if (!$mysqli->query($updateQuery)) {
                echo "Error updating application status count: " . $mysqli->error;
            }
        }
    } elseif ($selectedInterviewValue === 'interview3') {
        $app_status = "Accepted";
        $pending_status = "Pending";
        $pending_result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($pending_result && $pending_result->num_rows > 0) {
            $decrementQuery = "UPDATE application_status_counts SET count = count - 1 WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW()) LIMIT 1";

            if (!$mysqli->query($decrementQuery)) {
                echo "Error decrementing application status count: " . $mysqli->error;
            }
        }

        $result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($result && $result->num_rows == 0) {
            $insertQuery = "INSERT INTO application_status_counts (status_name, count, last_updated_date) VALUES ('$app_status', 1, NOW())";

            if (!$mysqli->query($insertQuery)) {
                echo "Error inserting application status count: " . $mysqli->error;
            }
        } else {
            $updateQuery = "UPDATE application_status_counts SET count = count + 1 WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())";

            if (!$mysqli->query($updateQuery)) {
                echo "Error updating application status count: " . $mysqli->error;
            }
        }
    } elseif ($selectedInterviewValue === 'rejected') {
        $controlNumberToDelete = $_POST['controlNumber'];

        $pending_status = "Pending";
        $pending_result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($pending_result && $pending_result->num_rows > 0) {
            $decrementQuery = "UPDATE application_status_counts SET count = count - 1 WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW()) LIMIT 1";

            if (!$mysqli->query($decrementQuery)) {
                echo "Error decrementing application status count: " . $mysqli->error;
            }
        }

        $rejected_status = "Rejected";
        $rejected_result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$rejected_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($rejected_result && $rejected_result->num_rows == 0) {
            $insertRejectedQuery = "INSERT INTO application_status_counts (status_name, count, last_updated_date) VALUES ('$rejected_status', 1, NOW())";

            if (!$mysqli->query($insertRejectedQuery)) {
                echo "Error inserting application status count: " . $mysqli->error;
            }
        } else {
            $updateRejectedQuery = "UPDATE application_status_counts SET count = count + 1 WHERE status_name = '$rejected_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())";

            if (!$mysqli->query($updateRejectedQuery)) {
                echo "Error updating application status count: " . $mysqli->error;
            }
        }

    }

     if ($selectedInterviewValue === "interview1") {
        $action = 'Interviews';
        $log = "The applicant with $controlNumber was successfully appointed for Level 1 Interviews";
        $role = "Admin";

        $insertAuditQuery = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, NOW(), ?)";
        $stmt = $auditMysqli->prepare($insertAuditQuery);
        $stmt->bind_param('sss', $action, $log, $role);
        if (!$stmt->execute()) {
            echo "Error inserting audit record: " . $stmt->error;
        }
        $stmt->close();
    }

    elseif($selectedInterviewValue === "interview2"){
        $action = 'Interviews';
        $log = "The applicant with $controlNumber was successfully appointed for Level 2 Interviews";
        $role = "Admin";

        $insertAuditQuery = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, NOW(), ?)";
        $stmt = $auditMysqli->prepare($insertAuditQuery);
        $stmt->bind_param('sss', $action, $log, $role);
        if (!$stmt->execute()) {
            echo "Error inserting audit record: " . $stmt->error;
        }
        $stmt->close();
    }
       elseif($selectedInterviewValue === "accepted"){
        $action = 'Accepted Applicant';
        $log = "The applicant with $controlNumber was Accepted";
        $role = "Admin";

        $insertAuditQuery = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, NOW(), ?)";
        $stmt = $auditMysqli->prepare($insertAuditQuery);
        $stmt->bind_param('sss', $action, $log, $role);
        if (!$stmt->execute()) {
            echo "Error inserting audit record: " . $stmt->error;
        }
        $stmt->close();
    }
    else{
    $action = 'Reject Applicant';
            $log = "The applicant with $controlNumber was Rejected";
            $role = "Admin";

            $insertAuditQuery = "INSERT INTO audits (actions, logs, audit_timestamp, role) VALUES (?, ?, NOW(), ?)";
            $stmt = $auditMysqli->prepare($insertAuditQuery);
            $stmt->bind_param('sss', $action, $log, $role);
            if (!$stmt->execute()) {
                echo "Error inserting audit record: " . $stmt->error;
            }
            $stmt->close();
    }



    $html_template_content = ''; 

    if (file_exists($html_template_file)) {
        $file_content = file_get_contents($html_template_file);
        $html_template_content .= '<br><br>' . $file_content;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'mail.sec-internsmanagement.elementfx.com';
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername; 
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($smtpUsername, 'SEC-INTERNSHIP PROGRAM');
        $mail->addAddress($to);
        
        $mail->addEmbeddedImage('../images/sec_logo.png', 'companyLogo');

        $mail->isHTML(true);
        $mail->Subject = $subject;

        $mail->Body = nl2br($message) . '<br>' . $html_template_content;

        if ($mail->send()) {
            echo 'Email sent successfully!';
        } else {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
$mysqli->close();
$auditMysqli->close();
?>
