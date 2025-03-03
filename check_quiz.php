<?php
// Include the database connection
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the quiz code from POST data
    $quizCode = $_POST['quiz_code'];

    // Check if quizCode is not empty and is a valid number
    if (!empty($quizCode) && is_numeric($quizCode)) {
        // Query to check if the quiz ID exists
        $query = "SELECT * FROM quizzes WHERE quiz_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $quizCode);
        $stmt->execute();
        $result = $stmt->get_result();

        // If a matching quiz ID is found
        if ($result->num_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            // If no matching quiz ID is found
            echo json_encode(['success' => false]);
        }

        $stmt->close();
    } else {
        // Invalid input
        echo json_encode(['success' => false]);
    }
} else {
    // Invalid request method
    http_response_code(405); // Method Not Allowed
}
?>
