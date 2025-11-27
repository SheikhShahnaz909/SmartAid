<?php
// feed.php — SmartAid Community Feed with Likes + Comments

session_start();
require_once 'config.php';

// ----------------------- Helper -----------------------
function e($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ----------------------- BACK URL -----------------------
$backUrl = 'homepage.php';
$allowedHomes = [
    'homepage.php',
    'donor_homepage.php',
    'reporter_homepage.php',
    'admin_dashboard.php',
];

if (!empty($_GET['home']) && in_array($_GET['home'], $allowedHomes, true)) {
    $backUrl = $_GET['home'];
} elseif (!empty($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'donor':
            $backUrl = 'donor_homepage.php';
            break;
        case 'reporter':
            $backUrl = 'reporter_homepage.php';
            break;
        case 'admin':
            $backUrl = 'admin_dashboard.php';
            break;
    }
}

// ----------------------- HANDLE LIKE -----------------------
if (isset($_POST['like_post']) && isset($_SESSION['user_id'])) {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $userId = (int) $_SESSION['user_id'];

    // Toggle like (if exists → unlike, else like)
    $check = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?");
    $check->execute([$postId, $userId]);

    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?")
            ->execute([$postId, $userId]);
    } else {
        $pdo->prepare("INSERT IGNORE INTO post_likes (post_id, user_id) VALUES (?, ?)")
            ->execute([$postId, $userId]);
    }

    header("Location: feed.php?home=" . urlencode($backUrl));
    exit;
}

// ----------------------- HANDLE COMMENT -----------------------
if (isset($_POST['comment_post']) && isset($_SESSION['user_id'])) {
    $postId  = (int) ($_POST['post_id'] ?? 0);
    $userId  = (int) $_SESSION['user_id'];
    $comment = trim($_POST['comment'] ?? '');

    if ($comment !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)"
        );
        $stmt->execute([$postId, $userId, $comment]);
    }

    header("Location: feed.php?home=" . urlencode($backUrl));
    exit;
}

