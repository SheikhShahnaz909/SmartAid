<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Donor Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* CSS styles remain the same as the original file */
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

        .logo-img img{
            width: 50px;
            height:50px;
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
            transition: background-color 0.3s;
        }

        .input-group input:focus {
            outline: none;
            background-color: #fff;
            box-shadow: 0 0 0 3px var(--light-green);
        }

        .input-group input::placeholder {
            color: #999;
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
            transition: background-color 0.3s, transform 0.1s;
        }

        .log-in-btn:hover {
            background-color: #248a4c;
        }

        .log-in-btn:active {
            transform: scale(0.98);
        }

        .links {
            margin-top: 20px;
            font-size: 0.9em;
        }

        .links a {
            color: var(--light-green);
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #fff;
            text-decoration: underline;
        }

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
            <div class="logo-img"><img src="images/circle-logo.png" alt="Smart Aid Logo"></div>
            <div class="app-name">Smart Aid</div>
            <h2>Create Your Donor Account</h2>
        </div>

        <div id="url-error-box" class="error-message"></div>

        <form action="signup.php" method="POST" onsubmit="return validateDonorSignupForm()">           
            
            <input type="hidden" name="role" value="donor"> <div class="input-group">
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
                <label for="location">Location</label>
                <input type="text" id="location" name="location" placeholder="Residence, City, State/Country">
            </div>
            
            <button type="submit" class="log-in-btn">Sign Up</button>
        </form>

        <div class="links">
            <a href="donor_login.html">Already have an account? Log In</a>
        </div>
    </div>
    <script>
        // Function to display a specific error message
        function displayError(boxId, message) {
            const errorBox = document.getElementById(boxId);
            errorBox.textContent = message;
            errorBox.style.display = 'block';
        }

        // Function to clear all validation error messages
        function clearValidationErrors() {
            document.getElementById('name-error-box').style.display = 'none';
            document.getElementById('password-error-box').style.display = 'none';
            document.getElementById('phone-error-box').style.display = 'none';
            document.getElementById('url-error-box').textContent = '';
            document.getElementById('url-error-box').style.display = 'none';
        }

        // Client-side validation function for donor sign-up
        function validateDonorSignupForm() {
            clearValidationErrors();
            let isValid = true;

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const phone = document.getElementById('phone').value.trim();

            // 1. Name Validation (Letters only, allowing spaces, hyphens, and apostrophes)
            const nameRegex = /^[a-zA-Z\s'-]+$/;
            if (name.length === 0 || !nameRegex.test(name)) {
                displayError("name-error-box", "Name is required and must only contain letters, spaces, hyphens, or apostrophes.");
                isValid = false;
            }

            // 2. Email Validation (Must contain @ and end with gmail.com or hotmail.com)
            const emailRegex = /@(\b(gmail|hotmail)\.com\b)$/i; 
            if (email.length === 0 || !emailRegex.test(email)) {
                displayError("url-error-box", "Email is required and must end with 'gmail.com' or 'hotmail.com'.");
                isValid = false;
            }

            // 3. Password Validation (Min 8 chars, at least one capital, one number, one symbol)
            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (password.length === 0 || !passwordRegex.test(password)) {
                displayError("password-error-box", "Password is required and must be at least 8 characters long and contain: one capital letter, one number, and one symbol (e.g., !@#$).");
                isValid = false;
            }

            // 4. Phone Number Validation (Exactly 10 digits)
            const phoneRegex = /^\d{10}$/; 
            const cleanPhone = phone.replace(/[^\d]/g, ''); 
            if (cleanPhone.length === 0 || !phoneRegex.test(cleanPhone)) {
                displayError("phone-error-box", "Phone number is required and must contain exactly 10 digits.");
                isValid = false;
            }

            return isValid;
        }

        // Script to display server-side signup error messages from URL parameters (original logic)
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const error = params.get('error');
            const successEmail = params.get('email');
            const errorBox = document.getElementById('url-error-box');

            if (error && errorBox) {
                 if(error === 'exists') {
                    errorBox.textContent = "An account with this email already exists.";
                 } else if (error === 'db_fail') {
                    errorBox.textContent = "Sign up failed due to a server error. Please try again.";
                 } else {
                     // Catch-all for other unhandled errors like invalid_name, invalid_phone etc.
                     errorBox.textContent = "Sign up failed due to invalid or missing data.";
                 }
                 errorBox.style.display = 'block';
            }
        });
    </script>
</body>
</html>