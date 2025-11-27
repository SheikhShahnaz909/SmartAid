<?php
// admin_mark_claimed.php
require_once 'admin_session.php';
require_once 'config.php';
require_once 'log_activity.php';  // helper we'll create in the next step

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$adminId  = (int)$_SESSION['admin_id'];
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reportId <= 0) {
    header('Location: admin_dashboard.php');
    exit;
}

// Set report status to "completed"
$stmt = $pdo->prepare("UPDATE reports SET status = 'completed' WHERE report_id = :id");
$stmt->execute([':id' => $reportId]);

// Log this admin action
log_activity($pdo, $adminId, 'admin', 'admin_mark_claimed', "Report ID {$reportId} marked as completed by admin");

// Redirect back to dashboard
header('Location: admin_dashboard.php');
exit;
