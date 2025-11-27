<?php
// donor_set_location.php — Donor map to set exact location
session_start();
require 'config.php';

// Only donors allowed
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    header("Location: donor_login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$errors = [];
$saved  = false;

// Load current stored coordinates (if any)
$stmt = $pdo->prepare("SELECT lat, lng FROM users WHERE user_id = :uid AND role = 'donor' LIMIT 1");
$stmt->execute([':uid' => $user_id]);
$current    = $stmt->fetch();
$currentLat = isset($current['lat']) ? (float)$current['lat'] : null;
$currentLng = isset($current['lng']) ? (float)$current['lng'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'] ?? '';
    $lng = $_POST['lng'] ?? '';

    if ($lat === '' || $lng === '') {
        $errors[] = "Please pick a location on the map or use the 'Use my location' button.";
    } else {
        $latF = (float)$lat;
        $lngF = (float)$lng;

        // Optional: treat (0,0) as invalid
        if ($latF === 0.0 && $lngF === 0.0) {
            $errors[] = "Location looks invalid (0,0). Please pick again on the map.";
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users SET lat = :lat, lng = :lng 
                 WHERE user_id = :uid AND role = 'donor'"
            );
            $stmt->execute([
                ':lat' => $latF,
                ':lng' => $lngF,
                ':uid' => $user_id
            ]);

            $saved      = true;
            $currentLat = $latF;
            $currentLng = $lngF;
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Set My Location — Smart Aid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <style>
    :root{
      --primary-green:#1A733E;
      --primary-green-dark:#114b2b;
      --card-radius:16px;
    }

    *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif;}

    body{
      background:url('images/signin-background.jpeg') center/cover no-repeat fixed;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
      color:#fff;
    }

    .card{
      width:100%;
      max-width:720px;
      background:rgba(0,0,0,0.55);
      border-radius:var(--card-radius);
      box-shadow:0 12px 40px rgba(0,0,0,0.5);
      padding:22px 22px 20px;
      backdrop-filter:blur(10px);
    }

    h2{
      text-align:center;
      font-size:1.4rem;
      margin-bottom:6px;
    }

    .subtitle{
      text-align:center;
      font-size:0.9rem;
      color:#e8f6ec;
      margin-bottom:14px;
    }

    #map{
      width:100%;
      height:360px;
      border-radius:14px;
      overflow:hidden;
      box-shadow:0 10px 30px rgba(0,0,0,0.4);
      margin-bottom:12px;
    }

    .controls{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      margin-bottom:8px;
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
      background:linear-gradient(135deg,var(--primary-green),var(--primary-green-dark));
      color:#fff;
      box-shadow:0 6px 16px rgba(0,0,0,0.4);
      transition:transform 0.15s, box-shadow 0.15s, filter 0.15s;
    }

    .btn:hover{
      transform:translateY(-1px);
      box-shadow:0 9px 20px rgba(0,0,0,0.5);
      filter:brightness(1.05);
    }

    .btn.secondary{
      background:#ffffff;
      color:var(--primary-green-dark);
      box-shadow:none;
    }

    .small{
      font-size:0.85rem;
      color:#e1f3e7;
    }

    form{
      margin-top:4px;
    }

    .hidden-inputs{
      display:none;
    }

    .msg{
      border-radius:9px;
      padding:9px 10px;
      margin-bottom:8px;
      font-size:0.9rem;
    }
    .msg.error{background:rgba(255,90,90,0.2);border:1px solid rgba(255,150,150,0.8);}
    .msg.success{background:rgba(137,242,171,0.18);border:1px solid rgba(190,255,210,0.9);}

    .footer-links{
      margin-top:10px;
      text-align:center;
      font-size:0.9rem;
    }
    .footer-links a{
      color:#caffd7;
      text-decoration:underline;
    }
    .footer-links a:hover{
      text-decoration:none;
    }

    @media(max-width:600px){
      .card{padding:18px 14px;}
      #map{height:300px;}
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Set My Location</h2>
    <p class="subtitle">Choose your exact location so Smart Aid can notify you about nearby needs.</p>

    <?php if (!empty($errors)): ?>
      <?php foreach($errors as $e): ?>
        <div class="msg error"><?php echo htmlspecialchars($e); ?></div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($saved): ?>
      <div class="msg success">
        Your location has been updated. You’ll now receive notifications for needs near you.
      </div>
      <script>
        setTimeout(function(){
          window.location.href = "donor_homepage.php";
        }, 2000);
      </script>
    <?php endif; ?>

    <div id="map"></div>

    <div class="controls">
      <button type="button" id="useMyLocation" class="btn secondary">📍 Use my current location</button>
      <span class="small">You can also drag / click on the map to fine-tune your location.</span>
    </div>

    <form method="POST" action="">
      <div class="hidden-inputs">
        <input type="text" id="lat" name="lat"
               value="<?php echo $currentLat !== null ? htmlspecialchars($currentLat) : ''; ?>">
        <input type="text" id="lng" name="lng"
               value="<?php echo $currentLng !== null ? htmlspecialchars($currentLng) : ''; ?>">
      </div>
      <button class="btn" type="submit">Save my location</button>
    </form>

    <div class="footer-links">
      <a href="donor_homepage.php">← Back to donor dashboard</a>
    </div>
  </div>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script>
    // PHP → JS values
    const initialLat = <?php echo $currentLat !== null ? $currentLat : 20.5937; ?>; // default: India center
    const initialLng = <?php echo $currentLng !== null ? $currentLng : 78.9629; ?>;
    const hasExisting = <?php echo ($currentLat !== null && $currentLng !== null) ? 'true' : 'false'; ?>;

    const map = L.map('map').setView([initialLat, initialLng], hasExisting ? 14 : 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    function setMarker(lat, lng) {
      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = L.marker([lat, lng], {draggable:true}).addTo(map);
        marker.on('dragend', function(e) {
          const pos = e.target.getLatLng();
          updateHiddenInputs(pos.lat, pos.lng);
        });
      }
      updateHiddenInputs(lat, lng);
    }

    function updateHiddenInputs(lat, lng) {
      document.getElementById('lat').value = lat.toFixed(7);
      document.getElementById('lng').value = lng.toFixed(7);
    }

    // Show existing marker if DB already has a location
    if (hasExisting) {
      setMarker(initialLat, initialLng);
    }

    // Click on map to set / move marker
    map.on('click', function(e) {
      setMarker(e.latlng.lat, e.latlng.lng);
    });

    // Use browser geolocation
    const useMyLocationBtn = document.getElementById('useMyLocation');
    useMyLocationBtn.addEventListener('click', function() {
      if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
      }
      useMyLocationBtn.disabled = true;
      useMyLocationBtn.textContent = 'Locating…';

      navigator.geolocation.getCurrentPosition(function(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        map.setView([lat, lng], 15);
        setMarker(lat, lng);
        useMyLocationBtn.disabled = false;
        useMyLocationBtn.textContent = '📍 Use my current location';
      }, function(err) {
        alert('Unable to get your location: ' + (err.message || 'permission denied'));
        useMyLocationBtn.disabled = false;
        useMyLocationBtn.textContent = '📍 Use my current location';
      }, {enableHighAccuracy:true, timeout:12000});
    });
  </script>
</body>
</html>
