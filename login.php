<?php
// login.php — Handles login for both donor and reporter roles.

// --- CRITICAL: Must be the absolute first line ---
session_start(); 

// --- CRITICAL: Database connection ---
require 'config.php'; // Ensure this file exists and contains the $pdo object

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect if accessing directly via URL
    header("Location: homepage.html");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';  // Comes from the hidden input in the login forms

// 1. Determine redirect page in case of failure (based on the submitted role)
$target_login_page = ($role === 'reporter') ? 'reporter_login.php' : 'donor_login.php';

// Basic validation for missing fields
if ($email === '' || $password === '') {
    header("Location: " . $target_login_page . "?error=missing_fields"); 
    exit();
}

try {
    // 2. Fetch user by email AND the requested role (SECURE PDO QUERY)
    // This ensures a Reporter cannot log in via the Donor form.
    $stmt = $pdo->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = :email AND role = :role LIMIT 1");
    $stmt->execute([':email' => $email, ':role' => $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Authentication Checks
    if (!$user) {
        // User not found with this email AND role combination
        header("Location: " . $target_login_page . "?error=invalid_credentials");
        exit();
    }

    // Verify password hash
    if (!password_verify($password, $user['password_hash'])) {
        header("Location: " . $target_login_page . "?error=invalid_credentials");
        exit();
    }

    // 4. Authentication Success — Set Session Variables
    $_SESSION['user_id']    = $user['user_id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role']; // CRITICAL for redirection and access control

    // 5. Final Role-Based Redirection
    if ($user['role'] === 'reporter') {
        header("Location: reporter_homepage.php");
    } else {
        // Handles 'donor' role
        header("Location: donor_homepage.php");
    }
    exit();
    
    function log_activity($conn, $userId, $role, $action, $details = '') {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, role, action, details, ip_address) VALUES (?,?,?,?,?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt->bind_param("issss", $userId, $role, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

} catch (PDOException $e) {
    // Log the error (e.g., database down) and redirect with a generic failure message
    error_log("Login DB Error: " . $e->getMessage());
    header("Location: " . $target_login_page . "?error=db_fail");
    exit();
}