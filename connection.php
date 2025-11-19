<?php
$host = "kprikaryasehat.site";
$user = "kprikary_kuliah";
$pass = "kuliahkautsar2025";
$db   = "kprikary_kuliah";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Connected to database successfully <br>";
?>
