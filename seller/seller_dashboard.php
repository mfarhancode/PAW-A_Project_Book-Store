
<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/seller_dashboard.css">
</head>
<body>

<?php include "../partials/header.php"; ?>

<div class="dashboard-container">

    <h1>Seller Dashboard</h1>
    <h2>Welcome <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
    <hr>

    
    <a href="seller_books.php">See all your books</a><br>
    <a href="add_book.php">Add Books</a><br>
    <a href="report.php">Report</a><br>

</div>

<?php include "../partials/footer.php"; ?>

</body>
</html>
