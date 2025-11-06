<?php
// reporter_signup.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Reporter Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        /* ✅ Original CSS preserved (from reporter_signup.html) :contentReference[oaicite:1]{index=1}*/
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        :root {
            --primary-green: #1A733E; 
            --light-green: #E0FFE0;
            --box-bg-color: rgba(255, 255, 255, 0.2); 
            --box-shadow-color: rgba(0, 0, 0, 0.1); 
            --text-color: #ffffff; 
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--primary-green);
            background-image: url(images/signin-background.jpeg); 
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 90%;
            max-width: 450px;
            padding: 40px;
            border-radius: 20px;
            background: var(--box-bg-color);
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px var(--box-shadow-color);
            text-align: center;
            color: var(--text-color);
        }

        .logo-area {
            margin-bottom: 30px;
        }

        .logo-icon {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            background-color: var(--primary-green);
            border-radius: 10px;
            font-size: 1.5em;
            font-weight: 700;
            color: var(--light-green);
            margin-bottom: 5px;
        }

        .app-name {
            font-size: 1.2em;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            text-align: left;
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95em;
        }

        .input-group input[type="text"],
        .input-group input[type="email"],
        .input-group input[type="password"],
        .input-group input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px; 
            background-color: rgba(255, 255, 255, 0.9); 
            color: #333;
            font-size: 1em;
        }

        .input-group input:focus {
            outline: none;
            background-color: #fff;
            box-shadow: 0 0 0 3px var(--light-green);
        }

        .log-in-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .links {
            margin-top: 20px;
            font-size: 0.9em;
        }

        .links a {
            color: var(--light-green);
            text-decoration: none;
        }

        .links a:hover {
            color: #fff;
            text-decoration: underline;
        }

        /* Error boxes (from original file) :contentReference[oaicite:2]{index=2} */
        .error-message {
            color: var(--text-color);
            font-weight: 600;
            margin-top: 5px;
            background: rgba(255, 0, 0, 0.4);
            padding: 8px;
            border-radius: 8px;
            display: none;
            font-size: 0.95em;
            text-align: left;
        }
    </style>

</head>
<body>

    <div class="login-container">
        <div class="logo-area">
            <div class="logo-icon">SA</div>
            <div class="app-name">Smart Aid</div>
            <h2>Create Your Reporter Account</h2>
        </div>

        <div id="email-error-box" class="error-message"></div>

        <!-- ✅ Backend action updated -->
        <form action="signup.php" method="POST" onsubmit="return validateReporterSignupForm()">

            <!-- Hidden role field required for backend -->
            <input type="hidden" name="role" value="reporter">

            <div class="input-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name">
                <div id="name-error-box" class="error-message"></div>
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Min 8 chars, incl. capital, number, symbol">
                <div id="password-error-box" class="error-message"></div>
            </div>

            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="(+91) 1234567890">
                <div id="phone-error-box" class="error-message"></div>
            </div>

            <div class="input-group">
                <label for="location">City/Region of Reporting</label>
                <input type="text" id="location" name="location" placeholder="City, State/Country">
            </div>

            <button type="submit" class="log-in-btn">Sign Up as Reporter</button>
        </form>

        <div class="links">
            <a href="reporter_login.php">Already have an account? Log In</a>
        </div>
    </div>

    <script>
        /* ✅ Original JS validation preserved (from reporter_signup.html) :contentReference[oaicite:3]{index=3} */
        function displayError(id, msg) {
            const el = document.getElementById(id);
            el.textContent = msg;
            el.style.display = 'block';
        }

        function clearValidationErrors() {
            document.getElementById('name-error-box').style.display = 'none';
            document.getElementById('password-error-box').style.display = 'none';
            document.getElementById('phone-error-box').style.display = 'none';
            document.getElementById('email-error-box').style.display = 'none';
        }

        function validateReporterSignupForm() {
            clearValidationErrors();
            let valid = true;

            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;
            const phone = document.getElementById("phone").value.trim();

            const nameRegex = /^[a-zA-Z\s'-]+$/;
            if (!nameRegex.test(name)) {
                displayError("name-error-box", "Name is required and must contain only letters.");
                valid = false;
            }

            const emailRegex = /@(\b(gmail|hotmail)\.com\b)$/i;
            if (!emailRegex.test(email)) {
                displayError("email-error-box", "Email must end with gmail.com or hotmail.com.");
                valid = false;
            }

            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (!passwordRegex.test(password)) {
                displayError("password-error-box", "Password must have 8 chars, capital, number, symbol.");
                valid = false;
            }

            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(phone)) {
                displayError("phone-error-box", "Phone must contain exactly 10 digits.");
                valid = false;
            }

            return valid;
        }
    </script>

</body>
</html>
