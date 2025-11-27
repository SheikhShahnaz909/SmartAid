<?php
// my_reports.php
session_start();
require 'config.php'; // $pdo

// Only reporters can see this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'reporter') {
    header("Location: reporter_homepage.php?error=auth_required");
    exit();
}

$reporter_id = (int)$_SESSION['user_id'];

// Fetch reports for this reporter
try {
    $sql = "SELECT report_id, report_type, description, location, status, created_at
            FROM reports
            WHERE reporter_id = :rid
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':rid' => $reporter_id]);
    $myReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$reporter_name = htmlspecialchars($_SESSION['user_name'] ?? 'Reporter');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reports - Smart Aid</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #1A733E;
            --light-green: #E0FFE0;
            --bg: #f4f7f5;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: var(--bg);
            padding: 30px;
            color: #114b2b;
        }
        .wrap {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--primary-green);
        }
        .sub {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #e4ede6;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #e9f5ee;
            color: #205738;
            font-weight: 600;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
        }
        .badge.pending { background:#fff3cd; color:#8a6d3b; }
        .badge.verified { background:#e8f5e9; color:#2e7d32; }
        .badge.in_progress { background:#e3f2fd; color:#1565c0; }
        .badge.completed { background:#e8f5e9; color:#1b5e20; }
        .badge.rejected { background:#ffebee; color:#c62828; }
        .back-link {
            display:inline-block;
            margin-top: 18px;
            text-decoration:none;
            color:var(--primary-green);
            font-weight:600;
        }
        .status-empty {
            text-align:center;
            padding:20px;
            color:#666;
        }
        .success-msg {
            background:#e1f8e5;
            border:1px solid #8fd19b;
            padding:8px 10px;
            border-radius:8px;
            font-size:13px;
            margin-bottom:10px;
            color:#1b5e20;
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1>My Reports</h1>
    <p class="sub">Hi, <?= $reporter_name ?>. Here are all the needs you have reported so far.</p>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-msg">Your need report was submitted successfully.</div>
    <?php endif; ?>

    <?php if (empty($myReports)): ?>
        <div class="status-empty">You have not reported any needs yet.</div>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Type &amp; Description</th>
                <th>Location</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($myReports as $r): ?>
                <tr>
                    <td>#<?= (int)$r['report_id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r['report_type']) ?></strong><br>
                        <span style="font-size:13px; color:#444;">
                            <?= nl2br(htmlspecialchars($r['description'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($r['location']) ?></td>
                    <td>
                        <span class="badge <?= htmlspecialchars($r['status']) ?>">
                            <?= htmlspecialchars($r['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a class="back-link" href="reporter_homepage.php">← Back to Dashboard</a>
</div>
</body>
</html>
