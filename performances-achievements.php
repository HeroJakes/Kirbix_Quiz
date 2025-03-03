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
        $quiz_detail_stmt->close();
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

    if ($points > 4000) {
        $new_level = 4;
    } elseif ($points > 3000) {
        $new_level = 3;
    } elseif ($points >= 2000) {
        $new_level = 2;
    } else {
        $new_level = 1;
    }

    $update_level_query = "UPDATE students SET level = ? WHERE user_id = ?";
    $update_level_stmt = $con->prepare($update_level_query);
    $update_level_stmt->bind_param("ii", $new_level, $user_id);

    // Execute and check if the update was successful
    if ($update_level_stmt->execute()) {
        echo "Level updated successfully!";
    } else {
        echo "Error updating level: " . $update_level_stmt->error;
    }

    $update_level_stmt->close();
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

    <title>Achievements</title>

    <style>
        .home {
            align-content: left;
        }

        .header-section {
            border-radius: 20px;
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
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
            height: auto;
            align-items: flex-start;
        }

        .content {
            background-color: #d5eeff;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 0px;
            text-align: center;
            flex: 1 1 calc(20% - 15px);
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

        .content {
            background-color: #fff;
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex-wrap: warp;
            width: 300px;
            height: 200px;
        }

        .content img {
            margin-top: 15px;
            width: 100px;
            height: 100px;
        }

        .content h5 {
            font-size: 20px;
            font-weight: bold;
            color: darkblue;
        }

        .content h3 {
            margin-top: 20px;
            font-size: 50px;
            font-weight: normal;
        }

        .img-icon img {
            width: 230px;
            height: 200px;
        }

        .score {
            justify-content: right;
        }

        .score img {
            width: 80px;
            height: 80px;
        }

        .details {
            display: flex;
        }

        .detail {
            margin-right: 10px;
        }

        @media (max-width: 500px) {

            /* Header Section */
            .header-section {
                flex-direction: column;
                /* Stack items vertically */
                text-align: center;
                /* Center text */
            }

            .header-section h3 {
                font-size: 24px;
                /* Adjust font size for mobile */
                margin-bottom: 15px;
            }

            .header-section img {
                margin-left: 0;
                width: 120px;
            }

            .content-list {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }

            .content {
                width: 100%;
                padding: 15px;
                margin-bottom: 10px;
                height: auto;
            }

            .content img {
                width: 80px;
                height: 80px;
                margin-top: 10px;
            }

            .content h5 {
                font-size: 18px;
            }

            .content h3 {
                font-size: 30px;
                margin-top: 15px;
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

            /* Img Icon */
            .img-icon img {
                width: 180px;
                height: 150px;
                margin-left: 25px;
            }

            /* Score */
            .score {
                justify-content: center;
            }

            .score img {
                width: 60px;
                height: 60px;
            }


            .details {
                flex-direction: column;
                align-items: center;
                flex-direction: row;
            }

            .detail {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .quiz-card h3 {
                font-size: 15px;
            }

            .quiz-card p {
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
            <div class="text">Achievements</div>
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
            <h3>Achievements</h3>
        </div>
        <div class="content-list">
            <div class="img-icon">
                <img src="images/performances5.png">
            </div>
            <div class="content">
                <h5>User level</h5>
                <h3>
                    <?php if ($points > 2000) {
                        echo '<p> 2 </p>>';
                    } elseif ($points > 3000) {
                        echo '<p>3 </p>';
                    } elseif ($points > 4000) {
                        echo '<p>4 </p>';
                    } else {
                        echo '<p>1</p>';
                    } ?>
                </h3>
            </div>
            <div class="content">
                <h5>Earned Badges</h5>
                <?php
                if ($points < 1000) {
                    echo '<img src="images/achievement1.png" alt="Achievement 1">';
                } elseif ($points <= 2000) {
                    echo '<img src="images/achievement2.png" alt="Achievement 2">';
                } else {
                    echo '<img src="images/achievement3.png" alt="Achievement 3">';
                }
                ?>
            </div>
            <div class="content">
                <h5>Points</h5>
                <h3><?php echo $points ?></h3>
            </div>
        </div>

        <div class="content-section">
            <div class="content-list">
                <div class="quiz-container">
                    <?php if (!empty($completed_quizzes)): ?>
                        <?php foreach ($completed_quizzes as $quiz): ?>
                            <?php
                            $image_number = ($quiz['quiz_id'] % 6) + 2;
                            $quiz_image = "images/quiz" . $image_number . ".png";
                            $score = $quiz_attempt_data[$quiz['quiz_id']] ?? 0; // Default to 0 if score is unavailable

                            // Determine achievement image based on score
                            if ($score > 80) {
                                $achievement_image = "images/score3.png";
                                $achievement_alt = "Achievement 1";
                            } elseif ($score >= 50) {
                                $achievement_image = "images/score2.png";
                                $achievement_alt = "Achievement 2";
                            } else {
                                $achievement_image = "images/score1.png";
                                $achievement_alt = "Achievement 3";
                            }
                            ?>
                            <div class="quiz-card">
                                <img src="<?php echo htmlspecialchars($quiz_image); ?>" alt="Quiz Image">
                                <div class="details">
                                    <div class="detail">
                                        <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                        <p>Instructor: <?php echo htmlspecialchars($quiz['instructor_name']); ?></p>
                                        <p><strong>Score: <?php echo htmlspecialchars($score); ?></strong></p>
                                    </div>
                                    <div class="score">
                                        <img src="<?php echo htmlspecialchars($achievement_image); ?>" alt="<?php echo htmlspecialchars($achievement_alt); ?>">
                                    </div>
                                </div>
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