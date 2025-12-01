<?php 
require "../connection.php";
session_start();

// 1. Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Ambil ID Seller dari Session
// Pastikan nama session sesuai dengan saat login (misal: user_id atau id)
$seller_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 

// 3. Filter Tanggal (Default: Awal bulan ini s/d Hari ini)
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- QUERY UTAMA: PENJUALAN PER BUKU (KHUSUS SELLER INI) ---
// Logika: 
// 1. Ambil data dari bst_sold_books (sold)
// 2. Join ke bst_books (b) pakai ISBN -> Untuk ambil Judul & Filter Seller ID
// 3. Join ke bst_payment_detail (pay) -> Untuk filter Tanggal
$query = "
    SELECT 
        b.judul as judul_buku,
        SUM(sold.qty) as total_qty,
        SUM(sold.price_at_sale) as total_pendapatan
    FROM 
        bst_sold_books sold
    JOIN 
        bst_books b ON sold.ISBN = b.ISBN
    JOIN 
        bst_payment_detail pay ON sold.payment_id = pay.payment_id
    WHERE
        b.seller_id = '$seller_id'  -- HANYA DATA SELLER INI
        AND DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    GROUP BY 
        b.judul -- Kelompokkan berdasarkan Judul Buku
    ORDER BY 
        total_qty DESC -- Urutkan dari yang paling laku
";

$execute = mysqli_query($conn, $query);

if(!$execute) {
    die("Error Query: " . mysqli_error($conn));
}

$rekap_penjualan = mysqli_fetch_all($execute, MYSQLI_ASSOC);

// --- PERSIAPAN DATA CHART ---
$labels_buku = [];
$data_qty = [];

// --- PERSIAPAN DATA TOTAL ---
$grand_total_qty = 0;
$grand_total_rp = 0;

foreach($rekap_penjualan as $val){
    // Potong judul jika kepanjangan (opsional, biar grafik rapi)
    $judul_pendek = strlen($val['judul_buku']) > 20 ? substr($val['judul_buku'], 0, 20) . '...' : $val['judul_buku'];
    
    $labels_buku[] = $judul_pendek;       // Sumbu X
    $data_qty[]    = $val['total_qty'];   // Sumbu Y

    // Hitung total bawah
    $grand_total_qty += $val['total_qty'];
    $grand_total_rp += $val['total_pendapatan'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Buku</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; }
        .container { max-width: 1000px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        /* Header Style */
        .header-top { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .header-top h2 { margin: 0; color: #333; }
        .btn-back { text-decoration: none; color: #555; font-weight: bold; display: flex; align-items: center; font-size: 16px; transition: 0.3s; }
        .btn-back:hover { color: #387bd6; }
        .icon-arrow { font-size: 24px; margin-right: 8px; }

        /* Table Style */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #387bd6; color: white; padding: 12px; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 12px; color: #333; }
        tr:hover { background-color: #f1f1f1; }

        /* Filter & Print */
        .filter-form { background: #eef2f7; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        input[type="date"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; background-color: #387bd6; color: white; font-weight: bold; }
        button:hover { background-color: #2a65b8; }
        
        /* Total Box */
        .total-box { display: flex; justify-content: space-around; padding: 20px; background-color: #e6f0ff; border: 1px solid #b3d1ff; margin-top: 30px; border-radius: 5px; }
        .total-item { font-size: 18px; color: #004085; }
        .total-item b { font-size: 22px; }

        @media print{
            .no-print, .filter-form { display: none; }
            .container { box-shadow: none; margin: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="container">
    
    <div class="header-top">
        <a href="report.php" class="btn-back">
            <i class="fas fa-arrow-left icon-arrow"></i> BACK
        </a>
        <h2>Laporan Penjualan (Per Buku)</h2>
    </div>

    <div class="filter-form no-print">
        <form method="GET" action="">
            <label>Dari:</label>
            <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>">
            <label>Sampai:</label>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>">
            <button type="submit">Filter</button>
        </form>
        <button onclick="window.print()" style="margin-left:auto; background:#555;">Cetak PDF</button>
    </div>

    <h3>Grafik Buku Terlaris</h3>
    

[Image of Bar Chart showing book sales distribution]

    <div style="position: relative; height:350px; width:100%">
        <canvas id="salesCanvas"></canvas>
    </div>
    <br><hr>

    <h3>Rincian Data (<?= date('d M Y', strtotime($tgl_awal)) ?> s.d <?= date('d M Y', strtotime($tgl_akhir)) ?>)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Judul Buku</th>
                <th width="15%">Terjual (Pcs)</th>
                <th width="20%">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(!empty($rekap_penjualan)):
                foreach($rekap_penjualan as $row): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                <td><b><?= $row['total_qty'] ?></b></td>
                <td>RP. <?= number_format($row['total_pendapatan'], 0, ',', '.') ?></td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
            <tr><td colspan="4" align="center">Belum ada data penjualan pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-item">Total Item Terjual: <b><?= $grand_total_qty ?> Pcs</b></div>
        <div class="total-item">Total Omzet: <b>RP. <?= number_format($grand_total_rp, 0, ',', '.') ?></b></div>
    </div>

</div>

<script>
    const ctx = document.getElementById('salesCanvas');

    new Chart(ctx, {
        type: 'bar', // Tipe Bar Chart (Batang)
        data: {
            labels: <?= json_encode($labels_buku) ?>, // SUMBU X: Nama Buku
            datasets: [{
                label: 'Jumlah Terjual (Pcs)',
                data: <?= json_encode($data_qty) ?>,  // SUMBU Y: Qty
                backgroundColor: 'rgba(56, 123, 214, 0.7)', // Warna Biru (#387bd6) transparan
                borderColor: 'rgba(56, 123, 214, 1)',     // Warna Biru Solid
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Unit'
                    },
                    ticks: {
                        stepSize: 1 // Agar angka di sumbu Y bulat (1, 2, 3) bukan desimal
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Judul Buku'
                    }
                }
            },
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Statistik Penjualan Buku Anda'
                }
            }
        }
    });
</script>
</body>
</html>