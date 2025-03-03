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

    <title>Guides & Tutorials</title>

    <style>
        .home {
            align-content: left;
        }

        .header-section {
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 30px;
            margin-bottom: 0px;
            display: flex;
            justify-content: space-between;
            align-items: left;
            justify-content: center;
            align-items: center;
            text-align: left;
        }

        .header-section h3 {
            font-size: 30px;
            color: black;
            font-weight: normal;
        }

        .header-section img {
            width: 150px;
            margin-left: 80px;
        }

        .content-list {
            background-color: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 10px;
            text-align: left;
            height: auto;
            /* Enable flexbox */
            justify-content: center;
            /* Center items horizontally */
            align-items: flex-start;
            /* Align items at the top */
            gap: 15px;
            /* Add spacing between items */
            flex-wrap: wrap;
            /* Allow wrapping to a new row if needed */
            height: auto;
            align-items: flex-start;
        }

        .content-list h4 {
            margin-bottom: 10px;
        }

        .content {
            background-color: #acbaf7;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 0px;
            text-align: center;
            flex: 1 1 calc(33.333% - 15px);
            /* Each box takes up 1/3 of the row width */
            max-width: calc(33.333% - 15px);
            /* Prevents boxes from exceeding 1/3 of the row */
            min-width: 190px;
            height: 200px;
        }

        .content img {
            margin-top: 15px;
            height: 75%;
            width: 95%;

        }

        .content-container {
            display: flex;
            gap: 20px;
        }


        @media (max-width: 500px) {

            .header-section {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }

            .header-section h3 {
                margin-bottom: 10px;
                font-size: 15px;
                color: black;
                font-weight: normal;
                text-align: center;
            }

            .header-section img {
                width: 80px;
                margin: 0;
            }

            .content-list {
                padding: 15px;
                gap: 10px;
            }

            .content {
                flex: 1 1 calc(100% - 10px);
                max-width: 100%;
                min-width: auto;
                height: auto;
            }

            .content-container{
                flex-direction: column;
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
            <div class="text">Guides & Tutorials</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div class="header-section">
            <h3>Guides & Tutorials</h3>
            <img src="images/support3.png" alt="Guides & Tutorials icon">
        </div>

        <div class="content-section">
            <div class="content-list">
                <h4>How to check overview performances?</h4>
                <div class="content-container">
                    <div class="content">
                        <img src="images/G1.png" alt="Guides & Tutorials">
                    </div>
                    <div class="content">
                        <img src="images/G1.3.png" alt="Guides & Tutorials">
                    </div>
                    <div class="content">
                        <img src="images/G1.2.png" alt="Guides & Tutorials">
                    </div>
                </div>
            </div>
            <div class="content-list">
                <h4>How to join quiz?</h4>
                <div class="content-container">
                    <div class="content">
                        <img src="images/G2.3.png" alt="Guides & Tutorials">
                    </div>
                    <div class="content">
                        <img src="images/G2.1.png" alt="Guides & Tutorials">
                    </div>
                    <div class="content">
                        <img src="images/G2.2.png" alt="Guides & Tutorials">
                    </div>
                </div>
            </div>
        </div>


    </section>
</body>
<script src="Javascript.js"></script>

</html>