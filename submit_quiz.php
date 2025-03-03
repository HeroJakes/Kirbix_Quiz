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

if (isset($_POST['quiz_id'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $submission_start_time = time(); 
    $score = 0; 

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') !== false) {
            $question_id = intval(str_replace('question_', '', $key)); 

            $answer_query = "SELECT is_correct FROM answers WHERE answer_id = ? AND question_id = ?";
            $stmt = $con->prepare($answer_query);
            $stmt->bind_param("ii", $value, $question_id);
            $stmt->execute();
            $stmt->bind_result($is_correct);
            $stmt->fetch();
            $stmt->close();

            if ($is_correct) {
                $score++;
            }

            $response_query = "INSERT INTO studentquizresponses (student_id, quiz_id, question_id, selected_answer) 
                               VALUES (?, ?, ?, ?)";
            $response_stmt = $con->prepare($response_query);
            $response_stmt->bind_param("iiis", $student_id, $quiz_id, $question_id, $value);
            $response_stmt->execute();
            $response_stmt->close();
        }
    }

    $time_taken = time() - $submission_start_time;

    $result_query = "INSERT INTO studentquizresults (student_id, quiz_id, score, attempt_date, time_taken) 
                     VALUES (?, ?, ?, NOW(), ?)";
    $result_stmt = $con->prepare($result_query);
    $result_stmt->bind_param("iiis", $student_id, $quiz_id, $score, $time_taken);
    $result_stmt->execute();
    $result_stmt->close();

    header("Location: quiz_result.php?quiz_id=$quiz_id&score=$score");
    exit();
} else {
    echo "Invalid submission.";
}

$con->close();
?>
