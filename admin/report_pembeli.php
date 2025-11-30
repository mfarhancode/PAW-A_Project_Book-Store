<?php 
require "../connection.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- QUERY UTAMA (SUDAH DIPERBAIKI) ---
$query = "
    SELECT 
        u.name as nama_pembeli, 
        SUM(sold.qty) as total_buku_dibeli,
        SUM(sold.price_at_sale) as total_uang_dikeluarkan
    FROM 
        bst_user u
    JOIN 
        bst_payment_detail pay ON u.id = pay.buyer_id  -- Bagian ini yang tadi error
    JOIN 
        bst_sold_books sold ON pay.payment_id = sold.payment_id
    WHERE
        u.level = 'buyer'
        AND DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    GROUP BY 
        u.id
    ORDER BY 
        total_buku_dibeli DESC
    LIMIT 10
";

$execute = mysqli_query($conn, $query);

// Cek error lagi untuk memastikan
if(!$execute) {
    die("Error Query Baru: " . mysqli_error($conn));
}

// Cek error query untuk debugging
if(!$execute) {
    die("Error Query: " . mysqli_error($conn));
}

$rekap_buyer = mysqli_fetch_all($execute, MYSQLI_ASSOC);

// Persiapan Data Chart
$nama_pembeli = [];
$total_qty = [];

foreach($rekap_buyer as $value){
    $nama_pembeli[] = $value['nama_pembeli'];
    $total_qty[] = $value['total_buku_dibeli'];
}

// Menghitung Total Keseluruhan untuk Kotak Bawah
$total_buku_all = 0;
$total_uang_all = 0;
foreach($rekap_buyer as $val) {
    $total_buku_all += $val['total_buku_dibeli'];
    $total_uang_all += $val['total_uang_dikeluarkan'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Top Pembeli</title>
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
        
        .header-top h2 { margin: 0; }

        .total-box { display: flex; justify-content: space-around; padding: 15px; background-color: #e6f7ff; border: 1px solid #b3e0ff; margin-top: 20px; }
        .filter-form { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; }
        .no-print { margin-bottom: 10px; }
        
        @media print{
            .no-print, .filter-form { display: none; }
        }
    </style>
</head>
<body>
<div class="container">
    
    <!-- HEADER -->
    <div class="header-top">
        <a href="admin_report.php" class="btn-back">
            <i class="fas fa-arrow-left icon-arrow"></i> BACK
        </a>
        <h2>Laporan Top Pembeli (Buyer)</h2>
    </div>

    <!-- FILTER TANGGAL -->
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
        <!-- Sesuaikan link export jika mau pakai -->
        <!-- <button onclick="window.location='export_excel.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>'">Export ke Excel</button> -->
    </div>

    <!-- GRAFIK -->
    <h3>Grafik Pembeli Teraktif (Jumlah Buku)</h3>
    <canvas id="buyerCanvas" style="height: 300px;"></canvas>
    <hr>

    <!-- TABEL -->
    <h3>Detail Data (<?= $tgl_awal ?> s.d <?= $tgl_akhir ?>)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pembeli</th>
                <th>Jumlah Buku Dibeli</th>
                <th>Total Belanja (RP)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(!empty($rekap_buyer)):
                foreach($rekap_buyer as $value): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($value['nama_pembeli']) ?></td>
                <td><?= $value['total_buku_dibeli'] ?> Pcs</td>
                <td>RP. <?= number_format($value['total_uang_dikeluarkan'], 0, ',', '.') ?></td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
            <tr><td colspan="4" align="center">Belum ada data pembelian pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <hr>

    <!-- TOTAL BOX -->
    <h3>Total Ringkasan (Top 10)</h3>
    <div class="total-box">
        <div>Total Buku Terjual: <b><?= $total_buku_all ?> Pcs</b></div>
        <div>Total Omzet Pembeli Ini: <b>RP. <?= number_format($total_uang_all, 0, ',', '.') ?></b></div>
    </div>
</div>

<script>
    const ctx = document.getElementById('buyerCanvas');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($nama_pembeli) ?>, // SUMBU X: Nama Pembeli
            datasets: [{
                label: 'Jumlah Buku Dibeli (Pcs)',
                data: <?= json_encode($total_qty) ?>, // SUMBU Y: Qty
                backgroundColor: 'rgba(153, 102, 255, 0.6)', // Warna Ungu (Beda dari seller)
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Buku (Unit)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Nama Buyer'
                    }
                }
            }
        }
    });
</script>
</body>
</html>