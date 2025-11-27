<?php
session_start();
require_once 'config.php';

// ------------------- CHECK AUTH -------------------
//if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'donor') {
  //  header('Location: donor_login.php');
    //exit;
//}

$user_id = (int) $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Donor');

// ------------------- DETERMINE RETURN LINK -------------------
$allowed_back_pages = ['homepage.php', 'donor_homepage.php', 'reporter_homepage.php'];

// 1️⃣ If link passed via URL
if (isset($_GET['home']) && in_array($_GET['home'], $allowed_back_pages, true)) {
    $back_link = $_GET['home'];
}
// 2️⃣ Else choose based on user role
elseif (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'donor') {
        $back_link = 'donor_homepage.php';
    } elseif ($_SESSION['user_role'] === 'reporter') {
        $back_link = 'reporter_homepage.php';
    } else {
        $back_link = 'homepage.php';
    }
}
// 3️⃣ Final fallback
else {
    $back_link = 'homepage.php';
}

// ------------------- FILE UPLOAD LOGIC -------------------

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$messages = [];
$errors = [];

function safeStr($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function handle_upload($fileInputName) {
    global $uploadDir, $errors;
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$fileInputName];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error (code ' . $f['error'] . ')';
        return null;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed, true)) {
        $errors[] = 'Only JPG / PNG / WEBP allowed.';
        return null;
    }
    if ($f['size'] > 4 * 1024 * 1024) {
        $errors[] = 'Image too large (max 4MB).';
        return null;
    }
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . "." . $ext;
    $dest = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        $errors[] = 'Failed to upload image.';
        return null;
    }
    @chmod($dest, 0644);
    return 'uploads/' . $filename;
}

// ------------------- POST ACTIONS -------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_post') {
        $caption = trim($_POST['caption'] ?? '');
        $imgPath = handle_upload('image');
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, caption, image_file) VALUES (:uid, :cap, :img)");
            $stmt->execute([':uid'=>$user_id, ':cap'=>$caption, ':img'=>$imgPath]);
            $messages[] = 'Post created.';
        } catch (Exception $e) {
            $errors[] = "Database error.";
        }
    }

    if ($action === 'delete_post') {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image_file FROM posts WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id'=>$postId, ':uid'=>$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $pdo->prepare("DELETE FROM posts WHERE id = :id AND user_id = :uid")->execute([':id'=>$postId, ':uid'=>$user_id]);
            if (!empty($row['image_file'])) {
                $path = __DIR__ . '/' . ltrim($row['image_file'], '/');
                if (file_exists($path)) unlink($path);
            }
            $messages[] = 'Post deleted.';
        }
    }

    if ($action === 'edit_post') {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');
        
        $stmt = $pdo->prepare("SELECT image_file FROM posts WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id'=>$postId, ':uid'=>$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $imgPath = $row['image_file'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newImg = handle_upload('image');
                if ($newImg) {
                    if ($imgPath) {
                        $oldPath = __DIR__ . '/' . ltrim($imgPath, '/');
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $imgPath = $newImg;
                }
            }

            $pdo->prepare("UPDATE posts SET caption=:cap, image_file=:img, updated_at=NOW() WHERE id=:id AND user_id=:uid")
                ->execute([':cap'=>$caption, ':img'=>$imgPath, ':id'=>$postId, ':uid'=>$user_id]);

            $messages[] = "Post updated.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = :uid ORDER BY created_at DESC");
$stmt->execute([':uid'=>$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Donations</title>

<!-- (HTML + CSS untouched as requested) -->
<style>
/* 🔥 Improved Styling + Full Image Fix */
body{font-family:Inter,system-ui,Arial;background:#eef7f1;margin:0;padding:20px;color:#0b3d20;}
.wrap{max-width:1000px;margin:auto;}
.panel,.card{background:white;border-radius:16px;padding:16px;box-shadow:0 6px 20px rgba(0,0,0,0.06);}
textarea,input[type=file]{width:100%;padding:10px;border:1px solid #cde2d3;border-radius:10px;background:#fcfffd;}
.btn{background:#1b7a47;color:white;border:none;padding:10px 16px;border-radius:10px;cursor:pointer;}
.btn-danger{background:#cc2a2a;color:white;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px;margin-top:15px;}
.card img{width:100%;height:auto;max-height:500px;object-fit:contain;border-radius:14px;background:#fafdfa;}
.actions{margin-top:10px;display:flex;gap:8px;}
.small{padding:8px 12px;border-radius:8px;border:none;cursor:pointer;background:#edf8f1;}
.small:hover{background:#d7f1df;}
.link-back{color:#1b7a47;text-decoration:none;font-weight:bold;}
</style>

</head>
<body>
<div class="wrap">

<h1>My Donation Posts</h1>
<p>Hello, <?php echo safeStr($user_name); ?> — share your donations here.</p>

<div class="panel">
<h3>Create New Post</h3>

<?php foreach($messages as $m) echo "<p style='color:#1b7a47;'>✔ $m</p>"; ?>
<?php foreach($errors as $e) echo "<p style='color:red;'>⚠ $e</p>"; ?>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="create_post">
<label>Caption</label>
<textarea name="caption" placeholder="Describe donation..."></textarea>
<br><label>Image</label>
<input type="file" name="image" accept="image/*">
<br><button class="btn" type="submit">Post Donation</button>
</form>
</div>

<!-- ⭐ FIXED BACK BUTTON BELOW ⭐ -->
<br><a class="link-back" href="<?= $back_link ?>">← Back to Dashboard</a>

<h2>Your Posts</h2>

<div class="grid">
<?php if(!$posts): ?>
<div class="card">No posts yet.</div>
<?php endif; ?>

<?php foreach($posts as $p): ?>
<div class="card">

<?php if(!empty($p['image_file']) && file_exists(__DIR__.'/'.ltrim($p['image_file'],'/'))): ?>
<img src="<?php echo safeStr($p['image_file']); ?>">
<?php endif; ?>

<p><?php echo nl2br(safeStr($p['caption'])); ?></p>
<small>Posted: <?= safeStr($p['created_at']) ?></small>

<div class="actions">
<button class="small" onclick="toggleEdit(event, <?= $p['id'] ?>)">Edit</button>

<form method="post">
<input type="hidden" name="action" value="delete_post">
<input type="hidden" name="post_id" value="<?= $p['id'] ?>">
<button class="small btn-danger" onclick="return confirm('Delete this post?')" type="submit">Delete</button>
</form>
</div>

<form method="post" enctype="multipart/form-data" id="form-<?= $p['id'] ?>" style="display:none;margin-top:10px;">
<input type="hidden" name="action" value="edit_post">
<input type="hidden" name="post_id" value="<?= $p['id'] ?>">
<textarea name="caption"><?php echo safeStr($p['caption']); ?></textarea>
<input type="file" name="image" accept="image/*">
<button class="btn small">Save</button>
</form>

</div>
<?php endforeach; ?>
</div>
</div>

<script>
function toggleEdit(e,id){
e.preventDefault();
let form=document.getElementById("form-"+id);
form.style.display=form.style.display==="none"?"block":"none";
}
</script>

</body>
</html>