// ----------------------- FETCH POSTS -----------------------
$sql = "
    SELECT 
        p.id,
        p.caption,
        p.image_file,
        p.created_at,
        u.name AS author
    FROM posts p
    INNER JOIN users u ON u.user_id = p.user_id
    WHERE u.role = 'donor'
    ORDER BY p.created_at DESC
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// For each post, fetch likes + comments
foreach ($rows as &$p) {
    $pid = (int)$p['id'];

    // Like count
    $p['likes'] = (int)$pdo->query("SELECT COUNT(*) FROM post_likes WHERE post_id = $pid")->fetchColumn();

    // Has current user liked?
    $p['liked'] = false;
    if (isset($_SESSION['user_id'])) {
        $uid   = (int)$_SESSION['user_id'];
        $stmtL = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?");
        $stmtL->execute([$pid, $uid]);
        if ($stmtL->fetch()) {
            $p['liked'] = true;
        }
    }

    // Comments
    $stmtC = $pdo->prepare("
        SELECT c.comment, c.created_at, u.name AS commenter
        FROM post_comments c
        INNER JOIN users u ON u.user_id = c.user_id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmtC->execute([$pid]);
    $p['comments'] = $stmtC->fetchAll(PDO::FETCH_ASSOC);
}
unset($p); // good practice
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Community Feed — SmartAid</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --bg:#f4f9f4;
      --card:#ffffff;
      --green:#185e34;
      --muted:#5b7364;
      --border:#d6e9dd;
    }
    *{box-sizing:border-box;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
    body{margin:0;background:var(--bg);color:#061c10;}
    .wrap{max-width:900px;margin:24px auto;padding:0 16px 24px;}
    header{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
    .back-link{color:var(--green);text-decoration:none;font-size:14px;font-weight:600;display:inline-flex;align-items:center;gap:4px;}
    .back-link span{font-size:16px;}
    h1{margin:0;font-size:24px;color:var(--green);}
    .feed-card{
      background:var(--card);
      border-radius:18px;
      padding:16px 18px 10px;
      box-shadow:0 12px 35px rgba(0,0,0,0.04);
      border:1px solid #e7f2ea;
    }
    .post{
      border-bottom:1px solid #edf4ee;
      padding:14px 0;
    }
    .post:last-child{border-bottom:none;}
    .post-header{
      display:flex;
      flex-direction:column;
      margin-bottom:6px;
    }
    .author{
      font-weight:600;
      font-size:14px;
      color:#09351f;
    }
    .time{
      font-size:12px;
      color:var(--muted);
    }
    .caption{
      font-size:14px;
      margin:8px 0;
      white-space:pre-wrap;
    }
    .post-image{
      margin-top:6px;
      border-radius:12px;
      border:1px solid #e3eee6;
      max-width:100%;
      max-height:350px;
      object-fit:contain;
      background:#f3fbf5;
      display:block;
    }
    .actions{
      display:flex;
      gap:12px;
      margin-top:10px;
      align-items:center;
      flex-wrap:wrap;
    }
    .btn-pill{
      border-radius:999px;
      padding:6px 14px;
      border:1px solid var(--green);
      background:#ffffff;
      font-size:13px;
      display:inline-flex;
      align-items:center;
      gap:8px;
      cursor:pointer;
      transition:background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.08s;
    }
    .btn-pill .label{
      font-weight:600;
    }
    .btn-pill .count{
      font-size:12px;
      padding:2px 8px;
      border-radius:999px;
      background:#eaf5ee;
      color:#185e34;
    }
    .btn-like.is-liked{
      background:var(--green);
      color:#ffffff;
      box-shadow:0 6px 16px rgba(24,94,52,0.35);
    }
    .btn-like.is-liked .count{
      background:#ffffff;
      color:var(--green);
    }
    .btn-pill:hover{
      background:#eaf5ee;
      transform:translateY(-1px);
      box-shadow:0 4px 12px rgba(0,0,0,0.06);
    }
    .btn-pill[disabled]{
      opacity:0.6;
      cursor:default;
      box-shadow:none;
      transform:none;
    }
    .comments{
      margin-top:8px;
    }
    .comment{
      background:#f4fbf5;
      border-radius:10px;
      padding:6px 10px;
      font-size:13px;
      margin-top:4px;
      border:1px solid #e0efe5;
    }
    .comment b{
      color:#09351f;
    }
    .comment-time{
      font-size:11px;
      color:var(--muted);
      margin-left:4px;
    }
    .comment-form{
      display:flex;
      gap:8px;
      margin-top:10px;
    }
    .comment-form textarea{
      flex:1;
      border-radius:999px;
      border:1px solid var(--border);
      padding:8px 12px;
      font-size:13px;
      resize:none;
      min-height:36px;
    }
    .comment-form textarea:focus{
      outline:none;
      border-color:var(--green);
      box-shadow:0 0 0 2px rgba(24,94,52,0.2);
    }
    .comment-submit{
      border-radius:999px;
      border:none;
      padding:8px 16px;
      background:var(--green);
      color:#ffffff;
      font-size:13px;
      font-weight:600;
      cursor:pointer;
      transition:background 0.2s, box-shadow 0.2s, transform 0.08s;
      white-space:nowrap;
    }
    .comment-submit:hover{
      background:#124423;
      box-shadow:0 5px 14px rgba(0,0,0,0.18);
      transform:translateY(-1px);
    }
    .empty{
      text-align:center;
      padding:24px 0;
      color:var(--muted);
      font-size:14px;
    }
    @media(max-width:600px){
      .feed-card{padding:14px 12px;}
      .comment-form{flex-direction:column;align-items:stretch;}
      .comment-submit{align-self:flex-end;}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <a href="<?= e($backUrl) ?>" class="back-link">
        <span>←</span><span>Back</span>
      </a>
      <h1>SmartAid Community Feed</h1>
    </header>

    <div class="feed-card">
      <?php if (empty($rows)): ?>
        <div class="empty">No posts yet. Donors can create posts from their dashboard.</div>
      <?php else: ?>
        <?php foreach ($rows as $post): ?>
          <article class="post">
            <div class="post-header">
              <span class="author"><?= e($post['author']); ?></span>
              <span class="time"><?= e($post['created_at']); ?></span>
            </div>

            <div class="caption"><?= nl2br(e($post['caption'])); ?></div>

            <?php if (!empty($post['image_file'])): ?>
              <img class="post-image" src="<?= e($post['image_file']); ?>" alt="Post image">
            <?php endif; ?>

            <div class="actions">
              <?php if (isset($_SESSION['user_id'])): ?>
                <!-- LIKE BUTTON -->
                <form method="post" style="margin:0;">
                  <input type="hidden" name="post_id" value="<?= (int)$post['id']; ?>">
                  <button type="submit"
                          name="like_post"
                          class="btn-pill btn-like <?= $post['liked'] ? 'is-liked' : ''; ?>">
                    <span class="label">Like</span>
                    <span class="count"><?= (int)$post['likes']; ?></span>
                  </button>
                </form>
              <?php else: ?>
                <button class="btn-pill" disabled>
                  <span class="label">Like</span>
                  <span class="count"><?= (int)$post['likes']; ?></span>
                </button>
              <?php endif; ?>

              <!-- COMMENT COUNT BUTTON (display only) -->
              <button class="btn-pill" type="button">
                <span class="label">Comments</span>
                <span class="count"><?= count($post['comments']); ?></span>
              </button>
            </div>

            <!-- COMMENTS LIST -->
            <?php if (!empty($post['comments'])): ?>
              <div class="comments">
                <?php foreach ($post['comments'] as $c): ?>
                  <div class="comment">
                    <b><?= e($c['commenter']); ?></b>
                    <span class="comment-time"><?= e($c['created_at']); ?></span><br>
                    <?= nl2br(e($c['comment'])); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- ADD COMMENT -->
            <?php if (isset($_SESSION['user_id'])): ?>
              <form method="post" class="comment-form">
                <input type="hidden" name="post_id" value="<?= (int)$post['id']; ?>">
                <textarea name="comment" placeholder="Write a comment..."></textarea>
                <button type="submit" name="comment_post" class="comment-submit">Post</button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
