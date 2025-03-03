<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kirbix";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id is provided in the URL
if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    // Query to delete the user from the database
    $sql = "DELETE FROM users WHERE user_id = ?";

    // Prepare and bind the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);

        // Execute the query
        if ($stmt->execute()) {
            // Redirect to user management page after successful deletion
            header("Location: user.php?success=1");
        } else {
            echo "Error deleting user: " . $conn->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>