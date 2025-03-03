<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $emailError = "";

    $adminEmail = "admin@gmail.com";
    $adminPassword = "admin";

    if ($email === $adminEmail && $password === $adminPassword) {
        $_SESSION['mySession'] = "admin";
        $_SESSION['role'] = "Admin";

        header("Location: admin-side/dashboard.php");
        exit();
    } else {

        $servername = "localhost";
        $dbname = "kirbix";
        $dbusername = "root";
        $dbpassword = "";
        $con = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);


        if (!$con) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $sql = "SELECT user_id, password_hash, role, status FROM users WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $stored_password, $role, $status);
            $stmt->fetch();

            if ($status === 'Suspended') {
                $emailError = "Your account is suspended. Please contact support.";
            } else if (password_verify($password, $stored_password)) {
                $update_last_login = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                $update_stmt = $con->prepare($update_last_login);
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
                $update_stmt->close();

                $_SESSION['mySession'] = $user_id;
                $_SESSION['role'] = $role;

                if (isset($_POST['remember_me'])) {
                    setcookie('email', $email, time() + (86400 * 30), "/");
                    setcookie('password', $password, time() + (86400 * 30), "/");
                } else {
                    setcookie('email', '', time() - 3600, "/");
                    setcookie('password', '', time() - 3600, "/");
                }

                if ($role === 'Instructor') {
                    $_SESSION['instructor_id'] = $user_id;
                    header("Location: in_dashboard.php");
                } elseif ($role === 'Student') {
                    header("Location: Dashboard.php");
                } else {
                    header("Location: Admin_Dashboard.php");
                }
                exit();
            } else {
                $emailError = "Invalid Email or Password!";
            }
        } else {
            $emailError = "Invalid Email or Password!";
        }

        $stmt->close();
        mysqli_close($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url(images/background.png);
            height: 100vh;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .container {
            width: 90%;
            max-width: 600px;
            background-color: #ffffff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: auto;
        }

        .header img {
            max-width: 120px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 0px;
            color: #333;
        }

        input[type="email"],
        input[type="submit"],
        input[type="reset"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 13px;
            color: #666;
        }

        input[type="checkbox"] {
            margin-right: 5px;
            font-size: 10px;
            color: #666;
        }

        input[type="submit"],
        input[type="reset"] {
            background-color: #1e1e1e;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover,
        input[type="reset"]:hover {
            background-color: #393939;
        }

        p {
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }

        p a {
            color: #007bff;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }

        .input-box {
            width: 100%;
            padding: 9px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* Ensures proper spacing between input and icon */
            box-sizing: border-box;
            /* Includes padding and border in width */
        }

        .input-box input {
            width: 100%;
            /* Occupies the available space */
            border: 0;
            outline: 0;
            font-size: 13px;
            color: #666;
            padding-right: 40px;
            /* Prevent overlap with the icon */
            box-sizing: border-box;
            /* Ensures the padding doesn't break layout */
        }

        .input-box img {
            width: 1.5em;
            height: auto;
            cursor: pointer;
            margin-left: -35px;
            /* Pulls the icon into view while keeping the layout intact */
        }


        @media (min-width: 768px) {
            .container {
                max-width: 800px;
                padding: 40px;
            }

            h1 {
                font-size: 28px;
            }

            input[type="email"]
             {
                font-size: 11px;
                padding: 15px;
            }

            input[type="submit"],
            input[type="reset"] {
                font-size: 13px;
                padding: 12px;
            }

            p {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <form method="post">
        <div class="container">
            <h1>Welcome to</h1>
            <div class="header">
                <img src="images/logo.png">
            </div>
            <br>
            <input type="email" placeholder="Email" name="email" value="<?php echo isset($_COOKIE['email']) ? $_COOKIE['email'] : ''; ?>" required>

            <br>
            <div class="input-box">
                <input type="password" placeholder="Password" name="password" id="password" required>
                <img src="images/eye-close.png" id="eyeicon">
            </div>
            <script>
                let eyeicon = document.getElementById("eyeicon");
                let password = document.getElementById("password");

                eyeicon.onclick = function() {
                    if (password.type == "password") {
                        password.type = "text";
                        eyeicon.src = "images/eye-open.png";
                    } else {
                        password.type = "password";
                        eyeicon.src = "images/eye-close.png";
                    }
                }
            </script>
            <?php if (!empty($emailError)) { ?>
                <p class="error-message"><?php echo $emailError; ?></p>
            <?php } ?>
            <div>
                <p><input type="checkbox" name="remember_me" <?php echo isset($_COOKIE['email']) ? 'checked' : ''; ?>>Remember me</p><br><br>
            </div>

            <div>
                <input type="submit" value="Login">
                <input type="reset" value="Reset">
            </div>

            <br>
            <p>No account yet? Please <a href="register.php">register</a></p>
        </div>
    </form>
</body>

</html>