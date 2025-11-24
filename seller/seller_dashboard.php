<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

echo "<h1>Seller Dashboard<h1>";
$name =  $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";
echo "<hr>";

echo "<a href='report.php'>Report</a>";
echo "<br><br>";
echo "<a href='seller_books.php'>See all your books</a>";
echo "<br><br>";
echo "<a href='add_book.php'>Add books</a>";

echo "<hr>";
echo "<a href='../logout.php'>Logout</a>";
?>