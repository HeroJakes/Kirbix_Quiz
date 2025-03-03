<?php
// Database connection
$servername = "localhost";
$dbname = "kirbix";
$dbusername = "root";
$dbpassword = "";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get quiz_id from the URL
$quiz_id = $_GET['quiz_id'];

// Delete quiz
$sql = "DELETE FROM quizzes WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);

if ($stmt->execute()) {
    echo "Quiz deleted successfully!";
    header("Location: QuizManager.php"); 
    exit();
} else {
    echo "Error deleting quiz: " . $conn->error;
}

$conn->close();
?>
