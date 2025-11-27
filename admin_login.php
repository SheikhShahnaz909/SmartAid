<?php
// admin_login.php
require_once 'admin_session.php';
require_once 'config.php'; // provides $pdo

// If already logged in, go to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Successful login: regenerate session id and set session vars
                session_regenerate_id(true);
                $_SESSION['admin_id']    = (int)$admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                // optional: store last login time in DB
                $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login — Smart Aid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <style>
    :root{
      --green:#185e34;
      --green-dark:#0f3c23;
      --bg-overlay:rgba(8,32,18,0.55);
      --card-bg:rgba(255,255,255,0.92);
      --border:#d4e9dc;
      --error-bg:#ffebee;
      --error-text:#c62828;
    }
    *{
      box-sizing:border-box;
      font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    }
    body{
      margin:0;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background:
        linear-gradient(var(--bg-overlay),var(--bg-overlay)),
        url('images/signin-background.jpeg') center/cover no-repeat fixed;
      color:#062012;
    }
    .shell{
      width:100%;
      max-width:420px;
      padding:20px;
    }
    .card{
      background:var(--card-bg);
      border-radius:18px;
      padding:22px 22px 18px;
      box-shadow:0 18px 45px rgba(0,0,0,0.25);
      border:1px solid rgba(255,255,255,0.7);
      backdrop-filter:blur(14px);
    }
    .brand{
      display:flex;
      align-items:center;
      gap:10px;
      margin-bottom:12px;
    }
    .logo-dot{
      width:34px;
      height:34px;
      border-radius:999px;
      background:radial-gradient(circle at 30% 30%, #4cd964, #1b5e38);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:700;
      font-size:16px;
      box-shadow:0 6px 14px rgba(8,76,41,0.45);
    }
    .brand-title{
      font-size:20px;
      font-weight:700;
      color:var(--green-dark);
    }
    .brand-sub{
      font-size:12px;
      color:#4d6b59;
    }
    h2{
      margin:4px 0 14px;
      font-size:18px;
      color:var(--green-dark);
    }

    .error{
      background:var(--error-bg);
      color:var(--error-text);
      padding:8px 10px;
      border-radius:9px;
      margin-bottom:10px;
      font-size:13px;
      border:1px solid #f3b5c0;
    }

    form label{
      display:block;
      margin-top:8px;
      margin-bottom:4px;
      font-size:13px;
      font-weight:600;
      color:#234233;
    }
    input[type="email"],
    input[type="password"]{
      width:100%;
      padding:10px 11px;
      border-radius:10px;
      border:1px solid var(--border);
      font-size:14px;
      outline:none;
      background:rgba(250,255,252,0.95);
      transition:border-color 0.18s, box-shadow 0.18s, background 0.18s;
    }
    input[type="email"]:focus,
    input[type="password"]:focus{
      border-color:var(--green);
      box-shadow:0 0 0 2px rgba(24,94,52,0.28);
      background:#ffffff;
    }

    button[type="submit"]{
      margin-top:14px;
      width:100%;
      padding:11px;
      border-radius:999px;
      border:none;
      background:linear-gradient(135deg,#1c6b3c,#0f3c23);
      color:#fff;
      font-weight:600;
      font-size:14px;
      cursor:pointer;
      box-shadow:0 10px 22px rgba(7,56,30,0.55);
      transition:transform 0.08s, box-shadow 0.18s, filter 0.18s;
    }
    button[type="submit"]:hover{
      filter:brightness(1.03);
      box-shadow:0 14px 30px rgba(7,56,30,0.7);
      transform:translateY(-1px);
    }
    button[type="submit"]:active{
      transform:translateY(0);
      box-shadow:0 8px 18px rgba(7,56,30,0.6);
    }

    .footer-text{
      margin-top:14px;
      font-size:12px;
      color:#3c5a48;
      text-align:center;
    }
    .footer-text a{
      color:#0f4e2b;
      text-decoration:none;
      font-weight:600;
    }
    .footer-text a:hover{
      text-decoration:underline;
    }

    @media (max-width:480px){
      .card{
        padding:18px 16px 16px;
      }
      .brand-title{
        font-size:18px;
      }
      body{
        background:
          linear-gradient(var(--bg-overlay),var(--bg-overlay)),
          url('images/signin-background.jpeg') center/cover no-repeat fixed;
      }
    }
  </style>
</head>
<body>
  <div class="shell">
    <div class="card">
      <div class="brand">
        <div class="logo-dot">SA</div>
        <div>
          <div class="brand-title">Smart Aid</div>
          <div class="brand-sub">Admin Console</div>
        </div>
      </div>

      <h2>Admin Sign in</h2>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required placeholder="admin@example.com">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">

        <button type="submit">Login</button>
      </form>

      <div class="footer-text">
        Back to site:
        <a href="homepage.php">Homepage</a>
      </div>
    </div>
  </div>
</body>
</html>
