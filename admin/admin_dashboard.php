<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

echo "<h1>Admin Page<h1>";
$name =  $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";

require "../connection.php";

// Total user
$qUser = $conn->query("SELECT COUNT(*) AS total FROM bst_user");
$userCount = $qUser->fetch_assoc()['total'];

// Total books
$qBooks = $conn->query("SELECT COUNT(*) AS total FROM bst_books");
$booksCount = $qBooks->fetch_assoc()['total'];

// Total sales (jumlah buku terjual)
$qSales = $conn->query("SELECT COALESCE(SUM(qty),0) AS total FROM bst_sold_books");
$salesCount = $qSales->fetch_assoc()['total'];

// Total report (jumlah transaksi)
$qReport = $conn->query("SELECT COUNT(*) AS total FROM bst_payment_detail");
$reportCount = $qReport->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
}

body {
    background: #f0f2f5;
    color: #333;
}

/* HEADER */
.header {
    background: #4a4aee;
    padding: 20px;
    text-align: center;
    color: white;
}

.header h1 {
    font-size: 32px;
    font-weight: bold;
}

.header h2 {
    font-size: 20px;
    margin-top: 6px;
}

/* NAVBAR */
.navbar {
    width: 200px;
    background: #ffffff;
    padding: 20px;
    border-right: 1px solid #ddd;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    margin-top: 100px;
}

.navbar a {
    display: block;
    margin-bottom: 15px;
    text-decoration: none;
    color: #333;
    font-weight: 600;
    padding: 8px 10px;
    border-radius: 6px;
    transition: 0.3s;
}

.navbar a:hover {
    background: #4a4aee;
    color: #fff;
}

/* CONTENT */
.content {
    margin-left: 240px;
    padding: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

/* CARD STYLE */
.content div {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    text-align: center;
    transition: 0.3s ease;
}

.content div:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.20);
}

.content h3 {
    font-size: 18px;
    color: #555;
    margin-bottom: 8px;
}

.content p {
    font-size: 30px;
    font-weight: bold;
    color: #4a4aee;
}

/* LOGOUT BUTTON */
a[href*='logout'] {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 18px;
    background: #e63946;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: 0.3s;
}

a[href*='logout']:hover {
    background: #c92b37;
}

/* FOOTER */
footer {
    text-align: center;
    padding: 15px;
    color: #666;
    margin-top: 40px;
}
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin</h1>
        <h2>DASHBOARD</h2>
    </div>
    <div class="navbar">
        <a href="#">Dashboard</a><br>
        <a href="users_data.php">Manage Users</a><br>
        <a href="all_books.php">All Book</a><br>
        <a href="admin_report.php">Report</a><br>
    </div>
    <section id="content" class="content">
        <div class="user">
            <h3>User</h3>
            <p><?= $userCount ?></p>
        </div>

        <div class="sales">
            <h3>Sales</h3>
            <p><?= $salesCount ?></p>
        </div>

        <div class="report">
            <h3>Report</h3>
            <p><?= $reportCount ?></p>
        </div>

        <div class="book">
            <h3>Books</h3>
            <p><?= $booksCount ?></p>
        </div>
    </section>
    <?php echo "<a href='../logout.php'>Logout</a>";?>
    <footer>
        <p>&copy;</p>
    </footer>
</body>
</html>
