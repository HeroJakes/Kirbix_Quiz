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

    <title>Support</title>

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
            padding: 20px;
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
            font-size: 40px;
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

        .support-list {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .support {
            background-color: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex-wrap: warp;
            width: 90%;
            max-width: 300px;
        }

        .support img {
            width: 200px;
        }

        .content-section button:hover {
            background-color: #022ff5;
        }

        .support-icon img {
            width: 170px;
            border-radius: 8px;
            margin-left: 100px;
            margin-bottom: 10px;
        }

        .support button {
            height: 58px;
        }

        body.dark .content-section {
            background-color: var(--sidebar-color);
        }

        body.dark .support {
            background-color: #352f44;
        }

        @media (max-width: 500px) {
            .title-section h2 {
                font-size: 25px;
                margin-left: 20px;
                margin-bottom: 0px;
                color: darkblue;
                align-items: center;
                display: flex;
            }

            .support-icon img {
                width: 100px;
                border-radius: 8px;
                margin-left: 0px;
                margin-bottom: 10px;
            }

            .support img {
                width: 80px;
            }

            .content-section h3 {
                font-size: 17px;
            }

            .support {
                background-color: white;
                padding: 50px;
                border-radius: 30px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                margin-bottom: 5px;
                justify-self: center;
                align-self: center;
                text-align: center;
                width: 200px;
                height: 200px;
            }

            .content-section button {
                font-size: 10px;
                margin-top: 0px;
                width: 120px;
                border-radius: 25px;
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
            <div class="text">Dashboard</div>
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
                <h2>Support Center</h2>
            </div>
            <div class="support-icon">
                <img src="images/support1.png" alt="support icon">
            </div>
        </div>

        <div class="content-section">
            <h3>How can we help you ?</h3>
            <div class="support-list">
                <div class="support">
                    <img src="images/support2.png" alt="FAQ">
                    <button onclick="window.location.href='support_FAQ.php';">Frequently Asked Question</button>
                </div>
                <div class="support">
                    <img src="images/support3.png" alt="Guides & Tutorial">
                    <button onclick="window.location.href='support_G&D.php';">Guides & Tutorial</button>
                </div>
                <div class="support">
                    <img src="images/support4.png" alt="Contact Support">
                    <button onclick="window.location.href='support_contact.php';">Contact Support</button>
                </div>
            </div>
        </div>

    </section>
</body>
<script src="Javascript.js"></script>

</html>