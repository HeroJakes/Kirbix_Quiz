<?php
// Include session and database connection
include('session.php');  // Include the session for logged-in user
include('conn.php');     // Include your database connection file

// Database connection parameters
$servername = "localhost";
$dbname = "kirbix";
$dbusername = "root";
$dbpassword = "";

// Create database connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the total number of quizzes created
$sql_total_quizzes = "SELECT COUNT(quiz_id) AS total_quizzes FROM quizzes";
$result_total_quizzes = $conn->query($sql_total_quizzes);
$total_quizzes = 0;
if ($result_total_quizzes->num_rows > 0) {
    $row = $result_total_quizzes->fetch_assoc();
    $total_quizzes = $row['total_quizzes'];
}

// Query to get the number of active quizzes (quizzes whose due date has not passed)
$current_date = date('Y-m-d H:i:s'); // Get the current date and time
$sql_active_quizzes = "SELECT COUNT(quiz_id) AS active_quizzes FROM quizzes WHERE date_due > '$current_date'";
$result_active_quizzes = $conn->query($sql_active_quizzes);
$active_quizzes = 0;
if ($result_active_quizzes->num_rows > 0) {
    $row = $result_active_quizzes->fetch_assoc();
    $active_quizzes = $row['active_quizzes'];
}

// Get the date 3 days from now
$due_in_3_days = date('Y-m-d H:i:s', strtotime('+3 days'));

// Query to find quizzes whose due_date is within the next 3 days
$sql_due_soon_quizzes = "
    SELECT COUNT(quiz_id) AS due_soon_quizzes
    FROM quizzes
    WHERE date_due BETWEEN '$current_date' AND '$due_in_3_days'
";
$result_due_soon_quizzes = $conn->query($sql_due_soon_quizzes);
$due_soon_quizzes = 0;
if ($result_due_soon_quizzes->num_rows > 0) {
    $row = $result_due_soon_quizzes->fetch_assoc();
    $due_soon_quizzes = $row['due_soon_quizzes'];
}

// Query to get quizzes for the dropdown
$sql_quizzes = "SELECT quiz_id, title FROM quizzes";
$result_quizzes = $conn->query($sql_quizzes);
$quizzes = [];
if ($result_quizzes->num_rows > 0) {
    while ($row = $result_quizzes->fetch_assoc()) {
        $quizzes[] = $row;
    }
}

// Query to get student results and email
$sql_student_results = "
    SELECT s.email, q.title, sq.score
    FROM studentquizresults sq
    JOIN quizzes q ON sq.quiz_id = q.quiz_id
    JOIN students s ON sq.student_id = s.student_id
";
$result_student_results = $conn->query($sql_student_results);
$student_results = [];
if ($result_student_results->num_rows > 0) {
    while ($row = $result_student_results->fetch_assoc()) {
        $student_results[] = $row;
    }
}

// Ensure that $selected_quiz_id is set based on the form submission
$selected_quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;

// Query to get student results and email
$sql_student_results = "
    SELECT s.email, q.title, sq.score
    FROM studentquizresults sq
    JOIN quizzes q ON sq.quiz_id = q.quiz_id
    JOIN students s ON sq.student_id = s.student_id
";
$result_student_results = $conn->query($sql_student_results);
$student_results = [];
if ($result_student_results->num_rows > 0) {
    while ($row = $result_student_results->fetch_assoc()) {
        $student_results[] = $row;
    }
}

$sql_performance_insights = "
    SELECT SUM(sq.score) AS total_score, COUNT(DISTINCT sq.student_id) AS total_students
    FROM studentquizresults sq
    JOIN quizzes q ON sq.quiz_id = q.quiz_id
    WHERE q.quiz_id = '$selected_quiz_id'
";
$result_performance_insights = $conn->query($sql_performance_insights);
$performance_insights = ['total_score' => 0, 'total_students' => 0];

if ($result_performance_insights->num_rows > 0) {
    $row = $result_performance_insights->fetch_assoc();
    $performance_insights['total_score'] = isset($row['total_score']) && $row['total_score'] !== NULL ? round($row['total_score'], 2) : 0;
    $performance_insights['total_students'] = isset($row['total_students']) && $row['total_students'] !== NULL ? round($row['total_students'], 2) : 0;
}

