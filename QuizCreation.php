<?php
// Include session and connection setup
include('session.php');
include('conn.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session is set
if (!isset($_SESSION['mySession'])) {
    echo "Session not set. Redirecting...";
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
} else {
    $user_id = $_SESSION['mySession'];

    $query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($instructor_id);
    $stmt->fetch();
    $stmt->close();

    if (!$instructor_id) {
        echo "<script>alert('Instructor not found! Please ensure you are assigned as an instructor.');window.location.href='login.php';</script>";
        exit();
    }

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

    $profile_picture_number = ($user_id % 5) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    $stmt->close();
    $con->close();
}

// Quiz creation logic starts here
if (isset($_POST['createquizBTN'])) {
    $servername = "localhost";
    $dbname = "kirbix";
    $dbusername = "root";
    $dbpassword = "";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        handleError("Connection failed: " . $conn->connect_error);
    }

    // Sanitize inputs
    $quiz_title = $conn->real_escape_string($_POST['quiz_title'] ?? '');
    $quiz_description = $conn->real_escape_string($_POST['quiz_description'] ?? '');
    $quiz_level = $conn->real_escape_string($_POST['quiz_level'] ?? '');
    $quiz_difficulty = $conn->real_escape_string($_POST['quiz_difficulty'] ?? '');
    $quiz_time_limit = $conn->real_escape_string($_POST['quiz_time_limit'] ?? '');
    $quiz_type = $conn->real_escape_string($_POST['quizType'] ?? '');
    $date_due = $conn->real_escape_string($_POST['date_due'] ?? '');

    if (!$quiz_title || !$quiz_description || !$quiz_level || !$quiz_difficulty || !$quiz_time_limit || !$quiz_type || !$date_due) {
        handleError("Error: All fields are required.");
    }

    // Validate number of questions
    $questionCount = countQuestions($_POST);
    if ($questionCount < 5) {
        handleError("Error: A quiz must have at least 5 questions.");
    }

    // Validate due date
    $current_date = new DateTime();
    $due_date = new DateTime($date_due);
    $min_due_date = clone $current_date;
    $min_due_date->modify('+1 week');
    if ($due_date < $min_due_date) {
        handleError("Error: The due date must be at least 1 week from today.");
    }

    // Insert quiz details
    $query = "INSERT INTO quizzes (instructor_id, title, description, difficulty, time_limit, date_created, date_due, quiz_status, quiz_level, quiz_type) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $date_created = date("Y-m-d H:i:s");
    $quiz_status = 'Pending';

    $stmt->bind_param("isssisssss", $instructor_id, $quiz_title, $quiz_description, $quiz_difficulty, $quiz_time_limit, $date_created, $date_due, $quiz_status, $quiz_level, $quiz_type);
    if (!$stmt->execute()) {
        handleError("Error inserting quiz: " . $stmt->error);
    }

    $quiz_id = $stmt->insert_id;
    $stmt->close();

    // Insert questions and their answers
    foreach ($_POST as $key => $value) {
        if (preg_match('/quiz_question_(\d+)/', $key, $matches)) {
            $question_index = $matches[1];
            $question_text = $conn->real_escape_string($value);

            // Capture the answer explanation
            $answer_explanation = $conn->real_escape_string($_POST["answer_explanation_$question_index"] ?? '');

            // Insert question into the database
            $query = "INSERT INTO questions (quiz_id, content, answer_explanation) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $quiz_id, $question_text, $answer_explanation);

            if (!$stmt->execute()) {
                handleError("Error inserting question: " . $stmt->error);
            }

            $question_id = $stmt->insert_id;
            $stmt->close();

            if ($quiz_type === "MCQ") {
                for ($i = 1; $i <= 4; $i++) {
                    $answer_key = "question_answer_choice_{$question_index}_{$i}";
                    $is_correct_key = "correct_answer_{$question_index}";

                    if (isset($_POST[$answer_key])) {
                        $answer_text = $conn->real_escape_string($_POST[$answer_key]);
                        $is_correct = ($_POST[$is_correct_key] == "Choice_$i") ? 1 : 0;

                        $query = "INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);

                        if (!$stmt->execute()) {
                            handleError("Error inserting answer: " . $stmt->error);
                        }
                        $stmt->close();
                    }
                }
            } elseif ($quiz_type === "T/F") {

                $tf_answers = ['True', 'False'];

                foreach ($tf_answers as $answer) {
                    $is_correct = ($answer === $_POST["quiz_answer_$question_index"]) ? 1 : 0;
                    $answer_text = $conn->real_escape_string($answer);

                    $query = "INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);

                    if (!$stmt->execute()) {
                        handleError("Error inserting True/False answer: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }
        }
    }
    $conn->close();
}

function handleError($message)
{
    echo "<script>alert('$message');window.history.back();</script>";
    exit();
}

function countQuestions($postData)
{
    $count = 0;
    foreach ($postData as $key => $value) {
        if (preg_match('/quiz_question_(\d+)/', $key)) {
            $count++;
        }
    }
    return $count;
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
    <link rel="stylesheet" href="in_style.css">
    <style>
        .open-logo {
            margin-top: 0px;
            margin-left: 0px;
        }

        button{
            margin-bottom: 20px;
        }
    </style>

    <title>Quiz Creation Page</title>

</head>

<body>

    <body>
    <section id="sidebar" class="hide">
        <a href="#" class="brand">
            <img class="open-logo" src="in_img/K.png" style="width: 40px">
        </a>
        <ul class="side-menu top">
            <li>
                <a href="in_dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <!-- <li>
                <a href="#">
                    <i class='bx bxs-doughnut-chart' ></i>
                    <span class="text">Analytics</span>
                </a>
            </li> -->
            <li class="active">
                <a href="QuizCreation.php">
                    <i class='bx bxs-plus-circle'></i>
                    <span class="text">Quiz Creation</span>
                </a>
            </li>
            <li>
                <a href="QuizManager.php" require=".home">
                    <i class='bx bx-book-open icon'></i>
                    <span class="text">Quiz Management</span>
                </a>
            </li>
            <li>
                <a href="in_support.php">
                    <i class='bx bx-support'></i>
                    <span class="text">Support</span>
                </a>
            </li>
            <!-- <li>
                <a href="#">
                    <i class='bx bxs-group' ></i>
                    <span class="text">Team</span>
                </a>
            </li> 
            </ul>
            <ul class="side-menu">
                <li>
                <a href="#">
                <i class='bx bx-support'></i>
                    <span class="text">Settings</span>
                </a>
            </li>-->
            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bx-log-out icon'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->


        <!-- CONTENT -->
        <section id="content">
            <!-- NAVBAR -->
            <nav>
                <i class="bx bx-menu"></i>
                <a href="#" class="nav-link">Categories</a>
                <form action="#">
                    <div class="form-input">
                        <input type="search" placeholder="Search...">
                        <button type="submit" class="search.btn">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </form>
                <!-- <a href="#" class="notification">
                <i class="bx bxs-bell"></i>
                <span class="num">8</span>
            </a> -->
                <a href="in_profile.php" class="profile">
                    <img src="images/pfp1.png" alt="Icon">
                </a>
            </nav>
            <!-- NAVBAR -->

            <!-- MAIN -->
            <main>
                <div class="head-title">
                    <div class="left">
                        <h1>Quiz Creation</h1>
                        <ul class="breadcrumb">
                        <li>
                            <a href="#">Quiz Creation</a>
                        </li>
                        <li><i class="bx bx-chevron-right"></i></li>
                        <li>
                            <a class="active" href="in_dashboard.php">Home</a>
                        </li>
                    </ul>
                </div>
                <div class="right">
                </div>

                </div>
                <div class="welcome-section">
                    <form method="post" action="#">
                        Quiz Title:<br>
                        <input type="text" name="quiz_title" required><br>
                        Description:<br>
                        <textarea name="quiz_description" required></textarea><br>
                        Quiz Level:<br>
                        <input type="radio" name="quiz_level" value="form_4" required> Form 4
                        <input type="radio" name="quiz_level" value="form_5" required> Form 5<br>
                        <label for="date_due">Due Date:</label>
                        <input type="date" name="date_due" required><br>
                        <select name="quiz_difficulty" required>
                            <option value="" disabled selected>Difficulty</option>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                        <select name="quiz_time_limit" required>
                            <option value="" disabled selected>Time Limit</option>
                            <option value="30">30 Minutes</option>
                            <option value="40">40 Minutes</option>
                            <option value="50">50 Minutes</option>
                            <option value="60">60 Minutes</option>
                        </select>
                        <select id="quizType" name="quizType" required>
                            <option value="" disabled selected>Quiz Type</option>
                            <option value="MCQ">Multiple Choice Question (MCQ)</option>
                            <option value="T/F">True/False</option>
                        </select>

                        <div id="questionContainer">
                        <br><hr><br>
                            <!-- Question templates will be appended here -->
                        </div>

                        <div class="button-container">
                            <button type="submit" name="createquizBTN">Create Quiz</button>
                            <button type="button" id="addQuestion">Add More Question</button>
                        </div>
                    </form>
                </div>
        </section>
    </body>

    <script src="QuizCreation.js"></script>
    <script src="in_dashboard.js"></script>