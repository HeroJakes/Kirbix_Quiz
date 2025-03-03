<?php
require __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

// Database connection
$conn = new mysqli("localhost", "root", "", "kirbix");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$totalUsers = $totalQuizzes = $totalIssues = $totalEngagement = $averageScore = 0;

// Total Engagement (same for all filters)
$engagementQuery = "SELECT COUNT(*) AS total FROM users WHERE status = 'Active'";
$result = $conn->query($engagementQuery);
$totalEngagement = $result->fetch_assoc()['total'];

// Handle filters
$filter = $_POST['filter'] ?? 'today'; // Default to 'today'

// Define the queries based on the filter (today, this month, last month, etc.)
switch ($filter) {
  case 'today':
      $userQuery = "SELECT COUNT(*) AS total FROM users WHERE DATE(date_created) = CURDATE()";
      $quizQuery = "SELECT COUNT(*) AS total FROM quizzes WHERE DATE(date_created) = CURDATE() AND quiz_status = 'Approved'";
      $issueQuery = "SELECT COUNT(*) AS total FROM supporttickets WHERE DATE(date_submitted) = CURDATE()";
      $scoreQuery = "SELECT AVG(score) AS average FROM studentquizresults WHERE DATE(attempt_date) = CURDATE()";
      break;

  case 'this_month':
      $userQuery = "SELECT COUNT(*) AS total FROM users WHERE YEAR(date_created) = YEAR(CURDATE()) AND MONTH(date_created) = MONTH(CURDATE())";
      $quizQuery = "SELECT COUNT(*) AS total FROM quizzes WHERE YEAR(date_created) = YEAR(CURDATE()) AND MONTH(date_created) = MONTH(CURDATE()) AND quiz_status = 'Approved'";
      $issueQuery = "SELECT COUNT(*) AS total FROM supporttickets WHERE YEAR(date_submitted) = YEAR(CURDATE()) AND MONTH(date_submitted) = MONTH(CURDATE())";
      $scoreQuery = "SELECT AVG(score) AS average FROM studentquizresults WHERE YEAR(attempt_date) = YEAR(CURDATE()) AND MONTH(attempt_date) = MONTH(CURDATE())";
      break;

  case 'last_month':
      $firstDayOfLastMonth = date('Y-m-01', strtotime('first day of last month'));
      $lastDayOfLastMonth = date('Y-m-t', strtotime('last day of last month'));

      $userQuery = "SELECT COUNT(*) AS total FROM users WHERE date_created BETWEEN '$firstDayOfLastMonth' AND '$lastDayOfLastMonth'";
      $quizQuery = "SELECT COUNT(*) AS total FROM quizzes WHERE date_created BETWEEN '$firstDayOfLastMonth' AND '$lastDayOfLastMonth' AND quiz_status = 'Approved'";
      $issueQuery = "SELECT COUNT(*) AS total FROM supporttickets WHERE date_submitted BETWEEN '$firstDayOfLastMonth' AND '$lastDayOfLastMonth'";
      $scoreQuery = "SELECT AVG(score) AS average FROM studentquizresults WHERE attempt_date BETWEEN '$firstDayOfLastMonth' AND '$lastDayOfLastMonth'";
      break;

  case 'last_2_months':
      $userQuery = "SELECT COUNT(*) AS total FROM users WHERE date_created >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)";
      $quizQuery = "SELECT COUNT(*) AS total FROM quizzes WHERE date_created >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH) AND quiz_status = 'Approved'";
      $issueQuery = "SELECT COUNT(*) AS total FROM supporttickets WHERE date_submitted >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)";
      $scoreQuery = "SELECT AVG(score) AS average FROM studentquizresults WHERE attempt_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)";
      break;

  case 'All':
      $userQuery = "SELECT COUNT(*) AS total FROM users";
      $quizQuery = "SELECT COUNT(*) AS total FROM quizzes WHERE quiz_status = 'Approved'";
      $issueQuery = "SELECT COUNT(*) AS total FROM supporttickets";
      $scoreQuery = "SELECT AVG(score) AS average FROM studentquizresults";
      break;

  default:
      $userQuery = "SELECT COUNT(*) AS total FROM users WHERE DATE(date_created) = CURDATE()";
      $quizQuery = "SELECT COUNT(*) AS total FROM quizzes WHERE DATE(date_created) = CURDATE() AND quiz_status = 'Approved'";
      $issueQuery = "SELECT COUNT(*) AS total FROM supporttickets WHERE DATE(date_submitted) = CURDATE()";
      $scoreQuery = "SELECT AVG(score) AS average FROM studentquizresults WHERE DATE(attempt_date) = CURDATE()";
      break;
}

// Execute the queries for filtered data
$result = $conn->query($userQuery);
$totalUsers = $result->fetch_assoc()['total'];

$result = $conn->query($quizQuery);
$totalQuizzes = $result->fetch_assoc()['total'];

$result = $conn->query($issueQuery);
$totalIssues = $result->fetch_assoc()['total'];

$result = $conn->query($scoreQuery);
$averageScore = $result->fetch_assoc()['average'] ?? 0;

// Close the database connection
$conn->close();

