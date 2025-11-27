<?php
require_once __DIR__ . '/../config.php';
session_start();

// must be logged-in donor
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, name, email, phone, address, bio, avatar, created_at FROM users WHERE user_id = ?");
    $stmt->execute([ (int)$_SESSION['user_id'] ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode($user);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
