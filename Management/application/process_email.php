<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Fetch emails from the form
    $hidden_email_input = isset($_POST['hidden_email_input']) ? $_POST['hidden_email_input'] : '';
    $subject = 'Application Status';
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    
    // Extract individual emails from the comma-separated list
    $emails = explode(',', $hidden_email_input);

    // Validate and sanitize inputs if needed
    if (empty($emails)) {
        echo "Error: No recipient email addresses provided.";
        exit();
    }

    // Additional HTML content
    $additionalContent = "<html><body>" .
    "<p>Greetings from the Securities and Exchange Commission!</p>" .
    "<p>We are pleased to inform you that you have been <strong>ACCEPTED</strong> to the SEC Internship Program (SIP). The following documents are attached for your reference:</p>" .
    "<ul>" .
    "<li>SEC Acceptance Letter;</li>" .
    "<li>SIP Internship Contract.</li>" .
    "</ul>" .
    "<p>Please review the attached internship contract and complete the details from your end, which are in <span style='color: red;'>red font</span>, so we can finalize. Kindly return the digitally signed copy to us or bring the originally signed copy with you when you report.</p>" .
    "<p>Furthermore, kindly send to this email the following for the issuance of the SEC Student-Intern ID:</p>" .
    "<ul>" .
    "<li>Full Name;</li>" .
    "<li>2x2 DIGITAL ID Picture;</li>" .
    "<li>Viber Number;</li>" .
    "<li>Preferred Nickname;</li>" .
    "<li>Name of Parent/Guardian; and</li>" .
    "<li>Mobile Number of Parent/Guardian.</li>" .
    "</ul>" .
    "<p>Congratulations, and we look forward to working with you.</p>" .
    "<p>For the SIP Management Team,</p>" .
    "<span style='font-size: 13px; font-weight: bold; margin-top: 20px;'>Portia Gema A. Abad</span>" .
    "<br>" .
    "<span style='font-size: 12px; font-weight: bold;'>Program Management Team</span>" .
    "<br>" .
    "<span style='font-size: 12px; color:#274e13; font-weight: bold;'>SEC Internship Program</span>" .
    "<br><br>" .
    "<span style='font-size: 12px; color:#274e13; font-weight: bold;'>Learning Resource and Information Division</span>" .
    "<br>" .
    "<span style='font-size: 12px; color:#274e13; font-weight: bold;'>Human Resource and Administrative Department</span>" .
    "<br><br>" .
    "<span style='font-size: 12px; color:#8c8c8c; '>Telephone: +63 2 8818 5994</span>" .
    "<br><br>" .
    "<img src='cid:companyLogo' alt='Company Logo' width='110' height='100'>" .
    "<footer>" .
    "<a href='#link1' style='color: gray; font-size: 11px;'>Official Website</a> |" .
    "<a href='#link2' style='color: gray; font-size: 11px;'> Facebook</a> |" .
    "<a href='#link3' style='color: gray; font-size: 11px;'> Twitter</a> |" .
    "<a href='#link2' style='color: gray; font-size: 11px;'> Linkdln</a> |" .
    "<br>" .
    "<br>" .
    "<span style='font-size: 11px; font-weight: bold;'>CONFIDENTIALITY AND PRIVACY NOTICE: </span>" .
    "<span style='font-size: 11px;'>This email message, including the attachments, if any, contains confidential information which may be privileged or otherwise protected from disclosure and intended solely for the use of the individual or entity to whom it is addressed and others authorized to receive it. If you have received this email by mistake, please notify the sender immediately via return email and delete the document and any copies thereof. This message is protected under R.A. No. 4200 (The Anti-Wire Tapping Law), R.A. No. 8792 (The E-Commerce Law), A.M. No. 01-7-01-SC (Rules on Electronic Evidence), and Republic Act No. 10173 (The Data Privacy Act of 2012).</span>" .
    "</footer>" .
    "</body></html>";


    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aaanonuevo05@gmail.com';
        $mail->Password = 'zkuz velx nzbq spsy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Additional SMTP settings
        $mail->setFrom('aaanonuevo05@gmail.com', 'Securities and Exchange Commision');
        foreach ($emails as $email) {
            $mail->addAddress($email);
        }
        $mail->AddEmbeddedImage('../images/sec_logo.png', 'companyLogo');
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message . $additionalContent;

        // Send email
        $mail->send();

        $response = array("status" => "success", "message" => "Emails have been sent successfully");
        echo json_encode($response);

    } catch (Exception $e) {
                $response = array("status" => "error", "message" => "Mailer Error: " . $mail->ErrorInfo, "hidden_email_input" => $hidden_email_input);
        echo json_encode($response);
    }
} else {
    // Redirect to a different page if not a POST request
    $response = array("status" => "error", "message" => "Invalid request method");
    echo json_encode($response);
    exit();
}
?>
