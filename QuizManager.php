<?php
include('session.php');
include('conn.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['mySession'])) {
    echo "Session not set. Redirecting...";
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
} else {
    $user_id = $_SESSION['mySession'];

    $servername = "localhost";
    $dbname = "kirbix";
    $dbusername = "root";
    $dbpassword = "";

    $con = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();

    $profile_picture_number = ($user_id % 5) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    $stmt->close();
    $con->close();
}

$servername = "localhost";
$dbname = "kirbix";
$dbusername = "root";
$dbpassword = "";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch quizzes
$sql = "SELECT quiz_id, title, description, difficulty, time_limit, date_due FROM quizzes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="in_style.css">

    <title>Quiz Management Page</title>

<style>
    .open-logo {
        margin-top: 0px;
        margin-left: 0px;
    }

    body,
    html {
        margin: 0;
        padding: 0;
        height: 100%;
        font-family: Arial, sans-serif;
    }


    .tbl_container {
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 50px;
    }

    .tbl_content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-x: auto;
        background-color: #fff;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        border-radius: 30px;
    }

    .tbl {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #ccc;
    }

    .tbl thead {
        background-color: #424949;
        color: #ffffff;
    }

    .tbl thead th {
        padding: 15px;
        text-align: left;
        white-space: nowrap;
        border: 1px solid #ccc;
        font-size: 0.9rem;
    }

    .tbl tbody tr td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ccc;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .tbl tbody tr:hover {
        background-color: #f9f9f9;
        transition: background-color 0.3s;
    }

    .tbl button {
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        border: none;
    }

    .btn_edit {
        background-color: #1e8449;
        color: #ffffff;
    }

    .btn_trash {
        background-color: #e74c3c;
        color: #ffffff;
    }

    @media (max-width: 768px) {

        .tbl thead th,
        .tbl tbody td {
            font-size: 0.75rem;
            padding: 8px;
        }

        .tbl button {
            padding: 4px 8px;
        }
    }

    .textq{
        color: #eee;
    }
</style>


</head>

<body>
<section id="sidebar" class="hide">
        <a href="#" class="brand">
            <img class="open-logo" src="in_img/K.png" style="width: 40px">
        </a>
        <ul class="side-menu top">
            <li>
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
            <li class="active">
                <a href="QuizManager.php" require=".home">
                    <i class='bx bx-book-open icon'></i>
                    <span class="text">Quiz Management</span>
                </a>
            </li>
            <li>
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
                    <h1>Quiz Manager</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Quiz Manager</a>
                        </li>
                        <li><i class="bx bx-chevron-right"></i></li>
                        <li>
                            <a class="active" href="in_dashboard.php">Home</a>
                        </li>
                    </ul>
                </div>
                <div class="right">

                </div>

                <!--<section class="home">-->
                <div class="head">
                    <div class="textq">Quiz Manager</div>
                </div>
            </div>

            <body>

                <div class="tbl_container" name="QuizManagerTable">
                    <div class="tbl_content">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Quiz ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Difficulty</th>
                                    <th>Time Limit</th>
                                    <th>Date Due</th>
                                    <th colspan="2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Check if there are rows in the result
                                if ($result->num_rows > 0) {
                                    // Output data of each row
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['quiz_id']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['difficulty']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['time_limit']) . " mins</td>";
                                        echo "<td>" . htmlspecialchars($row['date_due']) . "</td>";
                                        echo "<td>
                                <button class='btn_edit' onclick='editQuiz(" . $row['quiz_id'] . ")'>
                                    <i class='bx bx-edit-alt'></i> Edit
                                </button>
                              </td>";
                                        echo "<td>
                                <button class='btn_trash' onclick='deleteQuiz(" . $row['quiz_id'] . ")'>
                                    <i class='bx bx-trash'></i> Delete
                                </button>
                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No quizzes found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script src="QuizManager.js"></script>
                <script src="in_dashboard.js"></script>