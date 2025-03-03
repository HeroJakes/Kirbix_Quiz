<?php
session_start();

if (!isset($_SESSION['mySession'])) {
    echo "<script>alert('Please login!');window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['mySession'];
?>
