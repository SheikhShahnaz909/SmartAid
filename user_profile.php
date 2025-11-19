<?php
// user_profile.php - Allows logged-in users to view and update their profile

require 'config.php'; // ADDED: Required for database connection (though not used yet)
session_start();

// Security check: Must be logged in to view profile
if (!isset($_SESSION['user_id'])) {
    header("Location: homepage.html?error=auth_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);
$user_role = htmlspecialchars($_SESSION['user_role']);
$dashboard_link = ($user_role === 'donor') ? 'donor_homepage.php' : 'reporter_homepage.php';

// In a real application, you would fetch all other details (phone, location) from the database here.
// Example:
// $stmt = $pdo->prepare("SELECT phone, location FROM users WHERE user_id = :id");
// $stmt->execute([':id' => $user_id]);
// $details = $stmt->fetch();
// $user_phone = $details['phone'];
// $user_location = $details['location'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - <?php echo $user_name; ?>'s Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* CSS styles adapted for a profile page */
        :root {
            --primary-green: #1A733E; 
            --light-green: #E0FFE0;
            --box-bg-color: rgba(255, 255, 255, 0.95);
            --dark-text: #114b2b;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        /* ... (General styles for body, container, input-group remain similar to forms) ... */
        body {
            background: linear-gradient(180deg, #eaf8ef 0%, #f7fff9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark-text);
        }

        .profile-container {
            width: 90%;
            max-width: 500px;
            padding: 30px;
            border-radius: 15px;
            background: var(--box-bg-color);
            box-shadow: var(--box-shadow);
            text-align: left;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .role-badge {
            display: inline-block;
            background-color: var(--primary-green);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            margin-top: 10px;
            text-transform: capitalize;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.95em;
        }

        .input-group input[type="text"],
        .input-group input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
            background-color: #f9f9f9;
        }

        .save-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h2>Your Profile</h2>
            <span class="role-badge"><?php echo $user_role; ?></span>
        </div>

        <form action="update_profile.php" method="POST"> 
            <div class="input-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo $user_name; ?>" required>
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $user_email; ?>" readonly style="opacity: 0.7;">
            </div>

            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="9876543210"> 
            </div>

            <div class="input-group">
                <label for="location">Location</label>
                 <input type="text" id="location" name="location" value="Mangalore, Karnataka">
            </div>

            <button type="submit" class="save-btn">Save Changes</button>
        </form>

        <a href="<?php echo $dashboard_link; ?>" class="back-link">← Back to Dashboard</a>
        <a href="logout.php" class="back-link" style="color: red; margin-top: 10px;">Sign Out</a>
    </div>
</body>
</html>