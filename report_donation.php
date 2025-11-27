<?php
// report_donation.php
session_start();

// detect AJAX request
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// auth: only reporters can submit
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'reporter') {
    if ($is_ajax) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'auth_required']);
    } else {
        header("Location: reporter_homepage.php?error=auth_required");
    }
    exit;
}

$reporter_id      = (int)($_SESSION['user_id'] ?? 0);
$report_type      = trim($_POST['report_type'] ?? '');
$need_description = trim($_POST['need_description'] ?? '');
$need_location    = trim($_POST['need_location'] ?? '');
$lat              = isset($_POST['lat']) && $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
$lng              = isset($_POST['lng']) && $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;

if ($report_type === '' || $need_description === '' || $need_location === '') {
    if ($is_ajax) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'missing_fields']);
    } else {
        header("Location: report_need_form.php?error=missing_fields");
    }
    exit;
}

// include database config (attempt to reuse your config.php)
if (file_exists(__DIR__ . '/config.php')) {
    include_once __DIR__ . '/config.php';
}

// build $conn if not provided by config.php
if (!isset($conn) || !($conn instanceof mysqli)) {
    $db_host = $servername ?? 'localhost';
    $db_user = $username ?? 'root';
    $db_pass = $password ?? '';
    $db_name = $dbname ?? 'smartaid_db';
    $db_port = $db_port ?? 3307;

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($conn->connect_error) {
        $err = 'DB connect failed: ' . $conn->connect_error;
        if ($is_ajax) {
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>$err]);
        } else {
            die($err);
        }
        exit;
    }
}

// 1) Insert report (without lat/lng first)
$insert_sql = "INSERT INTO reports (reporter_id, report_type, description, location, status, created_at)
               VALUES (?, ?, ?, ?, 'New', NOW())";
$stmt = $conn->prepare($insert_sql);
if (!$stmt) {
    $err = "DB prepare failed: " . $conn->error;
    if ($is_ajax) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>$err]);
        exit;
    }
    die($err);
}

$stmt->bind_param("isss", $reporter_id, $report_type, $need_description, $need_location);
$ok = $stmt->execute();
$report_id = $stmt->insert_id;
$stmt->close();

if (!$ok) {
    $err = "Insert failed: " . $conn->error;
    if ($is_ajax) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>$err]);
        exit;
    }
    die($err);
}

// 2) Save lat/lng if available
if ($lat !== null && $lng !== null) {
    $updateLatLng = $conn->prepare("UPDATE reports SET lat = ?, lng = ? WHERE report_id = ?");
    if ($updateLatLng) {
        $updateLatLng->bind_param("ddi", $lat, $lng, $report_id);
        $updateLatLng->execute();
        $updateLatLng->close();
    }
}

// 3) Find donors within radius (5 km) using Haversine
$nearby_donors = [];
if ($lat !== null && $lng !== null) {
    $radius_km = 5.0;

    $sql = "
      SELECT user_id, name, email, lat, lng,
        (6371 * acos(
            cos(radians(?)) * cos(radians(lat)) *
            cos(radians(lng) - radians(?)) +
            sin(radians(?)) * sin(radians(lat))
        )) AS distance_km
      FROM users
      WHERE role = 'donor' AND lat IS NOT NULL AND lng IS NOT NULL AND status = 'active'
      HAVING distance_km <= ?
      ORDER BY distance_km ASC
      LIMIT 500
    ";
    $s = $conn->prepare($sql);
    if ($s) {
        $s->bind_param("dddi", $lat, $lng, $lat, $radius_km);
        $s->execute();
        $res = $s->get_result();
        while ($r = $res->fetch_assoc()) {
            $nearby_donors[] = $r;
        }
        $s->close();
    }
}

// 4) Email + notification text & report link
$reportUrl = "http://" . $_SERVER['HTTP_HOST'] . "/SmartAid/report_view.php?id=" . intval($report_id);
$subject = "Urgent: Person in need near you (approx location)";
$htmlBody = "<p>Hi,</p>
<p>A reporter has posted a need near your area:</p>
<ul>
  <li><strong>Type:</strong> " . htmlspecialchars($report_type) . "</li>
  <li><strong>Details:</strong> " . nl2br(htmlspecialchars(substr($need_description,0,400))) . "</li>
  <li><strong>Location (text):</strong> " . htmlspecialchars($need_location) . "</li>
</ul>
<p><a href='" . $reportUrl . "'>View the full report & respond</a></p>
<p>Thank you,<br/>SmartAid</p>";

// 5) Include mailer, if available
if (file_exists(__DIR__ . '/mailer_config.php')) {
    include_once __DIR__ . '/mailer_config.php';
}

// 6) Insert notifications & send mail to nearby donors
$notified_count = 0;
foreach ($nearby_donors as $donor) {
    $to            = $donor['email'] ?? '';
    $donor_user_id = (int)$donor['user_id'];

    // Short notification text
    $short = "New nearby need: " . substr($need_description, 0, 140);

    // Insert into notifications table
    $ins = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, is_read, related_id, created_at)
                           VALUES (?, 'nearby_need', ?, ?, 0, ?, NOW())");
    if ($ins) {
        $title = "Nearby need reported";
        $ins->bind_param("issi", $donor_user_id, $title, $short, $report_id);
        $ins->execute();
        $ins->close();
    }

    // Send email
    $sent = false;
    if (function_exists('send_email')) {
        try {
            $sent = send_email($to, $subject, $htmlBody);
        } catch (Throwable $e) {
            error_log("send_email threw: " . $e->getMessage());
            $sent = false;
        }
    } else {
        // basic fallback
        $txt = "A nearby need has been reported:\n\nType: {$report_type}\n\n"
             . strip_tags($need_description) . "\n\nView: {$reportUrl}";
        @mail($to, $subject, $txt, "From: no-reply@yourdomain.com\r\nReply-To: no-reply@yourdomain.com");
        $sent = true;
    }

    // Log donor notification in activity_logs
    $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, role, action, details, ip_address, created_at)
                               VALUES (?, 'donor', 'notified_nearby_need', ?, ?, NOW())");
    if ($logStmt) {
        $details = "report_id:$report_id; distance_km:" . round($donor['distance_km'] ?? 0, 2);
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
        $logStmt->bind_param("iss", $donor_user_id, $details, $ip);
        $logStmt->execute();
        $logStmt->close();
    }

    $notified_count++;
}

// 7) Log reporter activity
$logStmt2 = $conn->prepare("INSERT INTO activity_logs (user_id, role, action, details, ip_address, created_at)
                            VALUES (?, 'reporter', 'submitted_report', ?, ?, NOW())");
if ($logStmt2) {
    $details = "Report ID: $report_id; type: $report_type";
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '';
    $logStmt2->bind_param("iss", $reporter_id, $details, $ip);
    $logStmt2->execute();
    $logStmt2->close();
}

// 8) Return response or redirect
if ($is_ajax) {
    echo json_encode(['success'=>true, 'report_id'=>$report_id, 'notified'=>$notified_count]);
} else {
    // Back to form to show success message
    header("Location: report_need_form.php?status=success&notified=" . $notified_count);
}
exit;
