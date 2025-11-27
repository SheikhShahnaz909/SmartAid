<?php
require_once __DIR__ . '/../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
}

$input = [
  'name' => trim($_POST['name'] ?? ''),
  'phone' => trim($_POST['phone'] ?? ''),
  'address' => trim($_POST['address'] ?? ''),
  'bio' => trim($_POST['bio'] ?? '')
];

// basic validation
if ($input['name'] === '' || $input['name'] === null) {
    http_response_code(400); echo json_encode(['error'=>'Name required']); exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET name = :name, phone = :phone, address = :address, bio = :bio WHERE user_id = :uid");
    $stmt->execute([
        ':name'=>$input['name'],
        ':phone'=>$input['phone'],
        ':address'=>$input['address'],
        ':bio'=>$input['bio'],
        ':uid' => (int)$_SESSION['user_id']
    ]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'DB error']);
}
