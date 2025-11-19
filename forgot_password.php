<?php
// forgot_password.php - Form to request a password reset email
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* CSS styles adapted from login/signup for consistency */
        :root {
            --primary-green: #1A733E; 
            --light-green: #E0FFE0;
            --box-bg-color: rgba(255, 255, 255, 0.95);
            --dark-text: #114b2b;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        /* ... (General styles for body, container, input-group remain similar) ... */
        body {
            background: linear-gradient(180deg, #eaf8ef 0%, #f7fff9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark-text);
        }

        .reset-container {
            width: 90%;
            max-width: 400px;
            padding: 30px;
            border-radius: 15px;
            background: var(--box-bg-color);
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        h2 {
            color: var(--primary-green);
            margin-bottom: 10px;
        }

        p {
            color: #555;
            margin-bottom: 25px;
            font-size: 0.9em;
        }

        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .input-group input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Forgot Password?</h2>
        <p>Enter your account email address to receive a password reset link.</p>

        <form action="send_reset_link.php" method="POST"> 
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com">
            </div>

            <button type="submit" class="submit-btn">Send Reset Link</button>
        </form>

        <a href="donor_login.php" class="back-link">← Back to Login</a>
    </div>
</body>
</html>