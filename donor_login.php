<?php
session_start();

// If already logged in as donor, redirect
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'donor') {
    header("Location: donor_homepage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Donor Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        /* Your existing CSS — unchanged */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: url('images/signin-background.jpeg') no-repeat center center fixed;
            background-size: cover;
        }

        .login-container {
            width: 90%;
            max-width: 450px;
            margin: 100px auto;
            padding: 40px;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            border: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #1A733E;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
        }

        button:hover {
            background-color: #248a4c;
        }

        a {
            color: #E0FFE0;
        }

        .error {
            background: rgba(255,0,0,0.4);
            padding: 8px;
            border-radius: 8px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Donor Login</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Invalid Email or Password</div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <!-- IMPORTANT: Hidden role field -->
        <input type="hidden" name="role" value="donor">

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Log In</button>
    </form>

    <p><a href="forgot_password.php">Forgot Password?</a></p>
    <p><a href="donor_signup.php">Create Account</a></p>
</div>

</body>
</html>
