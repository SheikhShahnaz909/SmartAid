<?php
// my_donation.php
// Combined "Add donation" functionality and "My donations" listing
// Green-themed UI; uses prepared statements and stores donor_id from session

session_start();

// Use your existing config if present
if (file_exists(__DIR__ . '/config.php')) {
    include_once __DIR__ . '/config.php';
}

// Try to use an existing $conn or build one from common config vars
if (!isset($conn) || !($conn instanceof mysqli)) {
    $db_host = $servername ?? $DB_HOST ?? 'localhost';
    $db_user = $username ?? $DB_USER ?? 'root';
    $db_pass = $password ?? $DB_PASS ?? '';
    $db_name = $dbname ?? $DB_NAME ?? 'smartaid_db';
    $db_port = $db_port ?? $port ?? 3307;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($conn->connect_error) {
        die("Database connection failed: " . htmlspecialchars($conn->connect_error));
    }
}

// Determine current user (if logged in)
$is_logged_in = isset($_SESSION['user_id']);
$donor_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
$donor_email = $_SESSION['user_email'] ?? null;

// Handle POST - insert donation
$messages = [];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donation'])) {
    // Collect and sanitize input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $lat = isset($_POST['lat']) && $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
    $lng = isset($_POST['lng']) && $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;
    $available_until = trim($_POST['available_until'] ?? '') ?: null;

    // Basic server-side validation
    if ($name === '') $errors[] = "Donation name is required.";
    if ($lat === null || $lng === null) $errors[] = "Please provide coordinates (lat & lng) or use 'Use my location'.";
    // Add more validations as needed (e.g., available_until format)

    if (empty($errors)) {
        // Prepared statement to insert
        $sql = "INSERT INTO donations (donor_id, donor_email, name, description, address, lat, lng, available_until, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // mysqli bind: i (int), s (string), s, s, s, d (double), d, s
            $donor_id_param = $donor_id ? $donor_id : null; // allow null for anonymous
            // Bind parameters (use "isssddss" signature)
            // If your lat/lng columns are decimal defined as strings, change 'd' -> 's' accordingly.
            $stmt->bind_param(
                "isssddss",
                $donor_id_param,
                $donor_email,
                $name,
                $description,
                $address,
                $lat,
                $lng,
                $available_until
            );

            if ($stmt->execute()) {
                $messages[] = "Donation added successfully.";
                // After insert, you might want to clear POST data on reload (PRG pattern)
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $errors[] = "Insert failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "DB prepare failed: " . $conn->error;
        }
    }
}

