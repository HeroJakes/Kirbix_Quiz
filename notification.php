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

    // Fetch username
    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close(); // Close statement immediately after fetching result

    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

    // Fetch notifications
    $notifications_query = "SELECT message, type, date_sent FROM notifications WHERE user_id = ?";
    $notifications_stmt = $con->prepare($notifications_query);
    $notifications_stmt->bind_param("i", $user_id);
    $notifications_stmt->execute();
    $notifications_result = $notifications_stmt->get_result();

    $notifications = [];
    while ($row = $notifications_result->fetch_assoc()) {
        $notifications[] = $row;
    }

    // Free result and close statement
    $notifications_result->free();
    $notifications_stmt->close();

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

    <title>Notifications</title>

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
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: left;
            align-items: left;
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

        .content-list ul {
            list-style-type: none;
            padding-left: 0;
            /* Remove the default left padding */
            margin: 0;
            /* Remove the default margin */
        }

        .notification-container {
            text-align: left;
            align-items: left;
            margin-left: 10px;
        }

        .notification-item {
            display: flex;
            background-color: #d5eeff;
            margin-bottom: 30px;
            gap: 30px;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border-radius: 30px;
        }


        .notification-icon img {
            margin-left: 20px;
            width: 100px;
            height: 100px;
            margin-top: 0px;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-content h3{
            font-size: 18px;
            font-weight: lighter;
        }

        .notification-date {
            margin-left: 600px;
            color: gray;
            font-size: 10px;
        }


        @media (max-width: 500px) {

            .title-section h2 {
                font-size: 18px;
                margin-left: 20px;
                margin-bottom: 0px;
                color: darkblue;
                align-items: center;
                display: flex;
            }

            .content-icon img {
                width: 80px;
                height: 70px;
                border-radius: 8px;
                margin-left: 0px;
                margin-bottom: 10px;
            }

            .content img {
                width: 80px;
                height: 80px;
            }

            .content-section h3 {
                font-size: 17px;
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
                width: 270px;
                height: 300px;
            }

            .notification-container{
                margin-left: 0px;
            }

            .content-section button {
                font-size: 10px;
                margin-top: 0px;
                width: 120px;
                border-radius: 25px;
            }

            .notification-item {
                flex-direction: column;
                align-items: center;
                gap: 0px;
                text-align: center;
            }

            .notification-icon img {
                width: 50px;
                height: 50px;
                margin-left: 0px;
            }

            .notification-content {
                flex-grow: 0;
                margin-left: 0px;
            }

            .notification-content h3 {
                font-size: 10px;    
            }

            .notification-date {
                margin-left: 0px;
                font-size: 10px;
                color: gray;
            }

            .content-list {
                justify-content: center;
                gap: 15px;
                padding: 15px;
            }

            .main {
                left: 0;
                width: 100%;
            }

            .notification-type {
                font-size: 15px;
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
            <div class="text">Notifications</div>
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
                <h2>Notifications</h2>
            </div>
            <div class="content-icon">
                <img src="images/notification4.png" alt="Notification icon">
            </div>
        </div>


        <div class="content">
            <?php if (!empty($notifications)): ?>
                <div class="notification-container">
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        // Set the image and alt text based on the notification type
                        if ($notification['type'] == 'quiz') {
                            $notification_image = "images/notification1.png";
                            $notification_alt = "Quiz";
                        } elseif ($notification['type'] == 'achievement') {
                            $notification_image = "images/notification2.png";
                            $notification_alt = "Achievement";
                        } else {
                            $notification_image = "images/notification3.png";
                            $notification_alt = "Reminder";
                        }
                        ?>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <img src="<?php echo $notification_image; ?>" alt="<?php echo $notification_alt; ?>" class="notification-image">
                            </div>
                            <div class="notification-content">
                                <strong class="notification-type"><?php echo strtoupper(htmlspecialchars($notification['type'])); ?></strong>
                                <br><h3><?php echo htmlspecialchars($notification['message']); ?></h3><br>
                                <span class="notification-date"> Date Sent: <?php echo htmlspecialchars($notification['date_sent']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No notifications found.</p>
            <?php endif; ?>
        </div>

    </section>
</body>
<script src="Javascript.js"></script>

</html>