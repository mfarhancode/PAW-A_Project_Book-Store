<?php
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

echo "<h1>Admin Page<h1>";
$name =  $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";




echo "<a href='../logout.php'>Logout</a>";

?>
