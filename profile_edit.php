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
    $query = "SELECT username, email, password_hash, date_of_birth FROM users WHERE user_id = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die('Query prepare failed: ' . $con->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $password_hash, $date_of_birth);
    $stmt->fetch();
    $stmt->close(); // Close statement after use

    // Set profile picture
    $profile_picture_number = ($user_id % 4) + 1;
    $profile_picture = "images/pfp" . $profile_picture_number . ".png";

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

    <title>Edit User Profile</title>

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



        body.dark .content-section {
            background-color: var(--sidebar-color);
        }

        body.dark .support {
            background-color: #352f44;
        }

        .profile_icon img {
            width: 50px;
            height: 50px;
        }

        /* General container styling */
        .content-section {
            padding: 30px;
            background-color: #d5eeff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            margin: 0 auto;
            display: flex;
        }

        /* Centering form */
        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Label styling */
        label {
            font-size: 14px;
            color: #333;
            margin-bottom: 3px;
            /* Reduced margin */
            display: block;
        }

        /* Input fields styling */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            /* Reduced padding */
            margin: 4px 0;
            /* Reduced margin */
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }

        /* Input focus styling */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        /* Button container styling */
        .button-container {
            margin-top: 20px;
            text-align: center;
        }

        /* Submit button styling */
        button[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .profile-icon {
            flex-shrink: 0;
            /* Prevent shrinking */
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 80px;
        }

        .profile-icon img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

            content-section {
                flex-direction: column;
                text-align: center;
                margin-top: 5px;
                margin-bottom: 10px;
                padding: 20px;
            }

            .container {
                padding: 0 20px;
                width: 100%;
                align-items: center;
            }

            .container {
                padding: 0 20px;
                border-radius: 10px;
                /* Add padding for smaller screens */
            }

            label {
                font-size: 12px;
                margin-top: 10px;
                /* Decrease font size for labels */
            }

            input[type="text"],
            input[type="email"],
            input[type="password"],
            input[type="date"] {
                padding: 6px;
                /* Reduce padding for smaller screens */
                margin: 4px 0;
                /* Keep margin same */
                font-size: 14px;
            }

            button[type="submit"] {
                padding: 10px 20px;
                /* Adjust padding */
                font-size: 14px;
            }

            .profile-icon img {
                width: 100px;
                height: 100px;
                /* Resize profile image for smaller screens */
            }

            .main {
                left: 0;
                /* Adjust main section for smaller screens */
                width: 100%;
            }

            .content-section {
                flex-direction: column;
                /* Stack content section vertically */
                text-align: center;
            }

            .profile-icon {
                display: none;
            }

            .button-container{
                margin-bottom: 10px;
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
            <div class="text">Edit Profile</div>
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
            <div class="container">
                <form method="post">
                    <!-- Hidden field to store user ID -->
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>"><br>
                    <br>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"><br>
                    <br>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required value=""><br> <!-- Do not pre-fill passwords -->
                    <br>
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required value="<?php echo htmlspecialchars($date_of_birth); ?>"><br>

                    <div class="button-container">
                        <button type="submit" name="submitBtn">Submit</button>
                    </div>
                </form>
            </div>
            <div class="profile-icon">
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="bx bx-user-circle">
            </div>
        </div>


        <?php
        // Check if form is submitted
        if (isset($_POST['submitBtn'])) {
            include("conn.php");
            include("session.php");

            // Get user data from form
            $user_id = $_SESSION['mySession'];
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $date_of_birth = $_POST['date_of_birth'];

            // Securely hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the SQL query for update
            $sql = "UPDATE users 
            SET username = ?, email = ?, password_hash = ?, date_of_birth = ?
            WHERE user_id = ?";

            // Ensure $con is open and valid
            if ($con) {
                if ($stmt = $con->prepare($sql)) {
                    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $date_of_birth, $user_id);

                    // Execute and check for success
                    if ($stmt->execute()) {
                        echo '<script>alert("Record updated successfully!"); window.location.href = "profile_main.php";</script>';
                    } else {
                        echo '<script>alert("Error updating record: ' . $stmt->error . '");</script>';
                    }

                    $stmt->close();
                } else {
                    echo '<script>alert("Error preparing the statement: ' . $con->error . '");</script>';
                }
            } else {
                echo '<script>alert("Database connection is not open.");</script>';
            }

            // Close the connection after everything
            $con->close();
        }
        ?>

    </section>
</body>
<script src="Javascript.js"></script>
</html>