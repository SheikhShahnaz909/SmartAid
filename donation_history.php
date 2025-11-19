<?php
// donation_history.php - Donor's history of past donations

require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donor') {
    header("Location: donor_login.php?error=auth_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$donor_name = htmlspecialchars($_SESSION['user_name'] ?? 'Donor');
$message = '';
$history = [];

try {
    // NOTE: This SQL assumes a 'donations' table with a 'donor_id' column.
    $stmt = $pdo->prepare("SELECT item_name, quantity, status, DATE_FORMAT(submission_date, '%Y-%m-%d') as date 
                           FROM donations WHERE donor_id = :id ORDER BY submission_date DESC");
    $stmt->execute([':id' => $user_id]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Error fetching history. Database table might be missing.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Donation History</title>
    <style>
        :root {
            --primary-green: #1A733E; 
            --dark-text: #114b2b;
            --muted: #f4f7f5;
        }

        /* ... (General styles for body, container, etc. similar to dashboards) ... */
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
        .status-delivered { color: green; font-weight: 600; }
        .status-picked { color: blue; }

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
        <h2><?php echo $donor_name; ?>'s Donation History</h2>
        
        <?php if ($message): ?>
            <p style="color: red; text-align: center;"><?php echo $message; ?></p>
        <?php elseif (empty($history)): ?>
            <p style="text-align: center; color: #555;">You have not submitted any donations yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $donation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($donation['date']); ?></td>
                        <td><?php echo htmlspecialchars($donation['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($donation['quantity']); ?></td>
                        <td class="status-<?php echo strtolower($donation['status']); ?>">
                            <?php echo htmlspecialchars($donation['status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="donor_homepage.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>