$average_score = 0;
if ($performance_insights['total_students'] > 0) {
    $average_score = round($performance_insights['total_score'] / $performance_insights['total_students'], 2);  
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="in_style.css">
    <link rel="icon" type="image/x-icon" href="images/K.png">

    <title>Dashboard</title>
</head>
<body>
    
<section id="sidebar" class="hide">
        <a href="#" class="brand">
            <img class="open-logo" src="in_img/K.png" style="width: 40px">
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="in_dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <!-- <li>
                <a href="#">
                    <i class='bx bxs-doughnut-chart' ></i>
                    <span class="text">Analytics</span>
                </a>
            </li> -->
            <li >
                <a href="QuizCreation.php">
                    <i class='bx bxs-plus-circle'></i>
                    <span class="text">Quiz Creation</span>
                </a>
            </li>
            <li>
                <a href="QuizManager.php" require=".home">
                    <i class='bx bx-book-open icon'></i>
                    <span class="text">Quiz Management</span>
                </a>
            </li>
            <li >
                <a href="in_support.php">
                    <i class='bx bx-support'></i>
                    <span class="text">Support</span>
                </a>
            </li>
            <!-- <li>
                <a href="#">
                    <i class='bx bxs-group' ></i>
                    <span class="text">Team</span>
                </a>
            </li> 
            </ul>
            <ul class="side-menu">
                <li>
                <a href="#">
                <i class='bx bx-support'></i>
                    <span class="text">Settings</span>
                </a>
            </li>-->
            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bx-log-out icon'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->


    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class="bx bx-menu"></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search.btn">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </form>
            <!-- <a href="#" class="notification">
                <i class="bx bxs-bell"></i>
                <span class="num">8</span>
            </a> -->
            <a href="in_profile.php" class="profile">
            <img src="images/pfp1.png" alt="Icon">
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Instructor Dashboard</a>
                        </li>
                        <!-- <li><i class="bx bx-chevron-right"></i></li>
                        <li>
                            <a class="active" href="#">Home</a>
                        </li> -->
                    </ul>
                </div>
                <div class="right">
                    <a href="QuizCreation.php" class="btn top">
                        <i class='bx bx-plus-medical' ></i>
                        <span class="text">Create Quiz</span>
                    </a>
                    <a href="QuizManager.php" class="btn bottom">
                        <i class='bx bxs-cog' ></i>
                        <span class="text">Manage Quiz</span>
                    </a>
                </div>
                
            </div>

            <ul class="box-info">
                <li>
                    <img class="create" src="in_img/print.png">
                    <span class="text">
                        <h3><?php echo $total_quizzes; ?></h3>
                        <p class="TQC">Total Quizzes Created</p>
                    </span>
                </li>
                <!-- <li>
                    
                    <img class="cup" src="in_img/cup.png">
                    <span class="text">
                        <select name="quiz" id="quiz">
                            <option value="">haha</option>
                        </select>
                        <h3><?php echo round($average_performance, 2); ?></h3>
                        <p>Average Student Performance</p>
                    </span>
                </li> -->
                <li>
                    <img class="quizIcon" src="in_img/quiz.webp">
                    <span class="text">
                        <h3><?php echo $active_quizzes; ?></h3>
                        <p>Active Quizzes</p>
                    </span>
                </li>
                <li>
                    <img class="deadline" src="in_img/deadline.png">
                    <span class="text">
                        <h3><?php echo $due_soon_quizzes; ?></h3>
                        <p>Upcoming Quiz Deadlines (Within 3 days)</p>
                    </span>
                </li>
            </ul>


            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Submissions</h3>

                        <!-- Quiz Selection Form -->
                        <form method="GET" action="in_dashboard.php" id="quizForm">
                            <select name="quiz_id" id="quiz" onchange="this.form.submit()">
                                <option value="">All Quizzes</option>
                                <?php
                                foreach ($quizzes as $quiz) {
                                    $selected = isset($_GET['quiz_id']) && $_GET['quiz_id'] == $quiz['quiz_id'] ? 'selected' : '';
                                    echo "<option value='" . $quiz['quiz_id'] . "' $selected>" . $quiz['title'] . "</option>";
                                }
                                ?>
                            </select>
                        </form>

                    </div>

                    

                    <!-- <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date Order</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="in_img/users1.png" width="35" height="35">
                                    <p>John Doe</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status completed">Completed</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="in_img/user2.png" width="35" height="35">
                                    <p>Jennie</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="in_img/user3.png" width="35" height="35">
                                    <p>Jane</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status process">Process</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="in_img/user4.png" width="35" height="35">
                                    <p>Michael</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="in_img/user5.png" width="35" height="35">
                                    <p>Jessica</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status completed">Completed</span></td>
                            </tr>
                        </tbody>
                    </table> -->
                    

                    <table>
                        <thead>
                            <tr>
                                <th>Student Email</th>
                                <th>Quiz Title</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Filter student results based on selected quiz
                            $selected_quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;
                            $sql_filter_results = "
                                SELECT s.email, q.title, sq.score
                                FROM studentquizresults sq
                                JOIN quizzes q ON sq.quiz_id = q.quiz_id
                                JOIN students s ON sq.student_id = s.student_id
                            ";
                            if ($selected_quiz_id) {
                                $sql_filter_results .= " WHERE sq.quiz_id = '$selected_quiz_id'";
                            }
                            $result_filtered_results = $conn->query($sql_filter_results);

                            // Display filtered results
                            if ($result_filtered_results->num_rows > 0) {
                                while ($row = $result_filtered_results->fetch_assoc()) {
                                    echo "<tr>
                                            <td>{$row['email']}</td>
                                            <td>{$row['title']}</td>
                                            <td>{$row['score']}</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No submissions found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    
            

                </div>
                <div class="performance">
                    <!-- <div class="head">
                        <h3>Todos</h3>
                        <i class="bx bx-plus"></i>
                        <i class="bx bx-filter"></i>
                    </div>
                    <ul class="todo-list">
                        <li class="completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                        <li class="not-completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                        <li class="completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                        <li class="not-completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                    </ul> -->
                    
                    <li class="performance-insights">
                        
                        <p class="PI">Performance Insights</p>
                        <img class="cup" src="in_img/cup.png">
                        <span class="text">
                            <h3><?php echo round($average_score, 2); ?></h3>
                            <p>Average Student Performance</p>
                            <p>Total Score: <?php echo $performance_insights['total_score']; ?></p>
                            <p>Total Students: <?php echo $performance_insights['total_students']; ?></p>
                        </span>
                    </li>


                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->
    
    <script src="in_dashboard.js"></script>
</body>
</html>