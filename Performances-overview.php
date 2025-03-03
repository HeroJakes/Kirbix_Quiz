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

    // Fetch quiz IDs and scores for the student
    $quiz_id_query = "SELECT quiz_id, score FROM studentquizresults WHERE student_id = ?";
    $quiz_id_stmt = $con->prepare($quiz_id_query);
    $quiz_id_stmt->bind_param("i", $student_id);
    $quiz_id_stmt->execute();
    $quiz_id_result = $quiz_id_stmt->get_result();
    $quiz_attempt_data = [];
    while ($row = $quiz_id_result->fetch_assoc()) {
        $quiz_attempt_data[$row['quiz_id']] = $row['score']; // Store score
    }
    $quiz_id_stmt->close();

    // Get the number of quizzes taken by the student
    $number_of_quizzes_taken = count($quiz_attempt_data);

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
            $row['score'] = $quiz_attempt_data[$row['quiz_id']] ?? 'N/A'; // Fetch score
            $completed_quizzes[] = $row;
        }
    }

    // Fetch student level and points from 'students' table
    $query = "SELECT level, points FROM students WHERE user_id = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die('Query prepare failed: ' . $con->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($level, $points);
    $stmt->fetch();
    $stmt->close(); // Close statement after use
}

$con->close();
?>

<?php
// Prepare data for the top 5 recent quizzes
$top_recent_quizzes = array_slice($completed_quizzes, 0, 5); // Top 5 recent quizzes
$quiz_titles = [];
$quiz_scores = [];

foreach ($top_recent_quizzes as $quiz) {
    $quiz_titles[] = $quiz['title'];
    $quiz_scores[] = $quiz['score'];
}
?>


<?php
// Calculate average score
$total_score = 0;
$valid_quiz_count = 0;

foreach ($quiz_attempt_data as $quiz_id => $score) {
    if (is_numeric($score)) {
        $total_score += $score;
        $valid_quiz_count++;
    }
}

$average_score = $valid_quiz_count > 0 ? $total_score / $valid_quiz_count : 0;
?>



<script>
    // Pass PHP data to JavaScript
    const quizData = {
        titles: <?php echo json_encode($quiz_titles); ?>,
        scores: <?php echo json_encode($quiz_scores); ?>
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <link rel="stylesheet" href="css.css">

    <title>Overview Of Activity</title>

    <style>
        .main {
            position: absolute;
            top: 0;
            top: 0;
            left: 250px;
            height: 100vh;
            width: calc(100% - 250px);
            background-color: var(--body-color);
            transition: var(--tran-05);
        }

        .title-section {
            background-image: linear-gradient(to bottom right, #a5d3ff, #ffffff);
            padding: 10px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: left;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .title-text {
            text-align: left;
            align-items: left;
        }

        .title-section h2 {
            font-size: 30px;
            margin-bottom: 0px;
            color: darkblue;
            align-items: center;
            display: flex;
        }

        .content-section {
            background-color: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: left;
        }

        .content-list {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .content {
            background-color: white;
            padding: 20px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1;
            /* Allow equal distribution of width */
            min-width: 300px;
            /* Minimum width for responsiveness */
            max-width: 300px;
            /* Fix width for consistency */
            display: flex;
            flex-direction: column;
            /* Ensure inner content stacks vertically */
            justify-content: center;
            /* Center content vertically */
            align-items: center;
            /* Center content horizontally */
            height: 300px;
            /* Fixed height for all boxes */
            background-color: #d5eeff;
        }

        .content img {
            width: 140px;
            height: 140px;
        }

        .content-section button:hover {
            background-color: #022ff5;
        }

        .content-icon img {
            width: 180px;
            height: 150px;
            margin-left: 100px;
        }

        .content button {
            height: 58px;
        }

        body.dark .content-section {
            background-color: var(--sidebar-color);
        }

        body.dark .content {
            background-color: #352f44;
        }

        .content h2 {
            font-size: 60px;
            color: #2772db;
        }

        .content h3 {
            font-size: small;
        }

        @media (max-width: 500px) {


            .title-section {
                padding: 15px;
            }

            .title-section h2 {
                font-size: 18px;
                margin-left: 0px;
                margin-bottom: 0px;
                color: darkblue;
                text-align: center;
            }

            .content-icon img {
                width: 70px;
                height: 60px;
                border-radius: 8px;
                margin-left: 0;
                margin-bottom: 10px;
            }

            .content img {
                width: 70px;
            }

            .content-section h3 {
                font-size: 14px;
                margin-top: 10px;
            }

            .content {
                background-color: white;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                margin-bottom: 15px;
                justify-self: center;
                align-self: center;
                text-align: center;
                width: 180px;
                height: 180px;
                font-size: 14px;
                min-width: 200px;
            }


            .content-section button {
                font-size: 12px;
                margin-top: 10px;
                width: 120px;
                border-radius: 25px;
                padding: 10px;
            }


            .content-list {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            /* Main Section */
            .main {
                position: relative;
                left: 0;
                height: auto;
                width: 100%;
                padding-left: 15px;
            }

            .title-text {
                text-align: center;
                margin-bottom: 15px;
            }

            .bar-chart{
                max-width: 300px;
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
            <div class="text">Overview Of Activity</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div class="title-section">
            <div class="title-text">
                <h2>Overview Of Activity</h2>
            </div>
            <div class="content-icon">
                <img src="images/performances3" alt="Overview Of Activity">
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('quizPerformanceChart').getContext('2d');

                // Create bar chart for top 5 recent quizzes
                const quizPerformanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: quizData.titles, // X-axis labels (top 5 quiz titles)
                        datasets: [{
                            label: 'Quiz Scores',
                            data: quizData.scores, // Y-axis values (scores)
                            backgroundColor: 'rgba(54, 162, 235, 0.5)', // Bar color
                            borderColor: 'rgba(54, 162, 235, 1)', // Bar border color
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Top 5 Recent Quiz Analytics'
                            }
                        }
                    }
                });
            });
        </script>



        <div class="content-section">
            <div class="content-list">
                <div class="content">
                    <div class="bar-chart">
                        <canvas id="quizPerformanceChart" width="280" height="250"></canvas>
                    </div>
                </div>
                <div class="content">
                    <h4>Average Score</h4>
                    <br>
                    <h2><?php echo round($average_score, 2); ?>%</h2>
                </div>
                <div class="content">
                    <h4>Total Quizzes Completed</h4>
                    <br>
                    <h2><?php echo $number_of_quizzes_taken; ?> </h2>
                </div>
            </div>
        </div>

    </section>
</body>
<script src="Javascript.js"></script>

</html>