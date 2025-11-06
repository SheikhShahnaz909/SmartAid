<?php
// config.php

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'shahnaz909'); // CHANGE THIS
define('DB_PASS', 'password321'); // CHANGE THIS
define('DB_NAME', 'smartaid_db');

// Attempt to connect to the database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Kill the script and show error if connection fails
    die("Database connection failed: " . $e->getMessage());
}
?>