<?php
// forgot_password.php
require 'config.php';
require 'mailer_config.php';
session_start();

// Decide which login page to go back to
$role = $_GET['role'] ?? null;
$login_page = 'donor_login.php'; // default

if ($role === 'reporter') {
    $login_page = 'reporter_login.php';
} elseif ($role === 'donor') {
    $login_page = 'donor_login.php';
}

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

            // Detect if the user came from donor or reporter login
$role = $_GET['role'] ?? 'donor'; // default to donor if missing

// Build reset link with role
$reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://")
              . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/\\')
              . "/reset_password.php?uid=" . $user['user_id'] . "&token=" . $token . "&role=" . urlencode($role);

              
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root{
            --accent-dark: #114b2b;
            --accent-mid: #1c6f45;
            --card-radius: 14px;
            --max-width-card: 420px; /* New variable for card width */
        }

        *{box-sizing:border-box;margin:0;padding:0}
        html, body { min-height: 100%; }

        body{
            font-family:'Poppins',sans-serif;
            background: url('images/signin-background.jpeg') center/cover no-repeat fixed;
            color: #fff;
            height: 100vh;
            margin: 0;
            display:flex;
            align-items:center; /* CRITICAL FIX: Center card vertically */
            justify-content:center;
            padding:32px;
        }

        .card{
            background:rgba(0,0,0,0.4); /* Slightly darker glass effect */
            padding:30px;
            border-radius:var(--card-radius);
            box-shadow:0 10px 40px rgba(2,20,10,0.5);
            backdrop-filter: blur(8px);
            width:100%;
            max-width:var(--max-width-card); /* Use the card width variable */
            margin: auto; /* Ensure it stays centered */
        }
        
        form {
            width: 100%;
        }

        input{
            width:100%; /* Input must take full width of card */
            padding:12px;
            border-radius:8px;
            border:1px solid #ddd;
            margin-bottom:15px;
            color: #333; /* Dark text for readability */
        }
        
        .btn{
            width:100%; /* CRITICAL FIX: Ensure button fills card width */
            background:var(--accent-mid);
            color:#fff;
            padding:12px 12px;
            border-radius:8px;
            border:none;
            cursor:pointer;
            font-size:1em;
            font-weight:600;
            transition: background 0.3s;
        }
        .btn:hover{
            background:var(--accent-dark);
        }
        
        .msg{
            padding:12px;
            border-radius:8px;
            margin-bottom:15px;
            font-weight:600;
        }
        
        .error{
            background:#fdd;
            color:#a33;
        }
        .success{
            background:#e6ffe6;
            color:#0a5;
        }

        /* Styling for the Back to Login Link */
        .para{
            margin-top:20px;
            text-align:center;
        }
        .para a {
            color: #eee;
            text-decoration: underline; /* Standard link look */
            font-weight: 500;
            transition: color 0.2s;
            background: none; /* CRITICAL FIX: Remove button background */
            padding: 0;
            border-radius: 0;
            cursor: pointer;
            display: block; /* New line for better formatting */
        }
        .para a:hover {
            color: #fff;
            text-decoration: none;
        }
        /* Special class for success message link */
        .msg.success + .para a { 
            color: var(--accent-mid);
            text-decoration: none;
        }
        .msg.success + .para a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="card">
        <h3 style="text-align:center; margin-bottom: 20px;">Reset your password</h3>
        <?php if ($success): ?>
            <div class="msg success">If that email exists in our system, a password reset link has been sent. Check your inbox (and spam).</div>
            
            <div class="para">
                <a href="<?php echo htmlspecialchars($login_page); ?>">Back to login</a>
            </div>

        <?php else: ?>
            <?php if (!empty($errors)): foreach($errors as $e): ?>
                <div class="msg error"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; endif; ?>

            <form method="POST" action="">
                <label for="email" style="display:block; margin-bottom:5px; font-weight:600;">Enter your account email</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required>
                <button class="btn" type="submit">Send reset link</button>
            </form>
            
            <div class="para">
                <a href="<?php echo htmlspecialchars($login_page); ?>">Back to login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
