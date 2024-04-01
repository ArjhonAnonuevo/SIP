<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../configuration/application_config.php';

// Call the getDatabaseConfig function
$config = getDatabaseConfig();

// Extract database connection parameters
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];

// Create connection
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $to = $_POST['primaryEmail'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $selectedInterviewValue = $_POST['selectedInterviewValue'];
    $controlNumber = $_POST['controlNumber'];
    $html_template_file = '../send-emails/content.html';

    // Sanitize inputs
    $to = htmlspecialchars($to);
    $subject = htmlspecialchars($subject);
    // Sanitize other inputs as needed

    // Update status based on selected interview value
    switch ($selectedInterviewValue) {
        case 'interview1':
            $status = 'Level 1 Interview';
            $app_status = "Pending";
            break;
        case 'interview2':
            $status = 'Level 2 Interview';
            break;
        case 'interview3':
            $status = 'Orientation';
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

    // Prepare and execute SQL statement to update status
    $stmt = $mysqli->prepare("UPDATE status SET status_name = ? WHERE control_number = ?");
    $stmt->bind_param('ss', $status, $controlNumber);
    if (!$stmt->execute()) {
        // Handle database error
        echo "Database error: " . $stmt->error;
    }
    $stmt->close();
    $updateCount = true;

    // If the selected interview is "interview1", insert a record into the application_status_counts table with status "Pending"
    if ($selectedInterviewValue === 'interview1') {
        $app_status = "Pending";

        // Check if a record exists for the current month with status "Pending"
        $result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");
        if ($result && $result->num_rows == 0) {
            // Insert new record for the "Pending" status
            $insertQuery = "INSERT INTO application_status_counts (status_name, count, last_updated_date) VALUES ('$app_status', 1, NOW())";

            // Execute the insert query
            if (!$mysqli->query($insertQuery)) {
                echo "Error inserting application status count: " . $mysqli->error;
            }
        } else {
            // Update count for the existing "Pending" status record
            $updateQuery = "UPDATE application_status_counts SET count = count + 1 WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())";

            // Execute the update query
            if (!$mysqli->query($updateQuery)) {
                echo "Error updating application status count: " . $mysqli->error;
            }
        }
    } elseif ($selectedInterviewValue === 'interview3') {
        $app_status = "Accepted";

        // Check if there are pending applications for the current month
        $pending_status = "Pending";
        $pending_result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($pending_result && $pending_result->num_rows > 0) {
            // Decrement count for the existing "Pending" status record
            $decrementQuery = "UPDATE application_status_counts SET count = count - 1 WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW()) LIMIT 1";

            // Execute the decrement query
            if (!$mysqli->query($decrementQuery)) {
                echo "Error decrementing application status count: " . $mysqli->error;
            }
        }

        $result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($result && $result->num_rows == 0) {
            $insertQuery = "INSERT INTO application_status_counts (status_name, count, last_updated_date) VALUES ('$app_status', 1, NOW())";

            // Execute the insert query
            if (!$mysqli->query($insertQuery)) {
                echo "Error inserting application status count: " . $mysqli->error;
            }
        } else {
            // Update count for the existing "Accepted" status record
            $updateQuery = "UPDATE application_status_counts SET count = count + 1 WHERE status_name = '$app_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())";

            // Execute the update query
            if (!$mysqli->query($updateQuery)) {
                echo "Error updating application status count: " . $mysqli->error;
            }
        }

        // Perform application deletion
        $controlNumberToDelete = $_POST['controlNumber'];

        $queryDeleteFiles = "DELETE FROM interns_files WHERE control_number = ?";
        $stmtDeleteFiles = $mysqli->prepare($queryDeleteFiles);
        $stmtDeleteFiles->bind_param('s', $controlNumberToDelete);
        $stmtDeleteFiles->execute();
        $stmtDeleteFiles->close();

        $queryDeleteApplication = "DELETE FROM application WHERE control_number = ?";
        $stmtDeleteApplication = $mysqli->prepare($queryDeleteApplication);
        $stmtDeleteApplication->bind_param('s', $controlNumberToDelete);
        $stmtDeleteApplication->execute();
        $stmtDeleteApplication->close();

        $queryDeleteStatus = "DELETE FROM status WHERE control_number = ?";
        $stmtDeleteStatus = $mysqli->prepare($queryDeleteStatus);
        $stmtDeleteStatus->bind_param('s', $controlNumberToDelete);
        $stmtDeleteStatus->execute();
        $stmtDeleteStatus->close();
    } elseif ($selectedInterviewValue === 'rejected') {
        $controlNumberToDelete = $_POST['controlNumber'];

        $pending_status = "Pending";
        $pending_result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($pending_result && $pending_result->num_rows > 0) {
            // Decrement count for the existing "Pending" status record
            $decrementQuery = "UPDATE application_status_counts SET count = count - 1 WHERE status_name = '$pending_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW()) LIMIT 1";

            // Execute the decrement query
            if (!$mysqli->query($decrementQuery)) {
                echo "Error decrementing application status count: " . $mysqli->error;
            }
        }

        // Check if a record exists for the current month with status "Rejected"
        $rejected_status = "Rejected";
        $rejected_result = $mysqli->query("SELECT * FROM application_status_counts WHERE status_name = '$rejected_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())");

        if ($rejected_result && $rejected_result->num_rows == 0) {
            $insertRejectedQuery = "INSERT INTO application_status_counts (status_name, count, last_updated_date) VALUES ('$rejected_status', 1, NOW())";

            // Execute the insert query
            if (!$mysqli->query($insertRejectedQuery)) {
                echo "Error inserting application status count: " . $mysqli->error;
            }
        } else {
            $updateRejectedQuery = "UPDATE application_status_counts SET count = count + 1 WHERE status_name = '$rejected_status' AND MONTH(last_updated_date) = MONTH(NOW()) AND YEAR(last_updated_date) = YEAR(NOW())";

            // Execute the update query
            if (!$mysqli->query($updateRejectedQuery)) {
                echo "Error updating application status count: " . $mysqli->error;
            }
        }

        // Perform application deletion
        $queryDeleteFiles = "DELETE FROM interns_files WHERE control_number = ?";
        $stmtDeleteFiles = $mysqli->prepare($queryDeleteFiles);
        $stmtDeleteFiles->bind_param('s', $controlNumberToDelete);
        $stmtDeleteFiles->execute();
        $stmtDeleteFiles->close();

        $queryDeleteApplication = "DELETE FROM application WHERE control_number = ?";
        $stmtDeleteApplication = $mysqli->prepare($queryDeleteApplication);
        $stmtDeleteApplication->bind_param('s', $controlNumberToDelete);
        $stmtDeleteApplication->execute();
        $stmtDeleteApplication->close();

        $queryDeleteStatus = "DELETE FROM status WHERE control_number = ?";
        $stmtDeleteStatus = $mysqli->prepare($queryDeleteStatus);
        $stmtDeleteStatus->bind_param('s', $controlNumberToDelete);
        $stmtDeleteStatus->execute();
        $stmtDeleteStatus->close();
    }

    $html_template_content = '';

    if ($selectedInterviewValue === 'interview1') {
        // Get values for level 1 interview
        date_default_timezone_set('Asia/Manila');
        $date = $_POST['date'];
        $startTime = date('h:i A', strtotime($_POST['startTime']));
        $endTime = date('h:i A', strtotime($_POST['endTime']));
        $url = $_POST['url'];
        $id = $_POST['m_id'];
        $passcode = $_POST['passcode'];
        $fdate = DateTime::createFromFormat('Y-m-d', $date);
        if ($fdate === false) {
            echo "Invalid date format or date: $date";
        } else {
            $formattedDate = $fdate->format('F d, Y');
            $day = $fdate->format('l');
            $formattedDate .= " ($day)";
            $tomorrow = new DateTime('tomorrow');
            $isTomorrow = $fdate->format('Y-m-d') === $tomorrow->format('Y-m-d');
            $dayText = $isTomorrow ? 'tomorrow' : 'the other day on';
        }

        $html_template_content = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Document</title>
            <style>
                body{
                    font-size: 12px;
                }
                .url {
                    color: blue;
                    text-decoration: underline; 
                }
            </style>
        </head>
        <body>
            <p>We want to inform you that after reviewing your application and requirements, we are excited to move forward with the <strong>Level 1 Interview</strong> to be conducted by the <strong>SEC Internship Program (SIP) Management Team</strong> $dayText,<strong>$formattedDate</strong>, <strong>$startTime-$endTime</strong>
            via <strong>Zoom</strong> Video Communications through the following details:
            </p>
            <ul>
            <li>URL: <span class='url'>$url</span></li>
            <li>Meeting ID: <span class = 'id'>$id</span></li>
            <li>Passcode: <span class = 'passcode'>$passcode</span></li>

            <p> Kindly confirm your attendance on or before the scheduled interview. If you have further queries or concerns, please contact us through this email or at (+632) 8818-5994.</p>
            <p> Furthermore, kindly send us the accomplished Personal Data Sheet (PDS). Thank you, and we look forward to seeing you virtually.</p>
        </ul>
        </body>
        </html>";
    } elseif ($selectedInterviewValue == "interview2") {
        date_default_timezone_set('Asia/Manila');
        $int2_date = $_POST['int2Date'];
        $int2_time = date('h:i A', strtotime($_POST['int2Time']));
        $int2_department = $_POST['department'];

        $f_int2_date = DateTime::createFromFormat('Y-m-d', $int2_date);
        if ($f_int2_date === false) {
            echo "Invalid date format or date: $int2_date";
        } else {
            $formattedDate2 = $f_int2_date->format('F d, Y');
            $day = $f_int2_date->format('l');
            $formattedDate2 .= " ($day)";
            $tomorrow = new DateTime('tomorrow');
            $isTomorrow = $f_int2_date->format('Y-m-d') === $tomorrow->format('Y-m-d');
            $dayText = $isTomorrow ? 'tomorrow' : 'the other day on';
        }

        $html_template_content = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Document</title>
        </head>
        <body>
            <p>Good day! We had a great time getting to know you during the Level 1 Interview. In relation, we want to invite you to the Level 2 Interview with the <strong>$int2_department</strong> on <strong>$formattedDate2</strong> at <strong>$int2_time</strong>
              Kindly proceed first at the LRID, 7th Floor, 7907 Makati Avenue, Salcedo Village, Bel-air, Makati City 1209. </p>
            <p>Please confirm your attendance on or before the scheduled date of the interview. If you have further queries or concerns, please contact us through this email or at (+632) 8818-5994. </p>
            <p>Thank You and Goodluck</p>
        </body>
        </html>";
    } elseif ($selectedInterviewValue === 'interview3') {
        $html_template_content = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Document</title>
        </head>
        <body>
            <p>Greetings from the Securities and Exchange Commission!</p>
            <p>We are pleased to inform you that you have been <strong>ACCEPTED</strong> to the SEC Internship Program (SIP). The following documents are attached for your reference:</p>
            <ul>
                <li>SEC Acceptance Letter;</li>
                <li>SIP Internship Contract.</li>
            </ul>
            <p>Please review the attached internship contract and complete the details from your end, which are in <span style='color: red;'>red font</span>, so we can finalize. Kindly return the digitally signed copy to us or bring the originally signed copy with you when you report.</p>
            <p>Furthermore, kindly send to this email the following for the issuance of the SEC Student-Intern ID:</p>
            <ul>
                <li>Full Name;</li>
                <li>2x2 DIGITAL ID Picture;</li>
                <li>Viber Number;</li>
                <li>Preferred Nickname;</li>
                <li>Name of Parent/Guardian; and</li>
                <li>Mobile Number of Parent/Guardian.</li>
            </ul>
            <p>Congratulations, and we look forward to working with you.</p>
        </body>
        </html>";
    } else {
        $html_template_content = "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Rejection Email</title>
        </head>
        <body>
            <p>Thank you very much for your interest in the internship opportunity at [Company Name] and for taking the time to apply. We appreciate the enthusiasm and dedication you've shown throughout the application process.</p>
            <p>After careful consideration, we've decided to move forward with another candidate for this position. This decision was not easy, and we hope you understand that it was based on the specific needs and goals of the internship program.</p>
            <p>We encourage you to continue pursuing your career goals and to consider other opportunities that may align more closely with your interests and skills. We're always looking for talented individuals, and we hope you'll consider applying to our company in the future.</p>
            <p>Please feel free to stay in touch, and we wish you the best in your future endeavors.</p>
            <br>
            <p>Warm regards,</p>
        </body>
        </html>";
    }
    // Fetch HTML content from the file
    if (file_exists($html_template_file)) {
        $file_content = file_get_contents($html_template_file);
        $html_template_content .= '<br><br>' . $file_content;
    }

    // Instantiate PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aaanonuevo05@gmail.com';
        $mail->Password = 'zkuz velx nzbq spsy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('aaanonuevo05@gmail.com', 'Arjhon');
        $mail->addAddress($to);

        $mail->addEmbeddedImage('../images/sec_logo.png', 'companyLogo');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Set the email body
        $mail->Body = $message . '<br>' . $html_template_content;

        // Send email
        if ($mail->send()) {
            echo 'Email sent successfully!';
        } else {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    $mysqli->close();
}
?>
