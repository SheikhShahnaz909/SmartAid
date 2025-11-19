<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Reporter Login</title>
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-area">
            <div class="logo-icon"><img src="images/circle-logo.png"></div>
            <div class="app-name">Smart Aid</div>
            <h2>Reporter Login</h2>
        </div>

        <form action="login.php" method="POST">
           <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="name@example.com">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <input type="hidden" name="role" value="reporter"> <button type="submit" class="log-in-btn">Log In as Reporter</button>
        </form>

        <div class="links">
            <a href="#">Forgot Password?</a>
            <a href="reporter_signup.html">New Reporter? Sign Up</a>
        </div>
    </div>
</body>
</html>