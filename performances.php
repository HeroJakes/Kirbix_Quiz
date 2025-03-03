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

    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    $stmt->close();
    $con->close();
}
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

    <title>Performance Insights</title>

    <style>
        .main {
            position: absolute;
            top: 0;
            top: 0;
            left: 250px;
            height: 100vh;
            width: calc(100% - 250px);
            background-color: var(--body-color);
            transition: var(--tran-05);
        }

        .title-section {
            background-image: linear-gradient(to bottom right, #a5d3ff, #ffffff);
            padding: 10px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: left;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .title-text {
            text-align: left;
            align-items: left;
        }

        .title-section h2 {
            font-size: 30px;
            margin-bottom: 0px;
            color: darkblue;
            align-items: center;
            display: flex;
        }

        .content-section {
            background-color: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: left;
        }

        .content-list {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .content {
            background-color: white;
            padding: 20px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1;
            /* Allow equal distribution of width */
            min-width: 300px;
            /* Minimum width for responsiveness */
            max-width: 300px;
            /* Fix width for consistency */
            display: flex;
            flex-direction: column;
            /* Ensure inner content stacks vertically */
            justify-content: center;
            /* Center content vertically */
            align-items: center;
            /* Center content horizontally */
            height: 300px;
            /* Fixed height for all boxes */
            background-color: #d5eeff;
        }

        .content img {
            width: 140px;
            height: 140px;
        }

        .content-section button:hover {
            background-color: #022ff5;
        }

        .content-icon img {
            width: 250px;
            height: 200px;
            margin-left: 100px;
        }

        .content button {
            height: 58px;
        }

        body.dark .content-section {
            background-color: var(--sidebar-color);
        }

        body.dark .content {
            background-color: #352f44;
        }

        @media (max-width: 500px) {
            .title-section {
                padding: 15px;
                /* Adjust padding for smaller screens */
            }

            .title-section h2 {
                font-size: 15px;
                margin-left: 0px;
                margin-bottom: 0px;
                color: darkblue;
                text-align: center;
            }

            .content-icon img {
                width: 100px;
                /* Further reduce icon size */
                margin-left: 0;
                margin-bottom: 0px;
                height: 80px;
                /* Provide some space at the bottom */
            }

            .content img {
                width: 70px;
                height: 60px;
            }

            .content-section h3 {
                font-size: 16px;
                /* Make the subtitle smaller */
            }

            .content-section{
                padding: 20px;
            }

            .content {
                background-color: white;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                margin-bottom: 10px;
                justify-self: center;
                align-self: center;
                text-align: center;
                width: 160px;
                height: 180px;
                font-size: 14px;
                min-width: 200px;
            }

            .content-section button {
                font-size: 12px;
                /* Smaller font size for buttons */
                margin-top: 10px;
                /* Add margin to the top */
                width: 130px;
                /* Adjust button width */
                border-radius: 25px;
                padding: 10px;
                /* Add some padding for better touchability */
            }

            .content-list {
                flex-direction: column;
                /* Stack items vertically for smaller screens */
                align-items: center;
                /* Center items */
                gap: 15px;
                /* Adjust gap between items */
            }

            .main {
                position: relative;
                /* Switch from absolute positioning to relative */
                top: 0;
                left: 0;
                height: auto;
                /* Allow content height to adjust */
                width: 100%;
                padding-left: 15px;
                /* Adjust left padding */
            }
        }
    </style>

</head>

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
            
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="Dashboard.php">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="quiz_management.php">
                            <i class='bx bx-book-open icon'></i>
                            <span class="text nav-text">Quiz Management</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="performances.php">
                            <i class='bx bx-bar-chart-alt-2 icon'></i>
                            <span class="text nav-text">Performance Insights</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="support.php">
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

            </div>
        </div>
    </nav>

    <section class="home">
        <div class="head">
            <div class="text">Performances Insights</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div class="title-section">
            <div class="title-text">
                <h2>Performances Insights</h2>
            </div>
            <div class="content-icon">
                <img src="images/performance1.png" alt="performances insight icon">
            </div>
        </div>

        <div class="content-section">
            <div class="content-list">
                <div class="content">
                    <img src="images/performances3" alt="Overview Of Activity">
                    <button onclick="window.location.href='Performances-overview.php';">Overview Of Activity</button>
                </div>
                <div class="content">
                    <img src="images/performances2" alt="Recent Quizzes Taken">
                    <button onclick="window.location.href='performances-Q.php';">Recent Quizzes Taken</button>
                </div>
                <div class="content">
                    <img src="images/performances4.png" alt="Achievements">
                    <button onclick="window.location.href='performances-achievements.php';">Achievements</button>
                </div>
            </div>
        </div>

    </section>
</body>
<script src="Javascript.js"></script>

</html>