<?php
// signup.php — donor + reporter signup

require 'config.php';   

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: homepage.html");
    exit();
}

// Validate role
$role = $_POST['role'] ?? '';
if ($role !== 'donor' && $role !== 'reporter') {
    header("Location: homepage.html");
    exit();
}

$redirect_page = ($role === 'reporter') ? 'reporter_login.php' : 'donor_login.php';
$error_page    = ($role === 'reporter') ? 'reporter_signup.php' : 'donor_signup.php';

// Sanitize inputs
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$location = trim($_POST['location'] ?? '');
$phone    = preg_replace('/\D/', '', $_POST['phone'] ?? '');   // CLEANED

// Validation rules
$name_regex  = "/^[a-zA-Z\s'-]+$/";
$phone_regex = "/^\d{10}$/";

// Required fields
if ($name === '' || $email === '' || $password === '' || $phone === '') {
    header("Location: $error_page?error=fields_required");
    exit();
}

// Validate fields
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: $error_page?error=invalid_email");
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

// Check if user exists
try {
    $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
    $stmt_check->execute([':email' => $email]);

    if ($stmt_check->rowCount() > 0) {
        header("Location: $error_page?error=exists");
        exit();
    }
} catch (PDOException $e) {
    header("Location: $error_page?error=db_fail");
    exit();
}

// Insert new user
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql_insert = "INSERT INTO users (name, email, password_hash, phone, location, role) 
               VALUES (:name, :email, :password_hash, :phone, :location, :role)";

$stmt_insert = $pdo->prepare($sql_insert);

try {
    $stmt_insert->execute([
        ':name'          => $name,
        ':email'         => $email,
        ':password_hash' => $password_hash,
        ':phone'         => $phone,
        ':location'      => $location,
        ':role'          => $role
    ]);

    header("Location: $redirect_page?signup=success&email=" . urlencode($email));
    exit();

} catch (PDOException $e) {
    header("Location: $error_page?error=db_fail");
    exit();
}
?>
