<?php
// login.php — handles login for donor + reporter

require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: homepage.html");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';  // optional, from the form

// basic checks
if ($email === '' || $password === '') {
    header("Location: donor_login.php?error=empty"); // default
    exit();
}

// fetch user by email
$stmt = $pdo->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    // email not found
    $target = ($role === 'reporter') ? 'reporter_login.php' : 'donor_login.php';
    header("Location: $target?error=invalid");
    exit();
}

// check password using password_verify (IMPORTANT after reset)
if (!password_verify($password, $user['password_hash'])) {
    $target = ($role === 'reporter') ? 'reporter_login.php' : 'donor_login.php';
    header("Location: $target?error=invalid");
    exit();
}

// OK — set session
$_SESSION['user_id']    = $user['user_id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = $user['role'];

// redirect by role
if ($user['role'] === 'reporter') {
    header("Location: reporter_homepage.php");
} else {
    header("Location: donor_homepage.php");
}
exit();
