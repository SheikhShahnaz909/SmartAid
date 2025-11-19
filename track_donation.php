<?php
// track_donation.php - Donor's active donations tracking page

require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donor') {
    header("Location: donor_login.php?error=auth_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$donor_name = htmlspecialchars($_SESSION['user_name'] ?? 'Donor');
$message = '';
$active_donations = [];

try {
    // Fetch only donations that are NOT delivered yet
    $stmt = $pdo->prepare("SELECT item_name, quantity, pickup_location, status 
                           FROM donations WHERE donor_id = :id AND status != 'Delivered' ORDER BY submission_date ASC");
    $stmt->execute([':id' => $user_id]);
    $active_donations = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Error fetching active donations. Database table might be missing.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Track Donations</title>
    <style>
        /* ... (CSS styles similar to donation_history.php) ... */
        :root {
            --primary-green: #1A733E; 
            --dark-text: #114b2b;
            --muted: #f4f7f5;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--muted);
            color: var(--dark-text);
            padding: 40px 20px;
        }

        .data-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--primary-green);
            margin-bottom: 20px;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary-green);
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f2f8f5;
        }

        .status-pending { color: orange; font-weight: 600; }
        .status-picked { color: blue; font-weight: 600; }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="data-container">
        <h2>Active Donations Being Tracked</h2>
        
        <?php if ($message): ?>
            <p style="color: red; text-align: center;"><?php echo $message; ?></p>
        <?php elseif (empty($active_donations)): ?>
            <p style="text-align: center; color: #555;">You currently have no active donations to track. All past donations are delivered!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Pickup Location</th>
                        <th>Current Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_donations as $donation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($donation['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($donation['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($donation['pickup_location']); ?></td>
                        <td class="status-<?php echo strtolower($donation['status']); ?>">
                            <?php echo htmlspecialchars($donation['status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top: 15px; font-size: 0.9em; color: #555;">Status updates are handled by the Smart Aid administrators.</p>
        <?php endif; ?>

        <a href="donor_homepage.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>