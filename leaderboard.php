<?php
session_start();
require "config.php"; // MUST provide $pdo

// ----------------------------------------------------
// 1) DETERMINE HOME LINK BASED ON USER ROLE OR PARAM
// ----------------------------------------------------
$home_link = "homepage.php"; // default

if (!empty($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === "donor") {
        $home_link = "donor_homepage.php";
    } elseif ($_SESSION['user_role'] === "reporter") {
        $home_link = "reporter_homepage.php";
    }
}

// Allow ?home=donor_homepage.php override
if (!empty($_GET['home']) && in_array($_GET['home'], ['homepage.php','donor_homepage.php','reporter_homepage.php'])) {
    $home_link = $_GET['home'];
}

$is_logged_in = isset($_SESSION['user_id']);
$logged_email = $_SESSION['user_email'] ?? null;

// ----------------------------------------------------
// 2) FETCH REAL LEADERBOARD DATA
// ----------------------------------------------------
$sql = "
SELECT 
    u.user_id,
    u.name,
    u.email,
    u.location,
    COUNT(d.id) AS donations
FROM users u
LEFT JOIN donations d ON u.user_id = d.donor_id
WHERE u.role = 'donor'
GROUP BY u.user_id
ORDER BY donations DESC, u.name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Aid | Leaderboard</title>

  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f5f9f7;
      color:#1d1d1d;
      display:flex; flex-direction:column; align-items:center;
      min-height:100vh; padding:40px 20px;
    }
    .header {
      display:flex; align-items:center; gap:15px;
      margin-bottom:30px; text-align:center;
    }
    .header img {
      width:55px;height:55px;border-radius:50%;
      object-fit:cover; box-shadow:0 4px 8px rgba(0,0,0,0.3);
    }
    .header h1 {font-size:2rem; color:#1d7940; font-weight:700;}
    .leaderboard {
      width:90%;max-width:700px;background:#fff;
      border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.2);
      max-height:500px;overflow-y:auto;
    }
    table {width:100%;border-collapse:collapse;}
    thead th {
        position:sticky; top:0; z-index:5;
        background:#1d7940;color:white;
        padding:15px;
    }
    td {padding:15px 20px;}
    tr:nth-child(even){background:#f0f7f3;}
    tr:hover:not(.current-user){background:#e7f4eb;transition:.3s;}
    .rank{font-weight:bold;color:#1d7940;}
    .count{font-weight:600;}
    .current-user {
      background:#d9f3e4 !important;
      border:2px solid #1d7940;
      font-weight:bold;
    }
    tr:first-child td.rank::after {content:" 🥇";}
    tr:nth-child(2) td.rank::after {content:" 🥈";}
    tr:nth-child(3) td.rank::after {content:" 🥉";}
    .back-btn{
      margin-top:25px;padding:10px 20px;border:none;
      background:#1d7940;color:white;border-radius:6px;
      cursor:pointer;font-size:1rem;transition:.3s;
    }
    .back-btn:hover{background:#166632;}
  </style>
</head>

<body>
  <div class="header">
    <img src="images/circle-logo.png" alt="Smart Aid Logo">
    <h1>Leaderboard 🏆</h1>
  </div>

  <div class="leaderboard">
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Donor Name</th>
          <th>Donations</th>
          <th>Location</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $rank = 1;
        foreach ($leaderboard as $row):
            $highlight = ($logged_email && $logged_email === $row['email']) ? "class='current-user'" : "";
        ?>
        <tr <?= $highlight ?>>
          <td class="rank"><?= $rank++; ?></td>
          <td><?= htmlspecialchars($row['name']); ?></td>
          <td class="count"><?= $row['donations']; ?></td>
          <td><?= htmlspecialchars($row['location'] ?: "Not Provided"); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <button class="back-btn" onclick="window.location.href='<?= htmlspecialchars($home_link); ?>'">
    ← Back to Home
  </button>

</body>
</html>
