<?php
session_start();
require "connection.php";


$username = $_POST['username'] ?? "";
$name = $_POST['name'] ?? "";
$password = $_POST['password'] ?? "";
$level = $_POST['level'] ?? "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($name)) $errors[] = 'Enter your name!';
    if (!empty($name) && strlen($name) < 3) $errors[] = 'Name must have 3 characters!';
    if (empty($username)) $errors[] = 'Username cannot be empty!';
    if (!empty($username) && strlen($username) < 5) $errors[] = 'Username must have at least 5 characters!';
    if (empty($password)) $errors[] = 'Password field cannot be empty!';
    if (!empty($password) && strlen($password) < 6) $errors[] = 'Password must have at least 6 characters!';
    if (empty($level)) $errors[] = 'Select the level!';
    
    if (empty($errors)){
            


    // bycrypt password 
    $pass = password_hash($password, PASSWORD_BCRYPT);

    // create account
    $sql = "INSERT INTO bst_user(level, name, username, password)
    VALUES('$level', '$name', '$username', '$pass')";

    
    if ($conn->query($sql) === TRUE) {
        // if query is excuted, redirect user to login page
        echo '<script>
            alert("Account Created Succesfully!\nRedirecting to Login page\nPlease Login")
            window.location.href = "login.php";
            </script>';
    } else {
        echo "Error: " . $conn->error;
    }
        }

}
?>



<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/register.css">
</head>
<body>

<div class="register-container">
    <div class="register-box">

        <h2>Create Account</h2>

        <?php foreach($errors as $error): ?>
            <p style="color:red"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">

            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">

            <label>Password:</label>
            <input type="password" name="password" value="<?= htmlspecialchars($password) ?>">
            <br>
            <label>Level:</label>
            <br>
            <select name="level">
                <option value="">--select--</option>
                <option value="seller" <?= $level=='seller' ? 'selected' : '' ?>>Seller</option>
                <option value="buyer" <?= $level=='buyer' ? 'selected' : '' ?>>Buyer</option>
            </select>

            <input type="submit" value="Register">
        </form>

        <p class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </p>

    </div>
</div>

</body>
</html>