// Fetch user's donations for "My Donations" list (if logged in)
$myDonations = [];
if ($is_logged_in) {
    $sql = "SELECT id, name, description, address, lat, lng, available_until, created_at
            FROM donations
            WHERE donor_id = ?
            ORDER BY created_at DESC
            LIMIT 200";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $donor_id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $myDonations[] = $row;
            }
        }
        $stmt->close();
    }
} else {
    // Optionally show message to login
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Donations — SmartAid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --green-600:#1d7940;
      --green-500:#22a54b;
      --green-100:#eaf6ec;
      --bg:#f3fbf6;
      --card:#ffffff;
      --muted:#6b6b6b;
      --radius:12px;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:system-ui,-apple-system,Segoe UI,Roboto,"Poppins",Arial;
      background: linear-gradient(180deg, var(--bg), #eef8f2);
      color:#222;
      padding:28px;
    }

    .container{
      max-width:980px;
      margin:0 auto;
    }

    header{
      display:flex;
      align-items:center;
      gap:14px;
      margin-bottom:18px;
    }
    header img{ width:58px; height:58px; border-radius:10px; object-fit:cover; box-shadow:0 6px 18px rgba(13,74,34,0.12); border:2px solid rgba(13,74,34,0.06); }
    header h1{ font-size:1.6rem; color:var(--green-600); margin:0; }

    .grid{
      display:grid;
      grid-template-columns: 1fr 420px;
      gap:18px;
      align-items:start;
    }

    /* Left: My Donations list (card) */
    .card{
      background:var(--card);
      border-radius:var(--radius);
      padding:18px;
      box-shadow: 0 8px 30px rgba(15,64,28,0.06);
    }
    .card h2{ margin:0 0 12px 0; color:var(--green-600); }
    .donation-row{
      display:flex;
      justify-content:space-between;
      gap:12px;
      padding:12px;
      border-radius:10px;
      background:linear-gradient(90deg, #fff, #fbfffb);
      margin-bottom:10px;
      border:1px solid rgba(30,110,60,0.04);
    }
    .donation-meta{ color:var(--muted); font-size:0.92rem;}
    .donation-actions{ display:flex; gap:8px; align-items:center; }

    /* Right: Add Donation form */
    .form {
      background: linear-gradient(180deg, #ffffff, #fbfff9);
      border-radius:12px;
      padding:16px;
      border: 1px solid rgba(29,121,64,0.06);
      box-shadow: 0 8px 24px rgba(14,70,35,0.04);
    }
    .form h3 { margin:0 0 10px 0; color:var(--green-600); }
    label{ display:block; margin-top:10px; font-weight:600; font-size:0.9rem; color:#234d2f;}
    input[type="text"], input[type="datetime-local"], textarea {
      width:100%;
      padding:10px 12px;
      border-radius:8px;
      border:1px solid #dfeee0;
      background: #fbfffdf0;
      margin-top:6px;
      font-size:0.95rem;
    }
    textarea{ min-height:90px; resize:vertical; }

    .btn {
      display:inline-block;
      padding:10px 14px;
      border-radius:10px;
      border:none;
      cursor:pointer;
      font-weight:700;
      color:#fff;
      background: linear-gradient(180deg, var(--green-500), var(--green-600));
      box-shadow: 0 8px 18px rgba(13,74,34,0.12);
      transition: transform .12s ease, box-shadow .12s ease;
    }
    .btn:hover{ transform: translateY(-2px); box-shadow: 0 14px 28px rgba(13,74,34,0.12);}
    .btn-ghost { background:transparent; color:var(--green-600); border:1px solid rgba(29,121,64,0.12); font-weight:700; box-shadow:none; padding:8px 12px; border-radius:8px; }

    .small { font-size:0.9rem; color:var(--muted); margin-top:6px; }
    .muted { color:var(--muted); }
    .msg { padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    .msg.ok { background:#ecf8ee; color:var(--green-600); border:1px solid rgba(29,121,64,0.08); }
    .msg.err { background:#fff4f4; color:#a33; border:1px solid rgba(160,30,30,0.06); }

    /* responsive */
    @media (max-width:980px) {
      .grid{ grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div onclick="window.location.href='donor_homepage.php'"
           style="
              cursor:pointer;
              display:flex;
              align-items:center;
              justify-content:center;
              width:35px;
              height:35px;
              border-radius:50%;
              background:rgba(255,255,255,0.34);
              backdrop-filter:blur(8px);
              box-shadow:0 5px 16px rgba(0,0,0,0.32);
           ">
        <span style="
              font-size:20px;
              font-weight:900;
              color:white;
              line-height:1;
              margin-left:-4px;
              letter-spacing:-4px;
           ">
          🡸
        </span>
      </div>
</a>
  <div class="container">
    <header>
      <img src="images/circle-logo.png" alt="Smart Aid">
      <h1>My Donations</h1>
    </header>

    <div class="grid">
      <!-- LEFT: My donations -->
      <div class="card">
        <h2>Donations you added</h2>

        <?php if (!$is_logged_in): ?>
          <div class="msg err">You are not logged in. Please <a href="donor_login.php">login</a> as a donor to add or view donations.</div>
        <?php else: ?>
          <?php if (!empty($messages)): ?>
            <?php foreach($messages as $m): ?>
              <div class="msg ok"><?php echo htmlspecialchars($m); ?></div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="msg err">
              <ul style="margin:0 0 0 18px;padding:0.2rem 0;">
              <?php foreach($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
              <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (empty($myDonations)): ?>
            <p class="small muted">You haven't added any donations yet. Use the form on the right to add one.</p>
          <?php else: ?>
            <?php foreach($myDonations as $d): ?>
              <div class="donation-row" title="<?php echo htmlspecialchars($d['description']); ?>">
                <div>
                  <div style="font-weight:700;"><?php echo htmlspecialchars($d['name']); ?></div>
                  <div class="donation-meta"><?php echo htmlspecialchars($d['address']); ?> • <?php echo htmlspecialchars($d['created_at']); ?></div>
                </div>
                <div style="text-align:right;">
                  <div style="font-weight:700; color:var(--green-600);"><?php echo htmlspecialchars($d['lat']); ?>, <?php echo htmlspecialchars($d['lng']); ?></div>
                  <?php if (!empty($d['available_until'])): ?>
                    <div class="small muted">Available until: <?php echo htmlspecialchars($d['available_until']); ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- RIGHT: Add donation form -->
      <aside class="form">
        <h3>Add a donation</h3>
        <form method="post" action="" id="donationForm">
          <input type="hidden" name="add_donation" value="1">
          <label for="name">Donation name</label>
          <input id="name" name="name" type="text" placeholder="e.g. 20 cooked meals, leftover sandwiches" required>

          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Add details like quantity, packaging, notes..."></textarea>

          <label for="address">Address (optional)</label>
          <input id="address" name="address" type="text" placeholder="Street, city, landmark">

          <div style="display:flex;gap:8px;margin-top:8px;">
            <div style="flex:1;">
              <label for="lat">Latitude</label>
              <input id="lat" name="lat" type="text" placeholder="28.70">
            </div>
            <div style="width:150px;">
              <label for="lng">Longitude</label>
              <input id="lng" name="lng" type="text" placeholder="77.10">
            </div>
          </div>

          <div style="display:flex;gap:8px;margin-top:8px;align-items:center;">
            <button type="button" class="btn-ghost" id="useLocBtn">Use my location</button>
            <button type="button" class="btn-ghost" id="clearLocBtn">Clear coords</button>
          </div>

          <div style="margin-top:12px; display:flex; gap:8px;">
            <button type="submit" class="btn">Add Donation</button>
            <button type="reset" class="btn-ghost">Reset</button>
          </div>

          <p class="small muted" style="margin-top:10px;">Tip: Provide coordinates or press "Use my location" for accuracy.</p>
        </form>
      </aside>
    </div><!-- /grid -->
  </div><!-- /container -->

<script>
  // JS: small helpers for geolocation and simple validation
  const useLocBtn = document.getElementById('useLocBtn');
  const clearLocBtn = document.getElementById('clearLocBtn');
  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');

  useLocBtn && useLocBtn.addEventListener('click', ()=> {
    if (!navigator.geolocation) return alert('Geolocation not supported by your browser.');
    useLocBtn.disabled = true; useLocBtn.textContent = 'Getting...';
    navigator.geolocation.getCurrentPosition(p => {
      useLocBtn.disabled = false; useLocBtn.textContent = 'Use my location';
      latInput.value = p.coords.latitude.toFixed(6);
      lngInput.value = p.coords.longitude.toFixed(6);
    }, err => {
      useLocBtn.disabled = false; useLocBtn.textContent = 'Use my location';
      alert('Could not get location: ' + err.message);
    }, { enableHighAccuracy: true, timeout:15000});
  });

  clearLocBtn && clearLocBtn.addEventListener('click', ()=> {
    latInput.value = ''; lngInput.value = '';
  });

  // Optional client-side form guard for required coords if needed
  document.getElementById('donationForm').addEventListener('submit', function(e) {
    // If you require coords, uncomment the next lines:
    // if (!latInput.value || !lngInput.value) {
    //   alert('Please provide coordinates or use "Use my location".');
    //   e.preventDefault();
    // }
  });
</script>
</body>
</html>
