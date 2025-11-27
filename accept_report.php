<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'donor') {
    header("Location: donor_login.php");
    exit();
}

$donor_id = $_SESSION['user_id'];
$report_id = intval($_GET['id'] ?? 0);

require_once "config.php";

// 1) Fetch the report
$stmt = $conn->prepare("SELECT report_id, accepted_by FROM reports WHERE report_id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();
$report = $res->fetch_assoc();
$stmt->close();

if (!$report) {
    die("Report not found.");
}

// 2) If already accepted by someone else
if (!is_null($report['accepted_by']) && $report['accepted_by'] != $donor_id) {
    header("Location: report_view.php?id=$report_id&msg=already_taken");
    exit();
}

// 3) If already accepted by the same donor, just redirect
if ($report['accepted_by'] == $donor_id) {
    header("Location: report_view.php?id=$report_id&msg=you_accepted");
    exit();
}

// 4) Lock accept using a safe UPDATE query
$update = $conn->prepare("
    UPDATE reports 
    SET accepted_by = ?, accepted_at = NOW()
    WHERE report_id = ? AND accepted_by IS NULL
");
$update->bind_param("ii", $donor_id, $report_id);
$update->execute();

if ($update->affected_rows > 0) {
    // SUCCESS — donor got the report!
    header("Location: report_view.php?id=$report_id&msg=accepted_success");
} else {
    // Another donor accepted milliseconds earlier
    header("Location: report_view.php?id=$report_id&msg=already_taken");
}

$update->close();
?>
