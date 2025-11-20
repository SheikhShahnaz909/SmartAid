<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: reporter_login.php"); exit(); }
$userName = $_SESSION['user_name'];
$initial = strtoupper($userName[0]);
?>
<!doctype html>
<html lang="en">
<head>
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

    /* Hero */
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
      grid-template-columns: repeat(2, 1fr);
      gap:16px;
      margin-top:20px;
      justify-items:center;
      align-items:flex-start;
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
    .icon.alerts{ background: linear-gradient(135deg,#1e7a43,#2f9a6a); }

    .small-btn{
      padding:8px 12px;
      border-radius:8px;
      background:var(--green-700);
      color:#fff;
      font-weight:700;
      text-decoration:none;
      font-size:13px;
    }

    /* Footer */
    .footer-container{
      margin-top:60px;
      background: var(--footer-bg);
      color: var(--footer-text);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px rgba(8,40,20,0.15);
      position: relative;
    }
    .footer-content {
      padding: 40px 40px 30px 40px;
      display: grid;
      grid-template-columns: 2.5fr 1fr 1fr;
      gap: 40px;
      font-size: 14px;
      max-width: var(--max-width);
      margin: 0 auto;
    }
    .footer-left h4{margin:0 0 10px 0; font-size:18px;font-weight:800;color:var(--footer-text);}
    .footer-bottom-strip{
      background: var(--orange-accent);
      text-align:center;
      padding:8px;
      color:var(--green-900);
      font-size:13px;
      border-bottom-left-radius: var(--radius);
      border-bottom-right-radius: var(--radius);
      font-weight:600;
    }

    @media (max-width:720px){
      .features{ grid-template-columns: 1fr; }
      nav.main-nav{ gap:8px; }
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
  
  </style>
</head>
<body>
  <div class="wrap">
    <header class="main-header">
      <a class="brand" href="#" aria-label="Smart Aid Home">
        <img src="circle-logo.png" alt="Smart Aid Logo" class="logo-img" />
        <div>
          <div style="font-weight:800;font-size:16px">Smart Aid</div>
          <div style="font-size:12px;color:var(--green-700);margin-top:2px">Reporter Dashboard</div>
        </div>
      </a>

      <nav class="main-nav">
        <a href="help.html">Help</a>
        <a href="nearby_needs.html">Nearby Needs</a>

        <div class="user-menu">
          <div class="user-initial" id="userInitial" aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown">R ▾</div>
          <div class="dropdown" id="userDropdown" role="menu" aria-labelledby="userInitial">
            <a href="view_profile.html" role="menuitem">View Profile</a>
            <a href="my_reports.html" role="menuitem">My Reports</a>
            <a href="settings.html" role="menuitem">Settings</a>
            <a href="signout.php" role="menuitem">Sign Out</a>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <section class="hero">
        <div>
          <h1>Welcome, <span id="reporterName">Reporter</span> 👋</h1>
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
            <a class="small-btn" href="report_form.html">Report Now</a>
          </div>
        </article>

        <article class="card">
          <div style="display:flex;align-items:center;gap:12px">
            <div class="icon find">🍽️</div>
            <h3>Find Food</h3>
          </div>
          <p>Locate nearby food distributions, community kitchens, or donor offers available right now.</p>
          <div class="foot">
            <a class="small-btn" href="find_food.html">Find Food</a>
          </div>
        </article>
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
        <a href="#">X</a>
        <a href="#">in</a>
        <a href="#">f</a>
        <a href="#">o</a>
      </div>

      <a class="back-to-top-btn" href="#top">↑ Back to Top</a>
    </div>

    <!-- CENTER LINKS -->
    <div class="footer-links">
      <h5>Site Map</h5>
      <ul>
        <li><a href="#">Homepage</a></li>
        <li><a href="#">Leaderboard</a></li>
        <li><a href="#">How It Works</a></li>
        <li><a href="#">Contact Us</a></li>
        
      </ul>
    </div>

  </div>

  <div class="footer-bottom-strip">
    Copyright © 2025, SmartAid.
  </div>

</footer>




  <script>
    // Populate reporter name/initial from localStorage or PHP session fallback
    const email = localStorage.getItem('reporterEmail');
    if(email){
      const initial = email.charAt(0).toUpperCase();
      const namePart = email.split('@')[0];
      document.getElementById('userInitial').textContent = initial + ' ▾';
      document.getElementById('reporterName').textContent = namePart.charAt(0).toUpperCase() + namePart.slice(1);
    } else {
      // if PHP session available, the server-rendered initial will already be present
      const phpInitial = '<?php echo $initial; ?>';
      if(phpInitial){
        document.getElementById('userInitial').textContent = phpInitial + ' ▾';
      }
    }

    // Dropdown toggle
    const userInitial = document.getElementById('userInitial');
    const userDropdown = document.getElementById('userDropdown');

    userInitial.addEventListener('click', (e)=>{
      const isShown = userDropdown.classList.toggle('show');
      userInitial.setAttribute('aria-expanded', isShown ? 'true' : 'false');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e)=>{
      if(!document.querySelector('.user-menu').contains(e.target)){
        userDropdown.classList.remove('show');
        userInitial.setAttribute('aria-expanded', 'false');
      }
    });

    // Keyboard accessibility: close on Escape
    document.addEventListener('keydown', (e)=>{
      if(e.key === 'Escape'){
        userDropdown.classList.remove('show');
        userInitial.setAttribute('aria-expanded', 'false');
      }
    });
  </script>
</body>
</html>
