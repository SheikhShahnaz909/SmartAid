<?php
// report_view.php — show a single report on map + details, and allow donor to claim it
session_start();

require_once __DIR__ . '/config.php';       // must define $pdo (PDO)
require_once __DIR__ . '/log_activity.php'; // activity log helper

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection (\$pdo) not available.");
}

// Get report ID from GET or POST (for claim action)
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($report_id <= 0 && isset($_POST['report_id'])) {
    $report_id = (int)$_POST['report_id'];
}
if ($report_id <= 0) {
    die("Report ID missing.");
}

// --------------------------------------------------------------------------------------
// Handle donor claim action (POST)
// --------------------------------------------------------------------------------------
$claimMessage = '';
$claimType    = ''; // 'success' | 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'claim_report') {
    // Only donors can claim
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
        header('Location: donor_login.php?error=auth_required');
        exit;
    }

    $donorId = (int)$_SESSION['user_id'];

    try {
        // Try to claim the report ONLY if not already accepted
        $stmt = $pdo->prepare("
            UPDATE reports
            SET accepted_by = :donor_id,
                status      = 'in_progress'
            WHERE report_id = :id
              AND (accepted_by IS NULL OR accepted_by = 0)
        ");
        $stmt->execute([
            ':donor_id' => $donorId,
            ':id'       => $report_id,
        ]);

        if ($stmt->rowCount() > 0) {
            // Success: we changed the row, so this donor claimed it
            $claimMessage = 'Thank you! You have claimed this report. The reporter can now coordinate with you.';
            $claimType    = 'success';

            // Log claimed_report in activity_logs
            log_activity(
                $pdo,
                $donorId,
                'donor',
                'claimed_report',
                "report_id:{$report_id}; donor_id:{$donorId}"
            );
        } else {
            // No rows updated → someone else already accepted it
            $claimMessage = 'This report has already been claimed by another donor.';
            $claimType    = 'error';
        }
    } catch (PDOException $e) {
        error_log('Error claiming report: '.$e->getMessage());
        $claimMessage = 'An error occurred while trying to claim this report.';
        $claimType    = 'error';
    }
}

