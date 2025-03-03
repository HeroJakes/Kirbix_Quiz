<?php

if (isset($_POST['quiz_id'])) {
    $quiz_id = $_POST['quiz_id'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'kirbix');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "DELETE FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $quiz_id);
    
    if ($stmt->execute()) {
        echo 'Quiz deleted successfully';
        
    } else {
        echo 'Error deleting quiz';
    }

    // Close the connection
    $stmt->close();
    $conn->close();
}
?>
