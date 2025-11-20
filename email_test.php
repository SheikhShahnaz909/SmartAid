<?php
require 'mailer_config.php';

$to = 'sheikhshhnz.09@gmail.com'; // send to yourself
$subject = 'SmartAid SMTP Test';
$body = '<h3>Email Test Successful</h3><p>Your SMTP setup is working!</p>';

if (send_email($to, $subject, $body)) {
    echo "Email sent! Check your inbox.";
} else {
    echo "Email failed. Check logs/reset_links.log.";
}
