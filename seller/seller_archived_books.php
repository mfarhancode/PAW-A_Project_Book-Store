<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$seller_id = $_SESSION['id'];
$search = $_GET['search'] ?? "";

// Pagination
$limit = 5;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;

// Count archived
$countSql = "SELECT COUNT(*) AS total FROM bst_books 
             WHERE seller_id = $seller_id 
             AND archived = 1
             AND judul LIKE '%$search%'";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$total = $countRow['total'];
$pages = ceil($total / $limit);

// Fetch archived books
$sql = "SELECT * FROM bst_books 
        WHERE seller_id = $seller_id 
        AND archived = 1
        AND judul LIKE '%$search%'
        LIMIT $start, $limit";

$result = mysqli_query($conn, $sql);

include "../partials/header.php";
?>

<link rel="stylesheet" href="../assets/seller_books.css">

<div class="container">

<h2>Archived Books</h2>

<a class="link_button" href="seller_dashboard.php">Dashboard</a>
<a class="link_button" href="seller_books.php">Active Books</a>

<hr>

<form method="GET">
    <input type="text" name="search" placeholder="Search..." value="<?= $search ?>">
    <button>Search</button>
</form>

<table border="1" cellpadding="5">
<tr>
    <th>No</th>
    <th>ID</th>
    <th>ISBN</th>
    <th>Judul</th>
    <th>Penulis</th>
    <th>Year</th>
    <th>Image</th>
    <th>Category</th>
    <th>Stock</th>
    <th>Description</th>
    <th>Price</th>
    <th>PDF</th>
    <th>Actions</th>
</tr>
<?php $n = $start + 1; ?>
<?php while ($row = mysqli_fetch_assoc($result)) {

    $isbn = $row['ISBN'];
    $check = mysqli_query($conn, "SELECT id FROM bst_sold_books WHERE ISBN = '$isbn'");
    $isSold = mysqli_num_rows($check) > 0;
?>
<tr>
    <td><?php echo $n++;?></td>
    <td><?= $row['id'] ?></td>
    <td><?= $row['ISBN'] ?></td>
    <td><?= $row['judul'] ?></td>
    <td><?= $row['penulis'] ?></td>
    <td><?= $row['tahun'] ?></td>
    <td><img src="../uploads/<?= $row['image'] ?>" width="60"></td>
    <td><?= $row['category'] ?></td>
    <td><?= $row['stok'] ?></td>
    <td><?= $row['description'] ?></td>
    <td><?= $row['harga'] ?></td>
    <td><a class="link_button" href="<?= $row['pdf_file'] ?>" target="_blank">File</a></td>

    <td>
        <div>

            <a class="link_button" href="edit_book.php?id=<?= $row['id'] ?>">Edit</a><br>

            <?php if ($isSold): ?>
                <span>Sold! can't delete</span><br>
            <?php else: ?>
                <a class="link_button" href="delete_book.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Delete this book?')">Delete</a><br>
            <?php endif; ?>

            <a class="link_button" href="toggle_archive.php?id=<?= $row['id'] ?>&action=unarchive">Unarchive</a>

        </div>
    </td>
</tr>
<?php } ?>
</table>

<?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="link_button" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
<?php endfor; ?>

</div>

<?php include "../partials/footer.php"; ?>