// --------------------------------------------------------------------------------------
// Fetch report (including lat/lng, accepted_by, reporter info)
// --------------------------------------------------------------------------------------
try {
    $stmt = $pdo->prepare("
        SELECT r.report_id, r.reporter_id, r.report_type, r.description, r.location,
               r.lat, r.lng, r.status, r.created_at, r.accepted_by,
               u.name AS reporter_name, u.email AS reporter_email
        FROM reports r
        LEFT JOIN users u ON u.user_id = r.reporter_id
        WHERE r.report_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DB error: " . htmlspecialchars($e->getMessage()));
}

if (!$report) {
    die("Report not found.");
}

// --------------------------------------------------------------------------------------
// Prepare JS/map values
// --------------------------------------------------------------------------------------
$latJs = ($report['lat'] !== null) ? (float)$report['lat'] : 'null';
$lngJs = ($report['lng'] !== null) ? (float)$report['lng'] : 'null';

$gmapsUrl = ($report['lat'] !== null && $report['lng'] !== null)
    ? "https://www.google.com/maps/search/?api=1&query=" . urlencode($report['lat'] . "," . $report['lng'])
    : "";

// Determine whether already claimed
$alreadyClaimed = !empty($report['accepted_by']);

// Current user role (for showing claim button)
$currentRole = $_SESSION['user_role'] ?? 'guest';

// --------------------------------------------------------------------------------------
// Log that someone viewed this report
// --------------------------------------------------------------------------------------
$viewerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$viewerRole = $_SESSION['user_role'] ?? 'guest';

log_activity(
    $pdo,
    $viewerId,
    $viewerRole,
    'view_report',
    "report_id:{$report_id}"
);

function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Report — Smart Aid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- Leaflet CSS -->
  <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>

  <style>
    *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif;}

    body{
      background:#f2faf4;
      min-height:100vh;
      display:flex;
      flex-direction:column;
    }

    #map{
      width:100%;
      height:380px;
    }

    .wrap{
      max-width:960px;
      margin:0 auto;
      padding:18px 16px 32px;
    }

    .card{
      background:#fff;
      border-radius:16px;
      box-shadow:0 10px 30px rgba(0,0,0,0.08);
      padding:20px 20px 18px;
    }

    h2{
      font-size:1.35rem;
      margin-bottom:6px;
      color:#114b2b;
    }

    .meta{
      font-size:0.9rem;
      color:#6a8273;
      margin-bottom:12px;
    }

    .field-label{
      font-size:0.88rem;
      color:#54705f;
      font-weight:600;
      margin-top:10px;
      margin-bottom:4px;
    }

    .field-value{
      font-size:0.96rem;
      color:#193526;
    }

    .badge{
      display:inline-block;
      padding:3px 9px;
      border-radius:999px;
      font-size:0.8rem;
      font-weight:600;
      background:#e8fff0;
      color:#1a733e;
      margin-left:6px;
    }

    .actions{
      margin-top:16px;
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      align-items:center;
    }

    .btn{
      border:none;
      border-radius:999px;
      padding:9px 14px;
      font-size:0.9rem;
      font-weight:600;
      cursor:pointer;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:6px;
      background:linear-gradient(135deg,#1A733E,#114b2b);
      color:#fff;
      text-decoration:none;
    }

    .btn.secondary{
      background:#fff;
      color:#114b2b;
      border:1px solid rgba(0,0,0,0.1);
    }

    .btn:disabled{
      opacity:0.6;
      cursor:not-allowed;
    }

    .status-text{
      margin-top:4px;
      font-size:0.85rem;
      color:#6c8072;
    }

    .alert{
      margin-bottom:12px;
      border-radius:10px;
      padding:10px 12px;
      font-size:0.9rem;
    }

    .alert.success{
      background:#e8f5e9;
      color:#1b5e20;
      border:1px solid #c8e6c9;
    }

    .alert.error{
      background:#ffebee;
      color:#b71c1c;
      border:1px solid #ffcdd2;
    }

    @media(max-width:600px){
      #map{height:320px;}
      .card{border-radius:14px;padding:16px;}
    }
  </style>
</head>
<body>
  <div id="map"></div>

  <div class="wrap">
    <div class="card">
      <?php if ($claimMessage !== ''): ?>
        <div class="alert <?php echo $claimType === 'success' ? 'success' : 'error'; ?>">
          <?php echo e($claimMessage); ?>
        </div>
      <?php endif; ?>

      <h2>
        Report #<?php echo e($report['report_id']); ?>
        <span class="badge"><?php echo e($report['report_type']); ?></span>
      </h2>
      <div class="meta">
        Submitted on <?php echo e($report['created_at']); ?>
        · Status: <?php echo e($report['status']); ?>
      </div>

      <div class="field-label">Description</div>
      <div class="field-value">
        <?php echo nl2br(e($report['description'])); ?>
      </div>

      <div class="field-label">Reported location (text)</div>
      <div class="field-value">
        <?php echo e($report['location']); ?>
      </div>

      <div class="actions">
        <button id="routeBtn" class="btn" type="button">
          Show route from my location
        </button>

        <?php if ($gmapsUrl !== ""): ?>
          <a class="btn secondary" href="<?php echo e($gmapsUrl); ?>" target="_blank">
            Open in Google Maps
          </a>
        <?php else: ?>
          <button class="btn secondary" type="button" disabled>
            Coordinates not available
          </button>
        <?php endif; ?>

        <?php if ($currentRole === 'donor'): ?>
          <?php if ($alreadyClaimed): ?>
            <button class="btn" type="button" disabled>
              Already claimed by a donor
            </button>
          <?php else: ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="action" value="claim_report">
              <input type="hidden" name="report_id" value="<?php echo (int)$report_id; ?>">
              <button class="btn" type="submit">
                🤝 I want to help
              </button>
            </form>
          <?php endif; ?>
        <?php else: ?>
          <!-- Non-donor viewers simply don't get the claim button -->
        <?php endif; ?>
      </div>

      <div class="status-text" id="routeStatus"></div>
    </div>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
          crossorigin=""></script>
  <script>
    const reportLat = <?php echo $latJs; ?>;
    const reportLng = <?php echo $lngJs; ?>;

    const fallbackLat = 20.5937; // India-ish
    const fallbackLng = 78.9629;

    const map = L.map('map');

    if (reportLat !== null && reportLng !== null) {
      map.setView([reportLat, reportLng], 15);
    } else {
      map.setView([fallbackLat, fallbackLng], 3);
    }

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    if (reportLat !== null && reportLng !== null) {
      L.marker([reportLat, reportLng]).addTo(map)
        .bindPopup("Reported need location").openPopup();
    }

    const routeBtn = document.getElementById('routeBtn');
    const routeStatus = document.getElementById('routeStatus');

    if (reportLat === null || reportLng === null) {
      routeBtn.disabled = true;
      routeStatus.textContent = "This report has no precise coordinates, only text address.";
    } else {
      routeBtn.addEventListener('click', function() {
        if (!navigator.geolocation) {
          routeStatus.textContent = "Geolocation is not supported by your browser.";
          return;
        }
        routeStatus.textContent = "Getting your location…";

        navigator.geolocation.getCurrentPosition(function(pos) {
          const myLat = pos.coords.latitude;
          const myLng = pos.coords.longitude;

          const url = "https://www.google.com/maps/dir/?api=1"
                    + "&origin=" + encodeURIComponent(myLat + "," + myLng)
                    + "&destination=" + encodeURIComponent(reportLat + "," + reportLng)
                    + "&travelmode=driving";
          window.open(url, "_blank");
          routeStatus.textContent = "";
        }, function(err) {
          routeStatus.textContent = "Unable to get your location: " + (err.message || "permission denied");
        }, {enableHighAccuracy:true, timeout:12000});
      });
    }
  </script>
</body>
</html>
