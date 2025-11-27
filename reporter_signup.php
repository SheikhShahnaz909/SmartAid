<?php
// reporter_signup.php - Reporter Sign Up Form
session_start();
// Redirect already logged-in reporters
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'reporter') { 
    header("Location: reporter_homepage.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Reporter Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        /* CSS styles provided in the original file */
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

         .logo-icon img{
            width:50px;
            height:auto;
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

 /* CORRECTED CSS for the Password Toggle */

.password-container {
    position: relative; 
    
}

.password-container input {
    width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px; 
            background-color: rgba(255, 255, 255, 0.9); 
            color: #333;
            font-size: 1em;
    /* Ensure input takes up the full width, leaving room for the icon */
    padding-right: 45px !important;
    width: 100%; /* Increased padding slightly for better spacing */
}

.password-container label{
    text-align: left;
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95em;
}
.toggle-password {
    position: absolute;
    top: 50%; /* Moves the top edge of the button halfway down the container */
    left: 330px;
    
    /* CRITICAL FIX: Shifts the button up by half its own height to perfectly center it */
    transform: translateY(-30%); 
    /* Styling */
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1em; /* Slightly larger for better visual alignment */
    padding: 0;
    line-height: 0.5;
    color: #666; 
    z-index: 10;
    display: flex; /* Ensure the content (the eye icon) is centered if using different text */
    align-items: center;
    justify-content: center;
    height: 100%; /* Allows translateY(-50%) to be more reliable */
}

.toggle-password:focus {
    outline: none;
}

.toggle-password:hover{
    background:none;
}

        /* Error boxes */
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
    <!-- BACK ARROW -->
<a href="role_selection.html" style="
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(255,255,255,0.2);
    width: 45px;
    height: 45px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    backdrop-filter: blur(5px);
    text-decoration: none;
">
    <span style="
        font-size: 25px;
        color: white;
        font-weight: 900;
    ">&larr;</span>
</a>
    <div class="login-container">
        <div class="logo-area">
            <div class="logo-icon"><img src="images/circle-logo.png"></div>
            <div class="app-name">Smart Aid</div>
            <h2>Create Your Reporter Account</h2>
        </div>

        <div id="email-error-box" class="error-message"></div>

        <form action="signup.php" method="POST" onsubmit="return validateReporterSignupForm()">

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

            <div class="password-container"> 
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required placeholder="Enter your password">
    
    <button type="button" id="togglePassword" class="toggle-password" aria-label="Toggle password visibility">
        👁️
    </button>

    <!-- 🔹 Add this: password error box -->
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
        /* JS validation */
        function displayError(id, msg) {
            const el = document.getElementById(id);
            el.textContent = msg;
            el.style.display = 'block';
        }

        function clearValidationErrors() {
    const ids = ['name-error-box', 'password-error-box', 'phone-error-box', 'email-error-box'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
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

            // Reporter specific email rule
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
            if (!phoneRegex.test(phone.replace(/[^\d]/g, ''))) { // Clean phone for check
                displayError("phone-error-box", "Phone must contain exactly 10 digits.");
                valid = false;
            } else {
                // Normalize phone number before submission
                document.getElementById("phone").value = phone.replace(/[^\d]/g, '');
            }

            return valid;
        }
        document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');

        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', function () {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Change the icon visual
                this.setAttribute('aria-label', type === 'password' ? 'Show password' : 'Hide password');
                this.textContent = (type === 'password') ? '👁️' : '🙈'; 
            });
        }
    });

        
    </script>

</body>
</html>