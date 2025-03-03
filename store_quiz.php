<?php
session_start(); 

if (!isset($_SESSION['mySession'])) {
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
}

if (isset($_GET['quiz_id'])) {
    $quiz_id = intval($_GET['quiz_id']); // Sanitize input
    if ($quiz_id > 0) {
        $_SESSION['quiz_id'] = $quiz_id; // Store quiz_id in the session
        header("Location: quiz_details.php"); // Redirect to the quiz details page
        echo "<script>alert('Quiz ID: $quiz_id');window.location.href='Dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Invalid Quiz ID: $quiz_id');window.location.href='Dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Quiz ID not provided!');window.location.href='Dashboard.php';</script>";
}
?>
