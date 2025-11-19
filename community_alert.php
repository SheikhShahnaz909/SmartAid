<?php
// community_alerts.php - Shows general community needs/donations (can be filtered by location)

require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reporter') {
    header("Location: reporter_login.php?error=auth_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$reporter_name = htmlspecialchars($_SESSION['user_name'] ?? 'Reporter');
$message = '';
$alerts = [];

try {
    // NOTE: This SQL simulates alerts by fetching all pending reports and donations.
    // In a real app, this would use a more complex JOIN and location filtering.
    
    // Fetch pending reports
    $stmt_reports = $pdo->prepare("SELECT report_type as type, description, location, 'Need' as category, submission_date FROM reports WHERE status IN ('New', 'InProgress') ORDER BY submission_date DESC LIMIT 5");
    $stmt_reports->execute();
    $alerts = $stmt_reports->fetchAll();

    // Fetch pending donations
    $stmt_donations = $pdo->prepare("SELECT item_name as type, quantity as description, pickup_location as location, 'Donation' as category, submission_date FROM donations WHERE status IN ('Pending', 'PickedUp') ORDER BY submission_date DESC LIMIT 5");
    $stmt_donations->execute();
    $alerts = array_merge($alerts, $stmt_donations->fetchAll());

    // Simple sort by date (newest first)
    usort($alerts, function($a, $b) {
        return strtotime($b['submission_date']) - strtotime($a['submission_date']);
    });

} catch (PDOException $e) {
    $message = "Error fetching community alerts. Database tables might be missing.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - Community Alerts</title>
    <style>
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

        .alert-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .alert-card.need {
            border-left: 5px solid red;
            background-color: #ffe0e0;
        }

        .alert-card.donation {
            border-left: 5px solid var(--primary-green);
            background-color: #eaf8ef;
        }

        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .alert-type {
            font-weight: 700;
            font-size: 1.1em;
        }

        .alert-category {
            font-size: 0.85em;
            padding: 3px 8px;
            border-radius: 5px;
            font-weight: 600;
        }

        .alert-category.need-tag { background: red; color: white; }
        .alert-category.donation-tag { background: var(--primary-green); color: white; }

        .alert-location {
            font-style: italic;
            color: #555;
            font-size: 0.9em;
        }
        
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
        <h2>Real-Time Community Alerts</h2>
        
        <?php if ($message): ?>
            <p style="color: red; text-align: center;"><?php echo $message; ?></p>
        <?php elseif (empty($alerts)): ?>
            <p style="text-align: center; color: #555;">No current alerts or active needs in the community.</p>
        <?php else: ?>
            <?php foreach ($alerts as $alert): 
                $is_donation = $alert['category'] === 'Donation';
                $card_class = $is_donation ? 'donation' : 'need';
                $tag_class = $is_donation ? 'donation-tag' : 'need-tag';
            ?>
            <div class="alert-card <?php echo $card_class; ?>">
                <div class="alert-header">
                    <span class="alert-type"><?php echo htmlspecialchars($alert['type']); ?></span>
                    <span class="alert-category <?php echo $tag_class; ?>"><?php echo htmlspecialchars($alert['category']); ?></span>
                </div>
                <p><?php echo htmlspecialchars($alert['description']); ?></p>
                <p class="alert-location">Location: <?php echo htmlspecialchars($alert['location']); ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="reporter_homepage.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>