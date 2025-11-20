<?php
// mailer_config.php
// Gmail SMTP configuration using PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

// --- YOUR GMAIL SMTP SETTINGS (CORRECTED) ---
define('SMTP_HOST', 'smtp.gmail.com');      // <--- FIXED
define('SMTP_PORT', 587);
define('SMTP_USER', 'sheikhshhnz.09@gmail.com');  // your Gmail
define('SMTP_PASS', 'zzatnaswqafikada');          // <--- REMOVE ALL SPACES
define('SMTP_FROM_EMAIL', 'sheikhshhnz.09@gmail.com');
define('SMTP_FROM_NAME', 'SmartAid');
// --------------------------------------------

/**
 * send_email($to, $subject, $htmlBody)
 */
function send_email($to, $subject, $htmlBody) {

    // We are NOT in dev mode anymore (you have real SMTP)
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // Email headers
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log error
        if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0700, true);
        file_put_contents(
            __DIR__ . '/logs/reset_links.log',
            date('c') . " MAIL ERROR: {$mail->ErrorInfo}\nTO: $to\nSUBJECT: $subject\n$htmlBody\n\n",
            FILE_APPEND
        );
        return false;
    }
}
