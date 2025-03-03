<?php
include('session.php');
include('conn.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['mySession'])) {
    echo "Session not set. Redirecting...";
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
} else {
    $user_id = $_SESSION['mySession'];

    $query = "SELECT student_id FROM students WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_id);
    $stmt->fetch();
    $stmt->close();

    $servername = "localhost";
    $dbname = "kirbix";
    $dbusername = "root";
    $dbpassword = "";

    $con = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();

    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    $stmt->close();

    $valid_levels = ['Form_4', 'Form_5'];
    $quiz_level = isset($_GET['level']) && in_array($_GET['level'], $valid_levels) ? $_GET['level'] : 'Form_4';

    $limit = 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $quiz_query = "SELECT quiz_id, title, description, instructor_id, date_created FROM quizzes WHERE quiz_level = ? AND quiz_status = 'Approved' ORDER BY date_created DESC LIMIT ?, ?";
    $quiz_stmt = $con->prepare($quiz_query);
    $quiz_stmt->bind_param("sii", $quiz_level, $offset, $limit);
    $quiz_stmt->execute();
    $quiz_result = $quiz_stmt->get_result();

    $quizzes = [];
    while ($row = $quiz_result->fetch_assoc()) {
        $instructor_query = "SELECT username FROM users WHERE user_id = ?";
        $instructor_stmt = $con->prepare($instructor_query);
        $instructor_stmt->bind_param("i", $row['instructor_id']);
        $instructor_stmt->execute();
        $instructor_stmt->bind_result($instructor_name);
        $instructor_stmt->fetch();
        $instructor_stmt->close();

        $row['instructor_name'] = $instructor_name ?? 'Unknown Instructor';
        $quizzes[] = $row;
    }

    $quiz_stmt->close();

    $count_query = "SELECT COUNT(*) FROM quizzes WHERE quiz_level = ? AND quiz_status = 'Approved'";
    $count_stmt = $con->prepare($count_query);
    $count_stmt->bind_param("s", $quiz_level);
    $count_stmt->execute();
    $count_stmt->bind_result($total_quizzes);
    $count_stmt->fetch();
    $count_stmt->close();

    $total_pages = ceil($total_quizzes / $limit);

    $achievements_query = "SELECT points_required FROM achievements WHERE student_id = ?";
    $achievements_stmt = $con->prepare($achievements_query);
    $achievements_stmt->bind_param("i", $student_id);
    $achievements_stmt->execute();
    $achievements_result = $achievements_stmt->get_result();

    $achievements = [];
    while ($row = $achievements_result->fetch_assoc()) {
        $achievements[] = $row['points_required'];
    }
    $achievements_stmt->close();

    $con->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <link rel="stylesheet" href="css.css">
    <style>
        .achievement {
            text-align: center;
            max-width: 180px;
        }


        .achievement p {
            font-size: 14px;
            margin: 0;
        }
    </style>

    <title>Dashboard Sidebar Menu</title>

</head>

<body>
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="images/K.png" alt="" class="close-logo">
                </span>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="Dashboard.php">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="quiz_management.php">
                            <i class='bx bx-book-open icon'></i>
                            <span class="text nav-text">Quiz Management</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="performances.php">
                            <i class='bx bx-bar-chart-alt-2 icon'></i>
                            <span class="text nav-text">Performance Insights</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="support.php">
                            <i class='bx bx-support icon'></i>
                            <span class="text nav-text">Support</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                    <a href="logout.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>


            </div>
        </div>
    </nav>

    <section class="home">
        <div class="head">
            <div class="text">Home Page</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div class="welcome-section">
            <div class="welcome-text">
                <h2>Hey <?php echo htmlspecialchars($username); ?>,<br>Welcome Back!</h2>
                <form method="GET" action="quiz_details.php">
                    <input type="number" name="quiz_id" placeholder="Enter a Quiz Code" required>
                    <button type="submit">Enter</button>
                </form>
            </div>
            <div class="mascot-welcome">
                <img src="images/mascot.png" alt="Image of Mascot">
            </div>
        </div>

        <div class="quiz-section">
            <h3>Let's Get Started!</h3>
            <p>Select a Level:</p>
            <div>
                <button onclick="window.location.href='?level=Form_4'">Form 4</button>
                <button onclick="window.location.href='?level=Form_5'">Form 5</button>
            </div>
            <div class="quiz-list">
                <?php if (!empty($quizzes)): ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php
                        $image_number = ($quiz['quiz_id'] % 6) + 2;
                        $quiz_image = "images/quiz" . $image_number . ".png";
                        ?>
                        <div class="quiz-card">
                            <img src="<?php echo $quiz_image; ?>" alt="Quiz Image">
                            <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                            <p>Instructor Name: <?php echo htmlspecialchars($quiz['instructor_name']); ?></p>
                            <button onclick="window.location.href='quiz_details.php?quiz_id=<?php echo $quiz['quiz_id']; ?>'">Start Quiz</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No quizzes available for the selected level.</p>
                <?php endif; ?>
            </div>
            <button onclick="window.location.href='quiz_management.php'" id="seeMoreButton" style="margin-top: 20px;">See More</button>
        </div>
        <div class="achievements-section">
            <h3>Your Top Achievements</h3>
            <div class="achievements-list">
                <?php
                if (in_array("1000", $achievements)) {
                    echo '<div class="achievement"><img src="images/achievement1.png" alt="Achievement 1"><p>Your First 1000</p></div>';
                }
                if (in_array("2000", $achievements)) {
                    echo '<div class="achievement"><img src="images/achievement2.png" alt="Achievement 2"><p>2000 Earned</p></div>';
                }
                if (in_array("3000", $achievements)) {
                    echo '<div class="achievement"><img src="images/achievement3.png" alt="Achievement 3"><p>3000 Points</p></div>';
                }

                if (empty($achievements)) {
                    echo '<div class="achievement"><p>No achievements yet.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>
</body>
<script src="Javascript.js"></script>

</html>