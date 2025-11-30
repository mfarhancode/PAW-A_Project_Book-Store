<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$id = intval($_GET['id']);
$action = $_GET['action'];

if ($action === "archive") {
    mysqli_query($conn, "UPDATE bst_books SET archived = 1 WHERE id = $id");
} else {
    mysqli_query($conn, "UPDATE bst_books SET archived = 0 WHERE id = $id");
}

header("Location: seller_books.php");
exit;
?>
