<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";


$name =  $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";

echo "<hr>";

echo "<h3>List of all the books.</h3>";


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
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
}

body {
    background: #f4f6fa;
    color: #333;
}

/* HEADER */
.header {
    background: #4a4aee;
    padding: 25px;
    text-align: center;
    color: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.header h1 {
    font-size: 32px;
    font-weight: 700;
}

.header h2 {
    font-size: 20px;
    margin-top: 6px;
    opacity: .9;
}

/* SIDEBAR */
.navbar {
    width: 220px;
    background: #ffffff;
    padding: 25px;
    border-right: 1px solid #ddd;
    height: calc(100vh - 100px);
    position: fixed;
    top: 100px;
    left: 0;
    overflow-y: auto;
}

.navbar a {
    display: block;
    margin-bottom: 18px;
    text-decoration: none;
    color: #333;
    padding: 10px 14px;
    border-radius: 8px;
    font-weight: 600;
    transition: .25s;
}

.navbar a:hover {
    background: #4a4aee;
    color: white;
}

/* CONTENT */
.content {
    margin-left: 250px;
    padding: 30px;
}

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

/* LOGOUT */
a[href*='logout'] {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 14px;
    background: #e63946;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

a[href*='logout']:hover {
    background: #c72d3a;
}

/* FOOTER */
footer {
    text-align: center;
    margin-top: 30px;
    color: #777;
}
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin</h1>
        <h2>ALL BOOK DATA</h2>
    </div>
    <div class="navbar">
        <a href="admin_dashboard.php">Dashboard</a><br>
        <a href="users_data.php">Manage Users</a><br>
        <a href="#">All Book</a><br>
        <a href="admin_report.php">Report</a><br>
    </div>
    <section id="content" class="content">
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


        <hr>
        <a href='../logout.php'>Logout</a>
        </div>

    </section>
    <footer>
        <p>&copy;</p>
    </footer>
</body>
</html>
