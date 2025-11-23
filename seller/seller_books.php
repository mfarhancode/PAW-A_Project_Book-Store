<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";


$name =  $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";

echo "<a href='seller_dashboard.php'>Dashboard</a>";


echo "<hr>";

echo "<h3>List of all your books.</h3>";


$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total records
$seller_id = $_SESSION['id'];
$countSql = "SELECT COUNT(*) AS total FROM bst_books WHERE judul LIKE '%$search%' AND seller_id = $seller_id";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$total = $countRow['total'];
$pages = ceil($total / $limit);

// Fetch records
$sql = "SELECT * FROM bst_books WHERE judul LIKE '%$search%' AND seller_id = $seller_id LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);
?>

<form method='GET'>
  <input type='text' name='search' placeholder='Search by title...' value='<?php echo $search; ?>'>
  <button type='submit'>Search</button>
</form>


<table border='1' cellpadding='5'>
  <tr>
    <th>ID</th>
    <th>ISBN</th>
    <th>Judul</th>
    <th>Penulis</th>
    <th>Year</th>
    <th>Image</th>
    <th>Category</th>
    <th>Stok</th>
    <th>Description</th>
    <th>Harga</th>
    <th>PDF File</th>
    <th>Action</th>
  </tr>

  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
  <tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['ISBN']; ?></td>
    <td><?php echo $row['judul']; ?></td>
    <td><?php echo $row['penulis']; ?></td>
    <td><?php echo $row['tahun']; ?></td>
    <td><img src='../uploads/<?php echo $row['image']; ?>' width='80'></td>
    <td><?php echo $row['category']; ?></td>
    <td><?php echo $row['stok']; ?></td>
    <td><?php echo $row['description']; ?></td>
    <td><?php echo $row['harga']; ?></td>
    <td> <a href="<?php echo $row['pdf_file']; ?>" target="_blank">File link</a> </td>
    <td>
      <a href='edit_book.php?id=<?php echo $row['id']; ?>'>Edit</a> |
      <a href='delete_book.php?id=<?php echo $row['id']; ?>' onclick="return confirm('Do you want to delete this data?');" >Delete</a>
    </td>
  </tr>
  <?php } ?>
</table>

<?php for ($i = 1; $i <= $pages; $i++) { ?>
  <a href='?page=<?php echo $i; ?>&search=<?php echo $search; ?>'><?php echo $i; ?></a>
<?php } ?>


<hr>
<a href='../logout.php'>Logout</a>
