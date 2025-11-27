<?php
// mailer_config.php

// This line is essential and relies on the successful Composer installation
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends an email using Gmail's SMTP server.
 * @param string $to The recipient email address.
 * @param string $subject The email subject.
 * @param string $htmlBody The email body in HTML format.
 * @return bool True on success, false on failure (error is logged).
 */
function send_email(string $to, string $subject, string $htmlBody): bool
{
    $mail = new PHPMailer(true);

    try {
        // Server settings (using Gmail SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        
        // **!!! CRITICAL: REPLACE WITH YOUR GMAIL CREDENTIALS !!!**
        // This must be your full Gmail address
        $mail->Username   = 'smrtaid@gmail.com'; 
        
        // This must be an App Password generated from your Google Account settings,
        // NOT your regular Gmail password, for security.
        $mail->Password   = 'wwrdyrsukmpoxqkl'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SMTPS (465)
        $mail->Port       = 465;

        // Sender details (The email the user sees it came from)
        $mail->setFrom('smrtaid@gmail.com', 'Smart Aid Password Reset');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody); // Plain text fallback
        //$mail->addReplyTo($to, $name);
        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log the detailed error (good for debugging)
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        // Return false to indicate failure
        return false;
    }
}
?>