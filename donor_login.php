<?php
session_start();
require 'config.php';

if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'donor') {
    header("Location: donor_homepage.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT user_id, name, email, password_hash 
                           FROM users WHERE email=:email AND role='donor' LIMIT 1");
    $stmt->execute([':email'=>$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        session_regenerate_id(true);

        $_SESSION['user_id']    = (int)$user['user_id'];
        $_SESSION['user_role']  = 'donor';
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name']  = $user['name'];

        header("Location: donor_homepage.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Donor Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{
    background:url('images/signin-background.jpeg') center/cover fixed;
    display:flex;justify-content:center;align-items:center;
    height:100vh;margin:0;font-family:Poppins,sans-serif;
}
.container{
    background:rgba(255,255,255,0.2);
    padding:35px;border-radius:20px;backdrop-filter:blur(10px);
    width:90%;max-width:420px;color:white;text-align:center;
}
input{width:90%;padding:10px;border-radius:8px;border:none;margin-top:10px;}
.password-container{position:relative;}
.toggle-password {
    position:absolute;right:18px;top:50%;transform:translateY(-50%);
    cursor:pointer;color:white;
}
button{width:100%;padding:12px;margin-top:15px;background:#3e7755;
    border:none;color:white;border-radius:8px;font-size:1.1em;}
button:hover{background:#248a4c;}
.error{background:rgba(255,0,0,0.4);padding:10px;border-radius:8px;margin-top:10px;}
</style>
</head>
<body>

<div class="container">
    <h2>Donor Login</h2>

    <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>

        <div class="password-container">
            <input type="password" id="donorPassword" name="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePass('donorPassword', this)">👁️</span>
        </div>

        <button type="submit">Login</button>
    </form>

    <p style="margin-top:10px;">
        <a href="forgot_password.php?role=donor" style="color:white;text-decoration:underline;">Forgot password?</a><br>
        <a href="donor_signup.php" style="color:white;text-decoration:underline;">Create Account</a>
    </p>
</div>


<script>
function togglePass(id, el){
    let field = document.getElementById(id);
    if(field.type === "password"){
        field.type = "text";
        el.textContent = "🙈";
    } else {
        field.type = "password";
        el.textContent = "👁️";
    }
}
</script>

</body>
</html>
