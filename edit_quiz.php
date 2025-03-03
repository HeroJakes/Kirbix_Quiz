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
$quiz_id = $_GET['quiz_id'] ?? null;

// Validate quiz_id
if (!$quiz_id || !is_numeric($quiz_id)) {
    header("Location: QuizManager.php");
    exit("Invalid quiz ID.");
}

// Fetch quiz details
$sql = "SELECT * FROM quizzes WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();

// Check if quiz exists
if (!$quiz) {
    header("Location: QuizManager.php");
    exit("Quiz not found.");
}

// Convert date_due to the format required by <input type="datetime-local">
$formatted_date_due = date('Y-m-d\TH:i', strtotime($quiz['date_due']));

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $difficulty = trim($_POST['difficulty']);
    $time_limit = (int) $_POST['time_limit'];
    $date_due = trim($_POST['date_due']);

    // Server-side validations
    if (empty($title) || empty($description) || empty($difficulty) || empty($time_limit) || empty($date_due)) {
        $error_message = "All fields must be filled.";
    } elseif ($time_limit < 30) {
        $error_message = "The time limit must be at least 30 minutes.";
    } elseif (strtotime($date_due) < strtotime($quiz['date_due'])) {
        $error_message = "The due date cannot be earlier than the current due date.";
    } else {
        // Update the quiz in the database
        $update_sql = "UPDATE quizzes SET title = ?, description = ?, difficulty = ?, time_limit = ?, date_due = ? WHERE quiz_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssi", $title, $description, $difficulty, $time_limit, $date_due, $quiz_id);

        if ($stmt->execute()) {
            // Redirect to QuizManager.php after successful update
            header("Location: QuizManager.php");
            exit();
        } else {
            $error_message = "Error updating quiz: " . $conn->error;
        }
    }
}

$conn->close();
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <link rel="stylesheet" href="css.css">

    <title>Quiz Edit Page</title>

</head>
<style>
    .edit-quiz-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        font-family: Arial, sans-serif;
    }

    .edit-quiz-container h1 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        color: #2c3e50;
        text-align: center;
        font-weight: bold;
    }

    .edit-quiz-container label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #34495e;
    }

    .edit-quiz-container input[type="text"],
    .edit-quiz-container input[type="number"],
    .edit-quiz-container input[type="datetime-local"],
    .edit-quiz-container textarea,
    .edit-quiz-container select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1rem;
        font-family: Arial, sans-serif;
        box-sizing: border-box;
    }

    .edit-quiz-container button[type="submit"] {
        background-color: #3498db;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        display: inline-block;
        margin-right: 10px;
    }

    .edit-quiz-container button[type="submit"]:hover {
        background-color: #2980b9;
    }

    .edit-quiz-container .btn_cancel {
        color: #3498db;
        text-decoration: none;
        font-size: 1rem;
        padding: 10px 20px;
        border: 1px solid #3498db;
        border-radius: 5px;
    }

    .edit-quiz-container .btn_cancel:hover {
        background-color: #ecf0f1;
        color: #2980b9;
    }

    @media (max-width: 768px) {
        .edit-quiz-container {
            max-width: 100%;
            margin: 20px;
        }
    }
</style>

<body>
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="images/K.png" alt="" class="close-logo">
                </span>
            </div>

            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">

                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Search...">
                </li>

                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="#">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class='bx bx-book-open icon'></i>
                            <span class="text nav-text">Quiz Management</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class='bx bx-bar-chart-alt-2 icon'></i>
                            <span class="text nav-text">Performance Insights</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class='bx bx-calendar icon'></i>
                            <span class="text nav-text">Schedule & Monitor</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class='bx bx-support icon'></i>
                            <span class="text nav-text">Support</span>
                        </a>
                    </li>


                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                    <a href="logout.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>

                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>

            </div>
        </div>

    </nav>
    <section class="home">
        <div class="head">
            <div class="text">Quiz Edit</div>
            <div class="icons">
                <i class='bx bx-bell'></i>
            </div>

        </div>
        </div>

        <body>
            <div class="edit-quiz-container">
                <h1>Edit Quiz</h1>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px;">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form id="editQuizForm" method="POST">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>"
                        required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5"
                        required><?php echo htmlspecialchars($quiz['description']); ?></textarea>

                    <label for="difficulty">Difficulty:</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="Easy" <?php if ($quiz['difficulty'] == 'Easy')
                            echo 'selected'; ?>>Easy</option>
                        <option value="Medium" <?php if ($quiz['difficulty'] == 'Medium')
                            echo 'selected'; ?>>Medium
                        </option>
                        <option value="Hard" <?php if ($quiz['difficulty'] == 'Hard')
                            echo 'selected'; ?>>Hard</option>
                    </select>

                    <label for="time_limit">Time Limit (mins):</label>
                    <input type="number" id="time_limit" name="time_limit"
                        value="<?php echo htmlspecialchars($quiz['time_limit']); ?>" required>

                    <label for="date_due">Date Due:</label>
                    <input type="datetime-local" id="date_due" name="date_due"
                        value="<?php echo $formatted_date_due; ?>" required>

                    <button type="submit">Save Changes</button>
                    <a href="QuizManager.php" class="btn_cancel">Cancel</a>
                </form>
            </div>

            <script src="QuizManager.js"></script>