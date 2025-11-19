<?php
// my_reports.php - Reporter's history of submitted needs

require 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reporter') {
    header("Location: reporter_login.php?error=auth_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$reporter_name = htmlspecialchars($_SESSION['user_name'] ?? 'Reporter');
$message = '';
$reports = [];

try {
    // NOTE: This SQL assumes a 'reports' table with a 'reporter_id' column.
    $stmt = $pdo->prepare("SELECT report_type, description, location, status, DATE_FORMAT(submission_date, '%Y-%m-%d') as date 
                           FROM reports WHERE reporter_id = :id ORDER BY submission_date DESC");
    $stmt->execute([':id' => $user_id]);
    $reports = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Error fetching reports. Database table might be missing.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Aid - My Reports</title>
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

        .status-new { color: red; font-weight: 600; }
        .status-inprogress { color: orange; font-weight: 600; }
        .status-resolved { color: green; font-weight: 600; }
        
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
        <h2>Reports Filed by <?php echo $reporter_name; ?></h2>
        
        <?php if ($message): ?>
            <p style="color: red; text-align: center;"><?php echo $message; ?></p>
        <?php elseif (empty($reports)): ?>
            <p style="text-align: center; color: #555;">You have not filed any reports yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['date']); ?></td>
                        <td><?php echo htmlspecialchars($report['report_type']); ?></td>
                        <td><?php echo htmlspecialchars($report['location']); ?></td>
                        <td class="status-<?php echo strtolower(str_replace(' ', '', $report['status'])); ?>">
                            <?php echo htmlspecialchars($report['status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="reporter_homepage.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>