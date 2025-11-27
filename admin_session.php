<?php
// admin_session.php
// Use strict mode and secure cookie params. Include before any output.
ini_set('session.use_strict_mode', 1);

// Get current cookie params and set secure options
$cookieParams = session_get_cookie_params();
$lifetime = 0; // expire on browser close

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => $cookieParams['path'] ?? '/',
    'domain'   => $cookieParams['domain'] ?? '',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
