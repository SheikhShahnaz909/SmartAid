<?php
// login.php → Handles BOTH Donor + Reporter login

require 'config.php';
session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: homepage.html");
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$requested_role = $_POST['role'] ?? 'donor';  // donor/reporter

// Validation
if (empty($email) || empty($password)) {
    header("Location: {$requested_role}_login.html?error=empty_fields");
    exit();
}

// Fetch user
$sql_fetch = "SELECT user_id, password_hash, role, name FROM users WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql_fetch);
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    header("Location: {$requested_role}_login.html?error=invalid_credentials");
    exit();
}

// Verify role matches portal (donor cannot login as reporter)
if ($user['role'] !== $requested_role) {
    header("Location: homepage.html?error=wrong_role");
    exit();
}

// Login success → store session data
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $email;
$_SESSION['user_role'] = $user['role'];

// Redirect to correct dashboard
if ($user['role'] === 'donor') {
    header("Location: donor_homepage.html");
} else {
    header("Location: reporter_homepage.html");
}
exit();
?>
