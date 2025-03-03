<?php
  // Database connection
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "kirbix"; 

  $conn = new mysqli($servername, $username, $password, $database);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  // Determine the filter (default to 'Approved')
  $filter = isset($_GET['filter']) ? $_GET['filter'] : 'Approved';

  // Fetch quizzes with quiz_type based on the filter
  if (isset($_GET['action']) && $_GET['action'] === 'fetchQuizDetails') {
    $quiz_id = intval($_GET['quiz_id']);
    $query = "
        SELECT 
            q.title, 
            q.description, 
            q.difficulty, 
            q.time_limit, 
            q.quiz_level, 
            q.quiz_type, 
            q.date_created, 
            u.username AS instructor_name 
        FROM quizzes q
        JOIN instructors i ON q.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        WHERE q.quiz_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quizDetails = $result->fetch_assoc();

    if ($quizDetails) {
        header('Content-Type: application/json');
        echo json_encode($quizDetails);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Quiz not found.']);
      }
      exit;
  }

  // Handle POST actions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $action = $_POST['action'];
      $quiz_id = $_POST['quiz_id'];

      if ($action === 'delete') {
          // Delete query
          $query = "DELETE FROM quizzes WHERE quiz_id = ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $quiz_id);
          $stmt->execute();
          echo "<script>alert('Quiz deleted successfully.'); window.location.href='content.php';</script>";
      } elseif ($action === 'approve') {
          // Approve query
          $query = "UPDATE quizzes SET quiz_status = 'Approved' WHERE quiz_id = ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $quiz_id);
          $stmt->execute();
          echo "<script>alert('Quiz approved successfully.'); window.location.href='content.php';</script>";
      } elseif ($action === 'reject') {
          // Reject query
          $query = "UPDATE quizzes SET quiz_status = 'Rejected' WHERE quiz_id = ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $quiz_id);
          $stmt->execute();
          echo "<script>alert('Quiz rejected successfully.'); window.location.href='content.php';</script>";
      }
  }

  // content.php: AJAX handler for fetching quiz details
  if (isset($_GET['action']) && $_GET['action'] === 'fetchQuizDetails') {
      $quiz_id = intval($_GET['quiz_id']);
      $query = "SELECT q.title, q.description, q.difficulty, q.date_created, q.quiz_type, u.username AS instructor_name 
                FROM quizzes q
                JOIN instructors i ON q.instructor_id = i.instructor_id
                JOIN users u ON i.user_id = u.user_id
                WHERE q.quiz_id = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("i", $quiz_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $quizDetails = $result->fetch_assoc();

      if ($quizDetails) {
          header('Content-Type: application/json');
          echo json_encode($quizDetails);
      } else {
          http_response_code(404);
          echo json_encode(['error' => 'Quiz not found.']);
      }
      exit;
  }

  if (isset($_GET['action']) && $_GET['action'] === 'fetchQuizQuestions') {
    $quiz_id = intval($_GET['quiz_id']);
    
    $quizTypeQuery = "SELECT quiz_type FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($quizTypeQuery);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $quizTypeResult = $stmt->get_result();
    $quizTypeRow = $quizTypeResult->fetch_assoc();
    $quizType = $quizTypeRow['quiz_type'];
    

    if ($quizType === 'T/F') {
      $query = "
          SELECT 
              q.content AS question_content, 
              q.answer_explanation, 
              a.answer_text AS correct_answer
          FROM questions q
          LEFT JOIN answers a ON q.question_id = a.question_id AND a.is_correct = 1
          WHERE q.quiz_id = ?
      ";
  } else {
      $query = "
          SELECT 
              q.content AS question_content, 
              q.answer_explanation, 
              a.answer_text AS correct_answer
          FROM questions q
          LEFT JOIN answers a ON q.question_id = a.question_id AND a.is_correct = 1
          WHERE q.quiz_id = ?
      ";
  }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
      $processed_content = str_replace(["\\n", "\\r"], ["\n", "\r"], $row['question_content']);

        $questions[] = [
          
            'content' => nl2br($processed_content),
            
            'answer_explanation' => $row['answer_explanation'],
            'correct_answer' => $row['correct_answer'] // Include the correct answer
        ];
    }

    if (!empty($questions)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'questions' => $questions
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No questions found for this quiz.']);
    }
    exit;
}

  // Pagination settings
  $rowsPerPage = 6; // Maximum number of quizzes per page
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $offset = ($page - 1) * $rowsPerPage;

  // Fetch quizzes for the current page
  $quizzesQuery = "
      SELECT q.quiz_id, q.title, COALESCE(q.quiz_type, 'N/A') AS quiz_type, 
             q.difficulty, q.date_created
      FROM quizzes q
      WHERE q.quiz_status = ?
      LIMIT ?, ?
  ";
  $stmt = $conn->prepare($quizzesQuery);
  $stmt->bind_param("sii", $filter, $offset, $rowsPerPage);
  $stmt->execute();
  $quizResult = $stmt->get_result();

  // Fetch the total number of quizzes matching the filter
  $totalQuery = "
      SELECT COUNT(DISTINCT q.quiz_id) as total
      FROM quizzes q
      WHERE q.quiz_status = ?
  ";
  $stmt = $conn->prepare($totalQuery);
  $stmt->bind_param("s", $filter);
  $stmt->execute();
  $totalResult = $stmt->get_result();
  $totalRow = $totalResult->fetch_assoc();
  $totalQuizzes = $totalRow['total'];

  // Calculate the total number of pages
  $totalPages = ceil($totalQuizzes / $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Content Management</title> 
  <script type="text/javascript" src="app.js" defer></script>
  <style>

    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    :root{
      --base-clr: #11121a;
      --line-clr: #42434a;
      --hover-clr: #222533;
      --text-clr: #e6e6ef;
      --accent-clr: #5e63ff;
      --secondary-text-clr: #b0b3c1;
    }
    *{
      margin: 0;
      padding: 0;
    }
    html{
      font-family: Poppins, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.5rem;
    }
    body{
      min-height: 100vh;
      min-height: 100dvh;
      background-color: var(--base-clr);
      color: var(--text-clr);
      display: grid;
      grid-template-columns: auto 1fr;
    }
    #sidebar{
      box-sizing: border-box;
      height: 100vh;
      width: 250px;
      padding: 5px 1em;
      background-color: var(--base-clr);
      border-right: 1px solid var(--line-clr);

      position: sticky;
      top: 0;
      align-self: start;
      transition: 300ms ease-in-out;
      overflow: hidden;
      text-wrap: nowrap;
    }
    #sidebar.close{
      padding: 5px;
      width: 60px;
    }
    #sidebar ul{
      list-style: none;
    }
    #sidebar > ul > li:first-child{
      display: flex;
      justify-content: flex-end;
      margin-bottom: 16px;
      .logo{
        font-weight: 600;
      }
    }
    #sidebar ul li.active a{
      color: var(--accent-clr);

      svg{
        fill: var(--accent-clr);
      }
    }

    #sidebar a, #sidebar .dropdown-btn, #sidebar .logo{
      border-radius: .5em;
      padding: .85em;
      text-decoration: none;
      color: var(--text-clr);
      display: flex;
      align-items: center;
      gap: 1em;
    }
    .dropdown-btn{
      width: 100%;
      text-align: left;
      background: none;
      border: none;
      font: inherit;
      cursor: pointer;
    }
    #sidebar svg{
      flex-shrink: 0;
      fill: var(--text-clr);
    }
    #sidebar a span, #sidebar .dropdown-btn span{
      flex-grow: 1;
    }
    #sidebar a:hover, #sidebar .dropdown-btn:hover{
      background-color: var(--hover-clr);
    }
    #sidebar .sub-menu{
      display: grid;
      grid-template-rows: 0fr;
      transition: 300ms ease-in-out;

      > div{
        overflow: hidden;
      }
    }
    #sidebar .sub-menu.show{
      grid-template-rows: 1fr;
    }
    .dropdown-btn svg{
      transition: 200ms ease;
    }
    .rotate svg:last-child{
      rotate: 180deg;
    }
    #sidebar .sub-menu a{
      padding-left: 2em;
    }
    #toggle-btn{
      margin-left: auto;
      padding: 1em;
      border: none;
      border-radius: .5em;
      background: none;
      cursor: pointer;

      svg{
        transition: rotate 150ms ease;
      }
    }
    #toggle-btn:hover{
      background-color: var(--hover-clr);
    }

    main{
      padding: min(30px, 7%);
    }
    main p{
      color: var(--secondary-text-clr);
      margin-top: 5px;
      margin-bottom: 15px;
    }
    .container{
      border: 1px solid var(--line-clr);
      border-radius: 1em;
      margin-bottom: 20px;
      padding: min(3em, 15%);

      h2, p { margin-top: 1em }
    }


    .logo-img{
      width: 200px;
      height: 50px;
      margin-top: 10px;
      margin-bottom: 30px;
    }

    .head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 60px;
    }

    .head .text {
      font-size: 40px;
      font-weight: 500;
      color: var(--text-color);
      padding: 12px 10px;
    }

    .head .profile{
      display: flex;
      justify-content: space-between;
      gap: 20px;
      margin-right: 4%;
    }

    .head .profile img {
      width: 60px;
      height: 60px;
    }

    .profile-text p{
      margin-bottom: 0px;
      margin-top: 1px;
    }


    @media(max-width: 800px){
      body{
        grid-template-columns: 1fr;
      }
      main{
        padding: 2em 1em 60px 1em;
      }
      .container{
        border: none;
        padding: 0;
      }
      
      #sidebar{
        height: 60px;
        width: 100%;
        border-right: none;
        border-top: 1px solid var(--line-clr);
        padding: 0;
        position: fixed;
        top: unset;
        bottom: 0;

        > ul{
          padding: 0;
          display: grid;
          grid-auto-columns: 60px;
          grid-auto-flow: column;
          align-items: center;
          overflow-x: scroll;
        }
        ul li{
          height: 100%;
        }
        ul a, ul .dropdown-btn{
          width: 60px;
          height: 60px;
          padding: 0;
          border-radius: 0;
          justify-content: center;
        }

        ul li span, ul li:first-child, .dropdown-btn svg:last-child{
          display: none;
        }

        ul li .sub-menu.show{
          position: fixed;
          bottom: 60px;
          left: 0;
          box-sizing: border-box;
          height: 60px;
          width: 100%;
          background-color: var(--hover-clr);
          border-top: 1px solid var(--line-clr);
          display: flex;
          justify-content: center;

          > div{
            overflow-x: auto;
          }
          li{
            display: inline-flex;
          }
          a{
            box-sizing: border-box;
            padding: 1em;
            width: auto;
            justify-content: center;
          }
        }
      }
    }



       .filter-buttons {
        margin: 20px 0;
    }

    .filter-buttons button {
        margin-right: 10px;
        padding: 10px 20px;
        background-color: #007BFF;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .filter-buttons button.active {
        background-color: #0056b3;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
  }

    table th, table td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: left;
    }

    table th {
        background-color: #f4f4f4;
        color: rgb(85, 85, 85);
    }

    .filter-buttons {
        margin-bottom: 30px;
    }

    .filter-buttons a {
        padding: 10px 20px;
        margin-right: 10px;
        background-color: #f1f1f1;
        border: 1px;
        text-decoration: none;
        color: #333;
        border-radius: 5px;
    }

    .filter-buttons a.active {
        background-color: #007bff;
        color: #fff;
    }

    button {
        padding: 5px 10px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .view-btn {
        background-color: #007bff;
        color: white;
        padding: 10px 30px;
        margin-right: 10px;
    }

    .view-btn:hover {
        background-color: #0056b3;
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
        padding: 10px 30px;
    }

    .approve-btn {
        background-color: #28a745;
        color: white;
        padding: 10px 30px;
    }

    .approve-btn:hover {
        background-color: #218838;
    }

    .reject-btn {
        background-color: #dc3545;
        color: white;
        padding: 10px 30px;
        margin-left: 10px;
    }

    .reject-btn:hover {
        background-color: #c82333;
    }

    #quizDetailsModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      color: black;
      max-width: 800px;
      text-align: left;
      display: flex;
      flex-direction: column;
    }

    .modal-layout {
      display: flex;
      gap: 20px;
    }

    .quiz-details,
    .question-details {
      flex: 1;
      background-color: #ffffff; /* Pure white background for clarity */
      padding: 20px;
      border-radius: 8px;
      border: 1px solid #ddd; /* Optional border for visual separation */
    }

    .modal-content h2 {
        color: black;
    }

    .quiz-details h2,
    .quiz-details p,
    .question-details p,
    .question-details h3 {
      margin-bottom: 15px;
      color: #000; /* Ensure headings are black */
    }

    .quiz-details p, .question-details p {
      font-size: 13px;
    }

    .modal-buttons button {
        margin: 10px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    #viewMoreBtn {
        background-color: #007bff;
        color: white;
    }

    #closeModalBtn {
        background-color: #dc3545;
        color: white;
    }

    .pagination {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }

    #paginationControls {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

      .page-link {
          padding: 10px 15px;
          margin: 0 5px;
          text-decoration: none;
          border: 1px solid #ddd;
          color: #007bff;
          border-radius: 5px;
          transition: background-color 0.3s;
      }

      .page-link.active {
          background-color: #007bff;
          color: #fff;
          border-color: #007bff;
      }

      .page-link:hover {
          background-color: #0056b3;
          color: #fff;
      }

    @media (max-width: 768px) {

      table {
        display: block;
        border: 0;
        margin: 0 auto; /* Center the table horizontally */
        width: 90%; /* Optional: adjust this width */
      }

      table thead {
        display: none;
      }

      table tr {
        display: block;
        margin-bottom: 15px;
        margin-left: 30px;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 20px;
        padding-right:30px;
        background-color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      table td {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;  
        border: none;
        border-bottom: 1px solid #ddd;
        color:black;
      }

      table td:last-child {
        border-bottom: 0;
      }

      table td::before {
        content: attr(data-label); 
        font-weight: bold;
        color: #333;
        width: 50%;
        display: inline-block;
        text-align: left;
      }

      .action-btn {
        width: 48%; /* Adjust button width for mobile */
        text-align: center;
        padding: 8px;
      }

      .edit-btn,
      .delete-btn {
        width: 100%; /* Full-width buttons */
      }

      .pagination {
        flex-wrap: wrap;
        justify-content: center; /* Center pagination */
        font-size: 14px;
      }

      .profile img {
        width: 40px;
        height: 40px; /* Smaller profile picture */
      }

      .modal-layout {
        flex-direction: column;
      }
        .filter-buttons button, .filter-buttons a {
            margin-right: 6px;
            padding: 6px 12px;
            font-size: 12px;
        }

        table th, table td {
            padding: 6px;
        }

        .modal-content {
            width: 90%;
        }



        .page-link {
          padding: 10px 15px;
          margin: 0 5px;
          text-decoration: none;
          border: 1px solid #ddd;
          color: #007bff;
          border-radius: 5px;
          transition: background-color 0.3s;
        }

        .page-link.active {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
      }

      .page-link:hover {
        background-color: #0056b3;
        color: #fff;
      }

        .view-btn, .delete-btn, .approve-btn, .reject-btn {
            padding: 6px 18px;
            font-size: 12px;
        }
    }

    #prevPageBtn,
    #nextPageBtn {
      background-color: #0056b3;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    #prevPageBtn[disabled] {
      background-color: #ccc;
      cursor: not-allowed;
    }

    #questionsContainer{
      color:black;
    }
  
  </style>
