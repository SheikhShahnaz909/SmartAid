<?php
// signup.php → Handles BOTH Donor + Reporter signup

require 'config.php';   // DB connection

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect non-POST requests to the main page
    header("Location: homepage.html");
    exit();
}

// Determine role (donor/reporter) and set redirection targets
$role = $_POST['role'] ?? '';
// CORRECTED: Use .php extensions for redirects
$redirect_page = ($role === 'reporter') ? 'reporter_login.php' : 'donor_login.php';
$error_page    = ($role === 'reporter') ? 'reporter_signup.php' : 'donor_signup.php';

// Collect and sanitize input data
$name     = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$phone    = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
$location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS);

// Validation regex
$name_regex  = "/^[a-zA-Z\s'-]+$/";
$phone_regex = "/^\d{10}$/";

// ✅ Basic Validation
if (empty($name) || empty($email) || empty($password) || empty($phone)) {
    header("Location: $error_page?error=fields_required");
    exit();
}
if (!preg_match($name_regex, $name)) {
    header("Location: $error_page?error=invalid_name");
    exit();
}
if (!preg_match($phone_regex, $phone)) {
    header("Location: $error_page?error=invalid_phone");
    exit();
}
// Note: Email strength/domain validation is handled client-side but should be enforced here too.

//  ✅ Check if email already exists
try {
    $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
    $stmt_check->bindParam(':email', $email);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        header("Location: $error_page?error=exists");
        exit();
    }
} catch (PDOException $e) {
    header("Location: $error_page?error=db_fail");
    exit();
}


// ✅ Insert new user (hash password)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql_insert = "INSERT INTO users (name, email, password_hash, phone, location, role)
               VALUES (:name, :email, :password_hash, :phone, :location, :role)";

$stmt_insert = $pdo->prepare($sql_insert);
$stmt_insert->bindParam(':name', $name);
$stmt_insert->bindParam(':email', $email);
$stmt_insert->bindParam(':password_hash', $password_hash);
$stmt_insert->bindParam(':phone', $phone);
$stmt_insert->bindParam(':location', $location);
$stmt_insert->bindParam(':role', $role);

try {
    if ($stmt_insert->execute()) {
        header("Location: $redirect_page?signup=success&email=" . urlencode($email));
        exit();
    } else {
        header("Location: $error_page?error=db_fail");
        exit();
    }
} catch (PDOException $e) {
    header("Location: $error_page?error=db_fail");
    exit();
}
?>