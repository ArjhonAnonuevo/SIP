<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../configuration/interns_config.php';

$config = getDatabaseConfig();
$dbhost = $config['dbhost'];
$dbuser = $config['dbuser'];
$dbpass = $config['dbpass'];
$dbname = $config['dbname'];
$smtpUsername = 'sip-management@sec-internsmanagement.elementfx.com';
$smtpPassword = 'sip-program';
$host = 'mail.sec-internsmanagement.elementfx.com';

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];

    $stmt = $conn->prepare("SELECT a.*, p.primary_email 
                            FROM interns_account a 
                            JOIN interns_profile p ON a.username = p.username 
                            WHERE a.username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['primary_email'];

        $token = bin2hex(random_bytes(16));

        $updateSql = "UPDATE interns_account SET reset_token=? WHERE username=?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ss", $token, $username);
        if ($stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host =  $host; 
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
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom($smtpUsername, 'SIP MANAGEMENT');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset';
                $mail->Body = "Your password reset token is: $token";

                $mail->send();
                echo "Password reset token sent to your email.";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Invalid username.";
    }

    $stmt->close();
}

$conn->close();
?>
