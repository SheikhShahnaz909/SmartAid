<?php
// forgot_password.php
require 'config.php';
require 'mailer_config.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        // Attempt to find user (we won't reveal existence)
        $stmt = $pdo->prepare("SELECT user_id, name, email FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // generate secure token
            $token = bin2hex(random_bytes(24)); // 48 hex chars
            $token_hash = password_hash($token, PASSWORD_DEFAULT);
            $expires_at = date('Y-m-d H:i:s', time() + 60*60); // 1 hour

            // Insert hashed token
            $stmtIns = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :th, :exp)");
            $stmtIns->execute([
                ':uid' => $user['user_id'],
                ':th'  => $token_hash,
                ':exp' => $expires_at
            ]);

            // Build reset link (use full domain in production)
            $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://")
                        . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/\\')
                        . "/reset_password.php?uid=" . $user['user_id'] . "&token=" . $token;

            $subject = "Smart Aid — Password reset request";
            $htmlBody = "<p>Hi " . htmlspecialchars($user['name']) . ",</p>"
                      . "<p>We received a request to reset your Smart Aid password. Click the link below to set a new password. This link will expire in 1 hour.</p>"
                      . "<p><a href=\"" . htmlspecialchars($reset_link) . "\">Reset your password</a></p>"
                      . "<p>If you did not request this, you can safely ignore this email.</p>"
                      . "<p>— Smart Aid team</p>";

            send_email($user['email'], $subject, $htmlBody);
        }

        // Generic success message regardless of whether email exists
        $success = true;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forgot password — Smart Aid</title>
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
    <h3>Reset your password</h3>
    <?php if ($success): ?>
      <div class="msg success">If that email exists in our system, a password reset link has been sent. Check your inbox (and spam).</div>
      <p style="margin-top:12px"><a href="donor_login.php">Back to login</a></p>
    <?php else: ?>
      <?php if (!empty($errors)): foreach($errors as $e): ?>
        <div class="msg error"><?php echo htmlspecialchars($e); ?></div>
      <?php endforeach; endif; ?>

      <form method="POST" action="">
        <label for="email">Enter your account email</label>
        <input id="email" name="email" type="email" placeholder="you@example.com" required>
        <button class="btn" type="submit">Send reset link</button>
      </form>

      <p style="margin-top:12px"><a href="donor_login.php">Back to login</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
