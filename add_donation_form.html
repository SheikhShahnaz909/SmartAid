<?php
// add_donation_form.php - Donor form to submit a new food donation

session_start();

// Security check: Ensure the user is logged in as a donor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donor') {
    header("Location: donor_login.php?error=auth_required");
    exit();
}

$donor_name = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Add New Donation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* CSS styles adapted from existing files for a consistent look */
        :root {
            --primary-green: #1A733E; 
            --light-green: #E0FFE0;
            --box-bg-color: rgba(255, 255, 255, 0.95); /* Near-white background for the form */
            --dark-text: #114b2b;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #eaf8ef 0%, #f7fff9 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark-text);
        }

        .form-container {
            width: 90%;
            max-width: 500px;
            padding: 30px;
            border-radius: 15px;
            background: var(--box-bg-color);
            box-shadow: var(--box-shadow);
            text-align: left;
        }

        h2 {
            text-align: center;
            color: var(--primary-green);
            margin-bottom: 25px;
            font-weight: 700;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.95em;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 2px var(--light-green);
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #248a4c;
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
    <div class="form-container">
        <h2>Submit Your Donation</h2>
        
        <form action="report_donation.php" method="POST">
            
            <input type="hidden" name="action" value="add_donation">
            <p style="margin-bottom: 20px; font-style: italic; color: #444;">
                Thank you, <?php echo $donor_name; ?>! Please provide details about the surplus food you wish to donate.
            </p>

            <div class="input-group">
                <label for="item_name">Item Name (e.g., Bread Loaves, Canned Beans)</label>
                <input type="text" id="item_name" name="item_name" required>
            </div>

            <div class="input-group">
                <label for="quantity">Quantity (e.g., Number of items/KGs)</label>
                <input type="number" id="quantity" name="quantity" required min="1">
            </div>

            <div class="input-group">
                <label for="expiry_date">Approximate Expiry Date (YYYY-MM-DD)</label>
                <input type="date" id="expiry_date" name="expiry_date" required>
            </div>

            <div class="input-group">
                <label for="pickup_location">Pickup Location (Full Address / Landmark)</label>
                <textarea id="pickup_location" name="pickup_location" rows="3" required></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Submit Donation</button>
        </form>

        <a href="donor_homepage.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>