<?php
session_start();
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) { header("Location: donor_login.php"); exit(); }

// Initialize session variables for notifications if they don't exist
if (!isset($_SESSION['notifications_unread'])) {
    $_SESSION['notifications_unread'] = 3;
}
if (!isset($_SESSION['notifications_list'])) {
    $_SESSION['notifications_list'] = [
        ['id'=>1,'text'=>'Your donation to Flood Relief was received','time'=>'2h'],
        ['id'=>2,'text'=>'New pickup scheduled for your donation','time'=>'1d'],
        ['id'=>3,'text'=>'Leaderboard updated — you moved up 2 spots!','time'=>'3d'],
    ];
}

// Get user name and initial for the avatar
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Donor';
$initial = strtoupper($userName[0]);

// Sample data for the Community Donor Feed (You would load this from a database in a real application)
$feedPosts = [
    [
        'user' => 'Jane D.',
        'time' => '1h ago',
        'text' => 'Just donated 10 boxes of fresh produce to our local shelter! Excited to see it go to good use. 🌱',
        'likes' => 15,
        'image' => 'donation-post-1.jpg' // Placeholder image
    ],
    [
        'user' => 'Alex P.',
        'time' => '5h ago',
        'text' => 'Shoutout to the Smart Aid team for the fast pickup of my winter clothes donation! Keep up the great work.',
        'likes' => 8,
        'image' => ''
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Smart Aid — Donor Dashboard</title>

    <style>
        :root{
            --green-900:#114b2b;
            --green-700:#185e34;
            --green-500:#37a264;
            --green-300:#9be0b5;
            --accent:#1e7a43;
            --muted:#f4f7f5;
            --card-shadow:0 6px 20px rgba(8,40,20,0.08);
            --radius:12px;
            --max-width:1100px;
            --footer-bg:#114b2b;
            --footer-text:#eaf8ef;
            --orange-accent:#f99d3d;
            --badge-bg:#ff4d4f;
            --blue-accent: #5f90d1; /* Added for status icon */
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family:Inter,system-ui,"Segoe UI",Roboto,Arial;
            background:linear-gradient(180deg, #d3f3e1 0%, #f7fff9 100%); 
            color:#08321b;
            line-height:1.45;
        }
        .wrap{max-width:var(--max-width);margin:36px auto;padding:24px;}

        /* --- HEADER STYLES --- (Retained) */
        header.main-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            position:relative;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:12px;
            text-decoration:none;
            color:var(--green-900);
        }
        .logo{
            width:52px;height:52px;
            border-radius:10px;
            overflow:hidden;
            box-shadow:var(--card-shadow);
        }
        .logo img{
            width:100%;
            height:100%;
            object-fit:cover;
            border-radius:10px;
        }
        .right-nav{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .right-nav a{
            text-decoration:none;
            color:var(--green-900);
            padding:8px 12px;
            border-radius:8px;
            font-weight:600;
            font-size:14px;
        }

        /* Notification & Avatar Button Styles (Retained) */
        .notif-btn{
            position:relative;
            width:42px;height:42px;border-radius:10px;
            display:flex;align-items:center;justify-content:center;
            background:transparent;border:none;cursor:pointer;font-size:18px;color:var(--green-900);
        }
        .notif-btn:hover{ background: rgba(24,94,48,0.05); }

        .notif-badge{
            position:absolute;
            top:6px; right:6px;
            min-width:18px;height:18px;padding:0 5px;
            background:var(--badge-bg); color:white;
            font-size:12px;font-weight:800;border-radius:999px;
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 2px 6px rgba(0,0,0,0.12);
        }

        #notifPanel{
            position:absolute;
            top:65px;
            right:86px;
            width:300px;
            max-width:calc(100% - 40px);
            background:white;border-radius:10px;
            box-shadow:0 12px 30px rgba(8,40,20,0.12);
            display:none;
            z-index:60;
            padding:10px;
        }
        #notifPanel h4{ margin:0 0 8px 0; font-size:15px; color:var(--green-900); }
        .notif-item{ padding:10px;border-radius:8px; display:flex;justify-content:space-between; gap:8px; align-items:center; }
        .notif-item + .notif-item{ margin-top:8px; }
        .notif-item .text{ font-size:14px; color:#214f36; }
        .notif-item .time{ font-size:12px; color:#6b8b74; opacity:0.85; }

        .user-initial{
            width:42px;height:42px;border-radius:50%;
            background-color:var(--green-700);
            color:white;
            display:flex;
            justify-content:center;
            align-items:center;
            font-weight:700;font-size:18px;
            cursor:pointer;
            box-shadow:0 4px 12px rgba(0,0,0,0.1);
            transition:0.2s ease;
        }
        .user-initial:hover{
            transform:scale(1.05);
            background-color:var(--green-500);
        }

        #profileMenu {
            position: absolute;
            top: 65px;
            right: 0;
            width: 180px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            display: none;
            z-index: 50;
            padding: 8px 0;
        }
        #profileMenu ul { list-style:none; margin:0; padding:0; }
        #profileMenu li { padding:10px 15px; }
        #profileMenu li:hover { background:#f2f6f3; }
        #profileMenu a{
            text-decoration:none;
            font-size:14px;
            font-weight:600;
            color:var(--green-900);
            display:block;
        }
        #profileMenu .divider{
            height:1px;
            background:#ccc;
            margin:6px 0;
        }

        /* --- HERO SECTION --- (Retained) */
        .hero{
            margin-top:18px;
            background:linear-gradient(100deg,var(--green-500) 0%,var(--green-300) 100%);
            border-radius:var(--radius);
            padding:36px; 
            display:flex;
            gap:24px;
            align-items:center;
            box-shadow:var(--card-shadow);
        }
        .eyebrow{
            display:inline-block;
            background:rgba(255,255,255,0.2); 
            color:var(--green-900);
            padding:6px 12px;
            border-radius:999px;
            font-weight:700;
            font-size:13px;
            margin-bottom:14px;
        }
        h1{margin:0 0 14px 0;font-size:32px;color:white;text-shadow:0 1px 3px rgba(0,0,0,0.1);} 
        p.lead{margin:0 0 18px 0;color:white;opacity:0.95;font-size:17px;} 

        /* --- ACTION CARDS --- (Retained & Extended) */
        .donor-actions{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); /* Adjusted minmax for 4 cards */
            gap:20px; 
            margin-top:30px; 
        }

        .card{
            background:white;
            border-radius:var(--radius);
            padding:24px; 
            box-shadow:0 10px 30px rgba(8,40,20,0.1); 
            min-height:160px; 
            display:flex;
            flex-direction:column;
            gap:15px;
            transition:transform 0.2s, box-shadow 0.2s;
            text-decoration:none; 
            color:inherit;
        }
        .card:hover{
            transform: translateY(-5px);
            box-shadow:0 15px 40px rgba(8,40,20,0.15);
        }

        .icon{
            width:50px;height:50px; 
            border-radius:12px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            color:white;
            font-size:24px; 
        }
        /* Specific Icon Colors */
        .icon.impact{background:linear-gradient(135deg,#37a264,#185e34);} /* Green Gradient */
        .icon.history{background:linear-gradient(135deg,#f99d3d,#d46c1a);} /* Orange/Amber Gradient */
        .icon.status{background:linear-gradient(135deg,var(--blue-accent),#3f6aa7);} /* Blue Gradient */
        .icon.feed{background:linear-gradient(135deg,#e37b92,#c94f6c);} /* Pink/Red Gradient for Social */

        .card h3{margin:0;font-size:18px;color:var(--green-900);} 
        .card p{margin:0;color:#2f5c45;font-size:14px;opacity:0.95;}
        .card .foot{margin-top:auto;display:flex;}

        .small-btn{
            padding:8px 12px;
            border-radius:8px;
            background:var(--green-700);
            color:#fff;
            font-weight:700;
            text-decoration:none;
            font-size:13px;
        }

        /* --- DONOR FEED STYLES --- */
        .feed-section{
            margin-top:40px;
            padding-top:20px;
            border-top:1px solid #d3f3e1;
        }
        .feed-section h2{
            font-size:24px;
            color:var(--green-900);
            margin-bottom:20px;
        }
        .feed-container{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
            gap:20px;
        }
        .feed-post{
            background:white;
            border-radius:var(--radius);
            padding:20px;
            box-shadow:0 4px 15px rgba(8,40,20,0.05);
        }
        .post-header{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:15px;
        }
        .post-avatar{
            width:40px;height:40px;
            border-radius:50%;
            background:var(--orange-accent);
            color:var(--green-900);
            font-weight:700;
            display:flex;justify-content:center;align-items:center;
            font-size:16px;
        }
        .post-info strong{
            color:var(--green-700);
        }
        .post-info small{
            display:block;
            color:#6b8b74;
            font-size:12px;
        }
        .post-body p{
            margin:0 0 15px 0;
            font-size:15px;
        }
        .post-image img{
            width:100%;
            border-radius:10px;
            margin-bottom:15px;
        }
        .post-actions{
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-top:1px solid #eee;
            padding-top:10px;
            font-size:14px;
            color:var(--green-700);
        }
        .post-actions button{
            background:none;
            border:none;
            color:var(--green-700);
            cursor:pointer;
            font-size:14px;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:6px;
        }
        .post-actions button:hover{
            opacity:0.7;
        }


        /* --- FOOTER STYLES --- (Retained) */
        .footer-container{
            margin-top:60px;background:var(--footer-bg);color:var(--footer-text);
            border-radius:var(--radius);box-shadow:0 10px 30px rgba(8,40,20,0.15);
        }
        .footer-bottom-strip{
            background:var(--orange-accent);
            text-align:center;
            padding:8px;
            color:var(--green-900);
            font-weight:600;
            border-bottom-left-radius:12px;
            border-bottom-right-radius:12px;
        }

        /* --- MEDIA QUERIES --- (Retained) */
        @media(max-width:900px){
            #notifPanel{ right:8px; left:8px; top:72px; width:auto; }
            .hero{flex-direction:column; text-align:center;}
            .donor-actions{ grid-template-columns:1fr; }
        }
        @media(max-width:600px){
            .donor-actions{ grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); }
        }.footer-container{
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

            <a class="brand" href="#">
                <div class="logo">
                    <img src="circle-logo.png" alt="logo">
                </div>
                <div>
                    <div style="font-weight:800;font-size:18px">Smart Aid</div>
                    <div style="font-size:12px;color:var(--green-700);margin-top:2px">
                        real-time community donation platform
                    </div>
                </div>
            </a>

            <div class="right-nav">
                <a href="leaderboard.html">Leaderboard 🏆</a>

                <button id="notifBtn" class="notif-btn" aria-haspopup="true" aria-expanded="false" title="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 1 0-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>

                    <?php if (!empty($_SESSION['notifications_unread'])): ?>
                        <span class="notif-badge" id="notifBadge"><?php echo (int)$_SESSION['notifications_unread']; ?></span>
                    <?php endif; ?>
                </button>

                <div id="notifPanel" role="dialog" aria-hidden="true">
                    <h4>Notifications</h4>
                    <?php if (!empty($_SESSION['notifications_list'])): ?>
                        <?php foreach ($_SESSION['notifications_list'] as $n): ?>
                            <div class="notif-item">
                                <div class="text"><?php echo htmlspecialchars($n['text']); ?></div>
                                <div class="time"><?php echo htmlspecialchars($n['time']); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <div style="margin-top:10px;text-align:center;">
                            <a href="#" onclick="clearNotifications();return false;" class="small-btn" style="padding:8px 10px;font-size:13px;">Mark all read</a>
                        </div>
                    <?php else: ?>
                        <div class="notif-item"><div class="text muted">No notifications</div></div>
                    <?php endif; ?>
                </div>

                <div id="userInitial" class="user-initial"></div>

                <div id="profileMenu">
                    <ul>
                        <li><a href="view-profile.html">View Profile</a></li>
                        <li><a href="my_donations.php">My Donations</a></li>
                        <li><a href="donor-settings.html">Settings</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php">Sign Out</a></li>
                    </ul>
                </div>
            </div>

        </header>

        <main>
            <section class="hero">
                <div class="hero-left">
                    <span class="eyebrow">Welcome <?php echo htmlspecialchars($userName); ?></span>
                    <h1>Share your generosity — Inspire our Community 💚</h1>
                    <p class="lead">
                        Contribute extra food, clothes, or other resources to those who need it most. See and share the impact of your actions!
                    </p>
                    <a href="community_feed.php" class="small-btn" style="background:#fff; color:var(--green-700); font-size:14px; padding:10px 18px;">View Donor Feed →</a>
                </div>
            </section>

            <section class="donor-actions">

                <a class="card" href="donor_impact.php">
                    <div class="icon impact">📈</div>
                    <h3>View Your Impact</h3>
                    <p>See personalized statistics: items saved, people helped, and environmental impact.</p>
                    <div class="foot">
                        <span class="small-btn" style="background:var(--green-700);">View Stats</span>
                    </div>
                </a>
                
                <a class="card" href="community_feed.php">
                    <div class="icon feed">💬</div>
                    <h3>Community Donor Feed</h3>
                    <p>Share your donations and see what other amazing donors are contributing!</p>
                    <div class="foot">
                        <span class="small-btn" style="background:var(--icon-feed);">Join Feed</span>
                    </div>
                </a>

                <a class="card" href="my_donations.php">
                    <div class="icon history">🧾</div>
                    <h3>Donation History</h3>
                    <p>Review all your past and completed contributions to the community.</p>
                    <div class="foot">
                        <span class="small-btn" style="background:var(--orange-accent); color:var(--green-900);">View History</span>
                    </div>
                </a>

                <a class="card" href="pickup_status.php">
                    <div class="icon status">🚚</div>
                    <h3>Check Pickup Status</h3>
                    <p>Track the live status of your pending donations, from confirmation to delivery.</p>
                    <div class="foot">
                        <span class="small-btn" style="background:var(--blue-accent);">Track Status</span>
                    </div>
                </a>

            </section>

            <section class="feed-section">
                <h2>Latest Donor Activity Feed</h2>
                <div class="feed-container">
                    
                    <?php foreach ($feedPosts as $post): ?>
                        <div class="feed-post">
                            <div class="post-header">
                                <div class="post-avatar"><?php echo htmlspecialchars($post['user'][0]); ?></div>
                                <div class="post-info">
                                    <strong><?php echo htmlspecialchars($post['user']); ?></strong>
                                    <small><?php echo htmlspecialchars($post['time']); ?></small>
                                </div>
                            </div>
                            <?php if (!empty($post['image'])): ?>
                                <div class="post-image">
                                    
                                </div>
                            <?php endif; ?>
                            <div class="post-body">
                                <p><?php echo htmlspecialchars($post['text']); ?></p>
                            </div>
                            <div class="post-actions">
                                <div>
                                    <button onclick="alert('Like clicked!');">👍 Like (<?php echo $post['likes']; ?>)</button>
                                </div>
                                <a href="#" style="text-decoration:none; color:inherit;">
                                    Share Your Own Post!
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="feed-post" style="text-align:center; display:flex; flex-direction:column; justify-content:center; align-items:center; min-height:220px;">
                         <a href="post_donation.php" class="small-btn" style="padding:12px 25px; font-size:16px; background:var(--green-500);">
                            <span style="font-size:24px; margin-right:5px;">✍️</span> Post a New Donation
                         </a>
                         <p style="margin-top:15px; font-size:14px;">Let the community know what you gave!</p>
                    </div>

                </div>
            </section>


        </main>

    </div>

   

    <script>
        const userInitial = document.getElementById("userInitial");
        const menu = document.getElementById("profileMenu");
        const notifBtn = document.getElementById("notifBtn");
        const notifPanel = document.getElementById("notifPanel");
        const notifBadge = document.getElementById("notifBadge");

        userInitial.textContent = "<?php echo $initial; ?>";

        // Toggle Profile Menu
        userInitial.addEventListener("click", (e) => {
            e.stopPropagation();
            const visible = menu.style.display === "block";
            menu.style.display = visible ? "none" : "block";
            notifPanel.style.display = "none"; // Close notifications if open
        });

        // Toggle Notification Panel
        notifBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            const visible = notifPanel.style.display === "block";
            menu.style.display = "none"; // Close profile menu if open
            notifPanel.style.display = visible ? "none" : "block";
            notifBtn.setAttribute("aria-expanded", !visible);
            notifPanel.setAttribute("aria-hidden", visible);
        });

        // Close on outside click
        document.addEventListener("click", (e) => {
            if (!userInitial.contains(e.target) && !menu.contains(e.target)) {
                menu.style.display = "none";
            }
            if (!notifBtn.contains(e.target) && !notifPanel.contains(e.target)) {
                notifPanel.style.display = "none";
            }
        });

        // Close on Escape key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                menu.style.display = "none";
                notifPanel.style.display = "none";
            }
        });

        // Function to mark notifications as read (simulated)
        function clearNotifications(){
            notifPanel.style.display = "none";
            if (notifBadge) notifBadge.style.display = "none";
            // In a real application, you would send an AJAX request here to update the session/database:
            // fetch('clear_notifications.php', { method: 'POST' }).catch(()=>{});
            console.log("Notifications marked as read."); 
        }
    </script><footer class="footer-container">

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

</body>
</html>