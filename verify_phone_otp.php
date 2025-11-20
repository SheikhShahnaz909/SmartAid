<?php
require 'config.php';
session_start();
$phone = preg_replace('/\D/','', $_POST['phone'] ?? '');
$otp = $_POST['otp'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM phone_otps WHERE phone=:p ORDER BY created_at DESC LIMIT 1");
$stmt->execute([':p'=>$phone]);
$row = $stmt->fetch();
if (!$row) { echo json_encode(['error'=>'no_otp']); exit; }
if (new DateTime() > new DateTime($row['expires_at'])) { echo json_encode(['error'=>'expired']); exit; }

if (!password_verify($otp, $row['otp_hash'])) {
  // increment attempts
  $pdo->prepare("UPDATE phone_otps SET attempts = attempts + 1 WHERE otp_id = :id")->execute([':id'=>$row['otp_id']]);
  echo json_encode(['error'=>'wrong']); exit;
}

// mark used
$pdo->prepare("UPDATE phone_otps SET used=1 WHERE otp_id = :id")->execute([':id'=>$row['otp_id']]);

// mark user phone_verified if user exists with phone
$pdo->prepare("UPDATE users SET phone_verified = 1 WHERE phone = :p")->execute([':p'=>$phone]);

echo json_encode(['ok'=>true]);