</head>
<body>
  <nav id="sidebar">
      <ul>
        <li>
          <img class="logo-img" src="Images/logo.png" alt="Kirbix">
          <button onclick=toggleSidebar() id="toggle-btn">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z" />
            </svg>
          </button>
        </li>
        <li>
          <a href="dashboard.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M520-600v-240h320v240H520ZM120-440v-400h320v400H120Zm400 320v-400h320v400H520Zm-400 0v-240h320v240H120Zm80-400h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z" />
            </svg>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="user.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm560 40-12-60q-12-5-22.5-10.5T584-204l-58 18-40-68 46-40q-2-14-2-26t2-26l-46-40 40-68 58 18q11-8 21.5-13.5T628-460l12-60h80l12 60q12 5 22.5 11t21.5 15l58-20 40 70-46 40q2 12 2 25t-2 25l46 40-40 68-58-18q-11 8-21.5 13.5T732-180l-12 60h-80Zm40-120q33 0 56.5-23.5T760-320q0-33-23.5-56.5T680-400q-33 0-56.5 23.5T600-320q0 33 23.5 56.5T680-240ZM400-560q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Z" />
            </svg>
            <span>User Management</span>
          </a>
        </li>
        
        <li class="active">
          <a href="content.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M360-240q-33 0-56.5-23.5T280-320v-480q0-33 23.5-56.5T360-880h360q33 0 56.5 23.5T800-800v480q0 33-23.5 56.5T720-240H360Zm0-80h360v-480H360v480ZM200-80q-33 0-56.5-23.5T120-160v-560h80v560h440v80H200Zm160-240v-480 480Z" />
            </svg>
            <span>Content</span>
          </a>
        </li>
        <li>
          <a href="performance.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M120-120v-80l80-80v160h-80Zm160 0v-240l80-80v320h-80Zm160 0v-320l80 81v239h-80Zm160 0v-239l80-80v319h-80Zm160 0v-400l80-80v480h-80ZM120-327v-113l280-280 160 160 280-280v113L560-447 400-607 120-327Z" />
            </svg>
            <span>Performance</span>
          </a>
        </li>
        <li>
          <a href="access.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M824-120 636-308q-41 32-90.5 50T440-240q-90 0-162.5-44T163-400h98q34 37 79.5 58.5T440-320q100 0 170-70t70-170q0-100-70-170t-170-70q-94 0-162.5 63.5T201-580h-80q8-127 99.5-213.5T440-880q134 0 227 93t93 227q0 56-18 105.5T692-364l188 188-56 56ZM397-400l-63-208-52 148H80v-60h160l66-190h60l61 204 43-134h60l60 120h30v60h-67l-47-94-50 154h-59Z" />
            </svg>
            <span>Access Logs</span>
          </a>
        </li>
        <li>
          <a href="support.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z" />
            </svg>
            <span>Support</span>
          </a>
        </li>

        <li>
          <a href="../login.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
              <path
                d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h280v80H200Zm440-160-55-58 102-102H360v-80h327L585-622l55-58 200 200-200 200Z" />
            </svg>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </nav>

    <main>
      <div class="head">
        <div class="text">Content Management</div>
        <div class="icons">
          <i class='bx bx-bell'></i>
        </div>
        <div class="profile">
          <img src="Images/profile.png" alt="Profile Picture" class="bx bx-user-circle">
          <div class="profile-text">
            <p>Ivan Lee</p>
            <p>Administrator</p>
          </div>
        </div>
      </div>


      <div class="content-management">
        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="?filter=Approved" class="filter-btn <?php echo $filter === 'Approved' ? 'active' : ''; ?>">Approved</a>
            <a href="?filter=Pending" class="filter-btn <?php echo $filter === 'Pending' ? 'active' : ''; ?>">Pending</a>
        </div>

        <!-- Quiz Table -->
        <table id="quizTable">
            <thead>
                <tr>
                    <th>Quiz ID</th>
                    <th>Title</th>
                    <th>Question Type</th>
                    <th>Difficulty</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($quizResult->num_rows > 0): ?>
                    <?php while ($row = $quizResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['quiz_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['quiz_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['difficulty']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                            <td>
                                <?php if ($filter === 'Approved'): ?>
                                    <button class="view-btn" data-quiz-id="<?php echo $row['quiz_id']; ?>">View</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="quiz_id" value="<?php echo $row['quiz_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</button>
                                    </form>
                                <?php elseif ($filter === 'Pending'): ?>
                                    <button class="view-btn" data-quiz-id="<?php echo $row['quiz_id']; ?>">View</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="quiz_id" value="<?php echo $row['quiz_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="approve-btn" onclick="return confirm('Are you sure you want to approve this quiz?');">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="quiz_id" value="<?php echo $row['quiz_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="reject-btn" onclick="return confirm('Are you sure you want to reject this quiz?');">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No quizzes found for the selected status.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>" class="page-link">&laquo; Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>" class="page-link">Next &raquo;</a>
        <?php endif; ?>
    </div>

    

    <!-- Modal -->
    <div id="quizDetailsModal" style="display:none;">
      <div class="modal-content">
        <div class="modal-layout">
          <!-- Left Section: Quiz Details -->
          <div class="quiz-details">
            <h2>Quiz Details</h2>
            <p><strong>Instructor Name:</strong> <span id="instructorName"></span></p>
            <p><strong>Quiz Title:</strong> <span id="quizTitle"></span></p>
            <p><strong>Description:</strong> <span id="quizDescription"></span></p>
            <p><strong>Question Type:</strong> <span id="quiz_type"></span></p>
            <p><strong>Difficulty Level:</strong> <span id="difficultyLevel"></span></p>
            <p><strong>Time Limit:</strong> <span id="timeLimit"></span> minutes</p>
            <p><strong>Quiz Level:</strong> <span id="quizLevel"></span></p>
            <p><strong>Date Created:</strong> <span id="dateCreated"></span></p>
          </div>
          <!-- Right Section: Question Details -->
          <div class="question-details">
            <h3>Question Details</h3>
            <div id="questionsContainer"></div>
            <div id="paginationControls" style="display: none;">
              <button id="prevPageBtn" disabled>Previous</button>
              <span id="pageIndicator"></span>
              <button id="nextPageBtn">Next</button>
            </div>
          </div>
        </div>
        <div class="modal-buttons">
          <button id="viewMoreBtn">View More</button>
          <button id="closeModalBtn">Close</button>
        </div>
      </div>
    </div>


  </main>
  <?php
  // Close the database connection
  $conn->close();
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
      const viewButtons = document.querySelectorAll('.view-btn');
      const modal = document.getElementById('quizDetailsModal');
      const closeModalBtn = document.getElementById('closeModalBtn');
      const viewMoreBtn = document.getElementById('viewMoreBtn');
      const quizDescription = document.getElementById('quizDescription');
      const instructorName = document.getElementById('instructorName');
      const quizTitle = document.getElementById('quizTitle');
      const questionType = document.getElementById('quiz_type');
      const difficultyLevel = document.getElementById('difficultyLevel');
      const dateCreated = document.getElementById('dateCreated');
      let currentQuizId = null; // Store the currently viewed quiz ID
      let quizQuestions = []; // Store questions
      let currentPage = 1;
      const itemsPerPage = 1;

      // Function to fetch and display quiz details
      function fetchQuizDetails(quizId) {
          currentQuizId = quizId; // Update current quiz ID
          resetViewMoreSection();
          fetch(`content.php?action=fetchQuizDetails&quiz_id=${quizId}`)
              .then(response => {
                  if (!response.ok) throw new Error('Network response was not ok');
                  return response.json();
              })
              .then(data => {
                  if (!data.error) {
                      instructorName.textContent = data.instructor_name;
                      quizTitle.textContent = data.title;
                      quizDescription.textContent = data.description || 'N/A';
                      questionType.textContent = data.quiz_type || 'N/A';
                      difficultyLevel.textContent = data.difficulty;
                      document.getElementById('timeLimit').textContent = data.time_limit || 'N/A';
                      document.getElementById('quizLevel').textContent = data.quiz_level || 'N/A';
                      dateCreated.textContent = data.date_created;

                      modal.style.display = 'flex';
                  } else {
                      alert(data.error);
                  }
              })
              .catch(error => console.error('Error fetching quiz details:', error));
      }

      // Function to fetch and display questions for the current quiz
      function fetchQuestions(quizId, page = 1) {
        fetch(`content.php?action=fetchQuizQuestions&quiz_id=${quizId}&page=${page}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (!data.error) {
                    quizQuestions = data.questions || [];
                    currentPage = page;
                    displayQuestions(currentPage);
                    setupPaginationControls();
                    document.getElementById('viewMoreSection').style.display = 'block';
                } else {
                    alert('No questions found.');
                }
            })
            .catch(error => console.error('Error fetching quiz questions:', error));
    }

      // Function to display questions
      function displayQuestions(page) {
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const questionsToShow = quizQuestions.slice(startIndex, endIndex);

        const container = document.getElementById('questionsContainer');
        container.innerHTML = ''; // Clear previous questions


        questionsToShow.forEach(question => {
            const questionDiv = document.createElement('div');
            questionDiv.classList.add('question');
            questionDiv.innerHTML = `
                <p><strong>Question:</strong> ${question.content}</p>
                <p><strong>Answer:</strong> ${question.correct_answer || 'N/A'}</p>
                <p><strong>Instructor's Explanation:</strong> ${question.answer_explanation || 'N/A'}</p>
            `;
            container.appendChild(questionDiv);
        });
    }

      function resetViewMoreSection() {
        const viewMoreSection = document.getElementById('viewMoreSection'); 
        const questionsContainer = document.getElementById('questionsContainer'); 
        const paginationControls = document.getElementById('paginationControls'); 

        if (viewMoreSection) viewMoreSection.style.display = 'none'; 
        if (questionsContainer) questionsContainer.innerHTML = ''; // Clear questions
        if (paginationControls) paginationControls.style.display = 'none'; // Hide pagination
        quizQuestions = []; // Clear stored questions
        currentPage = 1; // Reset pagination
    }

      // Function to setup pagination controls
      function setupPaginationControls() {
          const paginationControls = document.getElementById('paginationControls');
          const prevPageBtn = document.getElementById('prevPageBtn');
          const nextPageBtn = document.getElementById('nextPageBtn');
          const pageIndicator = document.getElementById('pageIndicator');
          const totalPages = Math.ceil(quizQuestions.length / itemsPerPage);

          paginationControls.style.display = quizQuestions.length > itemsPerPage ? 'block' : 'none';
          pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
          prevPageBtn.disabled = currentPage === 1;
          nextPageBtn.disabled = currentPage === totalPages;

          prevPageBtn.onclick = () => {
              if (currentPage > 1) {
                  currentPage--;
                  displayQuestions(currentPage);
                  setupPaginationControls();
              }
          };

          nextPageBtn.onclick = () => {
              if (currentPage < totalPages) {
                  currentPage++;
                  displayQuestions(currentPage);
                  setupPaginationControls();
              }
          };
      }

      // Event listener for "View More" button
      viewMoreBtn.addEventListener('click', function () {
          fetchQuestions(currentQuizId);
      });

      // Event listener for "Close" button
      closeModalBtn.addEventListener('click', function () {
          modal.style.display = 'none';
          resetViewMoreSection();
          document.getElementById('viewMoreSection').style.display = 'none';
          const questionsContainer = document.getElementById('questionsContainer');
          questionsContainer.innerHTML = ''; // Clear previous content
          currentPage = 1;
      });

      // Attach event listeners to view buttons
      viewButtons.forEach(button => {
          button.addEventListener('click', function () {
              const quizId = this.dataset.quizId;
              fetchQuizDetails(quizId);
          });
      });
  });
  </script>
</body>
</html>