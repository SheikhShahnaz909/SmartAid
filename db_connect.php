<?php
// db_connect.php

$host = "127.0.0.1";
$port = 3307;                 // from your SQL dump
$dbname = "smartaid_db";      // exact DB name
$username = "root";           // XAMPP default
$password = "";               // XAMPP default is empty

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Throw exceptions when errors happen
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
