<?php
// admin_view_report.php
require_once 'admin_session.php';
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($reportId <= 0) {
    die('Invalid report ID.');
}

$stmt = $pdo->prepare("
    SELECT r.*, u.name AS reporter_name, u.email AS reporter_email, u.phone AS reporter_phone
    FROM reports r
    LEFT JOIN users u ON u.user_id = r.reporter_id
    WHERE r.report_id = :id
    LIMIT 1
");
$stmt->execute([':id' => $reportId]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die('Report not found.');
}

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Report #<?= $reportId ?> — Smart Aid Admin</title>
  <style>
    body{font-family:system-ui,Arial;background:#f4f7f5;margin:0;padding:24px;}
    .card{max-width:700px;margin:0 auto;background:#fff;border-radius:12px;padding:18px;box-shadow:0 6px 18px rgba(0,0,0,0.06);}
    h1{margin-top:0;font-size:20px;color:#114b2b;}
    .label{font-weight:600;font-size:13px;color:#555;}
    .value{margin-bottom:10px;font-size:14px;}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;}
    .badge.pending{background:#fff3cd;color:#795548;}
    .badge.verified{background:#e8f5e9;color:#2e7d32;}
    .badge.in_progress{background:#e3f2fd;color:#1565c0;}
    .badge.completed{background:#e8f5e9;color:#1b5e20;}
    .badge.rejected{background:#ffebee;color:#c62828;}
    a.back{display:inline-block;margin-top:12px;color:#185e34;text-decoration:none;font-size:13px;}
  </style>
</head>
<body>
<div class="card">
  <h1>Report #<?= $reportId ?></h1>

  <div class="label">Type</div>
  <div class="value"><?= e($report['report_type']) ?></div>

  <div class="label">Description</div>
  <div class="value"><?= nl2br(e($report['description'])) ?></div>

  <div class="label">Location</div>
  <div class="value"><?= e($report['location']) ?></div>

  <div class="label">Status</div>
  <div class="value">
    <span class="badge <?= e($report['status']) ?>"><?= e($report['status']) ?></span>
  </div>

  <div class="label">Created At</div>
  <div class="value"><?= e($report['created_at']) ?></div>

  <hr style="margin:14px 0;">

  <div class="label">Reporter</div>
  <div class="value">
    <?= e($report['reporter_name'] ?? '—') ?><br>
    <small><?= e($report['reporter_email'] ?? '') ?> <?= e($report['reporter_phone'] ? ' | '.$report['reporter_phone'] : '') ?></small>
  </div>

  <a href="admin_dashboard.php" class="back">← Back to dashboard</a>
</div>
</body>
</html>
