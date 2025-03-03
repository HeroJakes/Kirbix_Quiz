<?php
function getStudentID($user_id, $con) {
    $stmt = $con->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['student_id'] ?? null;
}

function getQuizDetails($quiz_id, $con) {
    $stmt = $con->prepare("SELECT title, description, time_limit FROM quizzes WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function getQuizQuestions($quiz_id, $con) {
    $stmt = $con->prepare("SELECT * FROM questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $result;
}

function getCorrectAnswer($question_id, $con) {
    $stmt = $con->prepare("SELECT answer_text FROM answers WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['question_answer_selection'] ?? null;
}

function saveStudentAnswer($student_id, $quiz_id, $question_id, $selected_answer, $con) {
    $stmt = $con->prepare("INSERT INTO studentquizresponses (student_id, quiz_id, question_id, selected_answer) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $student_id, $quiz_id, $question_id, $selected_answer);
    $stmt->execute();
    $stmt->close();
}

function saveQuizResult($student_id, $quiz_id, $score, $con) {
    $time_taken = time(); 
    $stmt = $con->prepare("INSERT INTO studentquizresults (student_id, quiz_id, score, attempt_date, time_taken) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iiis", $student_id, $quiz_id, $score, $time_taken);
    $stmt->execute();
    $stmt->close();
}
?>