// Generate PDF when requested
if (isset($_POST['generate_pdf'])) {
  // Prepare the PDF content
  $html = "
      <h1>Report for $filter</h1>
      <p><strong>Total Engagement:</strong> $totalEngagement</p>
      <p><strong>Total Users:</strong> $totalUsers</p>
      <p><strong>Total Quizzes:</strong> $totalQuizzes</p>
      <p><strong>Total Issues:</strong> $totalIssues</p>
      <p><strong>Average Quiz Score:</strong> $averageScore</p>
  ";

  // Generate PDF using dompdf
  $dompdf = new Dompdf();
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();

  // Output the generated PDF to the browser
  $dompdf->stream("report_$filter.pdf", ["Attachment" => true]);
  exit;
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Monitoring</title>
    <link rel="stylesheet" href="css/performance.css">
    <script type="text/javascript" src="app.js" defer></script>
    <style>
      .pdf-form {
        display: flex;
        justify-content: flex-end; /* Aligns the content to the right */
        margin: 10px;
      }

      .generate-pdf-btn {
        background-color: #637aeb;
        color: #fff;
        padding: 12px 17px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease, transform 0.2s ease;
      }

      .generate-pdf-btn:hover {
        background-color: #5366c4;
        transform: translateY(-2px);
      }

      .generate-pdf-btn:active {
        background-color: #4b59b0;
        transform: translateY(0);
      }

      select {
        padding: 10px 15px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
        background-color: #fff;
        transition: border-color 0.3s ease;
        margin-right: 10px; /* Add space between select and button */
      }

      select:hover {
        border-color: #637aeb;
      }

      select:focus {
        border-color: #5366c4;
        outline: none;
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
      <li>
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
      <li class="active">
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
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z" />
          </svg>
          <span>Logout</span>
        </a>
      </li>

    </ul>
  </nav>
  <main>
    <div class="head">
      <div class="text">Performance Monitoring</div>
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
    <form method="POST">
            <div class="filter-bar">
                <button type="submit" name="filter" value="today" class="filter-btn">Today</button>
                <button type="submit" name="filter" value="this_month" class="filter-btn">This Month</button>
                <button type="submit" name="filter" value="last_2_months" class="filter-btn">Last 2 Months</button>
                <button type="submit" name="filter" value="All" class="filter-btn">All</button>
            </div>
      </form>

    <div class="bottom-part">
      <div class="card-medium">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113ZM120-240h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0 320Zm0-400Z"/></svg>
        <h3>Total Number of User Created</h3>
        <p><?php echo $totalUsers; ?></p>
      </div>

      <div class="card-medium">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M560-360q17 0 29.5-12.5T602-402q0-17-12.5-29.5T560-444q-17 0-29.5 12.5T518-402q0 17 12.5 29.5T560-360Zm-30-128h60q0-29 6-42.5t28-35.5q30-30 40-48.5t10-43.5q0-45-31.5-73.5T560-760q-41 0-71.5 23T446-676l54 22q9-25 24.5-37.5T560-704q24 0 39 13.5t15 36.5q0 14-8 26.5T578-596q-33 29-40.5 45.5T530-488ZM320-240q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H320Zm0-80h480v-480H320v480ZM160-80q-33 0-56.5-23.5T80-160v-560h80v560h560v80H160Zm160-720v480-480Z"/></svg>
        <h3>Total Number of Quizzes Approved</h3>
        <p><?php echo $totalQuizzes; ?></p>
      </div>

      <div class="card-medium">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240ZM330-120 120-330v-300l210-210h300l210 210v300L630-120H330Zm34-80h232l164-164v-232L596-760H364L200-596v232l164 164Zm116-280Z"/></svg>
        <h3>Total Number Issues Reported</h3>
        <p><?php echo $totalIssues; ?></p>
      </div>
      
      <div class="card-medium">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m438-338 226-226-57-57-169 169-84-84-57 57 141 141Zm42 258q-139-35-229.5-159.5T160-516v-244l320-120 320 120v244q0 152-90.5 276.5T480-80Zm0-84q104-33 172-132t68-220v-189l-240-90-240 90v189q0 121 68 220t172 132Zm0-316Z"/></svg>
        <h3>Total User Engagement (Active)</h3>
        <p><?php echo $totalEngagement; ?></p>
      </div>

      <div class="card-medium">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M160-640h640v-80H160v80Zm-80-80q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v240H160v240h164v80H160q-33 0-56.5-23.5T80-240v-480ZM598-80 428-250l56-56 114 112 226-226 56 58L598-80ZM160-720v480-180 113-413Z"/></svg>
        <h3>Average Quiz Score</h3>
        <p><?php echo number_format($averageScore, 2); ?></p>
      </div>
    </div>
    <form method="POST" action="performance.php" id="filter-form">
        <select name="filter" id="filter">
            <option value="today" <?php echo ($filter == 'today') ? 'selected' : ''; ?>>Today</option>
            <option value="this_month" <?php echo ($filter == 'this_month') ? 'selected' : ''; ?>>This Month</option>
            <option value="last_2_months" <?php echo ($filter == 'last_2_months') ? 'selected' : ''; ?>>Last 2 Months</option>
            <option value="All" <?php echo ($filter == 'All') ? 'selected' : ''; ?>>All</option>
        </select>
        <button type="submit" class="generate-pdf-btn" name="generate_pdf" value="true">Generate Report</button>
    </form>
  </main>
  <script>
        // Add interactivity with JavaScript
        document.addEventListener('DOMContentLoaded', () => {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const dataFields = {
                users: document.getElementById('users'),
                quizzes: document.getElementById('quizzes'),
                issues: document.getElementById('issues'),
                engagement: document.getElementById('engagement'),
            };

            // Highlight the active button
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    // Fetch new data based on the filter
                    const filter = button.getAttribute('data-filter');
                    fetch(`getData.php?filter=${filter}`)
                        .then(response => response.json())
                        .then(data => {
                            dataFields.users.textContent = data.users;
                            dataFields.quizzes.textContent = data.quizzes;
                            dataFields.issues.textContent = data.issues;
                            dataFields.engagement.textContent = data.engagement;
                        })
                        .catch(error => console.error('Error fetching data:', error));
                });
            });
        });
    </script>
</body>
</html>