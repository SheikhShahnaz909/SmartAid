<?php
// admin_dashboard.php
// Fully fixed and improved admin dashboard for SmartAid

require_once 'admin_session.php';
require_once 'config.php'; // provides $pdo (PDO instance)

// Prevent caching of admin pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Require login (admin_session.php should set $_SESSION['admin_id'] and admin info)
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$adminEmail = $_SESSION['admin_email'] ?? 'admin@example.com';

// Simple helper (safe) to run COUNT queries
function getCount(PDO $pdo, string $sql)
{
    try {
        $stmt = $pdo->query($sql);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// Handle POST actions (toggle user, update report, delete report, change admin password, delete donor post)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'toggle_user_status') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $newStatus = ($_POST['new_status'] ?? 'active') === 'blocked' ? 'blocked' : 'active';
            $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE user_id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $userId]);
        }

        if ($action === 'update_report_status') {
            $reportId = (int) ($_POST['report_id'] ?? 0);
            $status = $_POST['status'] ?? 'pending';
            $allowed = ['pending', 'verified', 'in_progress', 'completed', 'rejected'];
            if (!in_array($status, $allowed, true)) $status = 'pending';
            $stmt = $pdo->prepare("UPDATE reports SET status = :status WHERE report_id = :id");
            $stmt->execute([':status' => $status, ':id' => $reportId]);
        }

        if ($action === 'delete_report') {
            $reportId = (int) ($_POST['report_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = :id");
            $stmt->execute([':id' => $reportId]);
        }

        if ($action === 'change_admin_password') {
            $current = $_POST['current_password'] ?? '';
            $new1 = $_POST['new_password'] ?? '';
            $new2 = $_POST['confirm_password'] ?? '';
            if ($new1 !== '' && $new1 === $new2) {
                $id = (int) $_SESSION['admin_id'];
                $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && password_verify($current, $row['password_hash'])) {
                    $newHash = password_hash($new1, PASSWORD_BCRYPT);
                    $pdo->prepare("UPDATE admin_users SET password_hash = :ph WHERE id = :id")
                        ->execute([':ph' => $newHash, ':id' => $id]);
                }
            }
        }

        // Delete donor post (and its image file)
        if ($action === 'delete_donor_post') {
            $postId = (int) ($_POST['post_id'] ?? 0);

            // Find the post and its image path
            $stmt = $pdo->prepare("SELECT image_file FROM posts WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post) {
                // Delete DB row
                $pdo->prepare("DELETE FROM posts WHERE id = :id")->execute([':id' => $postId]);

                // Delete image file if exists
                if (!empty($post['image_file'])) {
                    $imgPath = __DIR__ . '/' . ltrim($post['image_file'], '/');
                    if (file_exists($imgPath)) {
                        @unlink($imgPath);
                    }
                }
            }
        }

    } catch (PDOException $e) {
        // Log error server-side (file) but don't disclose to admin UI
        error_log("Admin action error: " . $e->getMessage());
    }

    // redirect to avoid form resubmission and refresh data
    header('Location: admin_dashboard.php');
    exit;
}

// ----------------- Overview stats -----------------
$totalDonors    = getCount($pdo, "SELECT COUNT(*) FROM users WHERE role = 'donor'");
$totalReporters = getCount($pdo, "SELECT COUNT(*) FROM users WHERE role = 'reporter'");
$activeReports  = getCount($pdo, "SELECT COUNT(*) FROM reports WHERE status IN ('pending','verified','in_progress')");
$totalDonations = getCount($pdo, "SELECT COUNT(*) FROM donations");
$total_reports  = getCount($pdo, "SELECT COUNT(*) FROM reports");

