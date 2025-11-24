<?php

require "../connection.php";
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql="DELETE FROM bst_user WHERE id = $id";

    if (mysqli_query($conn,$sql)) {
    } else {
        echo "Error: Gagal menghapus data. " . mysqli_error($conn);
    }

}

header("Location: index.php");
exit;

?>