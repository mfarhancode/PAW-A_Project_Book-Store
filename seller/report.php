<?php
require "../connection.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil ID Seller dari Session
// Asumsi: Saat login, kamu menyimpan id user ke $_SESSION['user_id'] atau similar
$seller_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 

// --- LOGIKA QUERY DATA KHUSUS SELLER ---

// 1. Total Buku (Hanya milik seller ini)
// Pastikan tabel bst_books punya kolom 'seller_id' atau 'id_user'
$q_books = mysqli_query($conn, "SELECT COUNT(*) as total FROM bst_books WHERE seller_id = '$seller_id'");
$d_books = mysqli_fetch_assoc($q_books);
$total_books = $d_books['total'] ?? 0;

// 2. Total Buku Terjual (Penjualan)
// Join tabel sold_books dengan books untuk filter punya seller ini
$q_sold = mysqli_query($conn, "
    SELECT SUM(s.qty) as total_qty, SUM(s.price_at_sale) as total_duit
    FROM bst_sold_books s
    JOIN bst_books b ON s.ISBN = b.ISBN 
    WHERE b.seller_id = '$seller_id'
");
$d_sold = mysqli_fetch_assoc($q_sold);

$total_penjualan  = $d_sold['total_qty'] ?? 0; // Jumlah pcs buku terjual
$total_pendapatan = $d_sold['total_duit'] ?? 0; // Total uang

// 3. Total Pembeli (Unik)
$q_buyer = mysqli_query($conn, "
    SELECT COUNT(DISTINCT pay.buyer_id) as total_buyer
    FROM bst_sold_books s
    JOIN bst_books b ON s.ISBN = b.ISBN
    JOIN bst_payment_detail pay ON s.payment_id = pay.payment_id
    WHERE b.seller_id = '$seller_id'
");
$d_buyer = mysqli_fetch_assoc($q_buyer);
$total_pembeli = $d_buyer['total_buyer'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Report</title>
    <style>
        /* RESET & BASIC SETUP */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Agar footer selalu di bawah */
        }

        /* HEADER / NAVBAR */
        .navbar {
            background-color: #387bd6; /* Warna Biru Header */
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .brand {
            font-size: 18px;
            font-weight: 500;
        }

        .navbar .logout-link {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .navbar .logout-link:hover {
            text-decoration: underline;
        }

        /* MAIN CONTAINER */
        .container {
            width: 100%;
            max-width: 1200px; /* Lebar konten dimaksimalkan */
            margin: 40px auto;
            padding: 0 40px;
            flex: 1; /* Mengisi ruang kosong agar footer terdorong ke bawah */
        }

        /* TYPOGRAPHY */
        h1 {
            font-family: 'Times New Roman', Times, serif; /* Font Serif sesuai gambar */
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
        }

        h3 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin-bottom: 30px;
        }

        /* BUTTONS NAV (Tombol Navigasi Cepat) */
        .nav-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }

        .btn-nav {
            background-color: #387bd6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 14px;
            border-radius: 2px;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-nav:hover {
            background-color: #2a65b8;
        }

        /* REPORT CARDS GRID */
        .report-section {
            margin-top: 20px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
        }

        .card {
            background-color: #387bd6; /* Warna Biru Card */
            color: white;
            padding: 30px;
            border-radius: 5px; /* Radius sedikit tajam */
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
            background-color: #2a65b8;
        }

        .card-title {
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .card-value {
            font-size: 28px;
            font-weight: bold;
        }

        /* FOOTER */
        footer {
            background-color: #eeeeee;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
            color: #333;
            font-size: 14px;
        }

    </style>
</head>
<body>

    <header class="navbar">
        <div class="brand">Book Store</div>
        <a href="../logout.php" class="logout-link">Logout</a>
    </header>

    <div class="container">
        <h1>Seller Report</h1>
        <h3>Statistik Toko Anda</h3>
        <hr>

        <div class="nav-buttons">
            <a href="seller_dashboard.php" class="btn-nav">Back to Dashboard</a>
            <a href="seller_books.php" class="btn-nav">See all your books</a>
            <a href="add_book.php" class="btn-nav">Add Books</a>
        </div>

        <div class="report-section">
            <div class="cards-grid">
                
                <div class="card" onclick="location.href='report_penjualan.php'">
                    <div class="card-title">Penjualan</div>
                    <div class="card-value"><?= number_format($total_books); ?></div>
                </div>

                <div class="card" onclick="location.href='report_pendapatan.php'">
                    <div class="card-title">Total pendapatan</div>
                    <div class="card-value">Rp <?= number_format($total_pendapatan, 0, ',', '.');?></div>
                </div>

                <div class="card" onclick="location.href='report_buku.php'">
                    <div class="card-title">Buku Terjual</div>
                    <div class="card-value"><?= number_format($total_penjualan); ?> Pcs</div>
                </div>

                <div class="card" onclick="location.href='report_genre.php'">
                    <div class="card-title">genre</div>
                    <div class="card-value"> <?=$total_books?></div>
                </div>

            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 Book Store
    </footer>

</body>
</html>