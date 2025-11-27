<?php
session_start();

// ---- DATABASE CONNECTION ----
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "smartaid_db";
$port       = 3307;

// default display values (shown if DB is down)
$total_deliveries = '0+';
$total_users      = '0+';
$total_donations  = '0+';

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if (!$conn->connect_error) {

    // 1) Aid Deliveries = number of reports that have been accepted by a donor
    // (accepted_by is NOT NULL). You can change this to status='Resolved' if you prefer.
    $sql_deliveries = "
        SELECT COUNT(*) AS total_count
        FROM reports
        WHERE accepted_by IS NOT NULL
    ";
    if ($result_deliveries = $conn->query($sql_deliveries)) {
        if ($row = $result_deliveries->fetch_assoc()) {
            $total_deliveries = number_format((int)$row['total_count']) . '+';
        }
        $result_deliveries->free();
    }

    // 2) Active Users = donors + reporters in users table
    $sql_users = "
        SELECT COUNT(*) AS user_count
        FROM users
        WHERE role IN ('donor','reporter')
    ";
    if ($result_users = $conn->query($sql_users)) {
        if ($row = $result_users->fetch_assoc()) {
            $total_users = number_format((int)$row['user_count']) . '+';
        }
        $result_users->free();
    }

    // 3) Items Donated = count of donation records
    // (each row in donations table is one donation posted)
    $sql_donations = "
        SELECT COUNT(*) AS total_donations
        FROM donations
    ";
    if ($result_donations = $conn->query($sql_donations)) {
        if ($row = $result_donations->fetch_assoc()) {
            $total_donations = number_format((int)$row['total_donations']) . '+';
        }
        $result_donations->free();
    }

    $conn->close();
}

// --- End of Database Logic ---

// Default home if nothing else is known
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
?>


