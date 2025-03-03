<?php
include('session.php');
include('conn.php');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if session is set
if (!isset($_SESSION['mySession'])) {
    echo "Session not set. Redirecting...";
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
} else {
    $user_id = $_SESSION['mySession'];

    // Create DB connection
    $con = mysqli_connect("localhost", "root", "", "kirbix");

    // Check for connection error
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Fetch user details from 'users' table
    $query = "SELECT username, email, date_created FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die('Query prepare failed: ' . $con->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $date_created);
    $stmt->fetch();
    $stmt->close(); // Close statement after use

    // Set profile picture
    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    // Fetch student level and points from 'students' table
    $query = "SELECT level, points FROM students WHERE user_id = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die('Query prepare failed: ' . $con->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($level, $points);
    $stmt->fetch();
    $stmt->close(); // Close statement after use

    // Close DB connection
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

    <title>User Profile</title>

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
            margin-bottom: 20px;
        }

        .user_detail {
            display: flex;
            align-items: baseline;
            margin: 10px 0;
        }

        .user_detail h6 {
            font-size: 14px;
            color: #555;
            margin: 0;
            margin-right: 8px;
            font-weight: 600;
        }

        .user_detail h4 {
            font-size: 18px;
            margin: 0;
            font-weight: 600;
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

        .title-text {
            margin-right: 100px;
        }

        .content-list {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .content {
            background-color: #d5eeff;
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex-wrap: warp;
            width: 300px;
            height: 200px;
        }

        .content img {
            margin-top: 15px;
            width: 100px;
            height: 100px;
        }

        .content h5 {
            font-size: 20px;
            font-weight: bold;
            color: darkblue;
        }

        .content h3 {
            margin-top: 20px;
            font-size: 50px;
            font-weight: normal;
        }

        .support-icon img {
            width: 170px;
            border-radius: 8px;
            margin-left: 100px;
            margin-bottom: 10px;
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

        .profile_icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .profile_icon img {
            width: 40px;
            height: 40px;
        }

        .title-section {
            gap: 30px;
        }

        .profile_icon button {
            width: auto;
            padding: 5px 10px;
            font-size: 12px;
            text-align: center;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .profile_icon button:hover {
            background-color: #0056b3;
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

            .content-section {
                padding: 30px;
            }

            .content {
                background-color: white;
                padding: 20px;
                border-radius: 30px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                margin-bottom: 5px;
                justify-self: center;
                align-self: center;
                text-align: center;
                width: 200px;
                height: auto;
            }

            .profile_icon img {
                width: 35px;
                height: 35px;
            }

            .profile_icon button {
                width: auto;
                padding: 5px 8px;
                font-size: 11px;
                text-align: center;
                color: white;
                border: none;
                border-radius: 50px;
                cursor: pointer;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                height: 40px;
                margin-top: 0px;
                width: 100px;
            }

            .profile_icon button:hover {
                background-color: #0056b3;
            }

            .user_detail {
                flex-direction: column;
                align-items: flex-start;
                margin: 10px 0;
            }

            .user_detail h6 {
                font-size: 12px;
                margin-right: 5px;
            }

            .user_detail h4 {
                font-size: 16px;
                margin: 0;
                font-weight: 600;
            }

            .content-list {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 15px;
                margin-top: 20px;
            }

            .title-text {
                margin-right: 0px;
            }

            .profile-icon img {
                width: 100px;
                height: 100px;
                margin-left: 0px;
            }

            .profile-icon {
                align-items: center;
            }

            .profile_icon img {
                display: none;
            }

            .title-section {
                flex-wrap: wrap;
                gap: 8px;

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
            <div class="text">Main Profile</div>
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
                <div class="user_detail">
                    <h6>Username: </h6>
                    <h4><?php echo htmlspecialchars($username); ?></h4>
                </div>
                <div class="user_detail">
                    <h6>Email: </h6>
                    <h4><?php echo htmlspecialchars($email); ?></h4>
                </div>
                <div class="user_detail">
                    <h6>Date joined: </h6>
                    <h4><?php echo htmlspecialchars($date_created); ?></h4>
                </div>
            </div>
            <div class="profile-icon">
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:7.5em; height:7.5em;">
            </div>
            <div class="profile_icon">
                <a href="profile_user.php">
                    <img src="images/profile1.png">
                </a>
                <button onclick="window.location.href='profile_edit.php';">Edit Profile</button>
            </div>
        </div>

        <div class="content-section">
            <div class="content-list">
                <div class="content">
                    <h5>User level</h5>
                    <h3><?php
                        if ($points > 2000) {
                            echo '<p> 2 </p>>';
                        } elseif ($points > 3000) {
                            echo '<p>3 </p>';
                        } elseif ($points > 4000) {
                            echo '<p>4 </p>';
                        } else {
                            echo '<p>1</p>';
                        }
                        ?>
                    </h3>
                </div>
                <div class="content">
                    <h5>Earned Badges</h5>
                    <?php
                    if ($points < 1000) {
                        echo '<img src="images/achievement1.png" alt="Achievement 1">';
                    } elseif ($points <= 2000) {
                        echo '<img src="images/achievement2.png" alt="Achievement 2">';
                    } else {
                        echo '<img src="images/achievement3.png" alt="Achievement 3">';
                    }
                    ?>
                </div>
                <div class="content">
                    <h5>Points</h5>
                    <h3><?php echo $points ?></h3>
                </div>
            </div>
        </div>

    </section>
</body>
<script src="Javascript.js"></script>

</html>