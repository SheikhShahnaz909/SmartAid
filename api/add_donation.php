<?php
require_once __DIR__ . '/../config.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$item = trim($_POST['item'] ?? '');
$qty  = trim($_POST['qty'] ?? '');
$type = trim($_POST['type'] ?? '');

if ($item === '' || $qty === '' || $type === '') {
    http_response_code(400); echo json_encode(['error'=>'Missing fields']); exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO donations (user_id, item, quantity, type, status) VALUES (?, ?, ?, ?, 'saved')");
    $stmt->execute([(int)$_SESSION['user_id'], $item, $qty, $type]);
    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500); echo json_encode(['error'=>'DB error']);
}
