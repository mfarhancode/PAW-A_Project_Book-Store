<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}
require "../connection.php";

$buyer_id = $_SESSION['id'];
$name = $_SESSION['name'];

include "../partials/header.php";
echo "<h2>Welcome $name</h2>";

// CLEAR CART
if (isset($_GET['clear_cart'])) {
    mysqli_query($conn, "DELETE FROM bst_cart WHERE buyer_id='$buyer_id'");
    header("Location: cart.php");
    exit;
}

// DELETE ONE ITEM
if (isset($_GET['delete'])) {
    $cid = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM bst_cart WHERE id='$cid' AND buyer_id='$buyer_id'");
    header("Location: cart.php");
    exit;
}

// UPDATE via edit_qty.php (handled there)
// so no update handling here.

//
// GET CART ITEMS
$q = "
SELECT c.id, c.qty,
       b.judul, b.harga, b.ISBN, b.image, b.stok,
       u.name AS seller_name
FROM bst_cart c
JOIN bst_books b ON c.ISBN = b.ISBN
JOIN bst_user u ON b.seller_id = u.id
WHERE c.buyer_id = '$buyer_id'
";

$res = mysqli_query($conn, $q);

echo "<h3>Your Cart</h3>";

if (mysqli_num_rows($res) == 0) {
    echo "Your cart is empty.<br><br>";
    echo "<a class='link_button' href='products.php'>Continue Shopping</a>";
    include "../partials/footer.php";
    exit;
}
?>

<link rel="stylesheet" href="../assets/seller_books.css">

<!-- BIG CHECKOUT FORM wraps table so checkboxes + radios are sent reliably -->
<form id="checkoutForm" method="POST" action="checkout.php">

<table border="1" cellpadding="5">
<tr>
    <th>Pick</th>
    <th>Image</th>
    <th>Title</th>
    <th>ISBN</th>
    <th>Seller</th>
    <th>Qty</th>
    <th>Stock</th>
    <th>Price</th>
    <th>Digital Price</th>
    <th>Total</th>
    <th>Action</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($res)) {
    $cart_id = $row['id'];
    $qty = intval($row['qty']);
    $stock = intval($row['stok']);
    $price = intval($row['harga']);
    $digital = intval($price * 0.10);
    $subtotal = $qty * $price;
?>
<tr>

    <td>
        <!-- checkbox inside the checkout form -->
        <input type="checkbox" name="picked[]" value="<?= $cart_id ?>">
    </td>

    <td><img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="60"></td>

    <td><?= htmlspecialchars($row['judul']) ?></td>
    <td><?= htmlspecialchars($row['ISBN']) ?></td>
    <td><?= htmlspecialchars($row['seller_name']) ?></td>

    <td>
        <!-- show qty (user can change on separate page) -->
        <?= $qty ?>
        <br>
        <a class="link_button" href="edit_qty.php?id=<?= $cart_id ?>">Edit Qty</a>
    </td>

    <td><?= $stock ?></td>
    <td><?= $price ?></td>
    <td><?= $digital ?></td>
    <td><?= $subtotal ?></td>

    <td>
        <a class="link_button" href="cart.php?delete=<?= $cart_id ?>"
           onclick="return confirm('Remove this item?')">Remove</a>
    </td>

</tr>
<?php } ?>
</table>

<br>

<h3>Choose Purchase Type</h3>
<label><input type="radio" name="delivery_type" value="digital"> Digital copy (10% price)</label><br>
<label><input type="radio" name="delivery_type" value="delivery" checked> Hard copy</label>
<br><br>

<button type="submit" class="link_button">Proceed to Checkout</button>
</form>

<br>
<a href="products.php" class="link_button">Continue Shopping</a>
<a href="cart.php?clear_cart=1" class="link_button"
   onclick="return confirm('Clear entire cart?')">Clear Cart</a>

<?php include "../partials/footer.php"; ?>
