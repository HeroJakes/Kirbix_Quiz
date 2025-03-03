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

    $limit = 6;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $current_page = max(1, $current_page);
    $offset = ($current_page - 1) * $limit;

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
        .pagination {
            margin: 20px 0;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 12px;
            color: #bfbfbf;
            text-decoration: none;
            border: 1px solid #bfbfbf;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }

        .pagination a:hover {
            background-color: #9c9c9c;
            color: #ffffff;
        }

        .pagination a.active {
            background-color: #9c9c9c;
            color: #ffffff;
            pointer-events: none;
        }

        .pagination a.disabled {
            color: #cccccc;
            border-color: #cccccc;
            pointer-events: none;
        }
    </style>

    <title>Dashboard Sidebar Menu</title>
</head>

<body>
<nav class="sidebar close">
        <header>
            <div class="image-text">
                <a href="Dashboard.php">
                    <span class="image">
                        <img src="images/K.png" alt="" class="close-logo">
                    </span>
                </a>
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
                <li class="nav-link">
                    <a href="logout.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>

                </li>
            </div>
        </div>
    </nav>
    <section class="home">
        <div class="head">
            <div class="text">Quizzes</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
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
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?level=<?php echo $quiz_level; ?>&page=<?php echo $current_page - 1; ?>">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?level=<?php echo $quiz_level; ?>&page=<?php echo $i; ?>" <?php echo $i === $current_page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                    <a href="?level=<?php echo $quiz_level; ?>&page=<?php echo $current_page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
<script src="Javascript.js"></script>

</html>