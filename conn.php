<?php
$con = mysqli_connect("localhost", "root", "", "kirbix");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
