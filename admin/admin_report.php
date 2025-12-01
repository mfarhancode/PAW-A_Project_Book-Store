<?php
require "../connection.php";
session_start();


if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$query_seller = mysqli_query($conn, "SELECT COUNT(*) as total FROM bst_user WHERE level = 'seller'");
$data_seller  = mysqli_fetch_assoc($query_seller);
$total_user   = $data_seller['total']; 




$query_buyer  = mysqli_query($conn, "SELECT COUNT(*) as total FROM bst_user WHERE level = 'buyer'");
$data_buyer   = mysqli_fetch_assoc($query_buyer);
$total_sales  = $data_buyer['total'];

$query_genre  = mysqli_query($conn, "SELECT COUNT(DISTINCT category) as total FROM bst_books WHERE category IS NOT NULL AND category != ''");
$data_genre   = mysqli_fetch_assoc($query_genre);
$total_report = $data_genre['total']; 

$query_books  = mysqli_query($conn, "SELECT COUNT(*) as total FROM bst_books");
$data_books   = mysqli_fetch_assoc($query_books);
$total_books  = $data_books['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #bde0fe;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dashboard-container {
            width: 90%;
            max-width: 1000px;
            height: 80vh;
            background-color: #f8f9fe;
            display: flex;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background-color: #f8f9fe;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dcdcdc;
        }

        .menu { list-style: none; margin-top: 20px; }
        .menu li { margin-bottom: 20px; }
        .menu a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            display: block;
            transition: 0.3s;
        }
        .menu a:hover { color: #5351e0; font-weight: bold; }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            padding: 50px;
            background-color: #f8f9fe;
        }

        .main-content h2 {
            margin-bottom: 40px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 22px;
            letter-spacing: 1px;
        }

        /* STYLING KARTU */
        .cards-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 60px 100px;
        }

        .card {
            background-color: #6ca0f5;
            width: 220px;
            height: 120px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer; 
        }

        .card:hover {
            transform: translateY(-5px);
            background-color: #5b8de0;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .card:active {
            transform: scale(0.98);
        }

        .card-title { font-size: 18px; margin-bottom: 5px; font-weight: 500; }
        .card-value { font-size: 22px; font-weight: 400; }

    </style>
</head>
<body>

    <div class="dashboard-container">
        
        <div class="sidebar">
            <h2>Admin</h2>
            <ul class="menu">
                <li><a href="admin_report.php" style="font-weight:bold;">Dashboard</a></li>
                <li><a href="users_data.php">Manager User</a></li>
                <li><a href="all_books.php">All Book</a></li>
                <li><a href="report_books.php">Report</a></li> 
            </ul>
        </div>

        <div class="main-content">
            <h2><?php include "../partials/header.php";?></h2>

            <div class="cards-wrapper">
                
                <div class="card" onclick="location.href='report_penjual.php'">
                    <div class="card-title">Penjual</div>
                    <div class="card-value"><?= number_format($total_user); ?></div>
                </div>

                <div class="card" onclick="location.href='report_pembeli.php'">
                    <div class="card-title">Pembeli</div>
                    <div class="card-value"><?= number_format($total_sales); ?></div>
                </div>

                <div class="card" onclick="location.href='report_genre.php'">
                    <div class="card-title">Genre</div>
                    <div class="card-value"><?= number_format($total_report); ?></div>
                </div>

                <div class="card" onclick="location.href='report_books.php'">
                    <div class="card-title">Books</div>
                    <div class="card-value"><?= number_format($total_books); ?></div>
                </div>

            </div>
            <?php include "../partials/footer.php"; ?>
        </div>

    </div>

</body>
</html>
