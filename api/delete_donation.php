<?php
require_once __DIR__ . '/../config.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit;}
if (empty($_SESSION['user_id'])) { http_response_code(401); exit; }
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Bad id']); exit; }
$stmt = $pdo->prepare("DELETE FROM donations WHERE id = ? AND user_id = ?");
$stmt->execute([$id, (int)$_SESSION['user_id']]);
echo json_encode(['success'=>true]);
