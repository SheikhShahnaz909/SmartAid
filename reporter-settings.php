<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Aid — Reporter Settings</title>

  <style>
    /* Donor-style dropdown */
.d-toggle {
  font-weight:700;
  background:linear-gradient(120deg,#0ea5a3,#0f766e);
  color:white;
  padding:8px 12px;
  border-radius:8px;
  cursor:pointer;
  user-select:none;
  display:flex;
  align-items:center;
  justify-content:center;
  width:42px;
  height:42px;
}

.d-toggle .initial {
  font-size:16px;
  font-weight:800;
}

.d-dropdown{
  position:absolute;
  top:52px;
  right:0;
  background:white;
  border-radius:10px;
  box-shadow:0 6px 18px rgba(0,0,0,0.18);
  width:180px;
  display:none;
  overflow:hidden;
  z-index:100;
}

.d-dropdown a{
  display:block;
  padding:10px 14px;
  color:#0f766e;
  font-weight:600;
  text-decoration:none;
  border-bottom:1px solid #eee;
}

.d-dropdown a:last-child{
  border-bottom:none;
}

.d-dropdown a.signout{
  color:#b91c1c;
  font-weight:700;
}

    :root{
      --bg-overlay: rgba(4,15,10,0.55);
      --card-bg: rgba(255,255,255,0.95);
      --text: #042023;
      --accent: #0f766e;
      --danger: #dc2626;
      --muted: #4b5563;
      --shadow: 0 10px 30px rgba(2,6,23,0.35);
      --radius:12px;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    html,body{height:100%;margin:0;}
    body{
      /* background image + subtle dark overlay (signin-background.jpg expected at same folder) */
      background:
        linear-gradient(var(--bg-overlay), var(--bg-overlay)),
        url('signin-background.jpeg.jpg') center/cover no-repeat fixed;
      color:var(--text);
    }

    /* Header */
    header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:18px 28px;
      backdrop-filter: blur(6px);
    }

    .header-left{display:flex;align-items:center;gap:14px}
    /* donor-like circular back button */
    .back-circle{
      width:48px;height:48px;border-radius:50%;display:inline-grid;place-items:center;
      background:rgba(255,255,255,0.06);border:2px solid rgba(255,255,255,0.12);
      cursor:pointer;box-shadow:var(--shadow);
    }
    .back-circle svg{width:18px;height:18px;transform:translateX(-1px);stroke:white;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}

    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:48px;height:48px;border-radius:50%;overflow:hidden;box-shadow:var(--shadow)}
    .logo img{width:100%;height:100%;object-fit:cover}
    .brand h1{color:white;font-size:18px;margin:0;font-weight:700}
    .brand .sub{font-size:13px;color:rgba(255,255,255,0.85)}

    /* profile dropdown */
    .profile-wrap{position:relative}
    .profile-btn{
      display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;border:none;
      background:linear-gradient(120deg,#0ea5a3,#0f766e);color:white;font-weight:700;cursor:pointer;
      box-shadow:0 8px 20px rgba(11,78,72,0.12);
    }
    .profile-avatar{width:32px;height:32px;border-radius:8px;display:inline-grid;place-items:center;background:rgba(255,255,255,0.08);font-weight:800}
    .profile-menu{
      position:absolute;right:0;top:52px;min-width:200px;background:var(--card-bg);box-shadow:var(--shadow);
      border-radius:10px;padding:8px;display:none;z-index:80;
    }
    .profile-menu button{display:block;width:100%;text-align:left;padding:8px;border-radius:8px;border:none;background:transparent;cursor:pointer;font-weight:700}
    .profile-menu hr{border:none;border-top:1px solid rgba(6,51,43,0.06);margin:8px 0}

    /* Main layout */
    main{max-width:1100px;margin:32px auto;padding:18px;display:grid;grid-template-columns:320px 1fr;gap:20px;}
    .panel{background:var(--card-bg);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow)}
    .profile-quick{display:flex;gap:12px;align-items:center;margin-bottom:12px}
    .quick-avatar{width:56px;height:56px;border-radius:12px;background:linear-gradient(120deg,#ffd,#f8b);display:flex;align-items:center;justify-content:center;font-weight:700}
    .small-muted{font-size:13px;color:var(--muted)}
    nav.settings-menu{display:flex;flex-direction:column;gap:8px;margin-top:8px}
    nav.settings-menu button{
      background:transparent;border:none;text-align:left;padding:10px;border-radius:8px;font-weight:700;cursor:pointer;color:#0b3b35;
    }
    nav.settings-menu button.active{
      background:linear-gradient(90deg,rgba(15,118,110,0.08),transparent);
      color:var(--accent);
    }

    .card{background:var(--card-bg);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow)}
    .row{display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap}
    .field{flex:1 1 200px;display:flex;flex-direction:column;gap:6px}
    label{font-size:13px;color:#47585a}
    input[type="text"],input[type="email"],input[type="tel"]{
      padding:10px;border-radius:10px;border:1px solid #e6eef6;background:transparent;font-size:14px;
    }
    .switch{width:46px;height:28px;background:#e6eef6;border-radius:20px;position:relative;padding:4px;cursor:pointer}
    .switch .dot{width:20px;height:20px;background:white;border-radius:50%;position:absolute;left:4px;top:4px;transition:.18s}
    .switch.on{background:linear-gradient(90deg,var(--accent),#0891b2)}
    .switch.on .dot{left:22px}
    .btn-primary{background:var(--accent);color:white;border:none;padding:10px 14px;border-radius:10px;font-weight:700;cursor:pointer}
    .btn-ghost{background:transparent;border:1px solid rgba(6,51,43,0.1);padding:8px 12px;border-radius:10px;cursor:pointer}
    .btn-danger{background:var(--danger);color:white;border:none;padding:10px;border-radius:10px;cursor:pointer}
    .danger{background:rgba(255,240,240,0.6);border:1px solid rgba(220,38,38,0.08);padding:12px;border-radius:10px;margin-top:16px}

    /* responsive */
    @media (max-width:900px){
      main{grid-template-columns:1fr; padding:12px; margin:18px auto;}
      .brand h1{font-size:16px}
    }
  </style>
</head>

<body>
  
<header>
    <div style="display:flex;align-items:center;gap:14px">

      <!-- Super-thick centered back arrow -->
      <div onclick="window.location.href='reporter_homepage.php'"
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
  
      </button>

      <div class="brand" role="banner">
        <div class="logo"><img src="circle-logo.jpg" alt="Smart Aid logo"></div>
        <div>
          <h1>Smart Aid</h1>
          <!-- changed top label to Dashboard as requested -->
          <div class="sub">Reporter Settings</div>
        </div>
      </div>
    </div>

  <!-- profile dropdown (compact) -->
<!-- Donor-style dropdown for Reporter -->
<div style="position:relative;">
  <div id="dropdownToggle" class="d-toggle">
    <div class="initial">R</div>
  </div>

  <div id="dropdownMenu" class="d-dropdown">
    <a href="reporter_homepage.html">Home</a>
    <a href="reporter_profile.html">View Profile</a>
    <a href="my_reports.php">My Reports</a>
    
    <a href="logout.php" class="signout">Log Out</a>
  </div>
</div>



</header>

  <main>

    <!---------------- LEFT NAV ---------------->
    <aside class="panel" aria-label="Settings navigation">
      <div class="profile-quick">
        <div class="quick-avatar" id="quickAvatar">R</div>
        <div>
          <div id="displayNameLeft" style="font-weight:800;cursor:pointer">Reporter</div>
          <div class="small-muted" id="displayEmailLeft">reporter@example.com</div>
        </div>
      </div>

      <nav class="settings-menu" role="navigation" aria-label="Settings sections">
        <button class="active" data-section="account">Account</button>
        <button data-section="privacy">Privacy & Security</button>
        <button data-section="support">Help & Support</button>
      </nav>
    </aside>

    <section>
      <!------------- ACCOUNT ------------->
      <div id="section-account" class="card">
        <h2>Account</h2>
        <p class="small-muted">Update your profile and login details.</p>

        <div class="row">
          <div class="field">
            <label for="nameInput">Full name</label>
            <input id="nameInput" type="text" placeholder="Your name">
          </div>
          <div class="field">
            <label for="emailInput">Email</label>
            <input id="emailInput" type="email" placeholder="you@example.com">
          </div>
          <div class="field">
            <label for="phoneInput">Phone</label>
            <input id="phoneInput" type="tel" placeholder="+91 9xxxxxxxxx">
          </div>
        </div>

        <div class="row">
          <div class="field">
            <label>Change password</label>
            <button class="btn-ghost" id="changePasswordBtn">Change password</button>
          </div>

          <div class="field">
            <label>Last login</label>
            <div class="small-muted" id="lastLogin">—</div>
          </div>
        </div>

        <button class="btn-primary" id="saveAccount">Save changes</button>
      </div>

      <!------------- PRIVACY ------------->
      <div id="section-privacy" class="card" style="display:none;margin-top:16px">
        <h2>Privacy & Security</h2>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
          <div>
            <strong>Show my reporter profile publicly</strong>
            <div class="small-muted">Turn off to stay anonymous.</div>
          </div>
          <div id="profileSwitch" class="switch on" aria-pressed="true"><div class="dot"></div></div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <strong>Two-factor authentication</strong>
            <div class="small-muted">Adds extra security.</div>
          </div>
          <div id="twofaSwitch" class="switch"><div class="dot"></div></div>
        </div>
      </div>

      <!------------- SUPPORT ------------->
      <div id="section-support" class="card" style="display:none;margin-top:16px">
        <h2>Help & Support</h2>

        <div>
          <strong>FAQ</strong>
          <div class="small-muted">Reporter common questions</div>
        </div>

        <div style="margin-top:12px">
          <strong>Contact Support</strong>
          <div class="small-muted">support@smart-aid.org</div>
        </div>
      </div>

    </section>
  </main>

  <script>
    /* ----- Navigation between sections ----- */
    const navBtns = document.querySelectorAll("nav.settings-menu button");
    const panels = {
      account: document.getElementById("section-account"),
      privacy: document.getElementById("section-privacy"),
      support: document.getElementById("section-support")
    };
    function showSection(name){
      Object.values(panels).forEach(p=>p.style.display="none");
      panels[name].style.display="";
      navBtns.forEach(b=>b.classList.toggle("active", b.dataset.section===name));
    }
    navBtns.forEach(b=>b.addEventListener("click", ()=> showSection(b.dataset.section)));
    showSection("account");

   /* ----- safe back button (no error if element missing) ----- */
const backBtnEl = document.getElementById('backBtn');
if (backBtnEl) {
  backBtnEl.addEventListener('click', ()=> {
    window.location.href = 'reporter_homepage.html';
  });
}

  /* ----- robust profile dropdown (only R toggles menu) ----- */
(function(){
  const profileWrap = document.querySelector('.profile-wrap');
  const profileToggle = document.getElementById('profileToggle');
  const profileMenu = document.getElementById('profileMenu');

  if (!profileWrap || !profileToggle || !profileMenu) {
    console.warn('Profile dropdown: missing element(s)', { profileWrap, profileToggle, profileMenu });
    return;
  }

  // Ensure hidden initially
  profileMenu.style.display = profileMenu.style.display || 'none';
  profileToggle.setAttribute('aria-expanded', 'false');
  profileMenu.setAttribute('aria-hidden', 'true');

  function openMenu(){
    profileMenu.style.display = 'block';
    profileToggle.setAttribute('aria-expanded','true');
    profileMenu.setAttribute('aria-hidden','false');
    profileWrap.classList.add('open');
  }
  function closeMenu(){
    profileMenu.style.display = 'none';
    profileToggle.setAttribute('aria-expanded','false');
    profileMenu.setAttribute('aria-hidden','true');
    profileWrap.classList.remove('open');
  }
  function toggleMenu(){
    if (profileMenu.style.display === 'block') closeMenu(); else openMenu();
  }

  // Toggle on R click
  profileToggle.addEventListener('click', function(e){
    e.stopPropagation();
    toggleMenu();
  });

  // Close when clicking outside
  document.addEventListener('click', function(e){
    if (!profileWrap.contains(e.target)) closeMenu();
  });

  // Close on Escape
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeMenu();
  });

  // Menu item handlers (kept same targets)
  document.getElementById('goReporterHome')?.addEventListener('click', ()=> window.location.href='reporter_homepage.html');
  document.getElementById('goViewProfile')?.addEventListener('click', ()=> window.location.href='reporter_profile.html');
  document.getElementById('goSettings')?.addEventListener('click', ()=> window.location.href='reporter-settings.html');
  document.getElementById('signOut')?.addEventListener('click', ()=> {
    alert('Signed out (mock)');
    // real app: clear session + redirect to login
    // window.location.href = 'signin.html';
  });
})();

    /* ----- switches ----- */
    function addSwitch(id){
      const sw = document.getElementById(id);
      if(!sw) return;
      sw.addEventListener('click', ()=> {
        sw.classList.toggle('on');
      });
    }
    addSwitch('profileSwitch');
    addSwitch('twofaSwitch');

    /* ----- Save account (mock) ----- */
    document.getElementById('saveAccount').addEventListener('click', ()=> {
      localStorage.setItem('rs_name', document.getElementById('nameInput').value);
      localStorage.setItem('rs_email', document.getElementById('emailInput').value);
      localStorage.setItem('rs_phone', document.getElementById('phoneInput').value);
      alert('Account saved!');
    });

    /* prefill from localStorage if present */
    window.addEventListener('DOMContentLoaded', ()=>{
      const name = localStorage.getItem('rs_name') || '';
      const email = localStorage.getItem('rs_email') || '';
      const phone = localStorage.getItem('rs_phone') || '';
      document.getElementById('nameInput').value = name;
      document.getElementById('emailInput').value = email;
      document.getElementById('phoneInput').value = phone;
      // adjust last login mock
      document.getElementById('lastLogin').textContent = localStorage.getItem('rs_lastLogin') || '—';
    });
  </script>



<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('dropdownToggle');
  const menu = document.getElementById('dropdownMenu');

  // safety check (no errors if elements missing)
  if (!toggle || !menu) return;

  toggle.addEventListener('click', function (e) {
    e.stopPropagation();
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  });

  // Close when clicking outside
  document.addEventListener('click', function (e) {
    if (!toggle.contains(e.target) && !menu.contains(e.target)) {
      menu.style.display = 'none';
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') menu.style.display = 'none';
  });
});
</script>

</body>
</html>
