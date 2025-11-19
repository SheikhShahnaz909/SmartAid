<?php
// leaderboard.php - Displays donor rankings

session_start();

// Optionally redirect to home if not logged in, but allowing public viewing is common for leaderboards.
// We will allow viewing for now, but include the user's data if they are logged in.
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $_SESSION['user_email'] ?? ''; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Smart Aid | Leaderboard</title>
  <style>
    /* [CSS styles from original leaderboard.html preserved] */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Poppins", sans-serif;
      background-color: #f5f9f7; 
      color: #1d1d1d;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 40px 20px;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 30px;
      text-align: center;
    }

    .header img {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    .header h1 {
      color: #1d7940; 
      font-size: 2rem;
      font-weight: 700;
    }

    .leaderboard {
      width: 90%;
      max-width: 700px;
      background: #fff; 
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
      max-height: 500px;
      overflow-y: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    }

    th, td {
      padding: 15px 20px;
      text-align: left;
    }

    th {
      background-color: #1d7940;
      color: white;
      font-weight: 600;
    }

    tr:nth-child(even) {
      background-color: #f0f7f3;
    }

    tr:hover:not(.current-user) {
      background-color: #e7f4eb;
      transition: 0.3s;
    }

    td.rank {
      font-weight: bold;
      color: #1d7940;
    }

    td.count {
      font-weight: 600;
    }

    .current-user {
      background-color: #d9f3e4 !important; 
      font-weight: bold; 
      border: 2px solid #1d7940; 
    }

    .current-user td.rank, .current-user td.count {
        color: #0d4a22; 
    }

    .current-user:hover {
        background-color: #c9ebd8 !important; 
    }

    tr:first-child td.rank::after {
      content: " 🥇";
    }
    tr:nth-child(2) td.rank::after {
      content: " 🥈";
    }
    tr:nth-child(3) td.rank::after {
      content: " 🥉";
    }

    @media (max-width: 600px) {
      th, td {
        font-size: 0.9rem;
        padding: 10px;
      }
      .header h1 {
        font-size: 1.4rem;
      }
      .header img {
        width: 45px;
        height: 45px;
      }
    }

    .back-btn {
      margin-top: 25px;
      padding: 10px 20px;
      background-color: #1d7940;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1rem;
      transition: background-color 0.3s;
    }

    .back-btn:hover {
      background-color: #166632;
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="images/circle-logo.png" alt="Smart Aid Logo">
    <h1>Leaderboard 🏆</h1>
  </div>

  <div class="leaderboard">
    <table id="leaderboardTable">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Donor Name</th>
          <th>Donations</th>
          <th>Location</th>
        </tr>
      </thead>
      <tbody>
        </tbody>
    </table>
  </div>

  <button class="back-btn" onclick="window.location.href='homepage.html'">← Back to Home</button>

  <script>
    // NOTE: In a production app, this data would be fetched from a PHP API endpoint.
    // For now, we use the client-side logic from the original file, adapted for session.

    const userEmail = "<?php echo $user_email; ?>";
    const isUserLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

    // Define the current user's data (if logged in)
    const currentUser = isUserLoggedIn 
      ? { name: "You (Current User)", donations: 7, location: "Moodbidri", isUser: true, uniqueId: userEmail }
      : null;

    // Sample donor data (copied from original leaderboard.html)
    let donors = [
      { name: "Ayesha Khan", donations: 40, location: "Bangalore" },
      { name: "Rahul Verma", donations: 35, location: "Hyderabad" },
      // ... (rest of the sample data)
      { name: "Priya Sharma", donations: 32, location: "Chennai" },
      { name: "Karan Patel", donations: 28, location: "Mumbai" },
      { name: "Sara Thomas", donations: 25, location: "Delhi" },
      { name: "Mohammed Ali", donations: 23, location: "Kochi" },
      { name: "Deepa Menon", donations: 21, location: "Pune" },
      { name: "Vikram Singh", donations: 19, location: "Jaipur" },
      { name: "Anjali Rao", donations: 18, location: "Kolkata" },
      { name: "Gaurav Soni", donations: 16, location: "Ahmedabad" },
      { name: "Natasha Iyer", donations: 15, location: "Lucknow" },
      { name: "Ravi Shankar", donations: 13, location: "Bhopal" },
      { name: "Shreya Jain", donations: 12, location: "Surat" },
      { name: "Imran Pasha", donations: 11, location: "Indore" },
      { name: "Jessica Dsouza", donations: 9, location: "Goa" },
      { name: "Tarun Reddy", donations: 8, location: "Visakhapatnam" },
      { name: "Preeti Gupta", donations: 6, location: "Mysore" },
      { name: "Zain Khan", donations: 5, location: "Nagpur" },
      { name: "Nisha Varma", donations: 4, location: "Patna" },
      { name: "Amit Joshi", donations: 3, location: "Ranchi" },
    ];

    // Add the current user only if logged in
    if (currentUser) {
      // Find the user's place in the list by matching email or manually push them for testing
      // For this test, we'll manually insert the user data for visibility
      donors.push(currentUser);
    }

    // Sort by donation count
    donors.sort((a, b) => b.donations - a.donations);

    // Display leaderboard
    const tableBody = document.querySelector("#leaderboardTable tbody");
    tableBody.innerHTML = '';

    donors.forEach((donor, index) => {
      const row = document.createElement("tr");
      
      if (donor.isUser) {
        row.classList.add('current-user');
        donor.name = "You"; 
        row.id = 'current-user-rank';
      }

      row.innerHTML = `
        <td class="rank">${index + 1}</td>
        <td>${donor.name}</td>
        <td class="count">${donor.donations}</td>
        <td>${donor.location}</td>
      `;
      tableBody.appendChild(row);
    });

    // Scroll to user's rank
    const userRow = document.getElementById('current-user-rank');
    if (userRow) {
        userRow.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center'
        });
    }
  </script>
</body>
</html>