<!doctype html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Aid: real-time community donation platform</title>

  <style>


    :root{
      --green-900: #164d27;
      --green-700: #2b6643;
      --green-500: #37a264;
      --green-300: #9be0b5;
      --accent: #1e7a43;
      --muted:#f4f7f5;
      --card-shadow: 0 6px 20px rgba(8,40,20,0.08);
      --radius: 12px;
      --max-width: 1100px;
      --glass: rgba(255,255,255,0.7);
      --footer-bg: #114b2b;
      --footer-text: #eaf8ef;
      --orange-accent: #f99d3d;
    }

    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: Inter, system-ui;
      background: linear-gradient(180deg, #eaf8ef 0%, #f7fff9 100%);
      color:#08321b;
      line-height:1.45;
    }

    .wrap{
      max-width:var(--max-width);
      margin:36px auto;
      padding:24px;
    }

    header.main-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
    }
    .brand{
      display:flex;
      align-items:center;
      gap:12px;
      text-decoration:none;
      color:var(--green-900);
    }
    .logo-img{
      width:52px;
      height:52px;
      border-radius:10px;
      object-fit:cover;
      box-shadow: var(--card-shadow);
    }

    nav.main-nav{
      display:flex;
      gap:12px;
    }
    nav.main-nav a{
      text-decoration:none;
      color:var(--green-900);
      padding:8px 12px;
      border-radius:8px;
      font-weight:600;
    }
    nav.main-nav a.cta{
      background:var(--green-700);
      color:#fff;
      padding:10px 14px;
    }

    /* HERO */
    .hero{
      margin-top:18px;
      background: linear-gradient(90deg,var(--green-300), #e7f7ee);
      border-radius: var(--radius);
      padding:28px;
      box-shadow: var(--card-shadow);
    }
    .eyebrow{
      display:inline-block;
      background: rgba(24,94,48,0.08);
      color:var(--green-700);
      padding:6px 10px;
      border-radius:999px;
      font-weight:700;
      font-size:13px;
      margin-bottom:14px;
    }
    h1{margin:0 0 14px;font-size:28px;color:var(--green-900);}
    p.lead{margin:0 0 18px;color:#204d34;}

    /* HIDE ALL LANGUAGES EXCEPT ENGLISH, KANNADA, HINDI */
.goog-te-menu2-item div,
.goog-te-menu2-item span {
    display: none !important;
}

/* Show only English */
.goog-te-menu2-item div[text="English"],
.goog-te-menu2-item span[text="English"] {
    display: block !important;
}

/* Show only Kannada */
.goog-te-menu2-item div[text="Kannada"],
.goog-te-menu2-item span[text="Kannada"] {
    display: block !important;
}

/* Show only Hindi */
.goog-te-menu2-item div[text="Hindi"],
.goog-te-menu2-item span[text="Hindi"] {
    display: block !important;
}


    /* FEATURES */
    .features{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:16px;
      margin-top:20px;
    }
    .card{
      width: 516px;
      height: auto;
      background:white;
      border-radius:12px;
      padding:18px;
      box-shadow:var(--card-shadow);
      display:flex;
      flex-direction:column;
    }
    .icon{
      width:44px;
      height:44px;
      border-radius:10px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:white;
      font-size:20px;
    }
    .icon.find{background:linear-gradient(135deg,var(--green-500),var(--green-700));}
    .icon.offer{background:linear-gradient(135deg,#4aa77a,#2f9a6a);}
    .icon.donate{background:linear-gradient(135deg,#1e7a43,#2f9a6a);}
    .small-btn{
      padding:8px 12px;
      background:var(--green-700);
      color:white;
      text-decoration:none;
      border-radius:8px;
      font-weight:700;
    }

    /* IMPACT */
    .info-strip{
      margin-top:20px;
      padding:18px;
      border-radius:12px;
      background: linear-gradient(90deg,#fff,#f6fff5);
      /* This is the new grid layout for the 4 sections */
      display:grid; 
      grid-template-columns: 2fr repeat(3, 1fr); 
      gap: 20px; 
      align-items: center;
      box-shadow:var(--card-shadow);
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

  <header class="main-header">
    <a class="brand" href="#">
      <img src="images/circle-logo.png" class="logo-img">
      <div>
        <div style="font-size:16px;font-weight:800;">Smart Aid</div>
        <div style="font-size:12px;color:var(--green-700);">real-time community donation platform</div>
      </div>
    </a>

    <nav class="main-nav">
      <a href="help.php">Help</a>
      <a class="cta" href="role_selection.html">Sign Up</a>

    </nav>
  </header>

  <main>

    <section class="hero">
      <div>
        <span class="eyebrow">Community • Real-time • Local</span>
        <h1>Smart Aid — Connecting surplus with need instantly</h1>
        <p class="lead">Connecting donors, pedestrians, and the needy to reduce food waste. Fast — local — safe.</p>
      </div>
    </section>

    <section class="features">

      <article class="card">
        <div style="display:flex;align-items:center;gap:12px">
          <div class="icon find">🏆</div>
          <h3>Leaderboard</h3>
        </div>
        <p>Top contributors inspiring change and spreading kindness every day.</p>
        <div class="foot">
          <a class="small-btn" href="leaderboard.php">View Leaderboard</a>
        </div>
      </article>

      <article class="card">
        <div style="display:flex;align-items:center;gap:12px">
          <div class="icon donate">📰</div>
          <h3>Feed</h3>
        </div>
        <p>See community posts, updates, and real-time stories from donors and helpers.</p>
        <div class="foot">
          <a class="small-btn" href="feed.php?home=homepage.php">View Feed</a>
        </div>
      </article>

    </section>

    <section class="info-strip">
      <div>
        <h3 style="margin:0;color:var(--green-900);">🌍 Our Impact</h3>
        <p style="margin:6px 0 0;">Together, we reduce food waste and spread kindness.</p>
      </div>

      <div style="text-align:center; padding: 0 10px; border-left: 1px solid #e0e0e0;">
        <div style="font-size:26px;font-weight:800;color:var(--green-700);"><?php echo $total_deliveries; ?></div>
        <div style="font-size:13px; font-weight: 600;">Aid Deliveries</div>
      </div>

      <div style="text-align:center; padding: 0 10px; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0;">
        <div style="font-size:26px;font-weight:800;color:var(--green-700);"><?php echo $total_users; ?></div>
        <div style="font-size:13px; font-weight: 600;">Active Users</div>
      </div>
      
      <div style="text-align:center; padding: 0 10px;">
        <div style="font-size:26px;font-weight:800;color:var(--green-700);"><?php echo $total_donations; ?></div>
        <div style="font-size:13px; font-weight: 600;">Items Donated</div>
      </div>
    </section>

  </main>

</div>

<footer class="footer-container">

  <div class="footer-content">

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

    <div class="footer-links">
      <h5>Site Map</h5>
      <ul>
        <li><a href="homepage.php">Homepage</a></li>
        <li><a href="leaderboard.php?home=homepage.php">Leaderboard</a></li>
<li><a href="help.php?home=homepage.php">Help</a></li>
<li><a href="contact_us.php?home=homepage.php">Contact Us</a></li>
<li><a href="admin_login.php" style="font-size:16px; opacity:0.8;">Admin Login</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom-strip">
    Copyright © 2025, SmartAid.
  </div>

</footer>

</body>
</html>