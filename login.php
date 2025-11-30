
<!DOCTYPE html>
<head>
  <link rel="stylesheet" href="assets/login.css">
</head>
<body>
 <!-- <h1>Login Page!</h1>
 <form method="post">
    <label>Username:</label>
    <input type="text" name="username" required>
    <br>
    <label>Password:</label>
    <input type="password" name="password">
    <br>
    <input type="submit" value="Submit">
 </form>     -->
 <div class="login-container">
    <div class="login-box">
        <h2>Welcome to Book Store</h2>
        <p>Please login to continue</p>

        <form method="post">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Login">
        </form>

        <p class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>
</body>
</html>

<?php

session_start();
require "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = htmlspecialchars($_POST['username']) ?? "";
  $password = htmlspecialchars($_POST['password']) ?? "";

  $q = "SELECT * FROM bst_user WHERE username = '$username'";
  $data = $conn->query($q);
  if ($data->num_rows == 0) {
        echo "<p style='color:red'>No data found with this username.</p>";
    } else {
      $user_data = mysqli_fetch_assoc($data);
      // echo $password;
      if (password_verify($password, $user_data['password'])) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['name'] = $user_data['name'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['level'] = $user_data['level'];
            $_SESSION['id'] = $user_data['id'];

            if ($user_data['level'] == 'seller'){
            header("Location: seller/seller_dashboard.php");
            exit;
            }elseif($user_data['level'] == 'admin'){
            header("Location: admin/admin_dashboard.php");
            exit;
            }elseif($user_data['level'] == 'buyer'){
            header("Location: buyer/products.php");
            exit;
            }
            
      } else {
                echo "<p style='color:red'>Wrong Password</p>";
      }
        // $name = $user_data['name'];
        // echo "<p>Welcome $name</p>";
    }
}
?>

<!-- <p>Don't have an account yet? <a href="register.php"> Create account</a></p> -->

