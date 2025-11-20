<?php
// reset_password.php
require 'config.php';
session_start();

$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
$token = $_GET['token'] ?? '';

$errors = [];
$success = false;
$valid_request = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($uid <= 0 || $token === '') {
        $errors[] = "Invalid reset link.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE user_id = :uid AND used = 0 ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([':uid'=>$uid]);
        $rows = $stmt->fetchAll();

        $found = false;
        foreach ($rows as $row) {
            if (new DateTime() > new DateTime($row['expires_at'])) continue;
            if (password_verify($token, $row['token_hash'])) { $found = $row; break; }
        }
        if (!$found) {
            $errors[] = "This reset link is invalid or has expired.";
        } else {
            $valid_request = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($uid <= 0 || $token === '') {
        $errors[] = "Invalid request.";
    } elseif ($new_password === '' || $new_password !== $confirm) {
        $errors[] = "Passwords must match and not be empty.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
        $errors[] = "Password must be at least 8 characters and include a capital letter, a number, and a symbol.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE user_id = :uid AND used = 0 ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([':uid'=>$uid]);
        $rows = $stmt->fetchAll();
        $found = false;
        $row_id = null;
        foreach ($rows as $row) {
            if (new DateTime() > new DateTime($row['expires_at'])) continue;
            if (password_verify($token, $row['token_hash'])) { $found = $row; $row_id = $row['id']; break; }
        }

        if (!$found) {
            $errors[] = "Reset token invalid or expired.";
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $pdo->beginTransaction();
            try {
                $stmt1 = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE user_id = :uid");
                $stmt1->execute([':ph'=>$new_hash, ':uid'=>$uid]);

                $stmt2 = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
                $stmt2->execute([':id'=>$row_id]);

                $stmt3 = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = :uid AND used = 0");
                $stmt3->execute([':uid'=>$uid]);

                $pdo->commit();
                $success = true;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Could not update the password. Try again.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reset password — Smart Aid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:Inter,Arial;background:#f4fff7;color:#08321b;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .card{background:white;padding:20px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.06);width:420px}
    input{width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;margin-bottom:12px}
    .btn{background:#185e34;color:#fff;padding:10px 12px;border-radius:8px;border:none;cursor:pointer}
    .msg{padding:10px;border-radius:8px;margin-bottom:8px}
    .error{background:#ffecec;color:#a33}
    .success{background:#e6ffef;color:#0a5}
  </style>
</head>
<body>
  <div class="card">
    <h3>Set a new password</h3>

    <?php if (!empty($errors)): foreach ($errors as $e): ?>
      <div class="msg error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; endif; ?>

    <?php if ($success): ?>
      <div class="msg success">Your password has been updated. You can now <a href="donor_login.php">log in</a>.</div>
    <?php elseif ($valid_request || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <form method="POST" action="">
        <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label>New password</label>
        <input type="password" name="password" required placeholder="New password">
        <label>Confirm password</label>
        <input type="password" name="confirm" required placeholder="Confirm password">
        <button class="btn" type="submit">Change password</button>
      </form>
    <?php else: ?>
      <div class="msg error">Invalid reset link or the link has expired.</div>
    <?php endif; ?>
  </div>
</body>
</html>
