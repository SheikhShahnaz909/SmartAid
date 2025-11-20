<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: donor_login.php'); exit; }
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['aadhaar'])) {
  header('Location: donor_profile.php?error=upload');
  exit;
}

$allowed = ['image/jpeg','image/png','application/pdf'];
$file = $_FILES['aadhaar'];
if ($file['error'] !== 0 || !in_array($file['type'],$allowed)) {
  header('Location: donor_profile.php?error=filetype');
  exit;
}

// create uploads dir if not exists
$dir = __DIR__.'/uploads/aadhaar';
if (!is_dir($dir)) mkdir($dir, 0700, true);

// move file, create unique name
$fname = $userId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
$dest = $dir . '/' . $fname;
move_uploaded_file($file['tmp_name'], $dest);

// store path (consider encrypting the file at rest)
$pdo->prepare("UPDATE users SET aadhaar_uploaded_file = :path, verification_status = 'pending', consent_aadhaar = 1 WHERE user_id = :id")
    ->execute([':path'=>$fname, ':id'=>$userId]);

header('Location: donor_profile.php?msg=uploaded');
exit;
