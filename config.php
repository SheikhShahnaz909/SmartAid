<?php
define('DB_HOST', '127.0.0.1');   
define('DB_PORT', '3307');        
define('DB_USER', 'root');        
define('DB_PASS', ''); 
define('DB_NAME', 'smartaid_db'); 
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {
    $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
