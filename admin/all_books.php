<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";


$name =  $_SESSION['name'];
// echo "<h2>Welcome $name!</h2>";

echo "<hr>";

// echo "<h3>List of all the books.</h3>";


$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total records
$seller_id = $_SESSION['id'];
$countSql = "SELECT COUNT(*) AS total FROM bst_books WHERE judul LIKE '%$search%'";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$total = $countRow['total'];
$pages = ceil($total / $limit);

// Fetch records
$sql = "SELECT * FROM bst_books WHERE judul LIKE '%$search%' LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);

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
            border-radius: 12px;
            overflow: hidden;
        }

        
        .sidebar {
            width: 250px;
            background-color: #f8f9fe;
            padding: 30px;
            border-right: 1px solid #e0e0e0;
        }
        .sidebar h2 { font-size: 24px; color: #333; margin-bottom: 20px; }
        .menu { list-style: none; }
        .menu li { margin-bottom: 15px; }
        .menu a { text-decoration: none; color: #333; display: block; transition: 0.3s; }
        .menu a:hover { color: #5351e0; font-weight: bold; }

        
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }
        .main-content h2 { margin-bottom: 20px; font-weight: bold; }
        /* CARD AREA */
        .sold-book {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .sold-book h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #4a4aee;
        }

        /* SEARCH */
        .sold-book input[type='text'] {
            padding: 10px;
            width: 260px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .sold-book button {
            padding: 10px 14px;
            margin-left: 6px;
            background: #4a4aee;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
        }

        .sold-book button:hover {
            background: #3b3bd1;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        table th {
            background: #4a4aee;
            color: white;
            padding: 10px;
            text-align: left;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        table tr:hover {
            background: #f1f1ff;
        }

        /* IMAGE */
        table img {
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        /* PAGINATION */
        .pagination {
            margin-top: 15px;
        }

        .pagination a {
            padding: 8px 12px;
            background: #eee;
            margin-right: 5px;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
        }

        .pagination a:hover {
            background: #4a4aee;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="Sidebar">
            <h2>Admin</h2>
            <ul class="menu">
                <a href="admin_dashboard.php">Dashboard</a><br>
                <a href="users_data.php">Manage Users</a><br>
                <a href="#">All Book</a><br>
                <a href="admin_report.php">Report</a><br>
            </ul>
        </div>
        <div class="main-content">
            <h2><?php include "../partials/header.php";?></h2>
           <div class="sold-book">
            <h3>Daftar Buku</h3>
            <form method='GET'>
            <input type='text' name='search' placeholder='Search by title...' value='<?php echo $search; ?>'>
            <button type='submit'>Search</button>
            </form>


            <table border='1' cellpadding='5'>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>ISBN</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Year</th>
                <th>Image</th>
                <th>Stok</th>
                <th>Harga</th>
            </tr>

            <?php $n = $start + 1; ?>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $n++;?></td>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['ISBN']; ?></td>
                <td><?php echo $row['judul']; ?></td>
                <td><?php echo $row['penulis']; ?></td>
                <td><?php echo $row['tahun']; ?></td>
                <td><img src='../uploads/<?php echo $row['image']; ?>' width='80'></td>
                <td><?php echo $row['stok']; ?></td>
                <td><?php echo $row['harga']; ?></td>
            </tr>
            <?php } ?>
            </table>

            <?php for ($i = 1; $i <= $pages; $i++) { ?>
            <a href='?page=<?php echo $i; ?>&search=<?php echo $search; ?>'><?php echo $i; ?></a>
            <?php } ?>


            </div>
        </div>
    </div>
</body>
</html>
