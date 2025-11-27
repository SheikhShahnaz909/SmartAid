<?php
// report_need_form.php - Reporter form to submit a new need report
session_start();

// Only logged-in reporters can access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'reporter') {
    header("Location: reporter_homepage.php");
    exit();
}

// Check if we just came back after a successful submission
$justSubmitted = isset($_GET['status']) && $_GET['status'] === 'success';
$notifiedCount = isset($_GET['notified']) ? (int)$_GET['notified'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Smart Aid - Report Community Need</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-green:#1A733E;
      --primary-green-dark:#114b2b;
      --accent:#ffb347;
      --bg1: #eaf8ef;
      --bg2: #fdfdfd;
      --card: rgba(255,255,255,0.98);
    }

    *{
      box-sizing:border-box;
      margin:0;
      padding:0;
      font-family:'Poppins',sans-serif;
    }

    body{
      background: radial-gradient(circle at top, #d4f5e3 0, #f7fff9 45%, #eef5ff 100%);
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:20px;
      color:#114b2b;
    }

    .form-container{
      width:100%;
      max-width:620px;
      background:var(--card);
      padding:26px 26px 22px;
      border-radius:18px;
      box-shadow:0 14px 40px rgba(0,0,0,0.12);
      border:1px solid rgba(26,115,62,0.08);
      position:relative;
      overflow:hidden;
    }

    .form-container::before{
      content:"";
      position:absolute;
      inset:0;
      background:linear-gradient(135deg,rgba(26,115,62,0.13),rgba(255,255,255,0));
      pointer-events:none;
      z-index:-1;
    }

    h2{
      text-align:center;
      color:var(--primary-green-dark);
      margin-bottom:6px;
      font-weight:700;
      letter-spacing:0.02em;
    }

    .subtitle {
      text-align:center;
      font-size:0.9rem;
      color:#627c6c;
      margin-bottom:18px;
    }

    .input-group{
      margin-bottom:14px;
    }

    label{
      display:block;
      margin-bottom:6px;
      font-weight:600;
      color:#124428;
      font-size:0.96rem;
    }

    input, select, textarea{
      width:100%;
      padding:10px 11px;
      border-radius:9px;
      border:1px solid #cddfd2;
      background:#fbfffc;
      font-size:0.96rem;
      transition:border 0.18s, box-shadow 0.18s, background 0.18s;
    }

    input:focus,
    select:focus,
    textarea:focus{
      outline:none;
      border-color:var(--primary-green);
      box-shadow:0 0 0 2px rgba(26,115,62,0.16);
      background:#ffffff;
    }

    textarea{
      min-height:110px;
      resize:vertical;
    }

    .btn-row{
      display:flex;
      gap:10px;
      align-items:center;
      margin-bottom:8px;
    }

    .btn{
      padding:10px 14px;
      border-radius:999px;
      border:none;
      background:linear-gradient(135deg,var(--primary-green),var(--primary-green-dark));
      color:#fff;
      font-weight:700;
      cursor:pointer;
      font-size:0.98rem;
      box-shadow:0 8px 18px rgba(17,75,43,0.3);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:6px;
      transition:transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s;
    }

    .btn:hover{
      transform:translateY(-1px);
      box-shadow:0 10px 22px rgba(17,75,43,0.34);
      filter:brightness(1.03);
    }

    .btn:active{
      transform:translateY(0);
      box-shadow:0 4px 10px rgba(17,75,43,0.3);
    }

    .btn.secondary{
      background:#ffffff;
      color:var(--primary-green-dark);
      border:1px solid rgba(26,115,62,0.26);
      box-shadow:none;
    }

    .btn.secondary:hover{
      background:#f2fbf6;
      box-shadow:0 4px 10px rgba(17,75,43,0.16);
    }

    .small{
      font-size:0.9rem;
      color:#586c61;
      margin-left:4px;
    }

    .note{
      font-size:0.88rem;
      color:#647a6a;
      margin-top:8px;
      padding:8px 10px;
      background:#f4fbf6;
      border-radius:8px;
      border-left:3px solid var(--primary-green);
    }

    .back-link{
      text-align:center;
      margin-top:14px;
      color:var(--primary-green-dark);
      font-weight:600;
      text-decoration:none;
      display:block;
      font-size:0.95rem;
    }

    .back-link:hover{
      text-decoration:underline;
    }

    /* Success message styles */
    .alert{
      border-radius:12px;
      padding:14px 14px 13px;
      margin-bottom:14px;
      display:flex;
      gap:10px;
      align-items:flex-start;
      background:linear-gradient(120deg,rgba(26,115,62,0.16),rgba(255,255,255,0.9));
      border:1px solid rgba(26,115,62,0.3);
    }

    .alert-icon{
      width:26px;
      height:26px;
      border-radius:50%;
      background:#ffffff;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:1.1rem;
      color:var(--primary-green-dark);
      flex-shrink:0;
      box-shadow:0 3px 8px rgba(0,0,0,0.06);
    }

    .alert-content h3{
      margin:0 0 4px;
      font-size:1.02rem;
      color:var(--primary-green-dark);
    }

    .alert-content p{
      margin:0;
      font-size:0.9rem;
      color:#355343;
    }

    .alert-content .sub{
      margin-top:4px;
      font-size:0.86rem;
      color:#6a8372;
    }

    .pill{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:3px 9px;
      border-radius:999px;
      background:rgba(26,115,62,0.07);
      font-size:0.8rem;
      color:#315642;
      margin-top:6px;
    }

    .pill span{
      font-size:0.8rem;
    }

    @media (max-width: 520px){
      .form-container{
        padding:20px 16px 18px;
        border-radius:14px;
      }
      h2{
        font-size:1.25rem;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Report a Need</h2>
    <p class="subtitle">Help us connect urgent needs with nearby donors in minutes.</p>

    <?php if ($justSubmitted): ?>
      <div class="alert">
        <div class="alert-icon">✓</div>
        <div class="alert-content">
          <h3>Report submitted successfully</h3>
          <p>Thank you for reporting this need. Nearby donors will be notified based on the location you provided.</p>
          <?php if ($notifiedCount > 0): ?>
            <p class="sub"><?php echo $notifiedCount; ?> donor(s) were notified about this need.</p>
          <?php else: ?>
            <p class="sub">No donors were found within 5 km of this location.</p>
          <?php endif; ?>
          <div class="pill">
            <span>⏳</span>
            <span>Redirecting to dashboard…</span>
          </div>
        </div>
      </div>

      <script>
        // Redirect to reporter homepage after a short delay
        setTimeout(function () {
          window.location.href = "reporter_homepage.php";
        }, 3000); // 3 seconds
      </script>
    <?php endif; ?>

    <?php if (!$justSubmitted): ?>
      <form id="reportForm" method="POST" action="report_donation.php">
        <input type="hidden" name="action" value="report_need">
        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">

        <div class="input-group">
          <label for="report_type">Type of Need</label>
          <select id="report_type" name="report_type" required>
            <option value="" disabled selected>— Choose a need type —</option>
            <option value="food">Food</option>
            <option value="clothing">Clothing</option>
            <option value="medical">Medical / First aid</option>
            <option value="hygiene">Hygiene / Sanitation</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="input-group">
          <label for="need_description">Description</label>
          <textarea id="need_description" name="need_description" required></textarea>
        </div>

        <div class="input-group">
          <label for="need_location">Location (address or landmark)</label>
          <input type="text" id="need_location" name="need_location" placeholder="specific address / landmark" required>
        </div>

        <div class="btn-row">
          <button type="button" id="useLocationBtn" class="btn secondary">📍 Use my location</button>
          <span id="locStatus" class="small"> (optional — helps find nearby donors)</span>
        </div>

        <div style="margin-top:10px;">
          <button type="submit" class="btn">Submit Report</button>
        </div>
        <p class="note">Tip: capturing coordinates (press “Use my location”) ensures donors nearby (within 5 km) are notified automatically.</p>
      </form>

      <a class="back-link" href="reporter_homepage.php">← Back to dashboard</a>
    <?php endif; ?>
  </div>

<script>
  // Geolocation helper
  const useLocationBtn = document.getElementById('useLocationBtn');
  const locStatus = document.getElementById('locStatus');
  const latInput = document.getElementById('lat');
  const lngInput = document.getElementById('lng');

  if (useLocationBtn) {
    useLocationBtn.addEventListener('click', function () {
      if (!navigator.geolocation) {
        locStatus.textContent = 'Geolocation not supported by your browser';
        return;
      }
      locStatus.textContent = 'Locating… allow the browser to share location';
      useLocationBtn.disabled = true;
      navigator.geolocation.getCurrentPosition((pos) => {
        const lat = pos.coords.latitude.toFixed(7);
        const lng = pos.coords.longitude.toFixed(7);
        latInput.value = lat;
        lngInput.value = lng;
        locStatus.textContent = `Location captured (${lat}, ${lng})`;
        useLocationBtn.disabled = false;
      }, (err) => {
        locStatus.textContent = 'Unable to get location: ' + (err.message || 'permission denied');
        useLocationBtn.disabled = false;
      }, { enableHighAccuracy: true, timeout: 12000 });
    });

    document.getElementById('reportForm').addEventListener('submit', function (e) {
      const lat = latInput.value.trim();
      const lng = lngInput.value.trim();
      // Optional: you can warn if coords are empty
    });
  }
</script>
</body>
</html>
