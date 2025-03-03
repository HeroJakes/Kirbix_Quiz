<?php
session_start();

$servername = "localhost";
$dbname = "kirbix";
$dbusername = "root";
$dbpassword = "";

$con = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['step'] = 1;
    $_SESSION['role'] = '';
    $_SESSION['email'] = '';
    $_SESSION['password_hash'] = '';
    $_SESSION['date_of_birth'] = '';
    $_SESSION['username'] = '';
}

if (!isset($_SESSION['step'])) $_SESSION['step'] = 1; 
if (!isset($_SESSION['role'])) $_SESSION['role'] = '';
if (!isset($_SESSION['email'])) $_SESSION['email'] = '';
if (!isset($_SESSION['password_hash'])) $_SESSION['password_hash'] = '';
if (!isset($_SESSION['date_of_birth'])) $_SESSION['date_of_birth'] = '';
if (!isset($_SESSION['username'])) $_SESSION['username'] = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['step'] == 1 && isset($_POST['role'])) {
        $_SESSION['role'] = $_POST['role'];
        $_SESSION['step'] = 2;
    } elseif ($_SESSION['step'] == 2 && isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $emailError = "This email is already registered. Please use a different email.";
        } else {
            $_SESSION['email'] = $email;
            $_SESSION['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $_SESSION['step'] = 3;
        }
    } elseif ($_SESSION['step'] == 3 && isset($_POST['date_of_birth'])) {
        $_SESSION['date_of_birth'] = $_POST['date_of_birth'];
        $_SESSION['step'] = 4;
    } elseif ($_SESSION['step'] == 4 && isset($_POST['username'])) {
        $_SESSION['username'] = $_POST['username'];
    
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $stmt->store_result();
    
        if ($stmt->num_rows > 0) {

            $usernameError = "Username already exists! Please choose another.";
        } else {

            $sql = "INSERT INTO users (username, email, password_hash, role, date_of_birth, date_created, status) 
                    VALUES (?, ?, ?, ?, ?, NOW(), 'Active')";
            $stmt = $con->prepare($sql);
            $stmt->bind_param(
                "sssss",
                $_SESSION['username'],
                $_SESSION['email'],
                $_SESSION['password_hash'],
                $_SESSION['role'],
                $_SESSION['date_of_birth']
            );
            $stmt->execute();
    
            $user_id = $stmt->insert_id;
    
            if ($_SESSION['role'] === 'Instructor') {
                $sql = "INSERT INTO instructors (user_id) VALUES (?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            } elseif ($_SESSION['role'] === 'Student') {
                $sql = "INSERT INTO students (user_id, email) VALUES (?, ?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("is", $user_id, $_SESSION['email']);
                $stmt->execute();
            }
    
            echo "<script>alert('Registration successful!');window.location.href='login.php';</script>";
            session_destroy(); 
            exit();
        }
    }
}    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" type="image/x-icon" href="images/K.png">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-image: url(images/background.png);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            text-align: center;
            margin: 10px auto;
            box-sizing: border-box;
        }

        .header img {
            max-width: 80px;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 10px 0;
            color: #333;
            font-size: 20px;
        }

        .header p {
            color: #666;
            font-size: 14px;
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


        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 13px;
            box-sizing: border-box;
        }

        button {
            background-color: #1e1e1e;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
            box-sizing: border-box;
        }

        button:hover {
            background-color: #393939;
        }

        a {
            color: #4A90E2;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .role-list {
            display: flex;
            gap: 15px;
            justify-content: space-between;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            flex: 1;
            max-width: 48%;
            box-sizing: border-box;
        }

        .role-card:hover {
            transform: scale(1.05);
        }

        .role-card img {
            width: 200px;
            height: 200px;
            border-radius: 20px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .role-card button {
            width: 100%;
            padding: 10px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header h2 {
                font-size: 18px;
            }

            .header img {
                max-width: 70px;
            }

            input[type="email"],
            input[type="password"],
            input[type="date"],
            input[type="text"] {
                font-size: 14px;
                padding: 10px;
            }

            button {
                padding: 10px;
                font-size: 14px;
            }

            .role-list {
                flex-direction: column;
                /* Stacks the cards */
                gap: 15px;
            }

            .role-card {
                max-width: 100%;
                /* Cards take full width in mobile view */
            }

            .role-card img {
                width: 240px;
                height: 120px;
                object-fit: cover;
            }

            .role-card button {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <form method="post">
        <?php if ($_SESSION['step'] == 1) { ?>
            <div class="container">
                <div class="header">
                    <img src="images/logo.png">
                    <h2>Firstly, Let us know you more!</h2>
                    <p>Are you an Instructor or a Student?</p>
                </div>
                <div class="role-list">
                    <div class="role-card">
                        <img src="images/instructor.png" alt="Image of Instructor">
                        <button type="submit" name="role" value="Instructor">Instructor</button>
                    </div>
                    <div class="role-card">
                        <img src="images/student.png" alt="Image of Student">
                        <button type="submit" name="role" value="Student">Student</button>
                    </div>
                </div>
                <div class="header">
                    <br>
                    <p>Already have an Account? Login <a href="login.php">here</a></p>
                </div>
            </div>

        <?php } elseif ($_SESSION['step'] == 2) { ?>
            <div class="container">
                <div class="header">
                    <img src="images/logo.png">
                </div>
                <h1>Email and Password</h1>
                <form method="post">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <?php if (!empty($emailError)) { ?>
                        <p class="error-message"><?php echo $emailError; ?></p>
                    <?php } ?>
                    <br>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required><br><br>
                    <button type="submit">Next</button>
                </form>
                <div class="header">
                    <br>
                    <p>Already have an Account? Login <a href="login.php">here</a></p>
                </div>
            </div>
        <?php } elseif ($_SESSION['step'] == 3) { ?>
            <div class="container" style="padding:20px; border-radius:20px;">
                <div class="header">
                    <img src="images/logo.png">
                    <h2>Your Date of Birth</h2>
                    <p>Who Knows? We might have a Suprise for you....</p>
                </div>
                <br><br>Date of Birth<br>
                <input type="date" name="date_of_birth" required max="<?php echo date('Y-m-d'); ?>">
                <button type="submit">Next</button>
                <div class="header">
                    <br>
                    <p>Already have an Account? Login <a href="login.php">here</a></p>
                </div>
            </div>

        <?php } elseif ($_SESSION['step'] == 4) { ?>
            <div class="container" style="<?php echo !empty($usernameError) ? 'padding-bottom: 40px;' : ''; ?>">
                <div class="header">
                    <h2>Create Username</h2>
                    <p>How would you want people to address you?</p>
                </div>
                <form method="post">
                    <br><br>Username<br>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    <?php if (!empty($usernameError)) { ?>
                        <p class="error-message"><?php echo $usernameError; ?></p>
                    <?php } ?>
                    <br>
                    <button type="submit">Register</button>
                    <br>
                </form>
                <div class="header">
                    <br>
                    <p>Already have an Account? Login <a href="login.php">here</a></p>
                </div>
            </div>
        <?php } ?>
    </form>
</body>

</html>