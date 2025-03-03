<?php
include('session.php');
include('conn.php');
include('functions.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['mySession'])) {
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['mySession'];
$student_id = getStudentID($user_id, $con);

if (isset($_GET['quiz_id'])) {

    $quiz_id = intval($_GET['quiz_id']);
    $quiz_details = getQuizDetails($quiz_id, $con);

    $time_limit = $quiz_details['time_limit'] * 60 ?: 600;
    $questions = getQuizQuestions($quiz_id, $con);

    if (empty($questions)) {
        echo "<script>alert('No questions available for this quiz.');window.location.href='Dashboard.php';</script>";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $score = 0;

        foreach ($questions as $question) {
            $question_id = $question['question_id'];
            $selected_answer = $_POST["question_$question_id"] ?? null;
            $correct_answer = getCorrectAnswer($question_id, $con);

            if ($selected_answer == $correct_answer) {
                $score++;
            }

            saveStudentAnswer($student_id, $quiz_id, $question_id, $selected_answer, $con);
        }

        saveQuizResult($student_id, $quiz_id, $score, $con);
        header("Location: quiz_result.php?quiz_id=$quiz_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attempt Quiz</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-image: url(images/background.png);
        }

        input[type=radio] {
            margin: 15px;
        }

        h2 {
            color: #395aff;
        }

        .quiz-start {
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 50px;
            padding: 50px;
        }

        .quiz-sec {
            background-color: rgba(255, 255, 255, 0.6);
            margin: 0px;
            border-radius: 50px;
            padding: 50px;
        }

        .container {
            width: 50%;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 50px;
            border-radius: 50px;
        }

        .quiz-start img {
            width: 100%;
            border-radius: 20px;
            margin-bottom: 15px;
        }

        .quiz-start h3 {
            font-size: 15px;
            margin-top: 20px;
        }

        .quiz-start p {
            font-size: 13px;
            margin-top: 5px;
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .left-content {
            flex: 1;
        }

        #timer .timer {
            font-size: 2rem;
            font-weight: bold;
            padding: 8px 20px 10px 20px;
            background-color: #395aff;
            color: white;
            border-radius: 50%;
            text-align: center;
            margin-left: 20px;
        }

        .minutes,
        .seconds {
            display: inline-block;
            margin: 0 5px;
        }

        .minutes {
            font-size: 1.5rem;
        }

        .seconds {
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .quiz-start {
                padding: 10px;
            }

            .container {
                width: 100%;
                background-color: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 30px;
                border-radius: 50px;
            }

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

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            #timer .timer {
                margin: 10%;
                padding: 8px 15px 10px 15px;
                text-align: center;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <section class="home">
        <div class="quiz-start">
            <div class="container">
                <div class="header">
                    <div class="left-content">
                        <h1><?php echo htmlspecialchars($quiz_details['title']); ?></h1>
                        <p><?php echo htmlspecialchars($quiz_details['description']); ?></p>
                    </div>
                    <div class="timer" id="timer"></div>
                </div>
                <hr>
                <form id="quiz-form" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <?php
                    $question_number = 1;
                    foreach ($questions as $question) {
                        $question_id = $question['question_id'];
                        $question_content = str_replace(["\\n", "\\r"], ["\n", "\r"], $question['content']);
                        echo "<div class='question'>";

                        echo "<br><h2>Question $question_number: </h2><h3>" . nl2br(htmlspecialchars($question_content)) . "</h3>";

                        $stmt = $con->prepare("SELECT answer_id, answer_text FROM answers WHERE question_id = ?");
                        $stmt->bind_param("i", $question_id);
                        $stmt->execute();
                        $answers_result = $stmt->get_result();

                        echo "<div class='answer-options'>";
                        while ($answer = $answers_result->fetch_assoc()) {
                            echo "<input type='radio' name='question_$question_id' value='{$answer['answer_id']}' required> {$answer['answer_text']}<br>";
                        }
                        $stmt->close();
                        echo "</div></div><br><br><hr>";

                        $question_number++;
                    }
                    ?>
                    <button type="submit">Submit Quiz</button>
                </form>
            </div>
        </div>
    </section>
</body>
<script>
    let timeLimit = localStorage.getItem('remainingTime') ? parseInt(localStorage.getItem('remainingTime')) : <?php echo $time_limit; ?>;

    function startCountdown() {
        const timerElement = document.getElementById('timer');
        const interval = setInterval(() => {
            const minutes = Math.floor(timeLimit / 60);
            const seconds = timeLimit % 60;
            timerElement.innerHTML = `<div class="timer">
            <span class="minutes">${minutes}</span>:<span class="seconds">${seconds < 10 ? '0' : ''}${seconds}</span>
        </div>`;
            timeLimit--;
            localStorage.setItem('remainingTime', timeLimit);

            if (timeLimit < 0) {
                clearInterval(interval);
                localStorage.removeItem('remainingTime');
                alert("Time is up! Submitting your quiz.");
                document.getElementById('quiz-form').submit();
            }
        }, 1000);
    }

    document.getElementById('quiz-form').addEventListener('submit', function() {
        window.onbeforeunload = null;
        localStorage.removeItem('remainingTime');
    });

    startCountdown();
</script>

</html>