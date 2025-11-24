<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$name = $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";

echo "<a href='mybooks.php'>My Books</a><br><br>";
echo "<a href='cart.php'>Cart</a>";
echo "<hr>";

echo "<br><b>Note: You can select on cart page whether you want digital copies of books or delivery. 
<br>(For digital copy, the price is 10% price of actual book's price.)</b> <br><br>";

$buyer_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isbn'])) {
    $isbn = $_POST['isbn'];
    $qty = $_POST['qty'];

    $check = mysqli_query(
        $conn,
        "SELECT qty FROM bst_cart 
         WHERE buyer_id='$buyer_id' AND isbn='$isbn'"
    );

    if (mysqli_num_rows($check) > 0) {
        mysqli_query(
            $conn,
            "UPDATE bst_cart 
             SET qty = qty + $qty 
             WHERE buyer_id='$buyer_id' AND isbn='$isbn'"
        );
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO bst_cart (buyer_id, isbn, qty)
             VALUES ('$buyer_id', '$isbn', $qty)"
        );
    }

    header("Location: cart.php");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) AS total FROM bst_books WHERE judul LIKE '%$search%'";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$total = $countRow['total'];
$pages = ceil($total / $limit);

$sql = "SELECT * FROM bst_books WHERE judul LIKE '%$search%' LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);
?>

<form method="GET">
  <input type="text" name="search" placeholder="Search by title..." value="<?php echo $search; ?>">
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
    <th>Stok</th>
    <th>Description</th>
    <th>Harga</th>
    <th>Add to cart</th>
  </tr>

  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
  <tr>
    <td><img src="../uploads/<?php echo $row['image']; ?>" width="80"></td>
    <td><?php echo $row['judul']; ?></td>
    <td><?php echo $row['ISBN']; ?></td>
    <td><?php echo $row['penulis']; ?></td>
    <td><?php echo $row['tahun']; ?></td>
    <td><?php echo $row['category']; ?></td>
    <td><?php echo $row['stok']; ?></td>
    <td><?php echo $row['description']; ?></td>
    <td><?php echo $row['harga']; ?></td>
    <td>
        <form method="POST">
          <input type="hidden" name="isbn" value="<?php echo $row['ISBN']; ?>">
          <input type="number" name="qty" value="1" min="1">
          <button type="submit">Add to cart</button>
        </form>
    </td>
  </tr>
  <?php } ?>
</table>

<?php for ($i = 1; $i <= $pages; $i++) { ?>
  <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
<?php } ?>

<hr>
<a href="../logout.php">Logout</a>
