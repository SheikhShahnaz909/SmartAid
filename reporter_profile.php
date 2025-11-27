<?php
// reporter_profile.php
session_start();
require 'config.php';

// Only reporters can see this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'reporter') {
    header("Location: reporter_login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email, phone, location FROM users WHERE user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: reporter_login.php");
    exit;
}

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$name     = $user['name'] ?? '';
$email    = $user['email'] ?? '';
$phone    = $user['phone'] ?? '';
$location = $user['location'] ?? '';

$initialSource = $name !== '' ? $name : $email;
$initial = $initialSource !== '' ? mb_strtoupper(mb_substr($initialSource, 0, 1, 'UTF-8')) : 'U';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Aid — View Profile</title>
  <style>
    :root{
      --card:#ffffffcc;
      --muted:#6b7280;
      --accent:#0f766e;
      --shadow: 0 8px 24px rgba(16,24,40,0.08);
      --radius:12px;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    html,body{height:100%;margin:0;-webkit-font-smoothing:antialiased;}
    body{
      background:
        linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.4)),
        url('signin-background.jpeg.jpg') center/cover no-repeat;
      font-family:Inter, system-ui, Roboto, Arial;
      color:#07201a;
    }

    header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:14px 22px;
      background:transparent;
      max-width:980px;margin:0 auto;
    }
    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#0ea5a3,#0f766e);display:flex;align-items:center;justify-content:center;color:white;font-weight:700}
    .muted{color:var(--muted);font-size:13px}

    .wrap{max-width:980px;margin:22px auto;padding:18px;display:grid;grid-template-columns:320px 1fr;gap:18px}

    .card{
      background:var(--card);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      padding:18px;
      backdrop-filter: blur(6px);
    }

    .profile-head{display:flex;flex-direction:column;align-items:center;gap:12px;text-align:center}
    .avatar{width:120px;height:120px;border-radius:16px;background:linear-gradient(120deg,#ffd,#f8b);display:flex;align-items:center;justify-content:center;font-weight:700;color:#0b1220;font-size:36px;overflow:hidden}
    .avatar img{width:100%;height:100%;object-fit:cover;display:block}
    h1{margin:6px 0 0 0;font-size:20px}

    .info-row{display:flex;flex-direction:column;gap:10px;margin-top:12px}
    .info-item{display:flex;gap:12px;align-items:center}
    .info-item strong{min-width:110px;color:#0b1220}

    .actions{display:flex;gap:10px;justify-content:center;margin-top:8px;flex-wrap:wrap}
    .btn{padding:10px 14px;border-radius:10px;border:none;cursor:pointer}
    .btn-primary{background:var(--accent);color:white;font-weight:600}
    .btn-ghost{background:transparent;border:1px solid rgba(15,118,110,0.12)}

    .small-card{background:linear-gradient(180deg, rgba(255,255,255,0.85), transparent);padding:12px;border-radius:10px}
    .socials{display:flex;gap:8px;flex-wrap:wrap}
    .socials a{display:inline-block;padding:8px 10px;border-radius:8px;background:#f3faf9;border:1px solid rgba(15,118,110,0.06);text-decoration:none;color:var(--accent);font-weight:600}

    .user-area{display:flex;align-items:center;gap:12px}
    .user-initial{
      width:42px;height:42px;border-radius:50%;
      background-color:rgba(15,118,110,0.95);
      color:white;display:flex;justify-content:center;align-items:center;font-weight:700;font-size:18px;cursor:pointer;
      box-shadow:0 4px 12px rgba(0,0,0,0.08);
    }
    #profileMenu { position: absolute; top:78px; right:22px; width:200px; background: white; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.18); display:none; z-index:200; padding:8px 0; }
    #profileMenu ul {list-style:none;margin:0;padding:0;}
    #profileMenu li { padding:10px 15px; }
    #profileMenu li:hover { background:#f2f6f3; }
    #profileMenu a{ text-decoration:none; font-size:14px; font-weight:600; color:#0f766e; display:block; }

    section.card h2{margin:0 0 8px 0}
    section.card h3{margin:10px 0 6px 0}

    @media (max-width:880px){
      .wrap{grid-template-columns:1fr;padding:12px}
      .profile-head{flex-direction:row;align-items:center;text-align:left}
      .avatar{width:80px;height:80px;font-size:28px}
      #profileMenu{right:12px}
    }
    .back-btn {
      position: absolute;
      top: 18px;
      left: 18px;
      width: 34px;
      height: 34px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: white;
      color: #0f766e;
      border-radius: 50%;
      font-size: 20px;
      font-weight: 900;
      text-decoration: none;
      box-shadow: 0 4px 12px rgba(0,0,0,0.18);
      z-index: 999;
      transition: 0.25s ease;
    }
    .back-btn:hover { transform: scale(1.12); }
  </style>
</head>
<body>
  <!-- Back Button -->
  <a href="reporter_homepage.php" class="back-btn">&#11013;</a>

  <header>
    <div class="brand">
      <div class="logo">SA</div>
      <div>
        <div style="font-weight:700;color:white">Smart Aid</div>
        <div class="muted" style="font-size:13px;color:rgba(255,255,255,0.9)">Reporter profile</div>
      </div>
    </div>

    <div class="user-area" style="position:relative;">
      <div id="userInitial" class="user-initial" aria-haspopup="true" aria-expanded="false">
        <?php echo h($initial); ?>
      </div>

      <div id="profileMenu" role="menu" aria-hidden="true">
        <ul>
          <li><a href="reporter_homepage.php">Home</a></li>
          <li><a href="my_reports.php">My Reports</a></li>
          <li><a href="reporter-settings.php">Settings</a></li>
          <li style="height:1px;background:#eee;margin:6px 0;"></li>
          <li><a href="logout.php">Log Out</a></li>
        </ul>
      </div>
    </div>
  </header>

  <div class="wrap">
    <!-- left: profile card -->
    <aside class="card" aria-labelledby="profileCard">
      <div class="profile-head">
        <div class="avatar" id="avatarBox">
          <span id="avatarLetter"><?php echo h($initial); ?></span>
        </div>

        <div>
          <h1 id="displayName"><?php echo h($name); ?></h1>
          <div class="muted" id="displayEmail"><?php echo h($email); ?></div>
        </div>

        <div class="actions" style="margin-top:10px">
          <button class="btn btn-primary" id="editProfileBtn">Edit profile</button>
        </div>
      </div>

      <div style="margin-top:14px" class="small-card">
        <div style="font-weight:700">Contact</div>
        <div class="muted" id="displayPhone">
          <?php echo $phone !== '' ? h($phone) : '—'; ?>
        </div>
        <div style="height:10px"></div>
        <div style="font-weight:700">Address</div>
        <div class="muted" id="displayAddress">
          <?php echo $location !== '' ? h($location) : '—'; ?>
        </div>
      </div>
    </aside>

    <!-- right: more details -->
    <section class="card" aria-labelledby="aboutSection">
      <h2 id="aboutSection">About</h2>
      <div class="muted" id="displayBio">No bio added yet.</div>

      <div style="margin-top:18px">
        <h3>Edit history</h3>
        <div class="muted" id="editHistory">Profile is loaded from your account details.</div>
      </div>

      <div style="margin-top:18px">
        <h3>Social links</h3>
        <div class="muted" style="font-size:13px;margin-bottom:8px">
          You can add social links later from the Settings page.
        </div>
        <div id="socialBox" class="small-card">
          <div style="display:flex;flex-direction:column;gap:8px">
            <div id="socialLinksContainer" class="socials"></div>
            <div id="noSocialsMsg" class="muted">No social links added yet.</div>
          </div>
        </div>
      </div>

      <div style="margin-top:18px">
        <h3>Resources</h3>
        <div class="muted">You can return to the dashboard from the top-right menu.</div>
      </div>
    </section>
  </div>

  <!-- Modal kept for future real editing -->
  <div id="modalEdit" class="modal-backdrop" role="dialog" aria-modal="true"
       style="position:fixed;inset:0;display:none;align-items:center;justify-content:center;
              padding:20px;z-index:60;background:rgba(3,7,18,0.45)">
    <div class="modal" role="document"
         style="width:100%;max-width:700px;background:white;border-radius:12px;
                padding:18px;box-shadow:0 10px 30px rgba(2,6,23,0.5)">
      <h3 style="margin-top:0">Edit profile</h3>
      <!-- keep your original modal content here if you want; omitted for brevity -->
    </div>
  </div>

  <script>
    const userInitial = document.getElementById('userInitial');
    const menu       = document.getElementById('profileMenu');

    userInitial.addEventListener('click', function (e) {
      e.stopPropagation();
      const visible = menu.style.display === 'block';
      menu.style.display = visible ? 'none' : 'block';
    });

    document.addEventListener('click', function (e) {
      if (!userInitial.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
      }
    });

    window.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') menu.style.display = 'none';
    });

    // “Edit profile” button → reporter settings page
    document.getElementById('editProfileBtn').addEventListener('click', function () {
      window.location.href = 'reporter-settings.php';
    });
  </script>
</body>
</html>
