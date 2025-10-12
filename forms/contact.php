<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Stop the script if the form is not submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Set a 405 (Method Not Allowed) response code and exit.
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Load PHPMailer classes
require '../assets/vendor/PHPMailer/src/Exception.php';
require '../assets/vendor/PHPMailer/src/PHPMailer.php';
require '../assets/vendor/PHPMailer/src/SMTP.php';

// --- CONFIGURATION ---
// Replace with your real receiving email address
$receiving_email_address = 'info@dnetlk.com';

// --- SMTP CREDENTIALS ---
// You must generate an App Password for this to work with Gmail.
$smtp_host = 'smtp.gmail.com';
$smtp_username = 'info@dnetlk.com';
$smtp_password = 'bkfmreoxxjmnpkqx';
$smtp_port = 587; // Use 587 for TLS, 465 for SSL

// --- FORM DATA ---
// Sanitize and validate input
$from_name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
$from_email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
$phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
$message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

// Basic validation
if (empty($from_name) || !filter_var($from_email, FILTER_VALIDATE_EMAIL) || empty($subject) || empty($message)) {
    // Set a 400 (Bad Request) response code and exit.
    http_response_code(400);
    echo 'Please fill out all required fields and provide a valid email address.';
    exit;
}

// --- SEND EMAIL ---
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = $smtp_port;

    // Recipients
    $mail->setFrom($from_email, $from_name);
    $mail_to_send = explode(',', $receiving_email_address);
    foreach ($mail_to_send as $key => $value) {
        $mail->addAddress(trim($value));
    }
    $mail->addReplyTo($from_email, $from_name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Submission: " . $subject;
    
    // Construct email body
    $email_body = "You have received a new message from your website contact form.<br><br>";
    $email_body .= "<b>Name:</b> " . htmlspecialchars($from_name) . "<br>";
    $email_body .= "<b>Email:</b> " . htmlspecialchars($from_email) . "<br>";
    if (!empty($phone)) {
        $email_body .= "<b>Phone:</b> " . htmlspecialchars($phone) . "<br>";
    }
    $email_body .= "<b>Service of Interest:</b> " . htmlspecialchars($subject) . "<br>";
    $email_body .= "<b>Message:</b><br>" . nl2br(htmlspecialchars($message));
    
    $mail->Body = $email_body;
    $mail->AltBody = strip_tags(str_replace("<br>", "\n", $email_body)); // Plain text version

    $mail->send();
    http_response_code(200);

} catch (Exception $e) {
    // Set a 500 (Internal Server Error) response code.
    http_response_code(500);
    // Provide a more detailed error message for debugging
    echo "An error occurred while trying to send your message.<br><br>";
    echo "<strong>Debugging Information:</strong><br>";
    echo "Mailer Error: " . $mail->ErrorInfo;
}

?>