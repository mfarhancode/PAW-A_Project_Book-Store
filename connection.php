<?php
$host = "HOSTNAME";
$user = "USERNAME";
$pass = "PASS";
$db   = "DB_NAME";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Connected to database successfully <br>";
?>
