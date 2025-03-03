<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $instructor_id = $_POST['instructor_id'];
    $result_id = $_POST['result_id'];
    $response_id = $_POST['response_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $difficulty = $_POST['difficulty'];
    $time_limit = $_POST['time_limit'];
    $date_created = $_POST['date_created'];
    $date_due = $_POST['date_due'];
    $quiz_status = $_POST['quiz_status'];

    // Database connection (adjust with your actual credentials)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "kirbix";

    $con = new mysqli($servername, $username, $password, $dbname);

    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Prepare and bind
    $stmt = $con->prepare("INSERT INTO quizzes (instructor_id, result_id, response_id, title, description, difficulty, time_limit, date_created, date_due, quiz_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssisss", $instructor_id, $result_id, $response_id, $title, $description, $difficulty, $time_limit, $date_created, $date_due, $quiz_status);

    // Execute the query
    if ($stmt->execute()) {
        echo "New quiz submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Submission Form</title>
</head>
<body>

<h2>Quiz Submission Form</h2>

<form action="submit_quiz.php" method="POST">
    <label for="instructor_id">Instructor ID:</label>
    <input type="number" id="instructor_id" name="instructor_id" required><br><br>

    <label for="result_id">Result ID:</label>
    <input type="number" id="result_id" name="result_id" required><br><br>

    <label for="response_id">Response ID:</label>
    <input type="number" id="response_id" name="response_id" required><br><br>

    <label for="title">Title:</label>
    <input type="text" id="title" name="title" maxlength="100"><br><br>

    <label for="description">Description:</label><br>
    <textarea id="description" name="description" rows="4" cols="50"></textarea><br><br>

    <label for="difficulty">Difficulty:</label>
    <select id="difficulty" name="difficulty">
        <option value="Easy">Easy</option>
        <option value="Medium">Medium</option>
        <option value="Hard">Hard</option>
        <option value="">Select Difficulty</option>
    </select><br><br>

    <label for="time_limit">Time Limit (minutes):</label>
    <input type="number" id="time_limit" name="time_limit"><br><br>

    <label for="date_created">Date Created:</label>
    <input type="datetime-local" id="date_created" name="date_created"><br><br>

    <label for="date_due">Date Due:</label>
    <input type="datetime-local" id="date_due" name="date_due"><br><br>

    <label for="quiz_status">Quiz Status:</label>
    <select id="quiz_status" name="quiz_status">
        <option value="Approved">Approved</option>
        <option value="Pending">Pending</option>
        <option value="Rejected">Rejected</option>
        <option value="">Select Status</option>
    </select><br><br>

    <input type="submit" value="Submit">
</form>

</body>
</html>
