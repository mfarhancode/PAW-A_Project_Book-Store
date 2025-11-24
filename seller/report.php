<?php
require "../connection.php";
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$total_books   = 14500;
$total_pembeli  = 1200;
$total_penjualan = 435;
$total_pendapatan  = 68;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>seller Report</title>
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
            
            /* PENTING: Agar kursor berubah jadi tangan */
            cursor: pointer; 
        }

        /* Efek Hover agar user tahu ini bisa diklik */
        .card:hover {
            transform: translateY(-5px); /* Naik sedikit */
            background-color: #5b8de0;   /* Warna sedikit lebih gelap */
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        /* Efek saat diklik */
        .card:active {
            transform: scale(0.98); /* Sedikit mengecil saat ditekan */
        }

        .card-title { font-size: 18px; margin-bottom: 5px; font-weight: 500; }
        .card-value { font-size: 22px; font-weight: 400; }

    </style>
</head>
<body>

    <div class="dashboard-container">
        
        <div class="sidebar">
            <h2>Report</h2>
            <ul class="menu">
                <li><a href="dashboard.php" style="font-weight:bold;">Dashboard</a></li>
                <li><a href="books.php">See all your books</a></li>
                <li><a href="report.php">Add book</a></li>
                <li><a href="report.php">Report</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h2>REPORT</h2>

            <div class="cards-wrapper">
                
                <div class="card" onclick="location.href='report_buku.php'">
                    <div class="card-title">Buku</div>
                    <div class="card-value"><?php echo $total_books; ?></div>
                </div>

                <div class="card" onclick="location.href='report_pembeli.php'">
                    <div class="card-title">Pembeli</div>
                    <div class="card-value"><?php echo $total_pembeli; ?></div>
                </div>

                <div class="card" onclick="location.href='report_penjualan.php'">
                    <div class="card-title">Penjualan</div>
                    <div class="card-value"><?php echo $total_penjualan; ?></div>
                </div>

                <div class="card" onclick="location.href='report_pendapatan.php'">
                    <div class="card-title">Pnndapatan</div>
                    <div class="card-value"><?php echo $total_pendapatan; ?></div>
                </div>

            </div>
        </div>

    </div>

</body>
</html>