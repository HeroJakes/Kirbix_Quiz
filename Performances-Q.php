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

    // Fetch user details
    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();

    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    $stmt->close();

    // Pagination variables
    $limit = 5;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    // Fetch student ID
    $student_query = "SELECT student_id FROM students WHERE user_id = ?";
    $student_stmt = $con->prepare($student_query);
    $student_stmt->bind_param("i", $user_id);
    $student_stmt->execute();
    $student_stmt->bind_result($student_id);
    $student_stmt->fetch();
    $student_stmt->close();

    // Fetch quiz IDs and attempt dates for the student
    $quiz_id_query = "SELECT quiz_id, attempt_date FROM studentquizresults WHERE student_id = ?";
    $quiz_id_stmt = $con->prepare($quiz_id_query);
    $quiz_id_stmt->bind_param("i", $student_id);
    $quiz_id_stmt->execute();
    $quiz_id_result = $quiz_id_stmt->get_result();
    $quiz_attempt_data = [];
    while ($row = $quiz_id_result->fetch_assoc()) {
        $quiz_attempt_data[$row['quiz_id']] = $row['attempt_date'];
    }
    $quiz_id_stmt->close();

    // Fetch quiz details
    $completed_quizzes = [];
    if (!empty($quiz_attempt_data)) {
        $quiz_ids = array_keys($quiz_attempt_data);
        $placeholders = implode(',', array_fill(0, count($quiz_ids), '?'));
        $quiz_detail_query = "SELECT quiz_id, title, description, instructor_id 
                              FROM quizzes 
                              WHERE quiz_id IN ($placeholders) AND quiz_status = 'Approved' 
                              ORDER BY date_created DESC LIMIT ?, ?";
        $quiz_detail_stmt = $con->prepare($quiz_detail_query);

        $types = str_repeat('i', count($quiz_ids)) . "ii";
        $params = array_merge($quiz_ids, [$offset, $limit]);
        $quiz_detail_stmt->bind_param($types, ...$params);

        $quiz_detail_stmt->execute();
        $quiz_detail_result = $quiz_detail_stmt->get_result();

        while ($row = $quiz_detail_result->fetch_assoc()) {
            // Fetch instructor name
            $instructor_query = "SELECT username FROM users WHERE user_id = ?";
            $instructor_stmt = $con->prepare($instructor_query);
            $instructor_stmt->bind_param("i", $row['instructor_id']);
            $instructor_stmt->execute();
            $instructor_stmt->bind_result($instructor_name);
            $instructor_stmt->fetch();
            $instructor_stmt->close();

            $row['instructor_name'] = $instructor_name ?? 'Unknown Instructor';
            $row['attempt_date'] = $quiz_attempt_data[$row['quiz_id']] ?? 'Unknown Date';
            $completed_quizzes[] = $row;
        }
        $quiz_detail_stmt->close();
    }
}

$con->close();
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
    <title>Recent Quizzes Taken</title>

    <style>
        .home {
            align-content: left;
        }

        .header-section {
            background-image: linear-gradient(to bottom right, #a5d3ff, #ffffff);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: left;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .header-section h3 {
            font-size: 30px;
        }

        .header-section img {
            width: 150px;
            margin-left: 80px;
        }

        .content-list {
            background-color: #d5eeff;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 10px;
            text-align: left;
            height: auto;
            /* Adjust height for flexibility */
            display: flex;
            /* Enable flexbox */
            justify-content: center;
            /* Center items horizontally */
            align-items: flex-start;
            /* Align items at the top */
            gap: 15px;
            /* Add spacing between items */
            flex-wrap: wrap;
            /* Allow wrapping to a new row if needed */
            height: auto;
            align-items: flex-start;
        }

        .content {
            background-color: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 0px;
            text-align: center;
            flex: 1 1 calc(20% - 15px);
            /* Make each box take up 25% width, minus gaps */
            max-width: 250px;
            min-width: 190px;
            height: 200px;
        }

        .quiz-container {
            display: flex;
            justify-content: flex-start;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
            /* Allows items to wrap to the next row */
        }

        .quiz-container>* {
            flex: 1 1 calc((100% - 60px) / 3);
        }

        .quiz-card {
            max-width: 300px;
        }

        @media (max-width: 500px) {
            .header-section {
                flex-direction: column;
                text-align: center;
            }

            .header-section h3 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .header-section img {
                margin-left: 0;
                width: 80px;
            }

            .content-list {
                flex-direction: column;
                padding: 20px;
            }

            .content {
                padding: 20px;
                width: 100%;
                margin-bottom: 10px;
                height: auto;
            }

            .quiz-container {
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .quiz-container>* {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }

            .quiz-card {
                max-width: 100%;
                padding: 10px;
            }

            .quiz-card h3{
                font-size: 15px;
            }

            .quiz-card p{
                font-size: 13px;
            }
        }
    </style>

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
            <div class="text">Recent Quizzes Taken</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div class="header-section">
            <h3>Recent Quizzes Taken</h3>
            <img src="images/performances2" alt="Recent Quizzes Taken">
        </div>

        <div class="content-section">
            <div class="content-list">
                <div class="quiz-container">
                    <?php if (!empty($completed_quizzes)): ?>
                        <?php foreach ($completed_quizzes as $quiz): ?>
                            <?php
                            $image_number = ($quiz['quiz_id'] % 6) + 2;
                            $quiz_image = "images/quiz" . $image_number . ".png";
                            ?>
                            <div class="quiz-card">
                                <img src="<?php echo htmlspecialchars($quiz_image); ?>" alt="Quiz Image">
                                <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                <p><strong>Instructor:</strong> <?php echo htmlspecialchars($quiz['instructor_name']); ?></p>
                                <p><small>Attempted on: <?php echo htmlspecialchars($quiz['attempt_date']); ?></small></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No quizzes completed yet.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        </div>


    </section>
</body>
<script src="Javascript.js"></script>

</html>