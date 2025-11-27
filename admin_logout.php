<?php
// admin_logout.php
require_once 'admin_session.php';

// Clear all session data
$_SESSION = [];

// Delete the session cookie securely
$params = session_get_cookie_params();
setcookie(
    session_name(),
    '',
    time() - 42000,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
);

// Destroy the session completely
session_destroy();

// Redirect admin back to login page
header('Location: admin_login.php');
exit;
