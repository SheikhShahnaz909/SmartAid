<?php
session_start();
$home_link = 'homepage.php';

// If ?home= is passed in the URL, that ALWAYS wins
if (isset($_GET['home'])) {
    $allowed = ['homepage.php', 'donor_homepage.php', 'reporter_homepage.php'];
    if (in_array($_GET['home'], $allowed, true)) {
        $home_link = $_GET['home'];
    }
}
// If no ?home=, fall back to logged-in role
elseif (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'donor') {
        $home_link = 'donor_homepage.php';
    } elseif ($_SESSION['user_role'] === 'reporter') {
        $home_link = 'reporter_homepage.php';
    }
}
// 1. ACCESS CONTROL GATE: Check for login status AND correct role.
if (!isset($_SESSION['user_id'])) {
    header("Location: donor_homepage.php"); 
    exit();
}
if ($_SESSION['user_role'] !== 'donor') {
    // Logged in, but as the wrong role: Force logout.
    header("Location: logout.php"); 
    exit();
}

// 2. DATA SETUP: Fetch user data and set dashboard link
$rawUserName = $_SESSION['user_name'] ?? $_SESSION['user_email'];
$userName = htmlspecialchars($rawUserName);
$initial = strtoupper($userName[0] ?? 'D');
$dashboard_link = 'donor_homepage.php'; // Define dashboard link for internal use


// ------------------- notification count snippet -------------------
$unread_count = 0;
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'donor') {
    $current_user_id = (int)$_SESSION['user_id'];

    // Try to use existing $conn from config.php, otherwise build one
    if (!isset($conn) || !($conn instanceof mysqli)) {
        if (file_exists(__DIR__ . '/config.php')) include_once __DIR__ . '/config.php';
    }

    if (isset($conn) && ($conn instanceof mysqli)) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0");
        if ($stmt) {
            $stmt->bind_param("i", $current_user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $unread_count = (int)$row['cnt'];
            }
            $stmt->close();
        }
    }
}

// Provide a small JSON endpoint for AJAX polling
if (isset($_GET['notif_count']) && $_GET['notif_count'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'unread' => $unread_count]);
    exit;
}
// ----------------- end notification count snippet -----------------


