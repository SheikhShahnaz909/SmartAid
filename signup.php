<?php
// signup.php — handles reporter signup (and optionally donor)
session_start();
require 'config.php'; // must define $pdo (PDO connection)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: role_selection.html");
    exit();
}

// 1. Get form data
$role      = trim($_POST['role'] ?? '');      // "reporter" from hidden input
$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$phone     = trim($_POST['phone'] ?? '');
$location  = trim($_POST['location'] ?? '');

// 2. Basic server-side validation
$errors = [];

if ($role !== 'reporter' && $role !== 'donor') {
    $errors[] = "Invalid role.";
}

if ($name === '') {
    $errors[] = "Name is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

// Same rule as your JS
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
    $errors[] = "Password must have at least 8 characters, include a capital letter, a number, and a symbol.";
}

// ensure phone is 10 digits
$phone_digits = preg_replace('/\D/', '', $phone);
if (strlen($phone_digits) !== 10) {
    $errors[] = "Phone must contain exactly 10 digits.";
}

if (!empty($errors)) {
    echo "<h3>Signup error</h3>";
    foreach ($errors as $e) {
        echo "<p>" . htmlspecialchars($e) . "</p>";
    }
    echo '<p><a href="reporter_signup.php">Go back</a></p>';
    exit();
}

// 3. CHECK IF EMAIL ALREADY EXISTS (regardless of role)
//   Your DB has UNIQUE(email), so email can only appear once total.
$stmt = $pdo->prepare("SELECT user_id, role FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$existing = $stmt->fetch();

if ($existing) {
    // Friendly message instead of fatal error
    echo "<h3>Signup error</h3>";
    if ($existing['role'] === 'reporter') {
        echo "<p>An account with this email already exists as a reporter. Please <a href='reporter_login.php'>log in here</a> or use a different email.</p>";
    } else {
        echo "<p>An account with this email already exists. Please use a different email or log in with your existing account.</p>";
    }
    echo '<p><a href="reporter_signup.php">Back to signup</a></p>';
    exit();
}

// 4. Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 5. Insert into users table
$sql = "INSERT INTO users (name, email, password_hash, phone, location, role, status, created_at)
        VALUES (:name, :email, :password_hash, :phone, :location, :role, 'active', NOW())";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'          => $name,
        ':email'         => $email,
        ':password_hash' => $password_hash,
        ':phone'         => $phone_digits,
        ':location'      => $location,
        ':role'          => $role
    ]);
} catch (PDOException $e) {
    // Just in case the UNIQUE constraint still triggers for some reason
    if ($e->getCode() === '23000') { // integrity constraint violation
        
    }
    throw $e; // unknown error, rethrow
}

// 6. Log the user in immediately
$user_id = $pdo->lastInsertId();

session_regenerate_id(true);
$_SESSION['user_id']   = $user_id;
$_SESSION['user_role'] = $role;
$_SESSION['user_name'] = $name;

// 7. Redirect to correct homepage
if ($role === 'reporter') {
    header("Location: reporter_homepage.php");
} else {
    header("Location: donor_homepage.php");
}
exit();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Signup Error — Smart Aid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body{
            font-family:'Poppins',sans-serif;
            background:url('images/signin-background.jpeg') center/cover no-repeat fixed;
            margin:0;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            padding:20px;
            color:#fff;
        }

        .card{
            background:rgba(0,0,0,0.45);
            padding:30px;
            width:100%;
            max-width:450px;
            border-radius:16px;
            text-align:center;
            backdrop-filter:blur(10px);
            box-shadow:0 10px 40px rgba(0,0,0,0.5);
        }

        h2{
            font-size:1.5rem;
            margin-bottom:10px;
            font-weight:600;
            color:#ffcccc;
        }

        p{
            font-size:0.97rem;
            margin-bottom:18px;
            line-height:1.45;
        }

        .btn{
            background:#ff4d4d;
            padding:12px 18px;
            border-radius:8px;
            color:#fff;
            text-decoration:none;
            font-weight:600;
            display:inline-block;
            transition:0.3s;
        }

        .btn:hover{
            background:#d93636;
        }

        .alt{
            margin-top:12px;
            font-size:0.85rem;
        }

        .alt a{
            color:#caffd1;
            text-decoration:underline;
        }

        .alt a:hover{
            text-decoration:none;
        }

        .icon{
            font-size:50px;
            margin-bottom:8px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h2>Signup Failed</h2>
        <p>An account with this email already exists.<br>Try a different email or log in with your existing account.</p>
        
        <a href="reporter_signup.php" class="btn">⬅ Back to Signup</a>

        <div class="alt">
            Already signed up? <a href="reporter_login.php">Log In</a>
        </div>
    </div>
</body>
</html>
<?php
exit();

