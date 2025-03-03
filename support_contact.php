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

    <title>Contact Support</title>

    <style>
        .content-section {
            background-image: linear-gradient(to bottom right, #a5d3ff, #ffffff);
            padding: 20px;
            border-radius: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex-direction: column;
            gap: 20px;
        }

        .content-section h4 {
            font-size: 40px;
            margin-bottom: 0px;
            color: darkblue;
            align-items: center;
            display: flex;
        }

        .content-section h6 {
            font-size: 20px;
            margin-bottom: 0px;
            color: #555;
            align-items: center;
            display: flex;
        }

        .issue_description textarea {
            width: 600px;
            height: 300px;
            border-radius: 10px;
        }

        .content-section button {
            width: 190px;
            margin-left: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 500px) {
        .content-section {
            flex-direction: column;
            align-items: center;
            padding: 15px;
            gap: 15px;
        }

        .content-section h4 {
            font-size: 30px; /* Adjust font size for h4 */
        }

        .content-section h6 {
            font-size: 16px; /* Adjust font size for h6 */
        }

        .issue_description textarea {
            width: 240px; /* Full width on smaller screens */
            height: 200px; /* Adjust height */
            margin-bottom: 10px;
        }

        .content-section button {
            width: 50%; /* Full width for button */
            margin-left: 0; /* Remove left margin */
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
            <div class="text">Contact Support</div>
            <div class="icons">
                <a href="notification.php">
                    <i class='bx bx-bell'></i>
                </a>
                <a href="profile_main.php">
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle" style="width:1.5em; height:1.5em;">
                </a>
            </div>
        </div>

        <div class="content-section">
            <h4>Contact Support</h4>
            <h6>How can we help you ?</h6>
            <form method="POST" action="">
                <div class="issue_description">
                    <br>
                    <textarea name="issue_description" required></textarea><br>
                </div>
                <button type="submit" name="submitBtn">Submit</button>
            </form>
        </div>
        </div>

        <?php
        // Handle form submission
        if (isset($_POST['submitBtn'])) {
            // Reconnect to the database
            $con = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Retrieve issue description from the form
            $issue_description = $_POST['issue_description'];

            // Use prepared statements to prevent SQL injection
            $query = "INSERT INTO supporttickets (user_id, issue_description, date_submitted, resolved_status) VALUES (?, ?, NOW(), ?)";
            $stmt = $con->prepare($query);

            if ($stmt) {
                $resolved_status = "Pending"; // Ensure status is a string
                $stmt->bind_param("iss", $user_id, $issue_description, $resolved_status);

                // Execute the statement and handle success/failure
                if ($stmt->execute()) {
                    echo '<script>alert("Submitted Successfully!");window.location.href = "support.php";</script>';
                } else {
                    echo "Error: " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $con->error;
            }

            // Close the database connection
            mysqli_close($con);
        }
        ?>


    </section>

    <script src="Dashboard.js"></script>
</body>
<script src="Javascript.js"></script>
</html>