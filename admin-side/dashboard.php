<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "kirbix";


$conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalUsers = $row['total_users'];
} else {
    $totalUsers = 0;
}

// Query to count total Quizz
$sql = "SELECT COUNT(*) AS total_quizzes FROM quizzes";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalQuizzes = $row['total_quizzes'];
} else {
    $totalQuizzes = 0; 
}

// Query to count the total number of reported issues
$sql = "SELECT COUNT(*) as total_issues FROM supporttickets";
$result = $conn->query($sql);

// Fetch the result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalIssues = $row['total_issues'];
} else {
    $totalIssues = 0;
}


// SQL query to count users who logged in in the last 7 days
$sql = "SELECT COUNT(*) AS user_count FROM users WHERE last_login >= NOW() - INTERVAL 1 WEEK";
$result = $conn->query($sql);

$user_count = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_count = $row['user_count'];
}

// Query for the latest 3 notifications (combined)
$quizzesQuery = "SELECT 'Quiz' AS type, title AS content, date_created FROM quizzes ORDER BY date_created DESC LIMIT 3";
$usersQuery = "SELECT 'User' AS type, username AS content, date_created FROM users ORDER BY date_created DESC LIMIT 3";

// Combine both queries and get only the latest 3 overall
$combinedQuery = "($quizzesQuery) UNION ($usersQuery) ORDER BY date_created DESC LIMIT 3";
$result = $conn->query($combinedQuery);

// Fetch notifications
$notifications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
// Close the connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="css/style.css">
  <script type="text/javascript" src="app.js" defer></script>
  <style>
    .noti .see-more {
      text-decoration: none;
      font-size: 1rem;
      color: #007bff;
      font-weight: bold;
      margin-left:25%;
    }

    .noti .see-more:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <nav id="sidebar">
    <ul>
      <li>
        <img class="logo-img" src="Images/logo.png" alt="Kirbix">
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z" />
          </svg>
        </button>
      </li>
      <li class="active">
        <a href="dashboard.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M520-600v-240h320v240H520ZM120-440v-400h320v400H120Zm400 320v-400h320v400H520Zm-400 0v-240h320v240H120Zm80-400h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z" />
          </svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="user.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm560 40-12-60q-12-5-22.5-10.5T584-204l-58 18-40-68 46-40q-2-14-2-26t2-26l-46-40 40-68 58 18q11-8 21.5-13.5T628-460l12-60h80l12 60q12 5 22.5 11t21.5 15l58-20 40 70-46 40q2 12 2 25t-2 25l46 40-40 68-58-18q-11 8-21.5 13.5T732-180l-12 60h-80Zm40-120q33 0 56.5-23.5T760-320q0-33-23.5-56.5T680-400q-33 0-56.5 23.5T600-320q0 33 23.5 56.5T680-240ZM400-560q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Z" />
          </svg>
          <span>User Management</span>
        </a>
      </li>
      <li>
        <a href="content.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-560h80v560h440v80H200Zm160-240v-480 480Z" />
          </svg>
          <span>Content</span>
        </a>
      </li>
      <li>
        <a href="performance.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M120-120v-80l80-80v160h-80Zm160 0v-240l80-80v320h-80Zm160 0v-320l80 81v239h-80Zm160 0v-239l80-80v319h-80Zm160 0v-400l80-80v480h-80ZM120-327v-113l280-280 160 160 280-280v113L560-447 400-607 120-327Z" />
            </svg>
            <span>Performance</span>
          </a>
      </li>
      <li>
        <a href="access.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M824-120 636-308q-41 32-90.5 50T440-240q-90 0-162.5-44T163-400h98q34 37 79.5 58.5T440-320q100 0 170-70t70-170q0-100-70-170t-170-70q-94 0-162.5 63.5T201-580h-80q8-127 99.5-213.5T440-880q134 0 227 93t93 227q0 56-18 105.5T692-364l188 188-56 56ZM397-400l-63-208-52 148H80v-60h160l66-190h60l61 204 43-134h60l60 120h30v60h-67l-47-94-50 154h-59Z" />
          </svg>
          <span>Access Logs</span>
        </a>
      </li>
      <li>
        <a href="support.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
          </svg>
          <span>Support</span>
        </a>
      </li>

      <li>
        <a href="../login.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z"/></svg>
          <span>Logout</span>
        </a>
      </li>

    </ul>
  </nav>
  <main>
    <div class="head">
      <div class="text">Dashboard</div>
      <div class="icons">
        <i class='bx bx-bell'></i>
      </div>
      <div class="profile">
        <img src="Images/profile.png" alt="Profile Picture" class="bx bx-user-circle">
        <div class="profile-text">
          <p>Ivan Lee</p>
          <p>Administrator</p>
        </div>
      </div>
    </div>


    <div class="dashboard">
      <div class="upper-part">
        <div class="card">
          <div class="welcome-card">
            <div class="text">
              <h1>Hey, Ivan Lee</h1><br>
              <h2>Welcome Back!</h1><br><br>
                <form method="POST" action="">
                  <input type="number" name="quiz_code" placeholder="Enter a Quiz Code" required>
                  <button type="submit">Enter</button>
              </form>
            </div>
            <img src="Images/dashboardimg.png"></img>
          </div>


          <div class="notification">
            <div class="noti">
              <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                fill="#e8eaed">
                <path
                  d="M160-200v-80h80v-280q0-83 50-147.5T420-792v-28q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v28q80 20 130 84.5T720-560v280h80v80H160Zm320-300Zm0 420q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM320-280h320v-280q0-66-47-113t-113-47q-66 0-113 47t-47 113v280Z" />
              </svg>
              <p>Notification</p>
              <a href="notification.php" class="see-more">See more</a>
            </div>

            <div class="notifications">
              <?php if (!empty($notifications)): ?>
                  <?php foreach ($notifications as $notification): ?>
                      <div class="card-small">
                          <h3><?php echo htmlspecialchars($notification['type']); ?> Notification</h3>
                          <p><?php echo htmlspecialchars($notification['content']); ?></p>
                          <small><?php echo date('F j, Y, g:i a', strtotime($notification['date_created'])); ?></small>
                      </div>  
                  <?php endforeach; ?>
              <?php else: ?>
                  <p>No notifications available.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="bottom-part">
        <div class="card-medium">
          <h3>Total Number of Users</h3>
          <p><?php echo $totalUsers; ?></p>
        </div>

        <div class="card-medium">
          <h3>Total Number of Quizzes</h3>
          <p><?php echo $totalQuizzes; ?></p>
        </div>

        <div class="card-medium">
          <h3>Total Number Issues Reported</h3>
          <p><?php echo $totalIssues; ?></p>
        </div>
        
        <div class="card-medium">
          <h3>Total User Engagement</h3>
          <p><?php echo $user_count; ?></p>
        </div>
      </div>
  </main>
</body>
</html>