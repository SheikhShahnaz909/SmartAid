<?php
// reporter_homepage.php

session_start();

// 🔐 STRICT BUT SIMPLE ACCESS CONTROL: reporter only
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'reporter') {
    header("Location: reporter_login.php");
    exit();
}

// 2. DATA SETUP: Fetch user data for display
$rawUserName = $_SESSION['user_name'] ?? ($_SESSION['user_email'] ?? 'Reporter');
$userName    = htmlspecialchars($rawUserName, ENT_QUOTES, 'UTF-8');
$display_name = $userName . '!';

$initial = strtoupper($userName[0] ?? 'R');
$dashboard_link = 'reporter_homepage.php';
?>
<!doctype html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Aid - Reporter Dashboard</title>
  <style>
    :root{
      --green-900: #114b2b;
      --green-700: #185e34;
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
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg, #eaf8ef 0%, #f7fff9 100%);
      color:var(--green-900);
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
      gap:16px;
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
    }

    nav.main-nav{
      display:flex;
      gap:12px;
      align-items:center;
    }
    nav.main-nav a{
      text-decoration:none;
      color:var(--green-900);
      padding:8px 12px;
      border-radius:8px;
      font-weight:600;
      font-size:14px;
    }

    .user-menu {
      position:relative;
      display:inline-block;
    }

    .user-initial {
      width:40px;
      height:40px;
      border-radius:50%;
      background:var(--green-700);
      color:white;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      font-size:16px;
      text-transform:uppercase;
      cursor:pointer;
      user-select:none;
    }

    .dropdown {
      position:absolute;
      right:0;
      top:48px;
      background:white;
      border-radius:10px;
      box-shadow: 0 8px 30px rgba(10,30,15,0.12);
      min-width:160px;
      overflow:hidden;
      transform-origin: top right;
      display:none;
      z-index:50;
    }
    .dropdown.show{ display:block; }
    .dropdown a{
      display:block;
      padding:10px 14px;
      text-decoration:none;
      color:var(--green-900);
      font-weight:600;
      font-size:14px;
      border-bottom:1px solid #f0f4f2;
    }
    .dropdown a:last-child{ border-bottom: none; }
    .dropdown a:hover{ background:var(--muted); }

    .hero{
      margin-top:18px;
      background: linear-gradient(90deg,var(--green-300), #e7f7ee);
      border-radius: var(--radius);
      padding:28px;
      display:flex;
      gap:24px;
      align-items:center;
      box-shadow: var(--card-shadow);
    }

    h1{
      margin:0 0 14px 0;
      font-size:28px;
      color:var(--green-900);
    }

    p.lead{
      margin:0 0 18px 0;
      color:#204d34;
      opacity:0.95;
    }

    .features{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap:20px;
      margin-top:20px;
    }
    .card{
      background:white;
      border-radius:12px;
      padding:18px;
      box-shadow: var(--card-shadow);
      display:flex;
      flex-direction:column;
      gap:12px;
      min-height: 220px;
      height:100%;
    }

    .icon{
      width:44px;
      height:44px;
      border-radius:10px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      color:white;
    }
    .icon.report{ background: linear-gradient(135deg,var(--green-500),var(--green-700)); }
    .icon.find{ background: linear-gradient(135deg,#4aa77a,#2f9a6a); }

    .card p {
        color: #08321b;
        margin-top: 5px;
    }

    .small-btn{
      padding:8px 12px;
      border-radius:8px;
      background:var(--green-700);
      color:#fff;
      font-weight:700;
      text-decoration:none;
      font-size:13px;
      margin-top: auto;
      display: inline-block;
    }

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

    @media (max-width:720px){
      .features{ grid-template-columns: 1fr; }
      nav.main-nav{ gap:8px; }
      .footer-content {grid-template-columns: 1fr;gap:20px;}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="main-header">
      <a class="brand" href="<?php echo $dashboard_link; ?>" aria-label="Smart Aid Home">
        <img src="images/circle-logo.png" alt="Smart Aid Logo" class="logo-img" />
        <div>
          <div style="font-weight:800;font-size:16px">Smart Aid</div>
          <div style="font-size:12px;color:var(--green-700);margin-top:2px">Reporter Dashboard</div>
        </div>
      </a>

      <nav class="main-nav">
        <a href="help.php?home=reporter_homepage.php">Help</a>
        <div class="user-menu">
          <div class="user-initial" id="userInitial" aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown">
            <?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?> 
          </div>
          <div class="dropdown" id="userDropdown" role="menu" aria-labelledby="userInitial">
            <a href="reporter_profile.php" role="menuitem">View Profile</a>
            <a href="reporter-settings.php" role="menuitem">Settings</a>
            <a href="my_reports.php" role="menuitem">My Reports</a>
            <a href="logout.php" role="menuitem">Log Out</a>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <section class="hero">
        <div>
          <h1>Welcome, <span id="reporterName"><?php echo $display_name; ?></span> 👋</h1>
          <p class="lead">Track, report, and connect communities in real time. Your efforts make a difference every day.</p>
        </div>
      </section>

      <section class="features">
        <article class="card">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="icon report">📍</div>
            <h3>Report Need</h3>
          </div>
          <p>Spot someone in need? Report instantly with location and details.</p>
          <div class="foot">
            <a class="small-btn" href="report_need_form.php">Report Now</a>
          </div>
        </article>

        <article class="card">
  <div style="display:flex;align-items:center;gap:12px">
    <div class="icon find">🍽️</div>
    <h3>Community Feed</h3>
  </div>
  <p>See what other donors in the community are contributing.</p>
  <div class="foot">
    <a class="small-btn" href="feed.php?home=reporter_homepage.php">View Feed</a>
  </div>
</article>


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
        <li><a href="<?php echo $dashboard_link; ?>">Homepage</a></li>
        <li><a href="leaderboard.php?home=reporter_homepage.php">Leaderboard</a></li>
        <li><a href="help.php?home=reporter_homepage.php">Help</a></li>
        <li><a href="contact_us.php?home=reporter_homepage.php">Contact Us</a></li>  
      </ul>
    </div>
  </div>
  <div class="footer-bottom-strip">
    Copyright © 2025, SmartAid.
  </div>
</footer>

<script>
  const userInitial = document.getElementById('userInitial');
  const userDropdown = document.getElementById('userDropdown');
  userInitial.addEventListener('click', (e)=>{
    e.stopPropagation();
    const isShown = userDropdown.classList.toggle('show');
    userInitial.setAttribute('aria-expanded', isShown ? 'true' : 'false');
  });

  document.addEventListener('click', (e)=>{
    const menu = document.querySelector('.user-menu');
    if(menu && !menu.contains(e.target)){
      userDropdown.classList.remove('show');
      userInitial.setAttribute('aria-expanded', 'false');
    }
  });

  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){
      userDropdown.classList.remove('show');
      userInitial.setAttribute('aria-expanded', 'false');
    }
  });
</script>
</body>
</html>
