<?php
// donor-settings.php
session_start();
require 'config.php';

// Only donors can access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    header("Location: donor_login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Messages
$errors = [];
$successMsg = "";

// Handle POST (account update or password change)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_account') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city  = trim($_POST['city'] ?? '');

        if ($name === '') {
            $errors[] = "Name cannot be empty.";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        // Enforce email uniqueness (excluding current user)
        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email AND user_id <> :uid LIMIT 1");
            $stmt->execute([':email' => $email, ':uid' => $userId]);
            if ($stmt->fetch()) {
                $errors[] = "That email is already used by another account.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE users
                SET name = :name, email = :email, phone = :phone, location = :loc
                WHERE user_id = :uid
                LIMIT 1
            ");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':loc' => $city,
                ':uid' => $userId
            ]);

            // Update session name so headers, etc. stay in sync
            $_SESSION['user_name'] = $name;

            $successMsg = "Account details updated successfully.";
        }

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new === '' || $confirm === '') {
            $errors[] = "New password and confirm password are required.";
        } elseif ($new !== $confirm) {
            $errors[] = "New password and confirm password do not match.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new)) {
            $errors[] = "New password must be at least 8 characters and include a capital letter, a number and a symbol.";
        } else {
            // Fetch current hash
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = :uid LIMIT 1");
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($current, $row['password_hash'])) {
                $errors[] = "Current password is incorrect.";
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE user_id = :uid LIMIT 1");
                $stmt->execute([':ph' => $newHash, ':uid' => $userId]);
                $successMsg = "Password updated successfully.";
            }
        }
    }
}

// Fetch fresh user data for display
$stmt = $pdo->prepare("SELECT name, email, phone, location, created_at FROM users WHERE user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: donor_login.php");
    exit;
}

$name     = $user['name'] ?? '';
$email    = $user['email'] ?? '';
$phone    = $user['phone'] ?? '';
$city     = $user['location'] ?? '';
$created  = $user['created_at'] ?? null;

// For avatars/initials
$initialSource = $name !== '' ? $name : $email;
$initial = $initialSource !== '' ? mb_strtoupper(mb_substr($initialSource, 0, 1, 'UTF-8')) : 'D';

