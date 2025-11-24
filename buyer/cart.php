<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}
require "../connection.php";

$buyer_id = $_SESSION['id'];
$name = $_SESSION['name'];
echo "<h2>Welcome $name</h2>";

$q = "SELECT c.id, c.qty, b.judul, b.harga, b.ISBN FROM bst_cart c JOIN bst_books b ON c.ISBN = b.ISBN WHERE c.buyer_id='$buyer_id'";
$res = mysqli_query($conn, $q);

echo "<h3>Your Cart</h3>";

if (mysqli_num_rows($res) == 0) {
    echo "Empty cart";
    exit;
}
?>
<form method="POST" action="checkout.php">
    <table border="1" cellpadding="5">
        <tr>
            <th>Pick</th>
            <th>Title</th>
            <th>ISBN</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_assoc($res)) {
            $total = $row['qty'] * $row['harga'];
        ?>
        <tr>
            <td><input type="checkbox" name="picked[]" value="<?php echo $row['id']; ?>"></td>
            <td><?php echo $row['judul']; ?></td>
            <td><?php echo $row['ISBN']; ?></td>
            <td><?php echo $row['qty']; ?></td>
            <td><?php echo $row['harga']; ?></td>
            <td><?php echo $total; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
    <h3>Choose Purchase Type</h3>
    <label>
        <input type="radio" name="delivery_type" value="digital"> Digital copy (10 percent price)
    </label>
    <br>
    <label>
        <input type="radio" name="delivery_type" value="delivery" checked> Hard copy
    </label>
    <br><br>

    <button type="submit">Proceed to Checkout</button>

</form>
