<?php
include('session.php');
include('conn.php');
include('functions.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['mySession'])) {
    echo "Session not set. Redirecting...";
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
} else {
    $user_id = $_SESSION['mySession'];
    $student_id = getStudentID($user_id, $con);

    $servername = "localhost";
    $dbname = "kirbix";
    $dbusername = "root";
    $dbpassword = "";

    $con = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";


    $quiz_id = intval($_GET['quiz_id']);

    $query_check_attempt = "SELECT result_id FROM studentquizresults WHERE student_id = ? AND quiz_id = ?";
    $stmt_check = $con->prepare($query_check_attempt);
    $stmt_check->bind_param("ii", $student_id, $quiz_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('You have already attempted this quiz. Redirecting to your results.'); window.location.href = 'quiz_result.php?quiz_id={$quiz_id}';</script>";
        $stmt_check->close();
        $con->close();
        exit();
    }

    $query = "SELECT title, description, difficulty, time_limit, date_due, quiz_level FROM quizzes WHERE quiz_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->bind_result($title, $description, $difficulty, $time_limit, $date_due, $quiz_level);
    $stmt->fetch();
    $stmt->close();
    
    $image_number = ($quiz_id % 6) + 2;
    $quiz_image = "images/quiz" . $image_number . ".png";

    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Details</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <link rel="stylesheet" href="css.css">
    <style>
        .quiz-start h2 {
                font-size: 15px;
            }

            .quiz-start p {
                font-size: 13px;
                margin-top: 15px;
            }

            .quiz-start button {
                margin-top: 80px;
            }
            
        @media (max-width: 768px) {
            .quiz-start{
                padding: 0px;
            }

            .container {
                width: 100%;
                background-color: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 30px;
                border-radius: 50px;
            }

            
        }
    </style>
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
            <div class="text">Quiz</div>
            <div class="icons">
                <i class='bx bx-bell'></i>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
            </div>
        </div>

        <div class="quiz-start">
            <div class="container">
                <div class="quiz-start-cfm">
                    <div class="wrap">
                        <img src="<?php echo $quiz_image; ?>" alt="Quiz Image">
                        <h1><?php echo htmlspecialchars($title); ?></h1>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($description); ?></p>
                        <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($difficulty); ?></p>
                        <p><strong>Time Limit:</strong> <?php echo htmlspecialchars($time_limit); ?> minutes</p>
                        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($date_due); ?></p>
                        <p><strong>Level:</strong> <?php echo htmlspecialchars($quiz_level); ?></p>
                        <button onclick="window.location.href='start_quiz.php?quiz_id=<?php echo $quiz_id; ?>'">Start Quiz</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
<script src="Javascript.js"></script>
</html>