// ----------------- Data sets -----------------
$latestReports = [];
try {
    $latestReports = $pdo->query("
        SELECT 
            r.report_id,
            r.report_type,
            r.status,
            r.created_at,
            r.accepted_by,
            u.name AS reporter_name,
            d.name AS donor_name
        FROM reports r
        LEFT JOIN users u ON u.user_id = r.reporter_id
        LEFT JOIN users d ON d.user_id = r.accepted_by
        ORDER BY r.created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$latestDonations = [];
try {
    $latestDonations = $pdo->query("SELECT id, name, created_at FROM donations ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$donors = [];
try {
    $donors = $pdo->query("SELECT u.user_id, u.name, u.email, u.phone, COALESCE(u.status,'active') AS status,
           (SELECT COUNT(*) FROM donations d WHERE d.name = u.name) AS total_donations
    FROM users u
    WHERE u.role = 'donor'
    ORDER BY u.created_at DESC
    LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$reporters = [];
try {
    $reporters = $pdo->query("SELECT u.user_id, u.name, u.email, u.phone, COALESCE(u.status,'active') AS status,
           (SELECT COUNT(*) FROM reports r WHERE r.reporter_id = u.user_id) AS total_reports
    FROM users u
    WHERE u.role = 'reporter'
    ORDER BY u.created_at DESC
    LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$reports = [];
try {
    $reports = $pdo->query("
        SELECT 
            r.report_id,
            r.report_type,
            r.description,
            r.location,
            r.status,
            r.created_at,
            r.accepted_by,
            u.name AS reporter_name,
            d.name AS donor_name
        FROM reports r
        LEFT JOIN users u ON u.user_id = r.reporter_id
        LEFT JOIN users d ON d.user_id = r.accepted_by
        ORDER BY r.created_at DESC
        LIMIT 100
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$donations = [];
try {
    $donations = $pdo->query("SELECT id, name, description, address, lat, lng, available_until, created_at 
        FROM donations 
        ORDER BY created_at DESC 
        LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$logs = [];
try {
    $logs = $pdo->query("SELECT id, user_id, role, action, details, ip_address, created_at 
        FROM activity_logs 
        ORDER BY created_at DESC 
        LIMIT 80")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

$recent_reports = [];
try {
    $recent_reports = $pdo->query("
        SELECT 
            r.report_id,
            r.report_type,
            r.location,
            r.created_at,
            r.status,
            r.accepted_by,
            u.name AS reporter_name,
            d.name AS donor_name
        FROM reports r
        LEFT JOIN users u ON u.user_id = r.reporter_id
        LEFT JOIN users d ON d.user_id = r.accepted_by
        ORDER BY r.created_at DESC
        LIMIT 50
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

// Donor posts (from posts table)
$donor_posts = [];
try {
    $donor_posts = $pdo->query("
        SELECT 
            p.id,
            p.caption,
            p.image_file,
            p.created_at,
            u.name  AS donor_name,
            u.email AS donor_email
        FROM posts p
        INNER JOIN users u ON u.user_id = p.user_id
        WHERE u.role = 'donor'
        ORDER BY p.created_at DESC
        LIMIT 100
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Aid — Admin Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <style>
        :root{
            --green-900:#114b2b;
            --green-700:#185e34;
            --green-500:#37a264;
            --green-100:#e8f5e9;
            --danger:#c62828;
            --bg:#f4f7f5;
        }
        *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif}
        body{background:var(--bg);min-height:100vh;display:flex;}
        .sidebar{
            width:230px;background:#ffffff;border-right:1px solid #dfe7e1;
            padding:18px 14px;display:flex;flex-direction:column
        }
        .brand{display:flex;align-items:center;gap:10px;margin-bottom:16px}
        .brand-logo img{width:50px;height:50px;border-radius:50%}
        .brand-title{font-weight:700;font-size:16px;color:var(--green-900)}
        .nav-section{margin-top:6px;font-size:11px;text-transform:uppercase;color:#777;margin-bottom:6px}
        .nav-link{
            display:block;padding:8px 10px;border-radius:10px;font-size:13px;
            color:#234;cursor:pointer;margin-bottom:3px;text-decoration:none
        }
        .nav-link.active{
            background:linear-gradient(135deg,#c8e6c9,#a5d6a7);
            color:#0b3d20;font-weight:600
        }
        .nav-foot{margin-top:auto;font-size:12px;color:#666}
        .nav-foot a{color:var(--green-700);text-decoration:none}
        .layout-main{flex:1;display:flex;flex-direction:column}
        header{
            padding:14px 22px;background:#ffffff;border-bottom:1px solid #dfe7e1;
            display:flex;align-items:center;justify-content:space-between
        }
        .admin-pill{
            display:flex;align-items:center;gap:10px;
            padding:6px 10px;border-radius:999px;background:#e8f5e9;font-size:13px
        }
        .admin-pill span.avatar{
            width:28px;height:28px;border-radius:50%;background:var(--green-700);
            color:#fff;display:grid;place-items:center;font-weight:700
        }
        .page{padding:16px 20px;overflow-y:auto;height:calc(100vh - 60px)}
        h2{font-size:18px;margin-bottom:10px;color:#123}
        h3{font-size:15px;margin:12px 0 6px;color:#234}
        .muted{font-size:12px;color:#777}
        .cards{
            display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));
            gap:14px;margin-bottom:20px
        }
        .card{
            background:#ffffff;border-radius:14px;padding:12px 14px;
            box-shadow:0 3px 12px rgba(0,0,0,0.04)
        }
        .card-label{font-size:11px;text-transform:uppercase;color:#777;margin-bottom:4px}
        .card-value{font-size:22px;font-weight:700;color:var(--green-900)}
        .card-sub{font-size:11px;color:#777;margin-top:2px}

        table{
            width:100%;border-collapse:collapse;font-size:13px;
            margin-top:6px;background:#fff;border-radius:10px;overflow:hidden
        }
        th,td{
            padding:8px 10px;border-bottom:1px solid #ecf0ec;
            text-align:left;vertical-align:top
        }
        th{background:#f7faf7;font-size:12px;font-weight:600;color:#445}
        tr:last-child td{border-bottom:none}
        .badge{
            display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px
        }
        .badge.pending{background:#fff3cd;color:#795548}
        .badge.verified{background:#e8f5e9;color:#2e7d32}
        .badge.in_progress{background:#e3f2fd;color:#1565c0}
        .badge.completed{background:#e8f5e9;color:#1b5e20}
        .badge.rejected{background:#ffebee;color:#c62828}
        .badge.blocked{background:#ffebee;color:#c62828}
        .badge.active{background:#e8f5e9;color:#2e7d32}

        /* claimed / unclaimed pills */
        .pill-claimed{
            display:inline-block;
            padding:2px 8px;
            border-radius:999px;
            font-size:10px;
            font-weight:600;
            margin-left:4px;
            background:#e3f2fd;
            color:#1565c0;
        }
        .pill-unclaimed{
            display:inline-block;
            padding:2px 8px;
            border-radius:999px;
            font-size:10px;
            font-weight:600;
            margin-left:4px;
            background:#fff3e0;
            color:#ef6c00;
        }

        .btn-sm{
            padding:4px 8px;border-radius:8px;border:none;font-size:11px;cursor:pointer
        }
        .btn-danger{background:#c62828;color:#fff}
        .btn-green{background:#2e7d32;color:#fff}
        .status-select{
            font-size:12px;padding:4px;border-radius:6px;border:1px solid #cfd8dc
        }
        .grid-2{
            display:grid;grid-template-columns:2fr 1.6fr;gap:14px;margin-top:8px
        }
        .list-box{
            background:#fff;border-radius:12px;padding:10px;
            box-shadow:0 3px 10px rgba(0,0,0,0.03)
        }
        .log-box{
            font-size:12px;max-height:260px;overflow:auto;background:#fff;
            border-radius:10px;padding:8px;border:1px solid #e0e0e0
        }

        /* Thumbnail for donor posts */
        .thumb-img{
            width:70px;height:70px;object-fit:cover;border-radius:8px;
            border:1px solid #e0e8e2;
        }

        @media(max-width:1000px){
            body{flex-direction:column}
            .sidebar{width:100%;flex-direction:row;overflow-x:auto;height:auto}
            .layout-main{height:auto}
            .cards{grid-template-columns:repeat(2,minmax(140px,1fr))}
        }
        @media(max-width:700px){
            .cards{grid-template-columns:1fr}
            .grid-2{grid-template-columns:1fr}
        }
    </style>
    <script>
        function showSection(id){
            document.querySelectorAll('.section').forEach(s => s.style.display='none');
            const sec = document.getElementById(id);
            if (sec) sec.style.display='block';

            document.querySelectorAll('.nav-link').forEach(n => n.classList.remove('active'));
            const link = document.querySelector('.nav-link[data-target="'+id+'"]');
            if (link) link.classList.add('active');
        }
        document.addEventListener('DOMContentLoaded', () => showSection('sec-overview'));
    </script>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <div class="brand-logo"><img src="images/circle-logo.png" alt="logo"></div>
        <div>
            <div class="brand-title">Smart Aid</div>
            <div style="font-size:11px;color:#777;">Admin Panel</div>
        </div>
    </div>

    <div class="nav-section">MAIN</div>
    <a class="nav-link active" data-target="sec-overview"  onclick="showSection('sec-overview')">Overview</a>
    <a class="nav-link"        data-target="sec-users"     onclick="showSection('sec-users')">Donors & Reporters</a>
    <a class="nav-link"        data-target="sec-reports"   onclick="showSection('sec-reports')">Need Reports</a>
    <a class="nav-link"        data-target="sec-donations" onclick="showSection('sec-donations')">Donations</a>

    <a class="nav-link"        data-target="sec-posts"     onclick="showSection('sec-posts')">Donor Posts</a>

    <a class="nav-link"        data-target="sec-logs"      onclick="showSection('sec-logs')">Activity Logs</a>
    <a class="nav-link"        data-target="sec-settings"  onclick="showSection('sec-settings')">Admin Settings</a>

    <div class="nav-foot">
        <div>Logged in as <b><?= htmlspecialchars($adminEmail) ?></b></div>
        <a href="admin_logout.php">Logout</a> · <a href="homepage.php">View site</a>
    </div>
</div>

<div class="layout-main">
    <header>
        <div>
            <div style="font-size:14px;color:#555;">Smart Aid • Real-Time Donation Platform</div>
            <div style="font-size:11px;color:#888;">Admin monitoring panel for donors & reporters</div>
        </div>
        <div class="admin-pill">
            <span class="avatar"><?= strtoupper(substr($adminEmail,0,1)) ?></span>
            <div style="line-height:1.2">
                <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($adminEmail) ?></div>
                <div style="font-size:11px;color:#777;">Administrator</div>
            </div>
        </div>
    </header>

    <main class="page">

        <!-- OVERVIEW -->
        <section id="sec-overview" class="section">
            <h2>Overview</h2>
            <p class="muted">Quick summary of donors, reporters, active needs and donations.</p>

            <div class="cards">
                <div class="card">
                    <div class="card-label">Total Donors</div>
                    <div class="card-value"><?= $totalDonors ?></div>
                    <div class="card-sub">Registered donor accounts</div>
                </div>
                <div class="card">
                    <div class="card-label">Total Reporters</div>
                    <div class="card-value"><?= $totalReporters ?></div>
                    <div class="card-sub">Community reporters</div>
                </div>
                <div class="card">
                    <div class="card-label">Active Need Reports</div>
                    <div class="card-value"><?= $activeReports ?></div>
                    <div class="card-sub">Pending / In-progress / Verified</div>
                </div>
                <div class="card">
                    <div class="card-label">Total Donations</div>
                    <div class="card-value"><?= $totalDonations ?></div>
                    <div class="card-sub">Donation listings created</div>
                </div>
            </div>

            <div style="margin-top:6px;">
                <div class="card" style="display:inline-block;min-width:200px;margin-bottom:12px;">
                    <div class="card-label">Total Need Reports</div>
                    <div class="card-value"><?= $total_reports ?></div>
                    <div class="card-sub">All submitted need reports</div>
                </div>

                <h3 style="margin-top:12px;">Recent Need Reports (Full)</h3>
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Type</th>
                      <th>Address</th>
                      <th>Reporter</th>
                      <th>Status</th>
                      <th>Created</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach($recent_reports as $r): ?>
                    <tr>
                      <td>#<?= $r['report_id'] ?></td>
                      <td><?= htmlspecialchars($r['report_type']) ?></td>
                      <td><?= htmlspecialchars($r['location']) ?></td>
                      <td><?= htmlspecialchars($r['reporter_name']) ?></td>
                      <td>
                        <span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
                        <?php if (!empty($r['accepted_by'])): ?>
                            <span class="pill-claimed">
                                Claimed<?= $r['donor_name'] ? ' by ' . htmlspecialchars($r['donor_name']) : '' ?>
                            </span>
                        <?php else: ?>
                            <span class="pill-unclaimed">Unclaimed</span>
                        <?php endif; ?>
                      </td>
                      <td><?= $r['created_at'] ?></td>
                      <td>
                        <a href="admin_view_report.php?id=<?= $r['report_id'] ?>">View</a> |
                        <a href="admin_mark_claimed.php?id=<?= $r['report_id'] ?>">Mark Claimed</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
            </div>

            <div class="grid-2">
                <div class="list-box">
                    <h3>Latest Need Reports</h3>
                    <table>
                        <tr>
                            <th>ID</th><th>Type</th><th>Reporter</th><th>Status</th><th>Created</th>
                        </tr>
                        <?php if ($latestReports): ?>
                            <?php foreach ($latestReports as $r): ?>
                                <tr>
                                    <td>#<?= $r['report_id'] ?></td>
                                    <td><?= htmlspecialchars($r['report_type']) ?></td>
                                    <td><?= htmlspecialchars($r['reporter_name'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
                                        <?php if (!empty($r['accepted_by'])): ?>
                                            <span class="pill-claimed">
                                                Claimed<?= $r['donor_name'] ? ' by ' . htmlspecialchars($r['donor_name']) : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="pill-unclaimed">Unclaimed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No reports yet.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>

                <div class="list-box">
                    <h3>Latest Donations</h3>
                    <table>
                        <tr>
                            <th>ID</th><th>Name</th><th>Created</th>
                        </tr>
                        <?php if ($latestDonations): ?>
                            <?php foreach ($latestDonations as $d): ?>
                                <tr>
                                    <td>#<?= $d['id'] ?></td>
                                    <td><?= htmlspecialchars($d['name']) ?></td>
                                    <td><?= htmlspecialchars($d['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No donations yet.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </section>

        <!-- USERS -->
        <section id="sec-users" class="section" style="display:none">
            <h2>Donors & Reporters</h2>
            <p class="muted">Monitor all users, block/unblock misuse, and see engagement.</p>

            <h3>Donors</h3>
            <table>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Total Donations</th><th>Status</th><th>Action</th>
                </tr>
                <?php if ($donors): ?>
                    <?php foreach ($donors as $u): ?>
                        <tr>
                            <td>#<?= $u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone']) ?></td>
                            <td><?= (int)$u['total_donations'] ?></td>
                            <td><span class="badge <?= htmlspecialchars($u['status']) ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="toggle_user_status">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $u['status']==='active' ? 'blocked' : 'active' ?>">
                                    <button class="btn-sm <?= $u['status']==='active'?'btn-danger':'btn-green' ?>" type="submit">
                                        <?= $u['status']==='active'?'Block':'Unblock' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No donors found.</td></tr>
                <?php endif; ?>
            </table>

            <h3 style="margin-top:18px;">Reporters</h3>
            <table>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Total Reports</th><th>Status</th><th>Action</th>
                </tr>
                <?php if ($reporters): ?>
                    <?php foreach ($reporters as $u): ?>
                        <tr>
                            <td>#<?= $u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone']) ?></td>
                            <td><?= (int)$u['total_reports'] ?></td>
                            <td><span class="badge <?= htmlspecialchars($u['status']) ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="action" value="toggle_user_status">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $u['status']==='active' ? 'blocked' : 'active' ?>">
                                    <button class="btn-sm <?= $u['status']==='active'?'btn-danger':'btn-green' ?>" type="submit">
                                        <?= $u['status']==='active'?'Block':'Unblock' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No reporters found.</td></tr>
                <?php endif; ?>
            </table>
        </section>

        <!-- REPORTS -->
        <section id="sec-reports" class="section" style="display:none">
            <h2>Need Reports</h2>
            <p class="muted">Verify, complete or reject reported needs to avoid misuse.</p>

            <table>
                <tr>
                    <th>ID</th><th>Type & Description</th><th>Reporter</th><th>Location</th><th>Status</th><th>Created</th><th>Actions</th>
                </tr>
                <?php if ($reports): ?>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td>#<?= $r['report_id'] ?></td>
                            <td>
                                <b><?= htmlspecialchars($r['report_type']) ?></b><br>
                                <span class="muted" style="font-size:11px;"><?= nl2br(htmlspecialchars($r['description'])) ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['reporter_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($r['location'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
                                <?php if (!empty($r['accepted_by'])): ?>
                                    <span class="pill-claimed">
                                        Claimed<?= $r['donor_name'] ? ' by ' . htmlspecialchars($r['donor_name']) : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="pill-unclaimed">Unclaimed</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($r['created_at']) ?></td>
                            <td>
                                <form method="post" style="margin-bottom:4px;">
                                    <input type="hidden" name="action" value="update_report_status">
                                    <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                                    <!-- auto-submit when status changes -->
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="pending"     <?= $r['status']==='pending'?'selected':'' ?>>Pending</option>
                                        <option value="verified"    <?= $r['status']==='verified'?'selected':'' ?>>Verified</option>
                                        <option value="in_progress" <?= $r['status']==='in_progress'?'selected':'' ?>>In-progress</option>
                                        <option value="completed"   <?= $r['status']==='completed'?'selected':'' ?>>Completed</option>
                                        <option value="rejected"    <?= $r['status']==='rejected'?'selected':'' ?>>Rejected</option>
                                    </select>
                                    <button class="btn-sm btn-green" type="submit">Update</button>
                                </form>

                                <form method="post" onsubmit="return confirm('Delete this report?');">
                                    <input type="hidden" name="action" value="delete_report">
                                    <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                                    <button class="btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No reports found.</td></tr>
                <?php endif; ?>
            </table>
        </section>

        <!-- DONATIONS -->
        <section id="sec-donations" class="section" style="display:none">
            <h2>Donations</h2>
            <p class="muted">Monitor donation posts made by donors.</p>

            <table>
                <tr>
                    <th>ID</th><th>Name</th><th>Description</th><th>Address</th><th>Lat</th><th>Lng</th><th>Available Until</th><th>Created At</th>
                </tr>
                <?php if ($donations): ?>
                    <?php foreach ($donations as $d): ?>
                        <tr>
                            <td>#<?= $d['id'] ?></td>
                            <td><?= htmlspecialchars($d['name']) ?></td>
                            <td><?= htmlspecialchars($d['description']) ?></td>
                            <td><?= htmlspecialchars($d['address']) ?></td>
                            <td><?= htmlspecialchars($d['lat']) ?></td>
                            <td><?= htmlspecialchars($d['lng']) ?></td>
                            <td><?= htmlspecialchars($d['available_until']) ?></td>
                            <td><?= htmlspecialchars($d['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">No donations found.</td></tr>
                <?php endif; ?>
            </table>
        </section>

        <!-- DONOR POSTS -->
        <section id="sec-posts" class="section" style="display:none">
            <h2>Donor Posts</h2>
            <p class="muted">Review and remove image posts created by donors (from donor_posts.php).</p>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Caption</th>
                    <th>Image</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                <?php if ($donor_posts): ?>
                    <?php foreach ($donor_posts as $p): ?>
                        <tr>
                            <td>#<?= (int)$p['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($p['donor_name']) ?><br>
                                <span class="muted" style="font-size:11px;"><?= htmlspecialchars($p['donor_email']) ?></span>
                            </td>
                            <td style="max-width:260px;">
                                <span class="muted" style="font-size:12px;">
                                    <?= nl2br(htmlspecialchars(mb_strimwidth($p['caption'] ?? '', 0, 200, '...','UTF-8'))) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($p['image_file'])): ?>
                                    <img class="thumb-img" src="<?= htmlspecialchars($p['image_file']) ?>" alt="post image">
                                <?php else: ?>
                                    <span class="muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['created_at']) ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this donor post?');">
                                    <input type="hidden" name="action" value="delete_donor_post">
                                    <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn-sm btn-danger">Delete Post</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No donor posts found.</td></tr>
                <?php endif; ?>
            </table>
        </section>

        <!-- LOGS -->
        <section id="sec-logs" class="section" style="display:none">
            <h2>Activity Logs</h2>
            <p class="muted">Monitor detailed system activity (logins, reports, donations, suspicious actions).</p>

            <div class="log-box">
                <?php if ($logs): ?>
                    <?php foreach ($logs as $lg): ?>
                        <div style="margin-bottom:6px;">
                            <span style="font-weight:600;">[<?= htmlspecialchars($lg['created_at']) ?>]</span>
                            <span class="muted">(<?= htmlspecialchars($lg['role'] ?? '-') ?> #<?= (int)$lg['user_id'] ?>, IP: <?= htmlspecialchars($lg['ip_address'] ?? '-') ?>)</span><br>
                            <span style="font-weight:600;"><?= htmlspecialchars($lg['action']) ?>:</span>
                            <span><?= nl2br(htmlspecialchars($lg['details'] ?? '')) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>No logs yet. Start calling your log function from login, report and donation pages.</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- SETTINGS -->
        <section id="sec-settings" class="section" style="display:none">
            <h2>Admin Settings</h2>
            <p class="muted">Update your admin password.</p>

            <div style="max-width:380px;background:#fff;border-radius:12px;padding:14px;box-shadow:0 3px 12px rgba(0,0,0,0.05);">
                <h3>Change Password</h3>
                <form method="post">
                    <input type="hidden" name="action" value="change_admin_password">
                    <label style="font-size:12px;" for="current_password">Current password</label>
                    <input type="password" name="current_password" id="current_password" required>

                    <label style="font-size:12px;" for="new_password">New password</label>
                    <input type="password" name="new_password" id="new_password" required>

                    <label style="font-size:12px;" for="confirm_password">Confirm new password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>

                    <button type="submit" class="btn-sm btn-green" style="margin-top:8px;">Update Password</button>
                </form>
            </div>
        </section>

    </main>
</div>
</body>
</html>
