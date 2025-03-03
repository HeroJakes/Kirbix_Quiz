<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="in_style.css">

    <title>test</title>
</head>
<body>
    

    <!-- SIDEBAR -->
    <section id="sidebar" class="hide">
        <a href="#" class="brand">
            <img class="open-logo" src="K.png" style="width: 40px">
            <!-- <span class="text">Kirbix</span> -->
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="test.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-doughnut-chart' ></i>
                    <span class="text">Analytics</span>
                </a>
            </li>
            <li>
                <a href="QuizManager.php" require=".home">
                    <i class='bx bx-book-open icon' ></i>
                    <span class="text">Quiz Manager</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-message-dots' ></i>
                    <span class="text">Message</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-group' ></i>
                    <span class="text">Team</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <!-- <li>
                <a href="#">
                    <i class='bx bxs-cog' ></i>
                    <span class="text">Settings</span>
                </a>
            </li> -->
            <li>
                <a href="#" class="logout">
                    <i class='bx bx-log-out icon'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->


    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class="bx bx-menu"></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search.btn">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </form>
            <a href="#" class="notification">
                <i class="bx bxs-bell"></i>
                <span class="num">8</span>
            </a>
            <a href="#" class="profile">
                <img src="" alt="">
            </a>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Instructor Dashboard</a>
                        </li>
                        <li><i class="bx bx-chevron-right"></i></li>
                        <li>
                            <a class="active" href="#">Home</a>
                        </li>
                    </ul>
                </div>
                <div class="right">
                    <a href="#" class="btn top">
                        <i class='bx bx-plus-medical' ></i>
                        <span class="text">Create Quiz</span>
                    </a>
                    <a href="#" class="btn bottom">
                        <i class='bx bxs-cog' ></i>
                        <span class="text">Manage Quiz</span>
                    </a>
                </div>
                
            </div>

            <ul class="box-info">
                <li>
                    <img class="create" src="/in_img/print.png">
                    <span class="text">
                        <h3>13</h3>
                        <p class="TQC">Total Quizzes Created</p>
                    </span>
                </li>
                <li>
                    
                    <img class="cup" src="/in_img/cup.png">
                    <span class="text">
                        <h3>1020</h3>
                        <p>Average Student Performance</p>
                    </span>
                </li>
                <li>
                    <img class="quizIcon" src="/in_img/quiz.webp">
                    <span class="text">
                        <h3>10</h3>
                        <p>Active Quizzes</p>
                    </span>
                </li>
                <li>
                    <img class="deadline" src="/in_img/deadline.png">
                    <span class="text">
                        <h3>20</h3>
                        <p>Upcoming Quiz Deadlines</p>
                    </span>
                </li>
            </ul>


            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Recent Orders</h3>
                        <i class="bx bx-search"></i>
                        <i class="bx bx-filter"></i>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date Order</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="users1.png" width="35" height="35">
                                    <p>John Doe</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status completed">Completed</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="user2.png" width="35" height="35">
                                    <p>Jennie</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="user3.png" width="35" height="35">
                                    <p>Jane</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status process">Process</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="user4.png" width="35" height="35">
                                    <p>Michael</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status pending">Pending</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="user5.png" width="35" height="35">
                                    <p>Jessica</p>
                                </td>
                                <td>01-10-2021</td>
                                <td><span class="status completed">Completed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="todo">
                    <div class="head">
                        <h3>Todos</h3>
                        <i class="bx bx-plus"></i>
                        <i class="bx bx-filter"></i>
                    </div>
                    <ul class="todo-list">
                        <li class="completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                        <li class="not-completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                        <li class="completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                        <li class="not-completed">
                            <p>Todo List</p>
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </li>
                    </ul>
                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->
    
    <script src="in_dashboard.js"></script>
</body>
</html>