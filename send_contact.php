<?php
// send_contact.php - Processes the contact form submission
session_start();
// Requires your PHPMailer configuration file
require 'mailer_config.php'; 

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact_us.php');
    exit();
}

// 1. Sanitize and Validate Input
$name = trim($_POST['name'] ?? '');
$user_email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

// Basic check for required fields
if (empty($name) || empty($user_email) || empty($message)) {
    header('Location: contact_us.php?status=fail_missing');
    exit();
}

// 2. Prepare Email Content
$to_support = 'smrtaid@gmail.com'; // Your official support email
$subject = "New Support Inquiry: " . substr(strip_tags($message), 0, 50) . "..."; // Shortened subject line

$htmlBody = "
    <html>
    <head><title>New Contact Form Submission</title></head>
    <body>
        <h2>New Message via Smart Aid Contact Form</h2>
        <p><strong>Sender Name:</strong> {$name}</p>
        <p><strong>Sender Email:</strong> {$user_email}</p>
        <p><strong>--- Message ---</strong></p>
        <div style='border: 1px solid #ccc; padding: 15px; background: #f9f9f9;'>
            " . nl2br($message) . "
        </div>
        <p>-------------------------</p>
        <p>Please reply directly to: {$user_email}</p>
    </body>
    </html>
";

// 3. Send the Email using the function from mailer_config.php
// Note: The send_email function must be defined in mailer_config.php
$success = send_email($to_support, $subject, $htmlBody);

if ($success) {
    header('Location: contact_us.php?status=success');
    exit();
} else {
    // The error details are logged in mailer_config.php
    header('Location: contact_us.php?status=fail');
    exit();
}