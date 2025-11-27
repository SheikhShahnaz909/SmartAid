<?php
require_once 'mailer_config.php';

$to = 'smrtaid@gmail.com';   // put your email here
$subject = 'SmartAid Email';
$body = '<p>This is a <strong>test email</strong> from SmartAid PHPMailer setup.</p>';

if (function_exists('send_email')) {
    $ok = send_email($to, $subject, $body);
    if ($ok) {
        echo "Email sent OK!";
    } else {
        echo "send_email returned false.";
    }
} else {
    echo "send_email() not found. Check mailer_config.php.";
}
