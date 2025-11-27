<?php
require_once __DIR__ . '/../config.php';
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
    http_response_code(400); echo json_encode(['error'=>'No file']); exit;
}

$file = $_FILES['avatar'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); echo json_encode(['error'=>'Upload error']); exit;
}

// validate MIME type (basic)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowed = ['image/jpeg','image/png','image/webp'];
if (!in_array($mime, $allowed, true)) {
    http_response_code(400); echo json_encode(['error'=>'Unsupported file type']); exit;
}

// create safe filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . (int)$_SESSION['user_id'] . '_' . time() . '.' . $ext;
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$target = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    http_response_code(500); echo json_encode(['error'=>'Failed to save file']); exit;
}

// store relative path in DB
$avatarPath = 'uploads/' . $filename;
$stmt = $pdo->prepare("UPDATE users SET avatar = :av WHERE user_id = :uid");
$stmt->execute([':av'=>$avatarPath, ':uid'=> (int)$_SESSION['user_id']]);

echo json_encode(['success'=>true, 'avatar'=>$avatarPath]);
