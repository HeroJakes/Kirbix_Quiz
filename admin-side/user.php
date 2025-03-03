<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kirbix";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination settings
$rowsPerPage = 6; // Maximum number of rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

// Query to get the total number of users
$totalQuery = "SELECT COUNT(*) as total FROM users";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalUsers = $totalRow['total'];

// Calculate the total number of pages
$totalPages = ceil($totalUsers / $rowsPerPage);

// Query to fetch users for the current page
$sql = "SELECT user_id, username, email, role, status FROM users LIMIT $offset, $rowsPerPage";
$result = $conn->query($sql);

?>

<?php if (isset($_GET['success'])): ?>
  <script type="text/javascript">
    alert("User deleted successfully.");
  </script>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management</title>
  <script type="text/javascript" src="app.js" defer></script>
  <script type="text/javascript">
  function confirmDelete(userId) {
    // Show a confirmation dialog
    const isConfirmed = confirm("Are you sure you want to delete this user?");
    
    // If the user confirms, redirect to the delete.php page
    if (isConfirmed) {
      window.location.href = "delete.php?user_id=" + userId;
    }
  }
</script>

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




  /* For mobile phones */
  @media (max-width: 768px) {

    .profile img {
      width: 40px;
      height: 40px; /* Smaller profile picture */
    }

  }


  .add-user-btn {
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
  }

  .add-user-btn:hover {
    background-color: #0056b3;
  }

  /* User Table */
  .user-table-container {
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    flex-direction: column;
    margin: 20px auto; /* Center the container itself */
    width: 100%;
  }


  .user-table {
    width: 100%; /* Default full width */
    border-collapse: collapse;
    margin-top: 10px;
  }

  .user-table thead {
    background-color: #007bff;
  }


  .user-table th,
  .user-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    word-wrap: break-word;
  }

  .user-table th {
    color: #fff;
    background-color: #007bff;
    font-weight: bold;
  }


  .user-table td {
    color: #333;
    background-color: white;
  }


  

  .action-btn {
    padding: 8px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
  }

  .edit-btn {
    background-color: #ffc107;
    color: #fff;
    margin-right: 5px;
  }

  .edit-btn:hover {
    background-color: #e0a800;
  }

  .delete-btn {
    background-color: #dc3545;
    color: #fff;
  }

  .delete-btn:hover {
    background-color: #b02a37;
  }

  .pagination {
          display: flex;
          justify-content: center;
          margin: 20px 0;
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

  .success-message {
    color: green;
    font-size: 16px;
    margin-bottom: 10px;
  }

  @media (max-width: 768px) {
    .user-table {
      display: block;
      border: 0;
      margin: 0 auto; /* Center the table horizontally */
      width: 70%; /* Optional: you can adjust this width */
    }

    .user-table thead {
      display: none; /* Hide headers */
    }

    .user-table tr {
      display: block;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 20px;
      background-color: white;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .user-table td {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border: none;
      border-bottom: 1px solid #ddd;
    }

    .user-table td:last-child {
      border-bottom: 0;
    }

    .user-table td::before {
      content: attr(data-label); 
      font-weight: bold;
      color: #333;
      width: 50%;
      display: inline-block;
      text-align: left;
    }

    .action-btn {
      width: 48%; /* Button width adjustment for mobile */
      text-align: center;
      padding: 8px;
    }

    .edit-btn,
    .delete-btn {
      width: 100%; /* Full-width buttons on small screens */
    }

    .pagination {
      flex-wrap: wrap;
      justify-content: center; /* Center pagination */
      font-size: 14px;
    }
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
      <li class="active">
        <a href="user.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
            <path
              d="M400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm560 40-12-60q-12-5-22.5-10.5T584-204l-58 18-40-68 46-40q-2-14-2-26t2-26l-46-40 40-68 58 18q11-8 21.5-13.5T628-460l12-60h80l12 60q12 5 22.5 11t21.5 15l58-20 40 70-46 40q2 12 2 25t-2 25l46 40-40 68-58-18q-11 8-21.5 13.5T732-180l-12 60h-80Zm40-120q33 0 56.5-23.5T760-320q0-33-23.5-56.5T680-400q-33 0-56.5 23.5T600-320q0 33 23.5 56.5T680-240ZM400-560q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Z" />
          </svg>
          <span>User Management</span>
        </a>
      </li>
      <li>
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
      <div class="text">User Management</div>
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

    <div class="user-table-container">
      <table class="user-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                  <a href="editform.php?user_id=<?php echo $row['user_id']; ?>" class="action-btn edit-btn">Edit</a>
                  <button class="action-btn delete-btn" data-id="<?php echo $row['user_id']; ?>" onclick="confirmDelete(<?php echo $row['user_id']; ?>)">Delete</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">No users found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination Controls -->
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>" class="page-link">&laquo; Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="page-link <?php if ($i == $page) echo 'active'; ?>">
          <?php echo $i; ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next &raquo;</a>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <?php
  // Close the database connection
  $conn->close();
  ?>


</body>

</html>