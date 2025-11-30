<?php
require "../connection.php";
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}


$id = "";
$nama = "";
$username = "";
$level = "";
$error_message = "";

if (isset($_POST['update'])) {

    $id = $_POST['id'];
    $nama = htmlspecialchars($_POST['nama']);
    $username = htmlspecialchars($_POST['username']);
    $level = htmlspecialchars($_POST['level']);


    $nama_db = mysqli_real_escape_string($conn, $nama);
    $username_db = mysqli_real_escape_string($conn, $username);
    $level_db = mysqli_real_escape_string($conn, $level);
    $id_db = (int)$id;


    $sql = "UPDATE bst_user SET 
            name = '$nama_db', 
            username = '$username_db', 
            level = '$level_db' 
            WHERE id = $id_db";

    if (mysqli_query($conn, $sql)) {
    
        header("Location: users_data.php");
        exit;
    } else {
        $error_message = "Error: Gagal mengupdate data. " . mysqli_error($conn);
    }
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "SELECT * FROM bst_user WHERE id = '$id' ";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama = $row['name'];
        $username = $row['username'];
        $level = $row['level'];
    } else {
        $error_message = "Data user tidak ditemukan.";
    }
} else if (!isset($_POST['update'])) {
    header("Location: users_data.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <h1>Edit Data User</h1>

    <?php if ($error_message): ?>
        <p style="color: red; font-weight:bold;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form action="" method="post">
        
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <label for="nama">Nama</label>
        <input type="text" name="nama" id="nama" value="<?php echo $nama; ?>" required><br>
        
        <label for="username">username</label>
        <input type="tel" name="username" id="username" value="<?php echo $username; ?>" required><br>
        
        <label for="level">level</label>
        <input type="text" name="level" id="level" value="<?php echo $level; ?>" required>
        
        <br><br>
        <button class="simpan" type="submit" name="update">Update Data</button>
        <button type="button" class="batal" onclick="location.href='users_data.php'">Batal</button>
    </form>
</body>
</html>