<?php
require_once __DIR__ . '/../config.php';
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
}
try {
    $stmt = $pdo->prepare("SELECT id, item, quantity, type, status, created_at FROM donations WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    http_response_code(500); echo json_encode(['error'=>'DB error']);
}
