<?php
// reporter_login.php
session_start();
require 'config.php';

// If already logged in -> redirect
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'reporter') {
    header("Location: reporter_homepage.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Please enter both email and password.";
    } else {
        // Fetch the reporter
        $stmt = $pdo->prepare("
            SELECT user_id, name, email, password_hash 
            FROM users 
            WHERE email = :email AND role = 'reporter' LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            
            session_regenerate_id(true);

            // 🔥 Correct session format (used by all reporter pages)
            $_SESSION['user_id']    = (int)$user['user_id'];
            $_SESSION['user_role']  = 'reporter';
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name']  = $user['name'];

            header("Location: reporter_homepage.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reporter Login — Smart Aid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root{ --accent-dark:#114b2b;--accent-mid:#1c6f45; }
    body{font-family:'Poppins',sans-serif;background:url('images/signin-background.jpeg') center/cover fixed;
         display:flex;justify-content:center;align-items:center;height:100vh;color:white;margin:0;}
    .card{background:rgba(0,0,0,0.4);padding:30px;border-radius:14px;width:100%;max-width:420px;backdrop-filter:blur(8px);}
    input{width:100%;padding:10px;border-radius:8px;border:none;margin-bottom:10px;}
    .btn{width:100%;padding:11px;background:var(--accent-mid);border:none;border-radius:8px;
         cursor:pointer;font-weight:600;color:white;}
    .btn:hover{background:var(--accent-dark);}
    .msg{background:#fdd;color:#b22;padding:10px;border-radius:8px;margin-bottom:10px;}
  </style>
</head>
<body>
<div class="card">
  <h3 style="text-align:center;">Reporter Login</h3>

  <?php foreach($errors as $e): ?>
      <div class="msg"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button class="btn">Login</button>
  </form>

  <div style="margin-top:12px;text-align:center;">
      <a href="forgot_password.php?role=reporter" style="color:white;">Forgot password?</a> |
      <a href="reporter_signup.php" style="color:white;">Sign Up</a>
  </div>
</div>
</body>
</html>
