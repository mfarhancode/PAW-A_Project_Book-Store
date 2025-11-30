<?php 
require "../connection.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Default tanggal: Awal bulan ini sampai hari ini
$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// --- QUERY UTAMA PER KATEGORI (SUDAH DIPERBAIKI) ---
// Perbaikan: Menggabungkan tabel berdasarkan kolom 'ISBN'
$query = "
    SELECT 
        b.category as nama_kategori, 
        SUM(sold.qty) as total_buku_terjual,
        SUM(sold.price_at_sale) as total_pendapatan_kategori
    FROM 
        bst_sold_books sold
    JOIN 
        bst_payment_detail pay ON sold.payment_id = pay.payment_id
    JOIN 
        bst_books b ON sold.ISBN = b.ISBN  -- PERBAIKAN DI SINI (Pakai ISBN)
    WHERE
        DATE(pay.timestamp) BETWEEN '$tgl_awal' AND '$tgl_akhir'
    GROUP BY 
        b.category
    ORDER BY 
        total_buku_terjual DESC
";

$execute = mysqli_query($conn, $query);

// Cek error query
if(!$execute) {
    die("Error Query: " . mysqli_error($conn));
}

$rekap_kategori = mysqli_fetch_all($execute, MYSQLI_ASSOC);

// Persiapan Data Chart
$list_kategori = [];
$list_qty = [];

foreach($rekap_kategori as $value){
    // Jika kategori kosong, beri label 'Tanpa Kategori'
    $nama = !empty($value['nama_kategori']) ? $value['nama_kategori'] : 'Tanpa Kategori';
    $list_kategori[] = $nama;
    $list_qty[] = $value['total_buku_terjual'];
}

// Menghitung Total Bawah
$grand_total_qty = 0;
$grand_total_rp = 0;
foreach($rekap_kategori as $val) {
    $grand_total_qty += $val['total_buku_terjual'];
    $grand_total_rp += $val['total_pendapatan_kategori'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kategori Terlaris</title>
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

        .total-box { display: flex; justify-content: space-around; padding: 15px; background-color: #fff4e6; border: 1px solid #ffd8a8; margin-top: 20px; }
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
        <h2>Laporan Kategori/Genre Terlaris</h2>
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

    <h3>Grafik Penjualan Per Kategori</h3>
    <canvas id="categoryCanvas" style="height: 300px;"></canvas>
    <hr>

    <h3>Rincian Data (<?= $tgl_awal ?> s.d <?= $tgl_akhir ?>)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Jumlah Terjual (Pcs)</th>
                <th>Total Pendapatan (RP)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(!empty($rekap_kategori)):
                foreach($rekap_kategori as $value): 
                    $nama_kat = !empty($value['nama_kategori']) ? $value['nama_kategori'] : 'Tanpa Kategori';
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($nama_kat) ?></td>
                <td><?= $value['total_buku_terjual'] ?></td>
                <td>RP. <?= number_format($value['total_pendapatan_kategori'], 0, ',', '.') ?></td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
            <tr><td colspan="4" align="center">Tidak ada data penjualan pada periode ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <hr>

    <h3>Ringkasan Penjualan</h3>
    <div class="total-box">
        <div>Total Buku Terjual: <b><?= $grand_total_qty ?> Pcs</b></div>
        <div>Total Omzet: <b>RP. <?= number_format($grand_total_rp, 0, ',', '.') ?></b></div>
    </div>
</div>

<script>
    const ctx = document.getElementById('categoryCanvas');
    
    new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: <?= json_encode($list_kategori) ?>,
            datasets: [{
                label: 'Jumlah Terjual',
                data: <?= json_encode($list_qty) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Komposisi Penjualan Berdasarkan Kategori'
                }
            }
        }
    });
</script>
</body>
</html>