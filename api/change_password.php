<?php
require_once __DIR__ . '/../config.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new === '' || $new !== $confirm) {
    http_response_code(400); echo json_encode(['error'=>'New passwords do not match or empty']); exit;
}

try {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) {
        http_response_code(403); echo json_encode(['error'=>'Current password incorrect']); exit;
    }
    $hash = password_hash($new, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")->execute([$hash, (int)$_SESSION['user_id']]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    http_response_code(500); echo json_encode(['error'=>'DB error']);
}
