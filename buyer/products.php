<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$buyer_id = $_SESSION['id'];

include "../partials/header.php";
?>

<link rel="stylesheet" href="../assets/products.css">  

<div class="container">

<h2>All Books</h2>

<a class="link_button" href="mybooks.php">My Books</a>
<a class="link_button" href="cart.php">Cart</a>

<hr>

<p><b>Note:</b> On checkout you can choose Digital or Delivery.<br>
(Digital price = 10% of book price)</p>
<br>

<?php
// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isbn'])) {

    $isbn = $_POST['isbn'];
    $qty = intval($_POST['qty']);

    // Get available stock
    $checkStock = mysqli_query($conn, "SELECT stok FROM bst_books WHERE ISBN='$isbn'");
    $stockRow = mysqli_fetch_assoc($checkStock);
    $stok = $stockRow['stok'];

    if ($qty > $stok) {
        echo "<p style='color:red'>You cannot add more than available stock ($stok).</p>";
    } else {
        // Check if already in cart
        $check = mysqli_query(
            $conn,
            "SELECT qty FROM bst_cart 
             WHERE buyer_id='$buyer_id' AND ISBN='$isbn'"
        );

        if (mysqli_num_rows($check) > 0) {
            mysqli_query(
                $conn,
                "UPDATE bst_cart 
                 SET qty = qty + $qty 
                 WHERE buyer_id='$buyer_id' AND ISBN='$isbn'"
            );
        } else {
            mysqli_query(
                $conn,
                "INSERT INTO bst_cart (buyer_id, ISBN, qty)
                 VALUES ('$buyer_id', '$isbn', $qty)"
            );
        }

        header("Location: cart.php");
        exit;
    }
}

// SEARCH + PAGINATION
$search = $_GET['search'] ?? '';

$limit = 5;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;

// count available books (not archived)
$countSql = "SELECT COUNT(*) AS total 
             FROM bst_books 
             WHERE archived = 0 AND judul LIKE '%$search%'";
$countResult = mysqli_query($conn, $countSql);
$total = mysqli_fetch_assoc($countResult)['total'];
$pages = ceil($total / $limit);

// fetch
$sql = "SELECT * FROM bst_books 
        WHERE archived = 0 
        AND judul LIKE '%$search%' 
        LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);
?>

<form method="GET">
    <input type="text" name="search" placeholder="Search title..." value="<?= $search ?>">
    <button type="submit">Search</button>
</form>

<table border="1" cellpadding="5">
<tr>
    <th>Image</th>
    <th>Judul</th>
    <th>ISBN</th>
    <th>Penulis</th>
    <th>Year</th>
    <th>Category</th>
    <th>Stock</th>
    <th>Description</th>
    <th>Harga</th>
    <th>Add to cart</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><img src="../uploads/<?= $row['image'] ?>" width="80"></td>
    <td><?= $row['judul'] ?></td>
    <td><?= $row['ISBN'] ?></td>
    <td><?= $row['penulis'] ?></td>
    <td><?= $row['tahun'] ?></td>
    <td><?= $row['category'] ?></td>
    <td><?= $row['stok'] ?></td>
    <td><?= $row['description'] ?></td>
    <td><?= $row['harga'] ?></td>

    <td>
        <?php if ($row['stok'] == 0): ?>
            <b style="color:red;">Out of stock</b>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="isbn" value="<?= $row['ISBN'] ?>">
                <input type="number" name="qty" value="1" min="1" max="<?= $row['stok'] ?>">
                <button type="submit">Add</button>
            </form>
        <?php endif; ?>
    </td>

</tr>
<?php } ?>
</table>

<?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="link_button" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
<?php endfor; ?>

</div>

<?php include "../partials/footer.php"; ?>
