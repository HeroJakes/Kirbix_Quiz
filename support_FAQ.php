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

    <title>Freqeunt Asked Question</title>

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
            text-align: center;
        }

        .header-section img {
            width: 200px;
            margin-left: 50px;
        }

        .accordion {
            margin: 60px auto;
            width: 600px;
        }

        .accordion li {
            list-style: none;
            width: 100%;
            margin: 20px;
            padding: 10px;
            border-radius: 8px;
            background-color: #acbaf7;
            box-shadow: 6px 6px 10px -1px rgba(0, 0, 0, 0.15),
                -6px -6px 10px -1px rgba(255, 255, 255, 0.7);
        }

        .accordion li label {
            display: flex;
            align-items: center;
            padding: 10px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;

        }

        label::before {
            content: "+";
            margin-right: 10px;
            font-size: 24px;
            font-weight: 600;
        }

        input[type="radio"] {
            display: none;
        }

        .accordion .content {
            color: #555;
            padding: 0 10px;
            line-height: 26px;
            max-height: 0;
            overflow: hidden;
        }

        .accordion input[type="radio"]:checked+label+.content {
            max-height: 400px;
            padding: 10px 10px 20px;
        }

        .accordion input[type="radio"]:checked+label::before {
            content: "-";
        }

        @media (max-width: 500px) {
        .header-section {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        .header-section img {
            width: 150px; /* Adjust image size for smaller screens */
            margin-left: 0;
            margin-bottom: 10px;
        }

        .accordion {
            width: 90%; /* Adjust accordion width for small screens */
            margin: 20px auto;
        }

        .accordion li {
            padding: 8px;
            margin: 0px;
        }

        .accordion li label {
            font-size: 14px;
            padding: 8px;
        }

        .accordion .content {
            padding: 8px 10px;
        }

        .content p{
            font-size: 10px;
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
                        <a href="#">
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
            <div class="text">Frequent Asked Question</div>
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
            <h3>Freqeunt Asked Question (FAQ) Section</h3>
            <img src="images/support2.png" alt="FAQ">
        </div>
        <div>
            <ul class="accordion">
                <li>
                    <input type="radio" name="accordion" id="first" checked>
                    <label for="first">How do I earn points?</label>
                    <div class="content">
                        <p>Points are awarded for completing a variety of activities on the platform,
                            making them an essential measure of your engagement. You can earn points
                            by completing quizzes, logging in regularly to maintain streaks, and participating
                            in events or challenges.These points can help you climb the leaderboard, unlock
                            badges, or access exclusive features and rewards.</p>
                    </div>
                </li>
                <li>
                    <input type="radio" name="accordion" id="second">
                    <label for="second">What are badges, and how can I earn them?</label>
                    <div class="content">
                        <p>Badges are digital rewards that recognize your achievements and
                            progress on the platform. They can be earned by completing specific
                            milestones, such as scoring high on quizzes, finishing learning modules,
                            or maintaining a daily login streak. Badges serve as a fun and motivating way
                            to showcase your accomplishments to others and encourage consistent engagement.</p>
                    </div>
                </li>
                <li>
                    <input type="radio" name="accordion" id="third">
                    <label for="third">What devices are supported?</label>
                    <div class="content">
                        <p>The platform works on desktops, tablets, and mobile devices with
                            a modern browser.</p>
                    </div>
                </li>
                <li>
                    <input type="radio" name="accordion" id="forth">
                    <label for="forth">How can I track my progress?</label>
                    <div class="content">
                        <p>You can track your progress using the "Performance Insights" section,
                            which provides a detailed overview of your learning journey. This feature
                            allows you to view your quiz scores, monitor progress trends over time, and
                            identify areas where you excel or need improvement. By regularly reviewing
                            these insights, you can set personalized goals and focus on strengthening
                            weaker topics.</p>
                    </div>
                </li>
            </ul>
        </div>

    </section>
</body>
<script src="Javascript.js"></script>
</html>