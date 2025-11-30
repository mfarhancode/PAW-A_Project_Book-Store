<?php 
require "../connection.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

$query = "
    SELECT 
        b.judul as judul_buku,
        b.ISBN,
        SUM(sold.qty) as total_qty,
        SUM(sold.price_at_sale) as total_pendapatan_buku
    FROM 
        bst_sold_books sold
    JOIN 
        bst_payment_detail pay ON sold.payment_id = pay.payment_id
    JOIN 
        bst_books b ON sold.ISBN = b.ISBN  -- Join pakai ISBN
    WHERE
        DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    GROUP BY 
        b.ISBN  -- Kelompokkan berdasarkan buku unik
    ORDER BY 
        total_qty DESC -- Urutkan dari yang paling laku
    LIMIT 15 -- Batasi 15 buku teratas agar grafik tidak kepenuhan
";

$execute = mysqli_query($conn, $query);

if(!$execute) {
    die("Error Query: " . mysqli_error($conn));
}

$rekap_buku = mysqli_fetch_all($execute, MYSQLI_ASSOC);

$list_judul = [];
$list_qty = [];

foreach($rekap_buku as $value){

    $judul_pendek = strlen($value['judul_buku']) > 20 ? substr($value['judul_buku'], 0, 20) . '...' : $value['judul_buku'];
    $list_judul[] = $judul_pendek;
    $list_qty[] = $value['total_qty'];
}

$grand_total_qty = 0;
$grand_total_rp = 0;
foreach($rekap_buku as $val) {
    $grand_total_qty += $val['total_qty'];
    $grand_total_rp += $val['total_pendapatan_buku'];
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
    
    <div class="header-top">
        <a href="admin_report.php" class="btn-back">
            <i class="fas fa-arrow-left icon-arrow"></i> BACK
        </a>
        <h2>Laporan Top Buku Terlaris</h2>
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
    </div>

    <h3>Grafik Buku Terlaris (Top 15)</h3>
    <canvas id="bookCanvas" style="height: 350px;"></canvas>
    <hr>

    <h3>Rincian Data (<?= $tgl_awal ?> s.d <?= $tgl_akhir ?>)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Buku</th>
                <th>ISBN</th>
                <th>Terjual (Pcs)</th>
                <th>Total Pendapatan (RP)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(!empty($rekap_buku)):
                foreach($rekap_buku as $value): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($value['judul_buku']) ?></td>
                <td><?= htmlspecialchars($value['ISBN']) ?></td>
                <td><?= $value['total_qty'] ?></td>
                <td>RP. <?= number_format($value['total_pendapatan_buku'], 0, ',', '.') ?></td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
            <tr><td colspan="5" align="center">Belum ada buku terjual pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <hr>

    <h3>Ringkasan (Dari Data Di Atas)</h3>
    <div class="total-box">
        <div>Total Item Terjual: <b><?= $grand_total_qty ?> Pcs</b></div>
        <div>Total Omzet: <b>RP. <?= number_format($grand_total_rp, 0, ',', '.') ?></b></div>
    </div>
</div>

<script>
    const ctx = document.getElementById('bookCanvas');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($list_judul) ?>,
            datasets: [{
                label: 'Jumlah Terjual (Pcs)',
                data: <?= json_encode($list_qty) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
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
                        text: 'Jumlah (Unit)'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Statistik Buku Paling Laku'
                }
            }
        }
    });
</script>
</body>
</html>