$lastLoginText = isset($_SESSION['last_login'])
    ? $_SESSION['last_login']
    : ($created ? date('Y-m-d H:i', strtotime($created)) : '—');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Smart Aid — Donor Settings</title>
  <style>
    :root{
      --bg-overlay: rgba(4, 15, 10, 0.55);
      --card-bg: rgba(255,255,255,0.95);
      --muted:#cbd5d9;
      --text:#042023;
      --accent:#0f766e;
      --danger:#dc2626;
      --shadow:0 10px 30px rgba(2,6,23,0.35);
      --radius:12px;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    html,body{height:100%;margin:0;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}

    body{
      background:
        linear-gradient(var(--bg-overlay), var(--bg-overlay)),
        url('signin-background.jpeg.jpg') center/cover no-repeat fixed;
      color:var(--text);
    }

    header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:14px 22px;
      gap:12px;
      backdrop-filter: blur(6px);
    }

    .brand{display:flex;align-items:center;gap:12px}
    .logo{
      width:48px;height:48px;border-radius:50%;overflow:hidden;box-shadow:0 6px 18px rgba(2,6,23,0.35);
      display:flex;align-items:center;justify-content:center;background:transparent;
    }
    .logo img{width:100%;height:100%;object-fit:cover;display:block}
    .brand h1{margin:0;font-size:18px;color:#fff;font-weight:700;letter-spacing:0.2px}
    .brand .sub{font-size:13px;color:rgba(255,255,255,0.9);margin-top:2px}

    main{
      max-width:1100px;
      margin:28px auto;
      padding:18px;
      display:grid;
      grid-template-columns:320px 1fr;
      gap:20px;
    }

    .panel{
      background: var(--card-bg);
      border-radius:var(--radius);
      padding:18px;
      box-shadow:var(--shadow);
      color: #06333a;
    }

    .profile-quick{display:flex;gap:12px;align-items:center;margin-bottom:12px}
    .quick-avatar{width:56px;height:56px;border-radius:12px;background:linear-gradient(120deg,#ffd,#f8b);display:flex;align-items:center;justify-content:center;font-weight:700;color:#042023}
    .small-muted{font-size:13px;color:#4b5563}

    nav.settings-menu{display:flex;flex-direction:column;gap:8px;margin-top:8px}
    nav.settings-menu button{
      text-align:left;padding:10px;border-radius:8px;border:none;background:transparent;cursor:pointer;font-weight:700;color:#0b3b35;
    }
    nav.settings-menu button.active{
      background:linear-gradient(90deg, rgba(15,118,110,0.09), transparent);
      color:var(--accent);
      box-shadow:inset 0 -2px 0 rgba(15,118,110,0.04);
    }

    footer.info{margin-top:14px;color:#4b5563;font-size:13px}

    .card{
      background: var(--card-bg);
      border-radius:var(--radius);
      padding:18px;
      box-shadow:var(--shadow);
      color:#042023;
    }

    h2{margin:0 0 8px 0}
    p.lead{margin:0 0 12px 0;color:#47585a}

    .row{display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap}
    .field{flex:1 1 200px;display:flex;flex-direction:column;gap:6px}
    label{font-size:13px;color:#47585a}
    input[type="text"], input[type="email"], input[type="tel"], input[type="password"], select{
      padding:10px;border-radius:10px;border:1px solid #e6eef6;background:transparent;font-size:14px;outline:none;
    }

    .prefs{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
    .pref-card{padding:12px;border-radius:10px;border:1px solid rgba(15,118,110,0.06);background:rgba(255,255,255,0.75)}
    .pref-card strong{display:block;color:#073935;margin-bottom:6px}

    .switch{width:46px;height:28px;background:#e6eef6;border-radius:20px;padding:4px;display:inline-block;position:relative;cursor:pointer}
    .switch .dot{width:20px;height:20px;border-radius:50%;background:white;position:absolute;left:4px;top:4px;transition:all .18s}
    .switch.on{background:linear-gradient(90deg,var(--accent), #0891b2)}
    .switch.on .dot{left:22px}

    .actions{display:flex;gap:10px;align-items:center}
    .btn-primary{background:var(--accent);color:white;padding:10px 14px;border-radius:10px;border:none;cursor:pointer;font-weight:700}
    .btn-ghost{background:transparent;border:1px solid rgba(6,51,43,0.06);padding:8px 12px;border-radius:10px;cursor:pointer}
    .btn-danger{background:var(--danger);color:white;padding:10px 12px;border-radius:10px;border:none;cursor:pointer}

    .danger{background:linear-gradient(180deg, rgba(255,240,240,0.6), transparent); border:1px solid rgba(220,38,38,0.08);padding:12px;border-radius:10px}

    .modal-backdrop{position:fixed;inset:0;background:rgba(3,7,18,0.55);display:none;align-items:center;justify-content:center;padding:20px;z-index:60}
    .modal{width:100%;max-width:520px;background:var(--card-bg);border-radius:12px;padding:18px;box-shadow:0 12px 40px rgba(2,6,23,0.6)}
    .modal .actions{display:flex;justify-content:flex-end;gap:8px}

    .d-dropdown{position:absolute;top:48px;right:0;background:white;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,0.18);width:180px;display:none;overflow:hidden;z-index:80}
    .d-dropdown a{display:block;padding:10px 14px;color:#0f766e;font-weight:600;text-decoration:none;border-bottom:1px solid #eee}
    .d-dropdown a:last-child{border-bottom:none;color:#b91c1c}

    /* Flash messages */
    .flash-wrap{max-width:1100px;margin:10px auto 0;padding:0 18px;}
    .flash{
      margin-bottom:8px;
      padding:10px 12px;
      border-radius:10px;
      font-size:14px;
      box-shadow:0 6px 18px rgba(0,0,0,0.18);
    }
    .flash-success{background:rgba(16,185,129,0.12);color:#064e3b;border:1px solid rgba(16,185,129,0.4);}
    .flash-error{background:rgba(248,113,113,0.12);color:#7f1d1d;border:1px solid rgba(248,113,113,0.4);}

    @media (max-width:880px){
      main{grid-template-columns:1fr;padding:12px}
      header{padding:12px}
    }
  </style>
</head>
<body>
  <!-- HEADER: back arrow + logo + D dropdown -->
  <header>
    <div style="display:flex;align-items:center;gap:14px">

      <!-- Back arrow -->
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

      <!-- Brand + Logo -->
      <div class="brand" style="display:flex;align-items:center;gap:12px">
        <div class="logo" aria-hidden="true">
          <img src="images/circle-logo.png" alt="Smart Aid logo">
        </div>
        <div>
          <h1>Smart Aid</h1>
          <div class="sub">Donor settings</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: initial + dropdown -->
    <div style="position:relative">
      <div id="dropdownToggle"
           style="font-weight:700;background:linear-gradient(120deg,#0ea5a3,#0f766e);color:white;padding:8px 12px;border-radius:8px;cursor:pointer;user-select:none;">
        <?php echo h($initial); ?>
      </div>

      <div id="dropdownMenu" class="d-dropdown" aria-hidden="true">
        <a href="donor_homepage.php">Home</a>
        <a href="donor_profile.php">View Profile</a>
        <a href="my_donation.php">My Donations</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </header>

  <!-- Flash messages -->
  <div class="flash-wrap">
    <?php if (!empty($errors)): ?>
      <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?php echo h($e); ?></div>
      <?php endforeach; ?>
    <?php elseif ($successMsg): ?>
      <div class="flash flash-success"><?php echo h($successMsg); ?></div>
    <?php endif; ?>
  </div>

  <main>
    <!-- LEFT PANEL -->
    <aside class="panel">
      <div class="profile-quick">
        <div class="quick-avatar" id="quickAvatar"><?php echo h($initial); ?></div>
        <div>
          <div id="displayNameLeft" style="font-weight:800;cursor:pointer"><?php echo h($name); ?></div>
          <div class="small-muted" id="displayEmailLeft"><?php echo h($email); ?></div>
        </div>
      </div>

      <nav class="settings-menu" aria-label="Settings sections">
        <button class="active" data-section="account">Account</button>
        <button data-section="donation">Donation Preferences</button>
        <button data-section="privacy">Privacy & Security</button>
        <button data-section="support">Help & Support</button>
      </nav>

      <footer class="info">
        <div class="small-muted">Version 1.0 • Safe & secure</div>
        <div class="small-muted">Last saved: <span id="lastSaved">Not yet</span></div>
      </footer>
    </aside>

    <!-- RIGHT: content -->
    <section>
      <!-- Account -->
      <div class="card" id="section-account">
        <h2>Account</h2>
        <p class="lead">Manage email, phone, login methods and password.</p>

        <div class="row">
          <div class="field">
            <label>Full name</label>
            <input id="nameInput" type="text" placeholder="Your full name"
                   value="<?php echo h($name); ?>">
          </div>
          <div class="field">
            <label>Email</label>
            <input id="emailInput" type="email" placeholder="you@example.com"
                   value="<?php echo h($email); ?>">
          </div>
          <div class="field">
            <label>Phone</label>
            <input id="phoneInput" type="tel" placeholder="+91 98765 43210"
                   value="<?php echo h($phone); ?>">
          </div>
        </div>

        <div class="row">
          <div class="field">
            <label>Change password</label>
            <button class="btn-ghost" id="changePasswordBtn">Change password</button>
          </div>
          <div class="field">
            <label>Login activity</label>
            <div class="small-muted">Last login: <span id="lastLogin"><?php echo h($lastLoginText); ?></span></div>
          </div>
        </div>

        <div class="actions" style="margin-top:8px">
          <button class="btn-primary" id="saveAccount">Save changes</button>
          <button class="btn-ghost" id="resetAccount">Reset</button>
        </div>
      </div>

      <!-- Donation preferences -->
      <div class="card" id="section-donation" style="margin-top:16px;display:none">
        <h2>Donation Preferences</h2>
        <p class="lead">Choose categories and locations you care about most.</p>

        <div class="prefs" style="margin-bottom:12px">
          <label class="pref-card"><input type="checkbox" class="cat" value="Food"> <strong>Food</strong><div class="small-muted">Groceries, meals</div></label>
          <label class="pref-card"><input type="checkbox" class="cat" value="Clothes"> <strong>Clothes</strong><div class="small-muted">Seasonal clothing</div></label>
          <label class="pref-card"><input type="checkbox" class="cat" value="Education"> <strong>Education</strong><div class="small-muted">Scholarships</div></label>
          <label class="pref-card"><input type="checkbox" class="cat" value="Medical"> <strong>Medical</strong><div class="small-muted">Medical aid</div></label>
        </div>

        <div class="row">
          <div class="field">
            <label>Preferred city / area</label>
            <input id="cityInput" type="text" placeholder="e.g., Mumbai, Delhi"
                   value="<?php echo h($city); ?>">
          </div>
          <div class="field">
            <label>Auto match requests</label>
            <div style="display:flex;align-items:center;gap:12px">
              <div id="autoMatchSwitch" class="switch"><div class="dot"></div></div>
              <div class="small-muted">Automatically notify when a request matches your preferences.</div>
            </div>
          </div>
        </div>

        <div class="actions" style="margin-top:8px">
          <button class="btn-primary" id="saveDonation">Save preferences</button>
        </div>
      </div>

      <!-- Privacy & Security -->
      <div class="card" id="section-privacy" style="margin-top:16px;display:none">
        <h2>Privacy & Security</h2>
        <p class="lead">Control what others can see and protect your account.</p>

        <div style="display:flex;flex-direction:column;gap:12px">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div style="font-weight:700">Show my donation history publicly</div>
              <div class="small-muted">If off, only you and admins can see full details.</div>
            </div>
            <div id="historySwitch" class="switch on"><div class="dot"></div></div>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div style="font-weight:700">Show me on leaderboard</div>
              <div class="small-muted">Hide if you'd rather donate privately.</div>
            </div>
            <div id="leaderboardSwitch" class="switch"><div class="dot"></div></div>
          </div>
        </div>

        <div style="margin-top:18px" class="danger">
          <h3 style="margin:0 0 10px 0">Deactivate or delete account</h3>
          <div class="small-muted">Deactivating will hide your account; deleting removes all data permanently.</div>
          <div style="margin-top:10px;display:flex;gap:10px">
            <button class="btn-ghost" id="deactivateBtn">Deactivate account</button>
            <button class="btn-danger" id="deleteBtn">Delete account</button>
          </div>
        </div>
      </div>

      <!-- Support -->
      <div class="card" id="section-support" style="margin-top:16px;display:none">
        <h2>Help & Support</h2>
        <p class="lead">Questions? We are here to help.</p>

        <div style="display:flex;flex-direction:column;gap:10px">
          <div>
            <strong>Contact support</strong>
            <div class="small-muted">smrtaid@gmail.com</div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Hidden forms for backend POST -->
  <form id="accountForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="update_account">
    <input type="hidden" name="name">
    <input type="hidden" name="email">
    <input type="hidden" name="phone">
    <input type="hidden" name="city">
  </form>

  <form id="passwordForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="change_password">
    <input type="hidden" name="current_password">
    <input type="hidden" name="new_password">
    <input type="hidden" name="confirm_password">
  </form>

  <!-- Change Password Modal -->
  <div id="modalChangePassword" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal" role="document">
      <h3 style="margin-top:0">Change password</h3>
      <div style="margin-top:8px">
        <label>Current password</label>
        <input id="currentPassword" type="password">
      </div>
      <div style="margin-top:8px">
        <label>New password</label>
        <input id="newPassword" type="password">
      </div>
      <div style="margin-top:8px">
        <label>Confirm new</label>
        <input id="confirmPassword" type="password">
      </div>
      <div class="actions" style="margin-top:12px">
        <button class="btn-ghost" id="closeChangePassword">Cancel</button>
        <button class="btn-primary" id="saveChangePassword">Change</button>
      </div>
    </div>
  </div>

  <!-- Confirm Modal -->
  <div id="modalConfirm" class="modal-backdrop">
    <div class="modal">
      <h3 id="confirmTitle">Confirm</h3>
      <p id="confirmMessage" class="small-muted">Are you sure?</p>
      <div class="actions" style="margin-top:12px">
        <button class="btn-ghost" id="cancelConfirm">Cancel</button>
        <button class="btn-danger" id="okConfirm">Yes, continue</button>
      </div>
    </div>
  </div>

  <script>
    const el = id => document.getElementById(id);

    // NAV: panels and buttons
    const navButtons = document.querySelectorAll('nav.settings-menu button');
    const panels = {
      account: el('section-account'),
      donation: el('section-donation'),
      privacy: el('section-privacy'),
      support: el('section-support')
    };
    function showSection(name){
      Object.values(panels).forEach(p => p.style.display = 'none');
      if(panels[name]) panels[name].style.display = '';
      navButtons.forEach(b => b.classList.toggle('active', b.dataset.section === name));
    }
    navButtons.forEach(b => b.addEventListener('click', ()=> showSection(b.dataset.section)));

    (function initSection(){
      showSection('account');
    })();

    // Dropdown toggle
    const toggle = el('dropdownToggle');
    const menu = el('dropdownMenu');
    toggle.addEventListener('click', e => {
      e.stopPropagation();
      menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    });
    document.addEventListener('click', e => {
      if(!toggle.contains(e.target) && !menu.contains(e.target)) menu.style.display = 'none';
    });

    // Save account -> POST to backend
    el('saveAccount').addEventListener('click', ()=> {
      const form = document.getElementById('accountForm');
      form.elements['name'].value  = el('nameInput').value.trim();
      form.elements['email'].value = el('emailInput').value.trim();
      form.elements['phone'].value = el('phoneInput').value.trim();
      form.elements['city'].value  = el('cityInput').value.trim();
      form.submit();
    });

    // Reset account fields to current page values (just reload)
    el('resetAccount').addEventListener('click', ()=> {
      window.location.reload();
    });

    // Donation preferences still saved locally for now (you can later move to DB)
    const cats = document.querySelectorAll('.cat');
    const savedCats = JSON.parse(localStorage.getItem('sa_categories') || '[]');
    cats.forEach(c => { if(savedCats.includes(c.value)) c.checked = true; });

    const autoMatchSwitch = el('autoMatchSwitch');
    function toggleSwitch(elm,on){ elm.classList.toggle('on', !!on); }
    toggleSwitch(autoMatchSwitch, localStorage.getItem('sa_autoMatch') === 'true');
    autoMatchSwitch.addEventListener('click', ()=> {
      const on = !autoMatchSwitch.classList.contains('on');
      toggleSwitch(autoMatchSwitch, on);
      localStorage.setItem('sa_autoMatch', on);
    });

    el('saveDonation').addEventListener('click', ()=> {
      const chosen = Array.from(document.querySelectorAll('.cat:checked')).map(i=>i.value);
      localStorage.setItem('sa_categories', JSON.stringify(chosen));
      localStorage.setItem('sa_city_pref', el('cityInput').value.trim());
      el('lastSaved').innerText = new Date().toLocaleString();
      alert('Donation preferences saved (local only for now).');
    });

    // Privacy switches (still local)
    const historySwitch = el('historySwitch');
    const leaderboardSwitch = el('leaderboardSwitch');
    toggleSwitch(historySwitch, localStorage.getItem('sa_showHistory') !== 'false');
    toggleSwitch(leaderboardSwitch, localStorage.getItem('sa_showLeaderboard') === 'true');
    historySwitch.addEventListener('click', ()=> {
      const on = !historySwitch.classList.contains('on');
      toggleSwitch(historySwitch,on);
      localStorage.setItem('sa_showHistory', on);
    });
    leaderboardSwitch.addEventListener('click', ()=> {
      const on = !leaderboardSwitch.classList.contains('on');
      toggleSwitch(leaderboardSwitch,on);
      localStorage.setItem('sa_showLeaderboard', on);
    });

    // Change password -> open modal
    el('changePasswordBtn').addEventListener('click', ()=> el('modalChangePassword').style.display = 'flex');
    el('closeChangePassword').addEventListener('click', ()=> el('modalChangePassword').style.display = 'none');

    // Save password -> POST to backend
    el('saveChangePassword').addEventListener('click', ()=> {
      const pf = document.getElementById('passwordForm');
      pf.elements['current_password'].value = el('currentPassword').value;
      pf.elements['new_password'].value     = el('newPassword').value;
      pf.elements['confirm_password'].value = el('confirmPassword').value;
      pf.submit();
    });

    // Deactivate / delete (still mock/local)
    el('deactivateBtn').addEventListener('click', ()=> {
      openConfirm('Deactivate account',
                  'This will only deactivate locally in this demo. In production you would call the backend.',
                  ()=> { alert('Deactivated (local mock only).'); });
    });
    el('deleteBtn').addEventListener('click', ()=> {
      openConfirm('Delete account',
                  'This will clear local demo data. In production this would delete your account from the server.',
                  ()=> {
                    localStorage.clear();
                    alert('Local demo data cleared. (Server account is NOT deleted in this demo.)');
                  });
    });

    function openConfirm(title,message,onOk){
      el('confirmTitle').innerText = title;
      el('confirmMessage').innerText = message;
      el('okConfirm').onclick = ()=> { el('modalConfirm').style.display = 'none'; onOk && onOk(); };
      el('cancelConfirm').onclick = ()=> el('modalConfirm').style.display = 'none';
      el('modalConfirm').style.display = 'flex';
    }

    // Close modals by backdrop or Escape
    document.querySelectorAll('.modal-backdrop').forEach(back => {
      back.addEventListener('click', e => { if(e.target === back) back.style.display = 'none'; });
    });
    window.addEventListener('keydown', e => {
      if(e.key === 'Escape') document.querySelectorAll('.modal-backdrop').forEach(m=>m.style.display='none');
    });

    // Clicking name on left -> go to real profile page
    document.querySelector('#displayNameLeft').addEventListener('click', ()=> {
      window.location.href = 'donor_profile.php';
    });
  </script>
</body>
</html>
