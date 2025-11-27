<?php
// reset_password.php
require 'config.php';
session_start();

// Decide which login page to go to based on role in query string
$role = $_GET['role'] ?? '';
$login_page = 'donor_login.php'; // default
if ($role === 'reporter') {
    $login_page = 'reporter_login.php';
} elseif ($role === 'donor') {
    $login_page = 'donor_login.php';
}

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
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    :root{
      --primary-green:#1A733E;
      --primary-green-dark:#0f4b29;
      --bg-soft:#eaf8ef;
      --card-bg:#ffffff;
      --error-bg:#ffecec;
      --error-text:#a33;
      --success-bg:#e6ffef;
      --success-text:#0a5;
    }

    *{
      box-sizing:border-box;
      margin:0;
      padding:0;
      font-family:'Poppins',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    }

    body{
      background:radial-gradient(circle at top,#d4f5e3 0,#f7fff9 45%,#eef5ff 100%);
      color:#08321b;
      display:flex;
      align-items:center;
      justify-content:center;
      min-height:100vh;
      padding:20px;
    }

    .card{
      background:var(--card-bg);
      padding:24px 22px 20px;
      border-radius:16px;
      box-shadow:0 14px 40px rgba(0,0,0,0.08);
      width:100%;
      max-width:430px;
      border:1px solid rgba(26,115,62,0.08);
      position:relative;
      overflow:hidden;
    }

    .card::before{
      content:"";
      position:absolute;
      inset:0;
      background:linear-gradient(135deg,rgba(26,115,62,0.12),rgba(255,255,255,0.9));
      opacity:0.7;
      z-index:-1;
    }

    h3{
      margin-bottom:8px;
      font-size:1.3rem;
      font-weight:600;
      color:var(--primary-green-dark);
    }

    .subtitle{
      font-size:0.9rem;
      color:#5a7266;
      margin-bottom:16px;
    }

    input{
      width:100%;
      padding:10px 11px;
      border-radius:9px;
      border:1px solid #d4e4d9;
      margin-bottom:12px;
      font-size:0.96rem;
      background:#fbfffc;
      transition:border 0.18s,box-shadow 0.18s,background 0.18s;
    }

    input:focus{
      outline:none;
      border-color:var(--primary-green);
      box-shadow:0 0 0 2px rgba(26,115,62,0.17);
      background:#ffffff;
    }

    label{
      display:block;
      margin:6px 0 4px;
      font-size:0.9rem;
      font-weight:600;
      color:#18462b;
    }

    .btn{
      background:linear-gradient(135deg,var(--primary-green),var(--primary-green-dark));
      color:#fff;
      padding:10px 12px;
      border-radius:999px;
      border:none;
      cursor:pointer;
      font-weight:600;
      font-size:0.96rem;
      width:100%;
      margin-top:8px;
      box-shadow:0 8px 18px rgba(17,75,43,0.3);
      transition:transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s;
    }

    .btn:hover{
      transform:translateY(-1px);
      box-shadow:0 10px 22px rgba(17,75,43,0.34);
      filter:brightness(1.03);
    }

    .btn:active{
      transform:translateY(0);
      box-shadow:0 4px 10px rgba(17,75,43,0.3);
    }

    .msg{
      padding:10px 11px;
      border-radius:9px;
      margin-bottom:8px;
      font-size:0.9rem;
    }

    .error{
      background:var(--error-bg);
      color:var(--error-text);
      border:1px solid rgba(163,51,51,0.35);
    }

    .success{
      background:var(--success-bg);
      color:var(--success-text);
      border:1px solid rgba(10,165,80,0.35);
    }

    .msg.success a{
      color:var(--primary-green-dark);
      font-weight:600;
      text-decoration:none;
    }

    .msg.success a:hover{
      text-decoration:underline;
    }

    @media (max-width:480px){
      .card{
        padding:20px 16px 16px;
        border-radius:14px;
      }
      h3{
        font-size:1.15rem;
      }
    }
  </style>
</head>
<body>
  <div class="card">
    <h3>Set a new password</h3>
    <p class="subtitle">Choose a strong password to keep your Smart Aid account secure.</p>

    <?php if (!empty($errors)): foreach ($errors as $e): ?>
      <div class="msg error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; endif; ?>

    <?php if ($success): ?>
      <div class="msg success">
        Your password has been updated. You can now
        <a href="<?php echo htmlspecialchars($login_page); ?>">log in</a>.
      </div>
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
