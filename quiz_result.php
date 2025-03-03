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
}

$user_id = $_SESSION['mySession'];

$profile_picture_number = ($user_id % 4) + 1;
$profile_picture = "images/pfp" . $profile_picture_number . ".png";

if (isset($_GET['quiz_id'])) {
    $quiz_id = intval($_GET['quiz_id']);
    $student_id = getStudentID($user_id, $con);

    if (!$student_id) {
        echo "<script>alert('Student ID not found!');window.location.href='dashboard.php';</script>";
        exit();
    }

    $score = 0;
    $accuracy = 0;

    $total_questions = count(getQuizQuestions($quiz_id, $con));
    if ($total_questions > 0) {
        $mark_per_question = 100 / $total_questions;
    }

    $review_query = "
        SELECT 
            q.content AS question_content, 
            sqr.selected_answer AS selected_answer_id, 
            a.answer_text AS selected_answer_text, 
            a.is_correct AS selected_answer_correct, 
            ca.answer_text AS correct_answer_text, 
            q.answer_explanation AS feedback 
        FROM questions q
        LEFT JOIN studentquizresponses sqr 
            ON q.question_id = sqr.question_id AND sqr.student_id = ?
        LEFT JOIN answers a 
            ON sqr.selected_answer = a.answer_id
        LEFT JOIN answers ca 
            ON q.question_id = ca.question_id AND ca.is_correct = 1
        WHERE q.quiz_id = ?";

    $review_stmt = $con->prepare($review_query);
    $review_stmt->bind_param("ii", $student_id, $quiz_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result();
    $review_data = $review_result->fetch_all(MYSQLI_ASSOC);
    $review_stmt->close();

    foreach ($review_data as $review) {
        if ($review['selected_answer_correct'] == 1) {
            $score += $mark_per_question;
        }
    }

    if ($total_questions > 0) {
        $accuracy = round(($score / 100) * 100, 2);
    }

    $points = $score;

    $current_points_query = "SELECT points FROM students WHERE student_id = ?";
    $current_points_stmt = $con->prepare($current_points_query);
    $current_points_stmt->bind_param("i", $student_id);
    $current_points_stmt->execute();
    $current_points_result = $current_points_stmt->get_result();
    $current_points_data = $current_points_result->fetch_assoc();
    $current_points_stmt->close();

    $current_points = $current_points_data['points'] ?? 0;

    $new_points = $current_points + $score;

    $update_points_query = "UPDATE students SET points = ? WHERE student_id = ?";
    $update_points_stmt = $con->prepare($update_points_query);
    $update_points_stmt->bind_param("ii", $new_points, $student_id);
    $update_points_stmt->execute();
    $update_points_stmt->close();

    $update_score_query = "UPDATE studentquizresults SET score = ? WHERE quiz_id = ? AND student_id = ?";
    $update_score_stmt = $con->prepare($update_score_query);

    $update_score_stmt->bind_param("dii", $score, $quiz_id, $student_id);
    $update_score_stmt->execute();
    $update_score_stmt->close();


    $_SESSION['quiz_completed'] = true;

    $achievement_milestone = null;
    $achievement_image = null;
    $achievement_id = null;

    if ($new_points >= 1000) {
        $achievement_milestone = 1;
        $achievement_image = "images/achievement1.png";
        $check_achievement_query = "SELECT achievement_id FROM achievements WHERE student_id = ? AND achievement_id = 1";
        $check_achievement_stmt = $con->prepare($check_achievement_query);
        $check_achievement_stmt->bind_param("i", $student_id);
        $check_achievement_stmt->execute();
        $check_result = $check_achievement_stmt->get_result();
        if ($check_result->num_rows === 0) {
            $insert_achievement_query = "INSERT INTO achievements (student_id, title, description, points_required) VALUES (?, 'Achievement 1', 'Earned 1000 points', 1000)";
            $insert_achievement_stmt = $con->prepare($insert_achievement_query);
            $insert_achievement_stmt->bind_param("i", $student_id);
            $insert_achievement_stmt->execute();
            $insert_achievement_stmt->close();
        }
    } elseif ($new_points >= 2000) {
        $achievement_milestone = 2;
        $achievement_image = "images/achievement2.png";
        $check_achievement_query = "SELECT achievement_id FROM achievements WHERE student_id = ? AND achievement_id = 2";
        $check_achievement_stmt = $con->prepare($check_achievement_query);
        $check_achievement_stmt->bind_param("i", $student_id);
        $check_achievement_stmt->execute();
        $check_result = $check_achievement_stmt->get_result();
        if ($check_result->num_rows === 0) {
            $insert_achievement_query = "INSERT INTO achievements (student_id, title, description, points_required) VALUES (?, 'Achievement 2', 'Earned 2000 points', 2000)";
            $insert_achievement_stmt = $con->prepare($insert_achievement_query);
            $insert_achievement_stmt->bind_param("i", $student_id);
            $insert_achievement_stmt->execute();
            $insert_achievement_stmt->close();
        }
    } elseif ($new_points >= 3000) {
        $achievement_milestone = 3;
        $achievement_image = "images/achievement3.png";
        $check_achievement_query = "SELECT achievement_id FROM achievements WHERE student_id = ? AND achievement_id = 3";
        $check_achievement_stmt = $con->prepare($check_achievement_query);
        $check_achievement_stmt->bind_param("i", $student_id);
        $check_achievement_stmt->execute();
        $check_result = $check_achievement_stmt->get_result();
        if ($check_result->num_rows === 0) {
            $insert_achievement_query = "INSERT INTO achievements (student_id, title, description, points_required) VALUES (?, 'Achievement 3', 'Earned 3000 points', 3000)";
            $insert_achievement_stmt = $con->prepare($insert_achievement_query);
            $insert_achievement_stmt->bind_param("i", $student_id);
            $insert_achievement_stmt->execute();
            $insert_achievement_stmt->close();
        }
    }

    echo "<script>
    var achievementMilestone = " . json_encode($achievement_milestone) . ";
    var achievementImage = " . json_encode($achievement_image) . ";
</script>";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <link rel="stylesheet" href="css.css">
    <style>
        .summary-container {
            background-color: #8587ff;
            padding: 20px;
            border-radius: 20px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-header {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 30px;
            color: white;
        }

        .content {
            background-color: white;
            border-radius: 100px 100px 20px 20px;
            padding: 80px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stats div {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            margin: 0 10px;
            text-align: center;
        }

        .review-section {
            margin-top: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
        }

        .question-review {
            margin-bottom: 20px;
        }

        .correct {
            background-color: green;
            color: white;
            border-radius: 40px;
            font-weight: bold;
            padding: 5px 20px;
        }

        .incorrect {
            background-color: red;
            color: white;
            border-radius: 40px;
            font-weight: bold;
            padding: 5px 20px;
        }

        .sumimg {
            width: 70%;
        }

        .quiz-start button,
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 20px;
            background-color: #1e90ff;
            color: white;
            cursor: pointer;
            font-weight: 400;
            font-size: 13px;
            margin-top: 15px;
        }

        .stats h2 {
            color: #3639e2;
        }

        h4 {
            margin-top: 15px;
        }

        h3 {
            color: #395aff;
        }

        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .popup-content img {
            max-width: 100%;
            height: auto;
            margin: 20px 0;
        }

        .popup-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #1e90ff;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        #achievement-image {
            width: 30%;
        }

        .question {
            background-color: #fff;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }


        @media (max-width: 768px) {

            .summary-container {
                padding: 10px;

            }

            .stats {
                flex-direction: column;
                align-items: center;
            }

            .stats div {
                margin-bottom: 15px;
                margin: 0;
                width: 50%;
            }

            .review-section {
                padding: 10px;
            }

            .content {
                padding: 10px;
            }

            h2 {
                font-size: 18px;
            }

            p {
                font-size: 12px;
            }

            .correct {
                font-size: 12px;
                padding: 0px 10px;
            }

            .incorrect {
                font-size: 12px;
                padding: 0px 10px;
            }

        }
    </style>
</head>

<body>
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <a href="Dashboard.php">
                        <img src="images/K.png" alt="" class="close-logo">
                    </a>
                </span>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Search...">
                </li>
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
            <div class="text">Dashboard</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div id="achievement-popup" class="popup-container" style="display: none;">
            <div class="popup-content">
                <h2>Congratulations!</h2>
                <p>You’ve reached a new milestone!</p>
                <img id="achievement-image" src="" alt="Achievement">
                <br>
                <button onclick="closePopup()">Close</button>
            </div>
        </div>
        <script>
            window.onload = function() {
                var achievementMilestone = <?php echo json_encode($achievement_milestone); ?>;
                var achievementImage = <?php echo json_encode($achievement_image); ?>;

                if (achievementMilestone && achievementImage) {
                    document.getElementById('achievement-image').src = achievementImage;
                    document.getElementById('achievement-popup').style.display = 'flex';
                }
            };

            function closePopup() {
                document.getElementById('achievement-popup').style.display = 'none';
            }
        </script>


        <div class="summary-container">
            <div class="summary-header">
                <h1>Quiz Summary</h1>
                <p>Review your performance below.</p>
                <img src="images/sum2.png" class="sumimg">
            </div>
            <div class="content">
                <div class="stats">
                    <div>
                        <h2>Points</h2>
                        <p>+<?php echo $points; ?> points</p>
                    </div>
                    <div>
                        <h2>Score</h2>
                        <p><?php echo round($score, 2) . " / 100"; ?></p>
                    </div>
                    <div>
                        <h2>Accuracy</h2>
                        <p><?php echo $accuracy; ?>%</p>
                    </div>
                </div>

                <div class="review-section">
                    <h2>Review Questions</h2><br>
                    <hr><br>

                    <?php foreach ($review_data as $review) : ?>
                        <div class="question-review">
                            <div class="question">
                                <p><?php
                                    $question_number = 1;

                                    $processed_content = str_replace(["\\n", "\\r"], ["\n", "\r"], $review['question_content']);
                                    echo "<h3>Question $question_number: </h3><h4>" . nl2br($processed_content) . "</h4>";
                                    ?></p><br>
                                <hr>


                                <h4>Your Answer:</h4>
                                <p>
                                    <?php
                                    echo htmlspecialchars($review['selected_answer_text'] ?? 'No answer selected');
                                    if ($review['selected_answer_correct'] == 1) {
                                        echo " <span class='correct'>Correct</span>";
                                    } else {
                                        echo " <span class='incorrect'>Incorrect</span>";
                                    }
                                    ?>
                                </p>

                                <h4>Correct Answer:</h4>
                                <p><?php
                                    $correct_answer_text = htmlspecialchars($review['correct_answer_text'] ?? 'No correct answer specified');
                                    $processed_correct_answer = str_replace(["\\n", "\\r"], ["\n", "\r"], $correct_answer_text);
                                    echo nl2br($processed_correct_answer);
                                    ?></p>

                                <h4>Feedback:</h4>
                                <p><?php
                                    $feedback_text = htmlspecialchars($review['feedback'] ?? 'No feedback provided');
                                    $processed_feedback = str_replace(["\\n", "\\r"], ["\n", "\r"], $feedback_text);
                                    echo nl2br($processed_feedback);

                                    $question_number++;
                                    ?></p>
                            </div>

                        <?php endforeach; ?>
                        <a href="Dashboard.php"><button type="submit">Done</button></a>
                        </div>
                </div>
            </div>
    </section>
</body>
<script src="Javascript.js"></script>

</html>