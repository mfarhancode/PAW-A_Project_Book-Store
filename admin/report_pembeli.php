<?php 
require "connection.php";
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '';

$query = "
    SELECT 
        DATE(pay.timestamp) as tanggal_harian, 
        SUM(sold.price_at_sale) as total_pendapatan
    FROM 
        bst_payment_detail as pay 
    JOIN 
        bst_sold_books as sold ON pay.payment_id = sold.id
    WHERE
        DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    GROUP BY 
        tanggal_harian
    ORDER BY
        tanggal_harian ASC
";
$execute = mysqli_query($conn, $query);
$rekap_harian = mysqli_fetch_all($execute, MYSQLI_ASSOC);

$tanggal = [];
$total_harga = [];
foreach($rekap_harian as $value){
    $tanggal[] = $value['tanggal_harian'];
    $total_harga[] = $value['total_pendapatan'];
}

$query_total = "
    SELECT 
        COUNT(DISTINCT pay.payment_id) as total_pelanggan, 
        SUM(sold.price_at_sale) as total_pendapatan_kumulatif
    FROM 
        bst_payment_detail as pay 
    JOIN 
        bst_sold_books as sold ON pay.payment_id = sold.id
    WHERE
        DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
";
$execute_total = mysqli_query($conn, $query_total);
$total_data = mysqli_fetch_assoc($execute_total);

$total_pelanggan = $total_data['total_pelanggan'];
$total_pendapatan_rp = number_format($total_data['total_pendapatan_kumulatif'], 0, ',', '.');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 900px; margin: 20px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .header-top {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .btn-back {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        .icon-arrow {
            color: #60a5fa;
            font-size: 32px;
            margin-right: 8px;
        }
        
    
        .header-top h2 {
            margin: 0; 
        }

        .total-box { display: flex; justify-content: space-around; padding: 15px; background-color: #e6f7ff; border: 1px solid #b3e0ff; margin-top: 20px; }
        .filter-form { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; }
        .no-print { margin-bottom: 10px; }
        @media print{
            .no-print, .filter-form {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-top">
        <a href="report.php" class="btn-back">
            <i class="fas fa-arrow-left icon-arrow"></i> BACK
        </a>
        <h2>Rekap Laporan Pembelian</h2>
    </div>

    <div class="filter-form no-print">
        <form method="GET" action="">
            <label for="tgl_awal">Dari Tanggal:</label>
            <input type="date" id="tgl_awal" name="tgl_awal" value="<?= $tgl_awal ?>">
            <label for="tgl_akhir">Sampai Tanggal:</label>
            <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?= $tgl_akhir ?>">
            <button type="submit">Filter Laporan</button>
        </form>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="no-print">Cetak (Print)</button>
        <button onclick="window.location='export_excel.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>'">Export ke Excel</button>
    </div>

    <h3>Grafik Pembelian</h3>
    <canvas id="my_canvas" style="height: 300px;"></canvas>
    <hr>

    <h3>Rekap Detail (<?= $tgl_awal ?> s.d <?= $tgl_akhir ?>)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach($rekap_harian as $value): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d M Y', strtotime($value['tanggal_harian'])) ?></td>
                <td>RP. <?= number_format($value['total_pendapatan'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <hr>

    <h3>Total Kumulatif</h3>
    <div class="total-box">
        <div>Jumlah Pelanggan: **<?= $total_pelanggan ?> Orang**</div>
        <div>Jumlah Pendapatan: **RP. <?= $total_pendapatan_rp ?>**</div>
    </div>
</div>

<script>

    const ctx = document.getElementById('my_canvas');
    new Chart(ctx, {
        type: 'bar',
        data: {
        labels: <?= json_encode($tanggal)  ?>,
        datasets: [{
            label: 'Total Pendapatan Harian (RP)',
            data: <?= json_encode($total_harga)  ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
        },
        options: {
        scales: {
            y: {
            beginAtZero: true
            }
        }
        }
    });
</script>
</body>
</html>