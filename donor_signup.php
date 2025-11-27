<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Smart Aid - Donor Sign Up</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* (kept your styling, slight cleanup) */
    :root{
      --primary-green:#1A733E;
      --light-green:#E0FFE0;
      --box-bg:rgba(255,255,255,0.12);
      --text:#fff;
    }
    *{box-sizing:border-box;font-family:'Poppins',sans-serif}
    body{
      margin:0;
      min-height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      background:var(--primary-green) url('images/signin-background.jpeg') center/cover no-repeat;
    }
    .login-container{
      width:95%;max-width:480px;padding:34px;border-radius:18px;background:var(--box-bg);backdrop-filter:blur(6px);
      border:1px solid rgba(255,255,255,0.18);color:var(--text);
      box-shadow:0 10px 30px rgba(0,0,0,0.15);
    }
    .logo-img img{width:50px;height:50px}
    h2{margin-top:10px;font-size:20px}
    .input-group{margin:12px 0}
    label{display:block;margin-bottom:6px;font-weight:600}
    input[type=text],input[type=email],input[type=password],input[type=tel]{
      width:100%;padding:10px;border-radius:8px;border:none;font-size:15px;
    }
    .log-in-btn{width:100%;
      padding:12px;
      border-radius:10px;
      border:none;
      background:var(--primary-green);
      color:#fff;
      font-weight:700;
      margin-top:12px;
      cursor:pointer;
    }
    .links{
      margin-top:12px;
      text-align:center;
    }

    /* CORRECTED CSS for the Password Toggle */

.password-container {
    position: relative; 
    
}

.password-container input {
    /* Ensure input takes up the full width, leaving room for the icon */
    padding-right: 45px !important;
    width: 100%; /* Increased padding slightly for better spacing */
}

.toggle-password {
    position: absolute;
    top: 50%; /* Moves the top edge of the button halfway down the container */
    left: 370px;
    
    /* CRITICAL FIX: Shifts the button up by half its own height to perfectly center it */
    transform: translateY(-85%); 
    
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
    .error-message{display:none;background:rgba(255,0,0,0.35);padding:8px;border-radius:8px;margin-top:8px}
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
    <div style="text-align:center">
      <div class="logo-img"><img src="images/circle-logo.png" alt="Smart Aid"></div>
      <div style="font-weight:800;font-size:18px">Smart Aid</div>
      <h2>Create your donor account</h2>
    </div>

    <div id="url-error-box" class="error-message" role="alert"></div>

    <form id="signupForm" action="signup.php" method="POST" onsubmit="return validateDonorSignupForm()">
      <input type="hidden" name="role" value="donor">

      <div class="input-group">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" required placeholder="Full name">
        <div id="name-error-box" class="error-message"></div>
      </div>

      <div class="input-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required placeholder="name@example.com">
      </div>

      <div class="password-container"> 
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Enter your password">
        
        <button type="button" id="togglePassword" class="toggle-password" aria-label="Toggle password visibility">
            👁️
        </button>

      <div class="input-group">
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" type="tel" required placeholder="1234567890">
        <div id="phone-error-box" class="error-message"></div>
      </div>

      <div class="input-group">
        <label for="location">Location</label>
        <input id="location" name="location" type="text" placeholder="City, State">
      </div>

      <button type="submit" class="log-in-btn">Sign Up</button>
    </form>

    <div class="links"><a href="donor_login.php" style="color:#fff;text-decoration:none">Already have an account? Log in</a></div>
  </div>

  <script>
    function displayError(id, msg){
      const el = document.getElementById(id);
      el.textContent = msg;
      el.style.display = 'block';
    }
    function clearErrors(){
      ['name-error-box','password-error-box','phone-error-box','url-error-box'].forEach(id=>{
        const e=document.getElementById(id); if (e) e.style.display='none';
      });
    }

    function validateDonorSignupForm(){
      clearErrors();
      let ok = true;
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const phone = document.getElementById('phone').value.replace(/[^\d]/g,'');

      // name
      if (!/^[a-zA-Z\s'-]{2,}$/.test(name)) {
        displayError('name-error-box','Enter a valid name (letters, spaces allowed).');
        ok = false;
      }

      // email (allow all valid emails)
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        displayError('url-error-box','Enter a valid email.');
        ok = false;
      }

      // password
      if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password)) {
        displayError('password-error-box','Password must be ≥8 chars, include a capital letter, a number and a symbol.');
        ok = false;
      }

      // phone
      if (!/^\d{10}$/.test(phone)) {
        displayError('phone-error-box','Phone must be exactly 10 digits.');
        ok = false;
      } else {
        // copy cleaned phone back into the field (so server gets normalized)
        document.getElementById('phone').value = phone;
      }

      return ok;
    }

    // show errors from server via query string
    (function(){
      const params = new URLSearchParams(location.search);
      const error = params.get('error');
      if (error) {
        const box = document.getElementById('url-error-box');
        if (error === 'exists') box.textContent = 'An account with this email already exists.';
        else if (error === 'db' || error === 'db_fail') box.textContent = 'Server error while signing up. Try again.';
        else box.textContent = 'Sign up failed: ' + error;
        box.style.display = 'block';
      }
    })();
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
