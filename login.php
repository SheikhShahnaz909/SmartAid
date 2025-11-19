<?php

echo "<pre>";
print_r($_POST);
echo "</pre>";
exit();

// login.php → Handles BOTH Donor + Reporter login

require 'config.php';
session_start();

// Set up redirect pages based on role input, defaulting to donor_login.php
$requested_role = $_POST['role'] ?? 'donor';
$error_login_page = "{$requested_role}_login.php"; // CORRECTED: Use .php extension

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect non-POST requests to the appropriate login page
    header("Location: {$error_login_page}");
    exit();
}

// 1. Collect and sanitize input data
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// 2. Validation
if (empty($email) || empty($password)) {
    header("Location: {$error_login_page}?error=empty_fields");
    exit();
}

// 3. Fetch user
try {
    $sql_fetch = "SELECT user_id, password_hash, role, name FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql_fetch);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // If DB is down or table is missing
    header("Location: {$error_login_page}?error=db_fail");
    exit();
}


// 4. Check credentials and role
if (!$user || !password_verify($password, $user['password_hash'])) {
    header("Location: {$error_login_page}?error=invalid_credentials");
    exit();
}

if ($user['role'] !== $requested_role) {
    // Redirect to the correct login page if they try to log into the wrong portal
    $correct_login = ($user['role'] === 'donor') ? 'donor_login.php' : 'reporter_login.php';
    header("Location: {$correct_login}?error=wrong_portal");
    exit();
}

// 5. Login success → store session data
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $email;
$_SESSION['user_role'] = $user['role'];

// 6. Redirect to correct dashboard (using .php extension)
$dashboard_file = ($user['role'] === 'donor') ? 'donor_homepage.php' : 'reporter_homepage.php';
header("Location: {$dashboard_file}");
exit();
?>