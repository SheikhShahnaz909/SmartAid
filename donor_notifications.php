<?php
// donor_notifications.php - Donor notification center

session_start();

// 1. Access control: only donors can view this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
    header("Location: donor_login.php?error=auth_required");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// 2. Include database config (PDO) + activity log helper
require_once __DIR__ . '/config.php';       // must define $pdo (PDO)
require_once __DIR__ . '/log_activity.php'; // our helper

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Database connection ($pdo) not available.');
}

// 3. Handle mark-as-read action (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    $nid = (int)$_POST['mark_read_id'];

    try {
        $stmt = $pdo->prepare(
            "UPDATE notifications 
             SET is_read = 1 
             WHERE id = :id AND user_id = :uid"
        );
        $stmt->execute([
            ':id'  => $nid,
            ':uid' => $user_id,
        ]);

        // Log that this donor marked a notification as read
        log_activity(
            $pdo,
            $user_id,
            'donor',
            'notification_mark_read',
            "notification_id:{$nid}"
        );
    } catch (PDOException $e) {
        error_log('Failed to mark notification read: '.$e->getMessage());
        // You can choose to show an error if you want
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 4. Fetch notifications
//    - Exclude 'nearby_need' notifications whose related report has been accepted (accepted_by IS NOT NULL)
$sql = "
    SELECT n.id, n.type, n.title, n.message, n.is_read, n.related_id, n.created_at
    FROM notifications n
    LEFT JOIN reports r ON r.report_id = n.related_id
    WHERE n.user_id = :uid
      AND NOT (n.type = 'nearby_need' AND r.accepted_by IS NOT NULL)
    ORDER BY n.created_at DESC
    LIMIT 200
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $user_id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query error: " . htmlspecialchars($e->getMessage()));
}

// 5. Log that donor opened the notifications page
log_activity(
    $pdo,
    $user_id,
    'donor',
    'view_notifications',
    'notifications_page_loaded'
);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Your Notifications - Smart Aid</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    body{
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      background:#f4fbf6;
      margin:0;
      padding:20px;
      color:#08321b;
    }
    .wrap{
      max-width:820px;
      margin:0 auto;
    }
    h1{
      color:#1d7940;
      margin-bottom:16px;
    }
    .note{
      background:#fff;
      border-radius:8px;
      padding:12px;
      box-shadow:0 8px 20px rgba(20,80,40,0.05);
      margin-bottom:10px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
    }
    .note.unread{
      border-left:6px solid #1d7940;
    }
    .meta{
      font-size:0.9rem;
      color:#444;
      margin-top:4px;
    }
    .rel{
      font-size:0.85rem;
      color:#777;
      margin-top:4px;
    }
    .btn{
      padding:8px 10px;
      border-radius:8px;
      border:none;
      background:#1d7940;
      color:#fff;
      cursor:pointer;
      font-size:0.9rem;
      font-weight:600;
    }
    .btn-ghost{
      background:transparent;
      color:#1d7940;
      border:1px solid rgba(29,121,64,0.08);
      padding:8px 10px;
      border-radius:8px;
      font-size:0.9rem;
      cursor:pointer;
    }
    a.view-link{
      color:#1d7940;
      text-decoration:none;
      font-weight:600;
    }
    a.view-link:hover{
      text-decoration:underline;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Your Notifications</h1>

    <?php if (empty($notes)): ?>
      <p>No notifications yet.</p>
    <?php else: ?>
      <?php foreach ($notes as $n): ?>
        <div class="note <?php echo $n['is_read'] ? '' : 'unread'; ?>">
          <div>
            <div style="font-weight:700;">
              <?php echo htmlspecialchars($n['title']); ?>
            </div>
            <div class="meta">
              <?php echo htmlspecialchars($n['message']); ?>
            </div>
            <div class="rel">
              Received: <?php echo htmlspecialchars($n['created_at']); ?>
              <?php if (!empty($n['related_id'])): ?>
                &nbsp;•&nbsp;
                <a class="view-link" href="report_view.php?id=<?php echo (int)$n['related_id']; ?>">
                  View report
                </a>
              <?php endif; ?>
            </div>
          </div>

          <div style="text-align:right;">
            <?php if (!$n['is_read']): ?>
              <form method="post" style="display:inline">
                <input type="hidden" name="mark_read_id" value="<?php echo (int)$n['id']; ?>">
                <button class="btn" type="submit">Mark as read</button>
              </form>
            <?php else: ?>
              <span style="color:#666;font-size:0.9rem;">Read</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</body>
</html>