?>
<!doctype html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Aid — Donor Dashboard</title>

  <style>
    /* Colors from homepage */
    :root{
      --green-900:#114b2b;
      --green-700:#185e34;
      --green-500:#37a264;
      --green-300:#9be0b5;
      --accent:#1e7a43;
      --muted:#f4f7f5;
      --card-shadow:0 6px 20px rgba(8,40,20,0.08);
      --radius:12px;
      --max-width:1100px;
      --orange-accent:#f99d3d;
      --badge-bg:#ff4d4f;
      --footer-bg: #114b2b;
--footer-text: #eaf8ef;}


    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
      background: linear-gradient(180deg, #eaf8ef 0%, #f7fff9 100%);
      color:#08321b;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      line-height:1.45;
    }

    .wrap{ max-width:var(--max-width); margin:36px auto; padding:24px; }

    /* Header (donor) */
    header.main-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
      position:relative;
    }
    .brand{ display:flex; align-items:center; gap:12px; text-decoration:none; color:var(--green-900); }
    .logo-box{ width:52px; height:52px; border-radius:10px; overflow:hidden; box-shadow: var(--card-shadow); }
    .logo-box img{ width:100%; height:100%; object-fit:cover; display:block; }

    .brand .title{
      display:flex;
      flex-direction:column;
      line-height:1;
    }
    .brand .title .main{ font-weight:800; font-size:16px; }
    .brand .title .sub{ font-size:12px; color:var(--green-700); margin-top:2px; }

    .header-controls{ display:flex; align-items:center; gap:12px; }

    .notif-btn{ position:relative; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:transparent; border:none; cursor:pointer; color:var(--green-900); font-size:18px; }
    .notif-badge{ position:absolute; top:6px; right:6px; min-width:18px; height:18px; padding:0 5px; background:var(--badge-bg); color:white; font-size:12px; font-weight:800; border-radius:999px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.12); }

    .user-initial{ width:42px; height:42px; border-radius:50%; background-color:var(--green-700); color:white; display:flex; justify-content:center; align-items:center; font-weight:700; font-size:18px; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.08); }

    /* Dropdown */
    #profileMenu{ position:absolute; top:70px; right:24px; width:200px; background:white; border-radius:10px; box-shadow:0 12px 30px rgba(8,40,20,0.12); display:none; z-index:70; padding:8px 0; }
    #profileMenu ul{ list-style:none; margin:0; padding:0; }
    #profileMenu li{ padding:10px 14px; }
    #profileMenu li:hover{ background:#f2f6f3; }
    #profileMenu a{ text-decoration:none; color:var(--green-900); font-weight:700; display:block; }

    /* Notif panel (small) */
    #notifPanel{ position:absolute; top:70px; right:86px; width:320px; background:white; border-radius:10px; box-shadow:0 12px 30px rgba(8,40,20,0.12); padding:12px; display:none; z-index:65; }

    /* HERO */
    .hero{ margin-top:18px; background: linear-gradient(90deg,var(--green-300), #e7f7ee); border-radius: var(--radius); padding:36px 28px; display:flex; gap:24px; align-items:center; box-shadow: var(--card-shadow); }
    .hero-left{ flex:1; }
    .eyebrow{ display:inline-block; background: rgba(24,94,48,0.08); color:var(--green-700); padding:6px 10px; border-radius:999px; font-weight:700; font-size:13px; margin-bottom:14px; }
    h1#hero-heading{ margin:0 0 8px 0; font-size:56px; color:var(--green-900); line-height:1; font-weight:900; }
    p.lead{ margin:10px 0 0 0; color:#204d34; opacity:0.95; font-size:16px; max-width:70%; }

    /* ACTION CARDS - only Community Donor Feed + Leaderboard */
    .donor-actions{ margin-top:28px; display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:20px; }
    .action-card{ background:white; border-radius:var(--radius); padding:20px; box-shadow:var(--card-shadow); min-height:140px; display:flex; flex-direction:column; gap:12px; color:inherit; text-decoration:none; }
    .action-card .icon{ width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; color:white; }
    .action-card .icon.feed{ background: linear-gradient(135deg,#4aa77a,#2f9a6a); }
    .action-card .icon.lb{ background: linear-gradient(135deg,#f0b45a,#f99d3d); }
    .action-card h3{ margin:0; font-size:18px; color:var(--green-900); }
    .action-card p{ margin:0; color:#2f5c45; font-size:14px; }
    .open-btn{ margin-top:auto; display:inline-block; padding:9px 14px; background:var(--green-700); color:white; border-radius:8px; text-decoration:none; font-weight:700; }

    .donation-cta {
  width: 100%;
  max-width: 650px;
  margin: 30px auto;
  padding: 30px;
  background: linear-gradient(135deg, #e9fff3, #d4f7e8);
  border-radius: 18px;
  text-align: center;
  box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.08);
  transition: transform .3s ease, box-shadow .3s ease;
}

.donation-cta:hover {
  transform: translateY(-5px);
  box-shadow: 0px 18px 35px rgba(0, 0, 0, 0.12);
}

.donation-cta h2 {
  font-size: 24px;
  font-weight: 700;
  color: #145c39;
  margin-bottom: 8px;
}

.donation-cta p {
  font-size: 15px;
  color: #316d52;
  margin-bottom: 22px;
}

.donation-btn {
  display: inline-block;
  padding: 14px 28px;
  background: #0b683b;
  color: white;
  border-radius: 50px;
  font-size: 17px;
  font-weight: 600;
  text-decoration: none;
  transition: background .3s ease, transform .25s ease;
  box-shadow: 0px 6px 18px rgba(11,104,59,0.22);
}

.donation-btn:hover {
  background: #084c2b;
  transform: scale(1.06);
}

@media (max-width: 600px) {
  .donation-cta {
    padding: 22px;
  }
  .donation-cta h2 {
    font-size: 20px;
  }
  .donation-btn {
    width: 90%;
    font-size: 16px;
  }
}


    /* Latest Donor Activity - KEEP EXACTLY AS YOUR UPLOADED FILE */
    .feed-section{ margin-top:40px; padding-top:20px; border-top:1px solid #e7f7ee; }
    .feed-section h2{ font-size:22px; color:var(--green-900); margin-bottom:18px; }
    .feed-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:20px; }
    .feed-post{ background:white; border-radius:var(--radius); padding:18px; box-shadow:0 6px 18px rgba(8,40,20,0.06); }
    .post-header{ display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .post-avatar{ width:40px;height:40px;border-radius:50%; background:var(--orange-accent); color:var(--green-900); display:flex;align-items:center;justify-content:center;font-weight:700; }
    .post-info strong{ color:var(--green-700); }
    .post-info small{ display:block; color:#6b8b74; font-size:12px; }
    .post-body p{ margin:0 0 12px 0; font-size:15px; color:#234b36; }
    .post-actions{ display:flex; justify-content:space-between; align-items:center; border-top:1px solid #eee; padding-top:10px; font-size:14px; color:var(--green-700); }
    .post-actions button{ background:none; border:none; color:var(--green-700); cursor:pointer; font-size:14px; font-weight:600; display:inline-flex; align-items:center; gap:8px; }
    .post-actions button:hover{ opacity:0.85; }

    /* Footer (unchanged style token) */
    .footer-container{ margin-top:60px; background: var(--orange-accent); color: var(--green-900); border-radius: var(--radius); text-align:center; padding:12px; font-weight:700; }

    @media(max-width:900px){
      #notifPanel{ right:8px; left:8px; top:76px; width:auto; }
      .hero{ flex-direction:column; text-align:center; padding:30px 18px; }
      .hero-heading h1{
         font-size:30px; 
        }

      .donor-actions{ grid-template-columns:1fr; }
      .feed-container{ grid-template-columns:1fr; }
    }

    .notif-badge.hidden { display:none; }

    hero-heading h1{
         font-size:30px; 
        }
    /* FOOTER */
    .footer-container{
      margin-top:60px;
      background: var(--footer-bg);
      color: var(--footer-text);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px rgba(8,40,20,0.15);
    }

    .footer-content {
      padding: 40px 40px 30px 40px;
      display: grid;
      grid-template-columns: 2.5fr 1fr 1fr;
      gap: 40px;
      max-width: var(--max-width);
      margin: 0 auto;
    }

    .footer-left h4{
      font-size:18px;
      font-weight:800;
      display:flex;
      align-items:center;
      gap:8px;
    }

    .footer-left p{
      opacity:0.8;
      margin-top:12px;
      max-width:300px;
    }

    .social-icons{
      display:flex;
      gap:15px;
      margin-top:20px;
    }
    .social-icons a{
      color:var(--footer-text);
      opacity:0.75;
      font-size:20px;
      text-decoration:none;
    }
    .social-icons a:hover{opacity:1;}

    .back-to-top-btn{
      display:inline-flex;
      align-items:center;
      gap:8px;
      margin-top:25px;
      padding:10px 18px;
      border:2px solid var(--footer-text);
      color:var(--footer-text);
      text-decoration:none;
      border-radius:8px;
      font-weight:600;
    }

    .footer-links h5{
      font-size:16px;
      font-weight:700;
    }
    .footer-links ul{
      padding:0;margin:0;list-style:none;
    }
    .footer-links ul li{margin-bottom:8px;}
    .footer-links ul li a{
      color:var(--footer-text);
      opacity:0.8;
      text-decoration:none;
    }
    .footer-links ul li a:hover{text-decoration:underline;opacity:1;}

    .footer-bottom-strip{
      background: var(--orange-accent);
      padding: 8px;
      text-align:center;
      color:var(--green-900);
      font-weight:600;
      border-bottom-left-radius:var(--radius);
      border-bottom-right-radius:var(--radius);
    }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- HEADER: donor header restored -->
    <header class="main-header">
      <a class="brand" href="#">
        <div class="logo-box">
          <img src="images/circle-logo.png" alt="Smart Aid Logo">
        </div>
        <div class="title">
          <div class="main">Smart Aid</div>
          <div class="sub">real-time community donation platform</div>
        </div>
      </a>

      <div class="header-controls">
        <button id="notifBtn" class="notif-btn" aria-haspopup="true" aria-expanded="false" title="Notifications">
  🔔
  <span id="notifBadge" class="notif-badge"><?php echo ($unread_count > 99 ? '99+' : (int)$unread_count); ?></span>
</button>

        <div id="userInitial" class="user-initial" title="Open profile menu">
    <?php echo $initial; ?>
</div>

      </div>

      <!-- Notifications panel (hidden by default) -->
      <div id="notifPanel" role="dialog" aria-hidden="true">
        <h4 style="margin:0 0 8px 0;color:var(--green-900)">Notifications</h4>
<div style="font-size:14px;color:#234b36">
  <a href="donor_notifications.php" style="color:#1d7940; font-weight:600;">View Notifications</a>
</div>
      </div>

      <!-- Profile dropdown -->
      <div id="profileMenu" aria-hidden="true">
        <ul>
          <li><a href="donor_profile.php">View Profile</a></li>
          <li><a href="my_donation.php">My Donations</a></li>
          <li><a href="donor-settings.php">Settings</a></li>
          <li><a href="donor_set_location.php" class="btn-location">Set / Update My Location</a></li>
          <li style="border-top:1px solid #f1f6f3;margin-top:6px;padding-top:8px;"><a href="logout.php">Log Out</a></li>
        </ul>
      </div>
    </header>

    <!-- HERO -->
    <main>
      <section class="hero" aria-labelledby="hero-heading">
        <div class="hero-left">
          <span class="eyebrow">Community • Real-time • Local</span>
          <h1 class="hero-heading" id="hero-heading">Welcome, <?php echo $userName; ?></h1>
          <p class="lead">Thank you for your generosity — this is your place to see recent community donations and updates.</p>
        </div>
      </section>

      <!-- ACTION CARDS -->
      <section class="donor-actions" aria-label="Quick actions">
        <a class="action-card" href="feed.php?home=donor_homepage.php">
          <div class="icon feed">💬</div>
          <h3>Community Feed</h3>
          <p>See what other donors in the community are contributing.</p>
          <span class="open-btn">Open Feed</span>
        </a>

        <a class="action-card" href="leaderboard.php">
          <div class="icon lb">🏆</div>
          <h3>Leaderboard</h3>
          <p>Check your ranking based on contributions.</p>
          <span class="open-btn">Open Leaderboard</span>
        </a>
      </section>

          <!-- sample CTA post-card -->
          <!-- Improved Post Donation CTA Card -->
<div class="donation-cta">
  <h2>Ready to Make an Impact?</h2>
  <p>Your contribution can help someone today. Share what you can offer.</p>

  <a href="donor_post.php" class="donation-btn">
    Post a New Donation
  </a>
</div>

        </div>
      </section>
    </main>

  </div>

  <!-- FOOTER -->
<footer class="footer-container">

  <div class="footer-content">

    <!-- LEFT -->
    <div class="footer-left">
      <h4>
        <svg viewBox="0 0 24 24" style="width:26px;height:26px;fill:var(--orange-accent);">
          <path d="M12 2L2 22H22L12 2ZM12 11.5L8.5 19H15.5L12 11.5Z"/>
        </svg>
        SMART AID
      </h4>

      <p>Empowering communities with a real-time platform to connect surplus food with those in need, reducing waste and fighting hunger.</p>

      <div class="social-icons">
        <a href="https://x.com/SmartAid2025"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://www.facebook.com/profile.php?id=61584193043021"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/smartaid_donation/"><i class="fa-brands fa-instagram"></i></a>
      </div>

      <a class="back-to-top-btn" href="#top">↑ Back to Top</a>
    </div>

    <!-- CENTER LINKS -->
    <div class="footer-links">
      <h5>Site Map</h5>
                <ul>
                    <!-- FIX 5: Dynamic Links -->
                    <li><a href="<?php echo $dashboard_link; ?>">Homepage</a></li>
                    <li><a href="leaderboard.php?home=donor_homepage.php">Leaderboard</a></li>
                    <li><a href="help.php?home=donor_homepage.php">Help</a></li>
                    <li><a href="contact_us.php?home=donor_homepage.php">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="footer-bottom-strip">
        Copyright © 2025,SmartAid. All Rights Reserved.
    </div>
  </footer>

  <script>

    (function(){
  const badge = document.getElementById('notifBadge');
  if (!badge) return;

  const pollIntervalMs = 10000; // 10s
  async function updateBadge() {
    try {
      const resp = await fetch(window.location.pathname + '?notif_count=1', { cache: 'no-store' });
      if (!resp.ok) throw new Error('Network response not ok');
      const j = await resp.json();
      if (j && j.success) {
        const n = parseInt(j.unread || 0, 10);
        if (n > 0) {
          badge.classList.remove('hidden');
          badge.textContent = (n > 99 ? '99+' : String(n));
        } else {
          badge.classList.add('hidden');
          badge.textContent = '0';
        }
      }
    } catch (e) {
      // fail silently; keep existing badge
      console.error('Notif poll failed', e);
    }
  }

  // initial run (page already has server value, but refresh to get latest)
  setTimeout(updateBadge, 500);
  setInterval(updateBadge, pollIntervalMs);
})();


    // profile dropdown toggle
    const userInitial = document.getElementById('userInitial');
    const profileMenu = document.getElementById('profileMenu');
    const notifBtn = document.getElementById('notifBtn');
    const notifPanel = document.getElementById('notifPanel');

    if (userInitial) {
      userInitial.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
        if (notifPanel) notifPanel.style.display = 'none';
      });
    }

    if (notifBtn) {
      notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (notifPanel) {
          notifPanel.style.display = notifPanel.style.display === 'block' ? 'none' : 'block';
        }
        if (profileMenu) profileMenu.style.display = 'none';
      });
    }

    document.addEventListener('click', (e) => {
      if (profileMenu && !profileMenu.contains(e.target) && userInitial && !userInitial.contains(e.target)) {
        profileMenu.style.display = 'none';
      }
      if (notifPanel && notifBtn && !notifBtn.contains(e.target) && !notifPanel.contains(e.target)) {
        notifPanel.style.display = 'none';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (profileMenu) profileMenu.style.display = 'none';
        if (notifPanel) notifPanel.style.display = 'none';
      }
    });
    
  </script>
  
</body>
</html>
