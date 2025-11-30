<?php 
require "../connection.php";
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
// Set Default Tanggal jika kosong (Hari ini s/d Hari ini)
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- QUERY UTAMA ---
// Logika: User (Seller) -> Punya Buku -> Buku Terjual -> Di Filter Tanggal Pembayaran
$query = "
    SELECT 
        u.name as nama_penjual, 
        SUM(sb.qty) as total_buku_terjual
    FROM 
        bst_user u
    JOIN 
        bst_books b ON u.id = b.id 
    JOIN 
        bst_sold_books sb ON b.ISBN = sb.ISBN
    JOIN
        bst_payment_detail pay ON sb.payment_id = pay.payment_id
    WHERE 
        u.level = 'seller' 
        AND DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    GROUP BY 
        u.id
    ORDER BY 
        total_buku_terjual DESC
";

// Eksekusi Query
// CATATAN: Jika tabel 'bst_books' kamu namanya beda, ubah query di atas.
$execute = mysqli_query($conn, $query);

// Cek Error Query (Untuk Debugging)
if (!$execute) {
    die("Query Error: " . mysqli_error($conn));
}

$data_seller = mysqli_fetch_all($execute, MYSQLI_ASSOC);

// Siapkan Data untuk Chart.js
$nama_penjual = [];
$jumlah_buku = [];

foreach($data_seller as $row){
    $nama_penjual[] = $row['nama_penjual'];
    $jumlah_buku[] = $row['total_buku_terjual'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Performa Penjual</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- MENGGUNAKAN CSS YANG SAMA DENGAN FILE KAMU SEBELUMNYA -->
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
        <h2>Grafik Performa Penjual</h2>
    </div>

    <!-- FILTER TANGGAL -->
    <div class="filter-form no-print">
        <form method="GET" action="">
            <label for="tgl_awal">Dari:</label>
            <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>">
            <label for="tgl_akhir">Sampai:</label>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>">
            <button type="submit">Filter Data</button>
        </form>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Cetak (Print)</button>
    </div>

    <!-- CANVAS GRAFIK -->
    <h3>Top Penjual (Berdasarkan Qty Buku Terjual)</h3>
    <canvas id="sellerCanvas" style="height: 400px;"></canvas>
    <hr>

    <!-- TABEL DATA -->
    <h3>Detail Data</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Penjual</th>
                <th>Jumlah Buku Terjual</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if (count($data_seller) > 0):
                foreach($data_seller as $val): 
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($val['nama_penjual']) ?></td>
                <td><?= $val['total_buku_terjual'] ?> Pcs</td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
            <tr>
                <td colspan="3" align="center">Tidak ada data penjualan pada periode ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- SCRIPT CHART.JS -->
<script>
    const ctx = document.getElementById('sellerCanvas').getContext('2d');
    
    // Warna-warni background agar tiap bar beda (Opsional)
    const backgroundColors = [
        'rgba(255, 99, 132, 0.6)',
        'rgba(54, 162, 235, 0.6)',
        'rgba(255, 206, 86, 0.6)',
        'rgba(75, 192, 192, 0.6)',
        'rgba(153, 102, 255, 0.6)',
        'rgba(255, 159, 64, 0.6)'
    ];

    new Chart(ctx, {
        type: 'bar', // Grafik Batang
        data: {
            labels: <?= json_encode($nama_penjual) ?>, // Sumbu X: Nama Penjual
            datasets: [{
                label: 'Jumlah Buku Terjual',
                data: <?= json_encode($jumlah_buku) ?>, // Sumbu Y: Angka Qty
                backgroundColor: backgroundColors,
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Unit (Pcs)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Nama Penjual'
                    }
                }
            },
            plugins: {
                legend: { display: false } // Sembunyikan legenda karena warnanya beda-beda
            }
        }
    });
</script>
</body>
</html>