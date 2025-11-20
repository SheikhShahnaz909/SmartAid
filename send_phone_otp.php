<?php
require 'config.php';
session_start();
$phone = preg_replace('/\D/','', $_POST['phone'] ?? '');
if (!$phone) { http_response_code(400); echo json_encode(['error'=>'no phone']); exit; }

$otp = random_int(100000, 999999);
$otp_hash = password_hash($otp, PASSWORD_DEFAULT);
$expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

$stmt = $pdo->prepare("INSERT INTO phone_otps (phone,otp_hash,expires_at) VALUES (:p,:h,:e)");
$stmt->execute([':p'=>$phone, ':h'=>$otp_hash, ':e'=>$expires]);

// SEND SMS: integrate provider here
// For dev: write OTP to server log file (never show to user in prod)
file_put_contents(__DIR__.'/logs/otps.log', date('c')." $phone => $otp\n", FILE_APPEND);

// return success
echo json_encode(['ok'=>true]);
