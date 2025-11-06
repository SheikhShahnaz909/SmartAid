<?php
// Handles submitting donation (donor) OR reporting need (reporter)

require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$action = $_POST['action'] ?? '';

// ✅ DONOR → Add donation
if ($role === "donor" && $action === "add_donation") {

    $item_name = $_POST['item_name'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $expiry = $_POST['expiry_date'] ?? '';
    $pickup = $_POST['pickup_location'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO donations (donor_id, item_name, quantity, expiry_date, pickup_location, status)
                           VALUES (:id, :item, :qty, :exp, :pickup, 'Pending')");
    $stmt->execute([
        ':id' => $user_id,
        ':item' => $item_name,
        ':qty' => $quantity,
        ':exp' => $expiry,
        ':pickup' => $pickup
    ]);

    echo "Donation submitted successfully!";
    exit();
}

// ✅ REPORTER → Report need
if ($role === "reporter" && $action === "report_need") {

    $type = $_POST['report_type'] ?? '';
    $desc = $_POST['need_description'] ?? '';
    $loc  = $_POST['need_location'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO reports (reporter_id, report_type, description, location, status)
                           VALUES (:id, :type, :desc, :loc, 'New')");
    $stmt->execute([
        ':id' => $user_id,
        ':type' => $type,
        ':desc' => $desc,
        ':loc' => $loc
    ]);

    echo "Report submitted successfully!";
    exit();
}

echo "Invalid action.";
?>
